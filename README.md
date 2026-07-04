# PHP Users REST API

A scalable RESTful Web API for managing user data, built with **PHP** and **Laravel**, using **Eloquent ORM** for persistence and **PostgreSQL** as the relational database.

This project demonstrates core engineering practices — layered architecture, REST conventions, validation, and clean persistence boundaries — applied in the PHP/Laravel ecosystem. Built with [GitHub Spec Kit](https://github.com/github/spec-kit) spec-driven development.

## Features

- RESTful CRUD operations for a `users` resource
- Layered architecture (controller → service → model)
- PostgreSQL persistence with Eloquent ORM and migrations
- BCrypt password hashing (`passwordHash` in JSON responses)
- Role-based user model (`USER`, `ADMIN`)
- Automatic `created_at` timestamp on user creation
- OpenAPI documentation at `/api/docs/`
- Docker Compose for one-command local setup
- PHPUnit feature and unit tests

## Tech Stack

| Layer | Technology |
|-------|------------|
| Language | PHP 8.4+ |
| Framework | Laravel 13 |
| ORM | Eloquent |
| Database | PostgreSQL 16 |
| API Docs | OpenAPI 3 (Swagger UI) |
| Containers | Docker Compose |
| Spec workflow | GitHub Spec Kit |

## Data Model

Table: `users`

| Column | Type | Notes |
|--------|------|-------|
| `id` | BIGINT, PK | Auto-increment primary key |
| `name` | VARCHAR(255) | User display name |
| `email` | VARCHAR(255) | Unique email |
| `password` | VARCHAR(255) | BCrypt hash; exposed as `passwordHash` in JSON |
| `role` | VARCHAR(10) | `USER` or `ADMIN` |
| `created_at` | TIMESTAMPTZ | Set at creation time |

## Prerequisites

- Docker installed and running (`docker info` succeeds)
- Git

Optional (for running without Docker):

- PHP 8.4+ with `pdo_pgsql` extension
- Composer 2
- PostgreSQL 16+

## Getting Started

### 1. Clone the repository

```bash
git clone <repo-url> php-api
cd php-api
```

### 2. Configure environment

```bash
cp .env.example .env
php artisan key:generate   # skip if using Docker defaults
```

Default values work for local Docker development.

### 3. Start the stack

```bash
docker compose up --build
```

This will:

1. Start PostgreSQL 16 and create the `PhpApi` database
2. Build the Laravel API image
3. Wait for PostgreSQL to become healthy
4. Run migrations (creates the `users` table)
5. Start the API on port **8080**

The API is available at **http://localhost:8080**.

### 4. Stop the stack

```bash
docker compose down        # stop containers (data persists in volume)
docker compose down -v     # stop and remove database volume
```

## API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/users` | List all users |
| GET | `/api/users/{id}` | Get user by ID |
| POST | `/api/users` | Create a new user |
| PUT | `/api/users/{id}` | Update a user |
| DELETE | `/api/users/{id}` | Delete a user |

## Examples

### Create user

```bash
curl -X POST http://localhost:8080/api/users \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Jane Doe",
    "email": "jane@example.com",
    "password": "your-secure-password",
    "role": "USER"
  }'
```

### List users

```bash
curl http://localhost:8080/api/users
```

### Update user

`password` is optional on update; omit it to keep the existing hash.

```bash
curl -X PUT http://localhost:8080/api/users/1 \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Jane Smith",
    "email": "jane.smith@example.com",
    "password": "new-password",
    "role": "ADMIN"
  }'
```

### Delete user

```bash
curl -X DELETE http://localhost:8080/api/users/1
```

Returns HTTP 204 with no body.

## API Documentation

- Swagger UI: http://localhost:8080/api/docs/
- OpenAPI schema: http://localhost:8080/api/schema/
- Design contract: `specs/001-users-api/contracts/users-api.openapi.yaml`

## Configuration

| Variable | Default (Docker) | Description |
|----------|------------------|-------------|
| `DB_HOST` | `postgres` | PostgreSQL hostname |
| `DB_PORT` | `5432` | PostgreSQL port |
| `DB_DATABASE` | `PhpApi` | Database name |
| `DB_USERNAME` | `postgres` | PostgreSQL user |
| `DB_PASSWORD` | `postgres` | PostgreSQL password |
| `APP_KEY` | *(generate)* | Laravel encryption key |
| `APP_DEBUG` | `true` | Debug mode |

## Testing

Tests use **PHPUnit** with an in-memory SQLite database — no PostgreSQL or Docker required.

```bash
composer install
php artisan test
```

Test suites:

| File | Covers |
|------|--------|
| `tests/Unit/UserServiceTest.php` | Business logic, password hashing, duplicate email |
| `tests/Feature/UserApiTest.php` | Full CRUD HTTP endpoints |

## Project Structure

```text
php-api/
├── app/
│   ├── Http/Controllers/Api/   # REST controllers
│   ├── Http/Requests/            # Input validation
│   ├── Http/Resources/           # Response DTOs
│   ├── Models/User.php           # Eloquent entity
│   └── Services/UserService.php  # Business logic
├── database/migrations/
├── routes/api.php
├── specs/001-users-api/          # Spec Kit feature artifacts
├── Dockerfile
├── docker-compose.yml
├── entrypoint.sh
└── .env.example
```

Layered architecture:

| Layer | PHP | Java (reference) |
|-------|-----|------------------|
| HTTP | `Controllers/Api/` | `controller/` |
| Business logic | `Services/` | `service/` |
| Data access | `Models/` | `repository/` + JPA entity |
| DTOs | `Requests/` + `Resources/` | `dto/` |

## Security Notes

- Passwords are hashed with BCrypt before being saved to the database
- `GET /api/users` and `GET /api/users/{id}` may include `passwordHash` in responses (demo/debug)
- `POST` and `PUT` success responses never echo plain-text `password`
- Input validation is enforced on all write operations
- No authentication on endpoints in v1 (matches Java demo)
- Use HTTPS in production

## License

MIT License.

**Author:** 
Lucas Soares
