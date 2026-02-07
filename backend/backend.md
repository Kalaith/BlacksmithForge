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
- **Endpoints:**
  - `POST /auth/register` — Register new user
  - `POST /auth/login` — Login user
  - `POST /auth/logout` — Logout user

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
2. **Install dependencies**: Run `composer install` to set up packages 
3. **Environment variables**: Copy `.env.example` to `.env` and configure database, JWT secret, etc.
4. **Database migration**: Use migration scripts to set up tables for all entities (materials, recipes, customers, etc.).
5. **Seeding**: Add initial game data using seeders.
6. **Routing**: Define RESTful routes in `routes/api.php`.
7. **Testing**: Add unit and integration tests in the `tests/` directory.
8. **Deployment**: Use environment-specific configs and scripts for production deployment.

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
