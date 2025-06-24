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

-   **Backend**: Laravel 12
-   **Frontend**: Bootstrap 5, Blade Templates
-   **Banco de Dados**: MariaDB / MySQL
-   **Autenticação**: SenhaÚnica USP
-   **Integração**: API ControlID

### Requisitos

-   PHP 8
-   Composer
-   MariaDB / MySQL
-   Git

### Configuração do Ambiente de Desenvolvimento

1. **Gerar chave**:

```bash
php artisan key:generate
```

2. **Configurar .env**:

```bash
cp .env.example .env
```

3. **Instalar dependências**:

```bash
composer install
```

4. **Executar migrações**:

```bash
php artisan migrate
```

### Comandos Artisan

| Comando                 | Descrição                                  | Automatização  |
| ----------------------- | ------------------------------------------ | -------------- |
| `fechaduras:sync-users` | Sincroniza usuários de todas as fechaduras | Diária 2:00 AM |

## Equipe de Desenvolvimento:

[Alan Neves](https://github.com/alan-neves)  
[Raphael Feitosa](https://github.com/oFandangos)  
[Ricardo Fontura](https://github.com/ricardfo)  
[Thiago G. Verissimo](https://github.com/thiagogomesverissimo)
