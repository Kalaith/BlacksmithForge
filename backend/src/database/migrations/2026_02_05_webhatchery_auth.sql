-- WebHatchery auth migration for existing Blacksmith Forge databases
-- Adds shared auth fields and game-specific profiles

ALTER TABLE users
    ADD COLUMN email VARCHAR(255) NULL,
    ADD COLUMN auth_provider VARCHAR(50) DEFAULT 'webhatchery';

ALTER TABLE users
    MODIFY password VARCHAR(255) NULL;

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
