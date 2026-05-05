# Backend Specification for Blacksmith Forge

This document outlines the requirements and endpoints for building a backend in PHP to support the Blacksmith Forge frontend. The backend will manage game data such as materials, recipes, customers, crafting actions, inventory, upgrades, and mini-games.

## Overview
The backend should provide RESTful APIs for all game features, handle authentication, persist game state, and ensure data integrity. Data can be stored in a relational database (e.g., MySQL).

---

## Core Entities

### 1. Materials
- **Fields:** id, name, type, rarity, quantity, properties
- **Endpoints:**
  - `GET /materials` — List all materials
  - `GET /materials/{id}` — Get material details
  - `POST /materials` — Add new material
  - `PUT /materials/{id}` — Update material
  - `DELETE /materials/{id}` — Remove material

### 2. Recipes
- **Fields:** id, name, required_materials, result_item, difficulty, time, unlock_level
- **Endpoints:**
  - `GET /recipes` — List all recipes
  - `GET /recipes/{id}` — Get recipe details
  - `POST /recipes` — Add new recipe
  - `PUT /recipes/{id}` — Update recipe
  - `DELETE /recipes/{id}` — Remove recipe

### 3. Customers
- **Fields:** id, name, avatar, order, patience, reward, status
- **Endpoints:**
  - `GET /customers` — List all customers
  - `GET /customers/{id}` — Get customer details
  - `POST /customers` — Add new customer
  - `PUT /customers/{id}` — Update customer
  - `DELETE /customers/{id}` — Remove customer

### 4. Inventory
- **Fields:** id, user_id, items (array of item_id, quantity)
- **Endpoints:**
  - `GET /inventory/{user_id}` — Get user inventory
  - `POST /inventory/{user_id}/add` — Add item to inventory
  - `POST /inventory/{user_id}/remove` — Remove item from inventory

### 5. Crafting
- **Fields:** id, user_id, recipe_id, materials_used, result, success, timestamp
- **Endpoints:**
  - `POST /craft` — Craft an item (requires recipe and materials)
  - `GET /crafting/history/{user_id}` — Get crafting history

### 6. Upgrades
- **Fields:** id, name, description, cost, effect, unlock_level
- **Endpoints:**
  - `GET /upgrades` — List all upgrades
  - `POST /upgrades/purchase` — Purchase an upgrade

### 7. Mini-Games (e.g., Hammering)
- **Fields:** id, user_id, game_type, score, result, timestamp
- **Endpoints:**
  - `POST /minigame/play` — Submit mini-game result
  - `GET /minigame/history/{user_id}` — Get mini-game history

---

## Authentication
- Shared Web Hatchery Login is the supported authentication flow.
- Protected endpoints require `Authorization: Bearer <token>`.
- Missing or invalid authentication returns `401` with `login_url`.
- `GET /api/v1/auth/login-info` exposes the configured login URL.
- `POST /api/v1/auth/guest-session` creates a guest JWT session.
- `POST /api/v1/auth/link-guest` accepts `guest_token` and merges guest progress into the authenticated Web Hatchery user.

---

## General Requirements
- Use PHP
- Use MySQL or MariaDB for data storage
- Implement input validation and error handling
- Use JWT or session-based authentication
- Ensure CORS is enabled for frontend communication
- Provide clear API documentation (OpenAPI/Swagger recommended)

---

## Example Data Models

```php
// Material
{
  "id": 1,
  "name": "Iron Ore",
  "type": "Ore",
  "rarity": "Common",
  "quantity": 100,
  "properties": { "hardness": 5 }
}

// Recipe
{
  "id": 1,
  "name": "Iron Sword",
  "required_materials": [ { "material_id": 1, "quantity": 2 } ],
  "result_item": "Iron Sword",
  "difficulty": "Medium",
  "time": 60,
  "unlock_level": 2
}

// Customer
{
  "id": 1,
  "name": "Sir Galen",
  "avatar": "url",
  "order": "Iron Sword",
  "patience": 100,
  "reward": 50,
  "status": "waiting"
}
```

---

## Additional Notes
- All endpoints should return JSON responses.
- Use appropriate HTTP status codes.
- Log all important actions for debugging and analytics.
- Design for scalability and future features (e.g., multiplayer, events).

---

## Next Steps

---

## Recommended Project Structure (PHP)

Organize your backend using a structure similar to Dragon's Den for maintainability and scalability:

```
backend/
  app/
    Controllers/
    Models/
    Services/
    Middleware/
  config/
  database/
    migrations/
    seeds/
  public/
    index.php
  routes/
    api.php
  storage/
  tests/
  vendor/
  .env
  composer.json
  README.md
```

## Setup Instructions

1. **Environment**: Use PHP 8.1+, Composer, and MySQL/MariaDB.
2. **Dependencies**: Runtime dependencies are managed by the WebHatchery root Composer install. The app `composer.json` keeps only scripts and local autoload metadata.
3. **Environment variables**: Copy `.env.example` to `.env` and explicitly configure every required value. Code must not provide fallback values for required environment variables.
4. **Database migration**: SQL lives under `backend/database/`. Run `backend/database/init_db.sql`, then ordered scripts in `backend/database/migrations/`.
5. **Seeding**: Run `php src/database/Seeder.php` after schema setup when seed data is needed.
6. **Testing**: Run `composer run cs-check` and `composer run test`.
7. **Deployment**: Use the project `publish.ps1` from the game root.

## Development Workflow

- Use Git for version control and branching.
- Run migrations and seeders after setup: `php artisan migrate --seed` (Laravel).
- Use PHPUnit for testing: `vendor/bin/phpunit`.
- Document API endpoints using Swagger/OpenAPI.
- Use logging for error tracking and analytics.

## Best Practices

- Use environment variables for sensitive data and configuration.
- Implement request validation and error handling in controllers.
- Use service classes for business logic.
- Organize code for scalability (future features, multiplayer, events).
- Version your API for backward compatibility.
- Regularly back up your database and monitor server health.

---

## Example: Dragon's Den Backend Setup (for reference)

- Modular structure with clear separation of concerns (controllers, models, services).
- Use of migrations and seeders for game data.
- RESTful API design for all game features.
- JWT authentication for secure user sessions.
- Automated tests and CI/CD pipeline for deployment.

---

## Next Steps (Extended)
- Set up PHP project structure as outlined above
- Define database schema for all entities
- Implement authentication and authorization
- Build and test each endpoint
- Document APIs for frontend integration
