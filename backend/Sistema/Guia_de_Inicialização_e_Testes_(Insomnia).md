**Guia Definitivo: Inicialização e Testes (Insomnia)**

Siga este guia para configurar o sistema do zero e validar todas as funcionalidades no Insomnia antes de realizar o commit no Git.

**🛠️ Passo 1: Preparação do Banco de Dados (XAMPP)**

1. Abra o **Painel de Controle do XAMPP** e inicie os módulos **Apache** e **MySQL**.

2. Acesse http://localhost/phpmyadmin.

3. Crie um novo banco de dados chamado: facial\_recognition.

   * *Dica: Use o Collation utf8mb4\_unicode\_ci para suportar acentos corretamente.*

**📂 Passo 2: Configuração do Projeto**

4. Extraia o projeto na pasta do seu servidor (ex: C:\\xampp\\htdocs\\facial-recognition-api).

5. Abra a pasta no seu terminal ou VS Code.

6. Se o arquivo .env não existir, faça uma cópia do .env.example:

cp .env.example .env

7. Verifique as credenciais no .env:

DB\_CONNECTION\=mysql  
DB\_HOST\=127.0.0.1  
DB\_PORT\=3306  
DB\_DATABASE\=facial\_recognition  
DB\_USERNAME\=root  
DB\_PASSWORD\=

**⚙️ Passo 3: Instalação e Migração**

No terminal, dentro da pasta do projeto, execute:

8. **Instalar dependências:** composer install

9. **Criar tabelas e dados iniciais:** php artisan migrate \--seed

   * *Isso cria o Admin padrão (admin@facial.com / admin123) e as turmas.*

**🚀 Passo 4: Iniciando o Servidor**

Inicie a API com o comando:

php \-S localhost:8000 \-t public

A API estará disponível em http://localhost:8000.

**🔑 Passo 5: Gestão de Token no Insomnia (Automático)**

Para não precisar copiar e colar o token manualmente toda vez:

10. **Login Inicial:** Execute a rota POST /api/auth/login com as credenciais do Admin.

11. **Configurar Ambiente:**

    * No Insomnia, clique em **Manage Environments** (topo esquerdo).

    * Crie uma variável chamada "token".

    * No valor, digite Response e escolha **"Response \=\> Body Attribute"**.

12. **Configurar a Tag:**

    * Clique na tag vermelha criada.

    * **Request:** Selecione a requisição de "Login Admin".

    * **Filter:** Digite $.access\_token.

    * **Trigger Behavior:** Escolha "Always".

13. **Usar nas Rotas:** Em todas as outras rotas, na aba **Auth \> Bearer**, digite {{ token }}.

**🧪 Passo 6: Roteiro de Testes das Regras de Negócio**

14. **Listar Alunos (GET /api/alunos):** Verifique se as datas aparecem como DD/MM/AAAA.

15. **Cadastrar Aluno (POST /api/alunos):** Envie a data como YYYY-MM-DD. O retorno deve vir formatado como DD/MM/AAAA.

16. **Reconhecimento Facial (POST /api/rekognition/facial-presence):**

    * Envie um face\_id cadastrado.

    * O sistema deve criar a atividade do dia e marcar a presença automaticamente.

17. **Dashboard (GET /api/dashboard/beneficios):** Verifique se o cálculo de 75% de frequência está correto.

**Dica para o Git:** Antes de dar o git add ., certifique-se de que o arquivo .env está no seu .gitignore para não subir suas senhas para o repositório\! 🚀