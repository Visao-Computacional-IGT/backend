# Facial Recognition API (Versão MySQL)

API para controle de frequência escolar com suporte a reconhecimento facial via Amazon Rekognition.


## 🚀 Início Rápido (XAMPP)
1. Crie o banco `facial_recognition` no MySQL.
2. Configure o `.env` com as credenciais do seu banco (usuário `root`, senha vazia por padrão).
3. Execute os comandos:
   ```bash
   composer install
   php artisan migrate --seed
   ```

## 📖 Documentação Completa
Para entender as regras de negócio, endpoints e como fazer o deploy na AWS, leia o arquivo:
👉 **[MANUAL_DO_SISTEMA.md]**

## 🛡️ Segurança e Auditoria
O sistema possui um módulo de auditoria obrigatória que registra todas as alterações manuais de presença e cadastros de alunos, garantindo conformidade com regras de benefícios sociais.
