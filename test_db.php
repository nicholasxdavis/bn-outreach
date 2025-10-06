<?php
// Test database connection and environment variables
header('Content-Type: text/plain');

echo "=== Environment Variables Test ===\n\n";

$vars = [
    'MARIADB_HOST',
    'MARIADB_DATABASE',
    'MARIADB_USER',
    'MARIADB_PASSWORD',
    'ZOHO_API',
    'ZOHO_CLIENT_SECRET'
];

foreach ($vars as $var) {
    $value = getenv($var);
    if ($value) {
        echo "$var: SET (length: " . strlen($value) . ")\n";
    } else {
        echo "$var: NOT SET\n";
    }
}

echo "\n=== Database Connection Test ===\n\n";

$db_host = getenv('MARIADB_HOST');
$db_name = getenv('MARIADB_DATABASE');
$db_user = getenv('MARIADB_USER');
$db_pass = getenv('MARIADB_PASSWORD');

if (!$db_host || !$db_name || !$db_user) {
    echo "ERROR: Missing required environment variables\n";
    exit;
}

echo "Attempting connection to: mysql:host=$db_host;dbname=$db_name\n";
echo "Using user: $db_user\n\n";

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "✓ Database connection successful!\n\n";

    // Test if users table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
    if ($stmt->rowCount() > 0) {
        echo "✓ 'users' table exists\n";

        // Count users
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
        $result = $stmt->fetch();
        echo "  Users in database: " . $result['count'] . "\n";
    } else {
        echo "✗ 'users' table does NOT exist\n";
        echo "  You need to run database_setup.php to create tables\n";
    }

    // Check other tables
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "\nAll tables in database:\n";
    foreach ($tables as $table) {
        echo "  - $table\n";
    }

} catch (PDOException $e) {
    echo "✗ Database connection FAILED\n";
    echo "Error: " . $e->getMessage() . "\n";
    echo "Error Code: " . $e->getCode() . "\n";
}
?>
