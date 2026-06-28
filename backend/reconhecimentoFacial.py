import cv2
from deepface import DeepFace
import time
import os
import numpy as np

# ==========================================
# 1. CONFIGURAÇÕES GERAIS E ROTAS
# ==========================================
cap = cv2.VideoCapture(0)

# Pastas da nova arquitetura
pasta_raw = "./fotosOriginais"           # Onde você coloca as fotos originais
pasta_filtrada = "./embeddings" # Onde o sistema salva as tratadas

ultimo_reconhecimento = time.time()
intervalo_verificacao = 2
nome_detectado = "Iniciando sistema..."

# ==========================================
# 2. SERVIÇOS DE PROCESSAMENTO DE IMAGEM
# ==========================================
def aplicar_filtro_homomorfico(frame, gamma_l=0.3, gamma_h=1.5, c=1, d0=30):
    """Aplica o filtro homomórfico para corrigir iluminação e extrair texturas."""
    hsv = cv2.cvtColor(frame, cv2.COLOR_BGR2HSV)
    h, s, v = cv2.split(hsv)
    
    v_log = np.log1p(np.array(v, dtype=np.float32))
    v_fft = np.fft.fft2(v_log)
    v_fft_shift = np.fft.fftshift(v_fft)
    
    linhas, colunas = v.shape
    centro_linha, centro_coluna = linhas // 2, colunas // 2
    u, v_grid = np.meshgrid(np.arange(linhas), np.arange(colunas), indexing='ij')
    
    d_quadrado = (u - centro_linha)**2 + (v_grid - centro_coluna)**2
    filtro = (gamma_h - gamma_l) * (1 - np.exp(-c * (d_quadrado / (d0**2)))) + gamma_l
    
    v_filtrado_fft = v_fft_shift * filtro
    v_ifft_shift = np.fft.ifftshift(v_filtrado_fft)
    v_ifft = np.fft.ifft2(v_ifft_shift)
    
    v_exp = np.expm1(np.real(v_ifft))
    v_normalizado = cv2.normalize(v_exp, None, 0, 255, cv2.NORM_MINMAX, dtype=cv2.CV_8U)
    
    hsv_tratado = cv2.merge([h, s, v_normalizado])
    frame_tratado = cv2.cvtColor(hsv_tratado, cv2.COLOR_HSV2BGR)
    
    return frame_tratado

# ==========================================
# 3. MIDDLEWARE DE SINCRONIZAÇÃO E CACHE
# ==========================================
def sincronizar_banco_dados():
    """Varre a pasta raw, aplica o filtro e salva na pasta filtrada."""
    print("\n[Middleware] Iniciando sincronização do banco de dados...")
    
    # Cria a pasta filtrada caso não exista
    if not os.path.exists(pasta_filtrada):
        os.makedirs(pasta_filtrada)
        
    novas_fotos = 0
    
    # Varre a pasta de fotos originais
    for arquivo in os.listdir(pasta_raw):
        # Ignora arquivos que não sejam imagens
        if not arquivo.lower().endswith(('.png', '.jpg', '.jpeg')):
            continue
            
        caminho_raw = os.path.join(pasta_raw, arquivo)
        caminho_filtrado = os.path.join(pasta_filtrada, arquivo)
        
        # Só processa se a foto ainda não estiver na pasta filtrada
        if not os.path.exists(caminho_filtrado):
            imagem_original = cv2.imread(caminho_raw)
            if imagem_original is not None:
                imagem_tratada = aplicar_filtro_homomorfico(imagem_original)
                cv2.imwrite(caminho_filtrado, imagem_tratada)
                novas_fotos += 1
                print(f" -> Filtro aplicado e salvo: {arquivo}")

    # Limpeza de Cache inteligente
    caminho_cache = os.path.join(pasta_filtrada, "representations_vgg_face.pkl")
    if novas_fotos > 0 and os.path.exists(caminho_cache):
        os.remove(caminho_cache)
        print("[Middleware] Cache antigo removido. Os vetores serão recalculados.")
    elif novas_fotos == 0:
        print("[Middleware] Nenhuma foto nova detectada. Banco já está atualizado.")

# ==========================================
# 4. MOTOR DE IDENTIDADE (IA)
# ==========================================
def reconhecer_rosto(frame_atual):
    """Garante a simetria do pipeline aplicando o filtro na câmera antes da busca."""
    try:
        # A simetria: frame da câmera passa pelo mesmo filtro que o banco de dados
        frame_processado = aplicar_filtro_homomorfico(frame_atual)
        
        # Apontamos para ler a pasta filtrada, não a raw
        resultados = DeepFace.find(img_path=frame_processado, 
                                   db_path=pasta_filtrada, 
                                   enforce_detection=False, 
                                   silent=True)
        
        if len(resultados) > 0 and not resultados[0].empty:
            caminho_foto = resultados[0]['identity'][0]
            nome_arquivo = caminho_foto.replace('\\', '/').split('/')[-1]
            nome = nome_arquivo.split('.')[0] 
            return nome
            
        return "Desconhecido"
    except Exception as e:
        return "Buscando rosto..."

# ==========================================
# 5. MAESTRO (LOOP PRINCIPAL DA CÂMERA)
# ==========================================

# Garantia de pastas na primeira execução
if not os.path.exists(pasta_raw):
    os.makedirs(pasta_raw)
    print(f"\n[Aviso] Pasta '{pasta_raw}' criada. Adicione suas fotos originais nela.")

# Roda o middleware assim que a aplicação abre para garantir que os dados existem
sincronizar_banco_dados()

while True:
    ret, frame = cap.read()
    if not ret:
        break

    # Efeito espelho
    frame = cv2.flip(frame, 1)
    tempo_atual = time.time()

    # Controle de fluxo assíncrono (o gargalo)
    if tempo_atual - ultimo_reconhecimento > intervalo_verificacao:
        nome_detectado = reconhecer_rosto(frame)
        ultimo_reconhecimento = tempo_atual

    # Desenho na tela
    cv2.putText(frame, f"Identidade: {nome_detectado}", (10, 50), 
                cv2.FONT_HERSHEY_SIMPLEX, 1, (0, 255, 0), 2)
    
    cv2.imshow("Reconhecimento Facial V2", frame)

    tecla = cv2.waitKey(1) & 0xFF

    # Gatilhos de Teclado
    if tecla == ord("q"):
        print("\nEncerrando aplicação...")
        break
        
    elif tecla == ord("r"):
        print("\n[Comando] Tecla 'r' pressionada: Rodando middleware de sincronização...")
        nome_detectado = "Sincronizando..."
        
        # Força a atualização da interface antes de travar o processo
        cv2.imshow("Reconhecimento Facial V2", frame)
        cv2.waitKey(1) 
        
        sincronizar_banco_dados()
        nome_detectado = "Recalculando banco..."

cap.release()
cv2.destroyAllWindows()