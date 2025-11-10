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

Versionamento e envio ao GitHub (resumo)
- Crie .gitignore (ex.: excluir vendor/, .env, node_modules/, .vscode/)
- Inicialize e faça commit:
  git init
  git add .
  git commit -m "Initial commit"
- Crie repositório no GitHub e conecte:
  git remote add origin https://github.com/SEU_USUARIO/NOME_REPO.git
  git branch -M main
  git push -u origin main

Observações importantes
- Não versionar arquivos sensíveis (senhas, .env). Adicionar ao .gitignore.
- Verifique encoding dos arquivos PHP (UTF-8 sem BOM) para evitar problemas com headers.

Contribuição
- Abra issues para bugs ou melhorias.
- Envie pull requests para a branch main (ou siga o fluxo definido no repositório).

Licença
- Adicione uma licença (ex.: MIT) se desejar tornar o projeto público. Substitua esta seção pela licença escolhida.

Se quiser, eu gero um README com exemplos de configuração (config.php), um .gitignore mais detalhado para PHP/WAMP e um template de LICENSE. Indique qual licença prefere.
