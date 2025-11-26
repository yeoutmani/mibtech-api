# mibtech-api

Symfony API for managing authors, books, and categories.

## Features

- Management of entities: Author, Book, Category
- RESTful API generated with ApiPlatform
- Security and user management (preconfigured)
- CORS configuration for external access
- Doctrine migrations for database structure
- Repositories for data access

## Installation

```bash
git clone https://github.com/yeoutmani/mibtech-api.git
cd mibtech-api
composer install
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

## Fix the code style

To automatically fix coding style issues across the project:

```bash
vendor/bin/php-cs-fixer fix
```

## Testing

To run integration and functional tests, you should use a dedicated test database. Copy `.env.test.example` to `.env.test` and configure the test database URL

Create the test database schema using migrations:

```bash
php bin/console --env=test doctrine:database:create 
php bin/console --env=test doctrine:migrations:migrate
```

Then run your tests:

```bash
php bin/phpunit
```
## Starting the Project with Docker

To start the project using Docker:

```bash
docker compose build --pull --no-cache
docker compose up -d
```

## Start Locally

```bash
symfony server:start
# or
php -S localhost:8000 -t public
```

## API Access


The API is available at:  
`http://localhost:8000/api`

### Example payload for creating a Book

```json
{
    "title": "book test",
    "description": "book test",
    "publicationDate": "2025-11-25",
    "author": "/api/authors/1",
    "categories": ["/api/categories/1"]
}
```

## Structure

- `src/Entity/`: Doctrine entities
- `src/Repository/`: Repositories
- `config/packages/`: Bundle configuration
- `migrations/`: Database migrations
- `templates/`: Twig templates
- `tests/`: Integration and functional tests
