-- DUOS Database Setup Script
-- Run this script in MySQL to create the database

CREATE DATABASE IF NOT EXISTS duos_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Grant privileges (if needed)
-- GRANT ALL PRIVILEGES ON duos_db.* TO 'root'@'localhost';
-- FLUSH PRIVILEGES;

USE duos_db;

-- Show confirmation
SELECT 'DUOS database created successfully!' as message;
