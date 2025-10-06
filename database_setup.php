<?php
// Read environment variables directly from Coolify
$db_host = getenv('MARIADB_HOST');
$db_name = getenv('MARIADB_DATABASE');
$db_user = getenv('MARIADB_USER');
$db_pass = getenv('MARIADB_PASSWORD');

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Create users table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `users` (
          `id` INT AUTO_INCREMENT PRIMARY KEY,
          `username` VARCHAR(255) NOT NULL UNIQUE,
          `password_hash` VARCHAR(255) NOT NULL
        );"
    );

    // Create zoho_tokens table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `zoho_tokens` (
          `id` INT AUTO_INCREMENT PRIMARY KEY,
          `user_id` INT NOT NULL UNIQUE,
          `access_token` TEXT NOT NULL,
          `refresh_token` TEXT NOT NULL,
          `expires_in` INT NOT NULL,
          `created_at` INT NOT NULL,
          FOREIGN KEY (user_id) REFERENCES users(id)
        );"
    );

    // Create business_states table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `business_states` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `user_id` INT NOT NULL,
            `business_id` VARCHAR(255) NOT NULL,
            `state_json` JSON NOT NULL,
            UNIQUE KEY `user_business` (`user_id`, `business_id`),
            FOREIGN KEY (user_id) REFERENCES users(id)
        );"
    );

    echo "Database tables created successfully!";

} catch (PDOException $e) {
    die("Could not connect to the database or create tables: " . $e->getMessage());
}
?>