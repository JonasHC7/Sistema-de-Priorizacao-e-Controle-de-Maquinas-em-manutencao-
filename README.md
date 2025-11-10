Sistema de Priorização e Controle de Máquinas em Manutenção

Descrição curta
- Aplicação para gerenciar e priorizar ordens de manutenção de máquinas, controlar status e historico de intervenções.

Principais funcionalidades
- Cadastro e autenticação de usuários
- Registro de máquinas e ordens de manutenção
- Priorização e status das ordens
- Relatórios básicos

Pré-requisitos
- Windows com WAMP (Apache + PHP + MySQL)
- PHP >= 7.4 (ajuste conforme uso)
- MySQL / MariaDB
- Composer (se o projeto usar dependências PHP)
- Git (para versionamento)

Instalação local (rápido)
1. Coloque a pasta do projeto em:
   c:\wamp64\www\Sistema-de-Prioritizacao-e-Controle-de-Maquinas-em-Manutencao
   (recomenda-se evitar espaços no nome da pasta para facilitar URLs)
2. Inicie o WAMP/Apache e o MySQL.
3. Se houver arquivo de banco de dados (.sql), importe via phpMyAdmin ou:
   - Acesse http://localhost/phpmyadmin → importe o arquivo .sql
4. Configure credenciais de acesso ao banco:
   - Edite o arquivo de configuração (ex.: config.php, .env) e ajuste host, database, user, password.
5. Acesse no navegador:
   http://localhost/Sistema-de-Prioritizacao-e-Controle-de-Maquinas-em-Manutencao/
   ou pelo nome de pasta que escolheu.
