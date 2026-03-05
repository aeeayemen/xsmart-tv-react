-- XSMART TV Database Schema

CREATE DATABASE IF NOT EXISTS xsmart_tv_db;
USE xsmart_tv_db;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    exp_date INT DEFAULT NULL, -- Unix timestamp
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Favorites table
CREATE TABLE IF NOT EXISTS favorites (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type ENUM('movie', 'series', 'live') NOT NULL,
    item_id VARCHAR(50) NOT NULL,
    name VARCHAR(255) DEFAULT '',
    image_url TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY (user_id, type, item_id)
);

-- History table (optional if we want to sync history too)
CREATE TABLE IF NOT EXISTS history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type ENUM('movie', 'series') NOT NULL,
    item_id VARCHAR(50) NOT NULL,
    name VARCHAR(255) DEFAULT '',
    image_url TEXT DEFAULT NULL,
    last_watched TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY (user_id, type, item_id)
);
