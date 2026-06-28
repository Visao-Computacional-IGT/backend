# Roteiro de Testes: API de Reconhecimento Facial

Siga este passo a passo no **Insomnia** para validar todas as funcionalidades antes de realizar o commit no Git.

---

## 1. Autenticação (Login)
O sistema usa JWT. Quase todas as rotas exigem o token no cabeçalho `Authorization: Bearer {token}`.

1.  Selecione a requisição **"Login Admin"**.
2.  Envie o e-mail `admin@facial.com` e a senha `admin123`.
3.  **Ação:** Copie o `access_token` retornado.
4.  **Configuração:** No Insomnia, vá na aba **Auth** das outras requisições, escolha **Bearer Token** e cole o token lá.

---

## 2. Gestão de Alunos (CRUD)
1.  **Listar Alunos:** Execute o GET `/api/alunos`. Deve retornar uma lista vazia ou os alunos cadastrados (as datas virão em formato `DD/MM/AAAA`).
2.  **Cadastrar Aluno:** Use o POST `/api/alunos`.
    *   **Importante:** Envie a data no padrão `YYYY-MM-DD` (ex: `2010-05-20`).
    *   O sistema vai retornar o JSON com a data formatada como `20/05/2010`.
3.  **Auditoria:** Execute o GET `/api/auditoria`. Verifique se o cadastro do aluno gerou um log automático.

---

## 3. Visão Computacional (Rekognition)
Simule a câmera enviando um rosto identificado.

1.  **Cadastrar Face:** Use o POST `/api/rekognition/register-face`.
    *   Envie o `aluno_id` do aluno que você criou e um `face_id` qualquer (ex: `face_teste_001`).
2.  **Presença Facial (RN03):** Use o POST `/api/rekognition/facial-presence` (**Rota Pública**).
    *   Envie apenas o `face_id` (`face_teste_001`).
    *   **O que deve acontecer:**
        *   O sistema cria uma `Atividade` para hoje automaticamente.
        *   Marca o aluno como `PRESENTE`.
        *   Marca todos os outros alunos do mesmo turno como `FALTA`.

---

## 4. Dashboard e Benefícios (RN09)
1.  **Relatório de Benefícios:** Execute o GET `/api/dashboard/beneficios`.
2.  **Validação:** Verifique se o aluno que teve a presença facial aparece como **Apto (Frequência 100%)** e os outros como **Não Aptos**.

---

## 5. Correção Manual e Justificativa
1.  **Correção Manual:** Use o POST `/api/presencas/manual`.
    *   Mude uma `FALTA` para `PRESENTE`.
2.  **Justificativa:** Use o POST `/api/justificativas`.
    *   Anexe um arquivo ou descrição para abonar uma falta.
3.  **Decisão Admin:** Use o POST `/api/justificativas/{id}/decide`.
    *   Aprove a justificativa e veja o status da presença mudar para `FALTA JUSTIFICADA`.

---

## ✅ Checklist de Sucesso
- [ ] O Token JWT funciona em todas as rotas protegidas.
- [ ] As datas no JSON de resposta aparecem como `DD/MM/AAAA`.
- [ ] A auditoria registra cada ação do Admin.
- [ ] A presença facial sobrescreve faltas automáticas corretamente.
