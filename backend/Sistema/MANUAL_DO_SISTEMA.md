**Manual do Sistema: API de Reconhecimento Facial**

Este documento unifica todas as informações técnicas, de negócio e de operação do sistema.

**1\. Visão Geral**

O sistema é uma API REST desenvolvida em **Lumen 10** para controle de frequência escolar via reconhecimento facial (Amazon Rekognition). Ele gerencia alunos, turmas, presenças e benefícios sociais automáticos.

**Tecnologias:**

* **Linguagem:** PHP 8.1+

* **Framework:** Lumen 10

* **Banco de Dados:** MySQL (compatível com XAMPP e AWS)

* **Autenticação:** JWT (JSON Web Token)

* **Integração:** Amazon Rekognition (Face ID)

**2\. Regras de Negócio Principais**

**2.1. Reconhecimento Facial (RN03)**

Quando um dispositivo de visão computacional envia um face\_id:

1. O sistema identifica o aluno.

2. Verifica se já existe uma Atividade para o dia e turno.

3. Se não existir, cria a atividade e marca **FALTA** para todos os outros alunos do turno.

4. Registra a presença do aluno identificado.

5. Se o aluno já tinha uma falta (marcada automaticamente pelo sistema), o status muda para **PRESENTE (Sobrescrito)**.

**2.2. Cálculo de Benefícios Sociais (RN09)**

O sistema calcula automaticamente quem está apto ao benefício social:

* **Critério:** Frequência ≥ 75%.

* **Status considerados presença:** PRESENTE, FALTA JUSTIFICADA e PRESENTE (Sobrescrito).

**2.3. Auditoria (RN11)**

Todas as alterações sensíveis (mudança manual de presença, cadastro de alunos) são gravadas na tabela de auditoria com o valor antigo e o novo valor.

**3\. Passo a Passo: Instalação e Uso**

**3.1. Ambiente Local (XAMPP)**

6. **Banco de Dados:** No phpMyAdmin, crie um banco chamado facial\_recognition.

7. **Configuração:** Renomeie .env.example para .env e ajuste as credenciais (padrão XAMPP: usuário root, senha vazia).

8. **Instalação:** No terminal da pasta do projeto:

composer install  
php artisan migrate \--seed

9. **Execução:** php \-S localhost:8000 \-t public

**3.2. Ambiente AWS (EC2/Linux)**

10. **MySQL:** Instale o MySQL no servidor e crie o banco.

11. **Rekognition:** Adicione suas chaves AWS no .env:

AWS\_ACCESS\_KEY\_ID\=sua\_chave  
AWS\_SECRET\_ACCESS\_KEY\=seu\_segredo  
AWS\_DEFAULT\_REGION\=us-east-1

12. **Agendamento:** Adicione ao Cron do servidor para marcar faltas automáticas:

\* \* \* \* \* cd /caminho/do-projeto && php artisan schedule:run \>\> /dev/null 2\>&1

**4\. Suporte e Manutenção**

* **Logs:** Localizados em storage/logs/.

* **Banco:** O MySQL deve estar com charset utf8mb4 para suportar acentos.

* **Auditoria:** Apenas administradores podem visualizar o log de auditoria via /api/auditoria.