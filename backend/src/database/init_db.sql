-- Blacksmith Forge Database Initialization Script
-- Run this script to create all required tables for the backend

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NULL,
    auth_provider VARCHAR(50) DEFAULT 'webhatchery',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Blacksmith profiles table (game-specific user data)
CREATE TABLE IF NOT EXISTS blacksmith_profiles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    forge_name VARCHAR(255) DEFAULT 'New Forge',
    level INT DEFAULT 1,
    reputation INT DEFAULT 0,
    coins INT DEFAULT 0,
    crafting_mastery JSON NULL,
    settings JSON NULL,
    last_seen_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_user_profile (user_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Materials table
CREATE TABLE IF NOT EXISTS materials (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    type VARCHAR(50) DEFAULT 'ore',
    rarity VARCHAR(50) DEFAULT 'common',
    quantity INT DEFAULT 0,
    properties JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Recipes table
CREATE TABLE IF NOT EXISTS recipes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    required_materials JSON NOT NULL,
    result_item VARCHAR(100) NOT NULL,
    difficulty VARCHAR(50) DEFAULT 'easy',
    time INT DEFAULT 60,
    unlock_level INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Customers table
CREATE TABLE IF NOT EXISTS customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    avatar VARCHAR(255),
    `order` VARCHAR(100),
    patience INT DEFAULT 100,
    reward INT DEFAULT 0,
    status VARCHAR(50) DEFAULT 'waiting',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Inventory table
CREATE TABLE IF NOT EXISTS inventory (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    items JSON NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Crafting history table
CREATE TABLE IF NOT EXISTS crafting (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    recipe_id INT NOT NULL,
    materials_used JSON NOT NULL,
    result VARCHAR(100),
    success BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (recipe_id) REFERENCES recipes(id) ON DELETE CASCADE
);

-- Upgrades table
CREATE TABLE IF NOT EXISTS upgrades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    cost INT DEFAULT 0,
    effect JSON,
    unlock_level INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Mini-games table
CREATE TABLE IF NOT EXISTS minigames (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    game_type VARCHAR(50),
    score INT DEFAULT 0,
    result VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Insert some sample data for testing
INSERT IGNORE INTO materials (name, type, rarity, quantity, properties) VALUES
('Iron Ore', 'ore', 'common', 100, '{"hardness": 5, "melting_point": 1538}'),
('Coal', 'fuel', 'common', 50, '{"energy": 25, "burn_time": 300}'),
('Silver Ore', 'ore', 'uncommon', 25, '{"hardness": 2.5, "melting_point": 962}'),
('Gold Ore', 'ore', 'rare', 10, '{"hardness": 2.5, "melting_point": 1064}'),
('Diamond', 'gem', 'legendary', 2, '{"hardness": 10, "brilliance": 95}');

INSERT IGNORE INTO recipes (name, required_materials, result_item, difficulty, time, unlock_level) VALUES
('Iron Sword', '[{"name": "Iron Ore", "quantity": 3}, {"name": "Coal", "quantity": 1}]', 'Iron Sword', 'easy', 120, 1),
('Silver Ring', '[{"name": "Silver Ore", "quantity": 1}]', 'Silver Ring', 'medium', 180, 3),
('Gold Crown', '[{"name": "Gold Ore", "quantity": 5}, {"name": "Diamond", "quantity": 1}]', 'Gold Crown', 'expert', 600, 10);

INSERT IGNORE INTO customers (name, avatar, `order`, patience, reward, status) VALUES
('Sir Blackwood', '🤴', 'Iron Sword', 80, 150, 'waiting'),
('Lady Silverstone', '👸', 'Silver Ring', 60, 300, 'waiting'),
('King Goldbeard', '👑', 'Gold Crown', 40, 1000, 'waiting');

INSERT IGNORE INTO upgrades (name, description, cost, effect, unlock_level) VALUES
('Better Furnace', 'Increases crafting speed by 25%', 500, '{"speed_multiplier": 1.25}', 1),
('Skilled Hands', 'Reduces chance of crafting failure', 1000, '{"success_bonus": 0.15}', 3),
('Master Forge', 'Unlocks legendary recipes', 2500, '{"unlock_legendary": true}', 5);
