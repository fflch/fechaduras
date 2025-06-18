# Fechaduras

Sistema para gerenciamento centralizado de fechaduras eletrônicas.

## Funcionalidades Principais

-   **Gerenciamento de Fechaduras**

    -   Cadastro de fechaduras (localização, IP, credenciais)
    -   Edição e remoção de fechaduras
    -   Visualização detalhada de cada fechadura

-   **Controle de Acessos**

    -   Sincronização de usuários cadastrados nas fechaduras
    -   Vinculação de usuários USP e setores nas fechaduras
    -   Histórico de acessos (liberados/negados)

-   **Automatização**
    -   Sincronização diária automática (2:00 AM)
    -   Atualização manual de logs quando necessário

## Tecnologias

-   **Backend**: Laravel 10.^
-   **Frontend**: Bootstrap 5, Blade Templates
-   **Banco de Dados**: MariaDB / MySQL
-   **Autenticação**: SenhaÚnica USP
-   **Integração**: API ControlID

## Estrutura do Banco de Dados

Principais tabelas:

| Tabela            | Descrição                                      |
| ----------------- | ---------------------------------------------- |
| `users`           | Usuários do sistema                            |
| `fechaduras`      | Armazena informações dos dispositivos físicos  |
| `logs`            | Registra os eventos de acesso                  |
| `setores`         | Setores da USP que podem acessar as fechaduras |
| `fechadura_user`  | Relacionamento N:N entre fechaduras e usuários |
| `fechadura_setor` | Relacionamento N:N entre fechaduras e setores  |

## Instalação e Configuração

### Requisitos

-   PHP 10.^
-   Composer
-   MariaDB
-   Git

### Configuração do Ambiente de Desenvolvimento

1. **Fork do repositório**:

    - Acesse [github.com/fflch/fechaduras](https://github.com/fflch/fechaduras)
    - Clique em "Fork" no canto superior direito

2. **Clonar seu fork**:

```bash
git clone git@github.com:SEU_USUARIO/fechaduras.git
cd fechaduras
```

3. **Configuras ambiente**:

```bash
cp .env.example .env
php artisan key:generate
```

4. **Configurar banco de dados**:

```bash
sudo mariadb
GRANT ALL PRIVILEGES ON *.* TO 'seu_usuario'@'%'  IDENTIFIED BY 'sua_senha' WITH GRANT OPTION;
create database fechaduras;
quit;
```

5. **Editar .env**:

    - Sistema

    ```
    APP_NAME=Laravel
    APP_ENV=local
    APP_KEY=Já_vai_estar_preenchido
    APP_DEBUG=true
    APP_URL=http://127.0.0.1:8000 # Altere apenas essa linha
    ```

    - Banco de Dados

    ```
    # Altere as linhas abaixo de acordo com as suas configurações do mariadb

    DB_CONNECTION=mariadb
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=fechaduras
    DB_USERNAME=seu_usuario
    DB_PASSWORD=sua_senha
    ```

    - Integração com replicado

    ```
    # Altere as linhas abaixo com os dados corretos

    REPLICADO_HOST=servidor.replicado.usp.br
    REPLICADO_PORT=1234
    REPLICADO_DATABASE=fflch
    REPLICADO_USERNAME=usuario_replicado
    REPLICADO_PASSWORD=senha_replicado
    REPLICADO_CODUNDCLG=1
    REPLICADO_SYBASE=1
    ```

    - Autenticação com SenhaÚnica USP

    ```
    # Altere as três linhas abaixo com os dados corretos

    SENHAUNICA_KEY=key
    SENHAUNICA_SECRET=secret
    SENHAUNICA_CALLBACK_ID=callback
    ```

    - Credenciais para o WSFoto

    ```
    # Alterar as duas linhas abaixo com as credenciais corretas

    WSFOTO_USER=usuario_wsfoto
    WSFOTO_PASS=senha_wsfoto
    ```

    - Tema visual institucional

    ```
    USP_THEME_SKIN=fflch
    ```

6. **Instalar dependências**:

```bash
composer install
```

7. **Executar migrações**:

```bash
php artisan migrate
```

8. **Iniciar servidor**:

```bash
php artisan serve
```

## Desenvolvimento

### Comandos Artisan

| Comando                 | Descrição                                  | Automatização  |
| ----------------------- | ------------------------------------------ | -------------- |
| `fechaduras:sync-users` | Sincroniza usuários de todas as fechaduras | Diária 2:00 AM |

### Rotas do Sistema

| Rota                                             | Tipo     | Descrição                                                        |
| ------------------------------------------------ | -------- | ---------------------------------------------------------------- |
| `/`                                              | `GET`    | Redireciona para `/fechaduras`                                   |
| `/fechaduras`                                    | `GET`    | Lista todas as fechaduras                                        |
| `/fechaduras/create`                             | `GET`    | Formulário de cadastro de nova fechadura                         |
| `/fechaduras`                                    | `POST`   | Armazena uma nova fechadura no banco de dados                    |
| `/fechaduras/{fechadura}`                        | `GET`    | Exibe detalhes de uma fechadura específica + usuários associados |
| `/fechaduras/{fechadura}/edit`                   | `GET`    | Formulário de edição de fechadura                                |
| `/fechaduras/{fechadura}`                        | `PUT`    | Atualiza os dados de uma fechadura                               |
| `/fechaduras/{fechadura}`                        | `DELETE` | Remove uma fechadura do sistema                                  |
| `/fechaduras/{fechadura}/logs`                   | `GET`    | Exibe o histórico de logs da fechadura                           |
| `/fechaduras/{fechadura}/logs`                   | `POST`   | Atualiza ou adiciona novos logs                                  |
| `/fotos`                                         | `POST`   | Processa e armazena fotos relacionadas às fechaduras             |
| `/fechaduras/{fechadura}/sincronizar`            | `POST`   | Força a sincronização dos dados da fechadura                     |
| `/fechaduras/{fechadura}/delete_user/{user}`     | `POST`   | Remove a associação de um usuário com a fechadura                |
| `/fechaduras/{fechadura}/create_fechadura_user`  | `POST`   | Cria nova associação entre usuário e fechadura                   |
| `/fechaduras/{fechadura}/create_fechadura_setor` | `POST`   | Cria nova associação entre setor e fechadura                     |

### Estrutura de Diretórios Relevantes

app/  
├── Actions/  
│ ├── CreateSetorAction.php  
│ └── SyncUsersActions.php  
├── Console/  
│ ├── Commands/  
│ │ └── SincronizarFechaduras.php  
├── Http/  
│ ├── Controllers/  
│ │ ├── FechaduraController.php  
│ │ ├── LogController.php  
│ │ └── ...Outros Controllers  
│ └── Requests/  
│ └── FechaduraRequest.php  
├── Models/  
│ ├── Fechadura.php  
│ ├── Log.php  
│ └── ...Outros Models  
└── Services/  
├── AccessLogService.php  
├── LockSessionService.php  
└── ...Outros services

database/  
├── migrations/  
│ ├── yyyy_mm_dd_xxxxxx_create_fechaduras_table.php  
│ ├── yyyy_mm_dd_zzzzzz_create_logs_table.php  
│ └── ...outras migrations  
└── seeders/

resources/  
├── views/  
│ └── fechaduras/  
│ ├── index.blade.php  
│ ├── show.blade.php  
│ └── ...outras views  
└── partials/

routes/  
├── console.php  
└── web.php

### Fluxo de Trabalho com Git

1. **Sincronizar seu fork**:

-   No GitHub, acesse seu fork do repositório
-   Clique em `Sync fork` no canto superior direito
-   No terminal digite:

```bash
git pull
```

2. **Após desenvolver, envie as alterações**:

```bash
git add .
git commit -m "descrição das alterações"
git push origin master
```

3. **Abrir Pull Request**:

-   No seu repositório fork no GitHub
-   Clique em `Contribute` depois em `Open Pull Request`

## Equipe de Desenvolvimento:

[Alan Neves](https://github.com/alan-neves)  
[Raphael Feitosa](https://github.com/oFandangos)  
[Ricardo Fontura](https://github.com/ricardfo)  
[Thiago G. Verissimo](https://github.com/thiagogomesverissimo)
