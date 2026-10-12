<?php
/**
 * MariaDB / MySQL Database Connection File
 * Uses MySQLi with Prepared Statements
 */

require_once __DIR__ . '/config.php';

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'placement_db');
define('DB_PORT', 3306);

function get_db_connection() {
    static $conn = null;

    if ($conn === null) {
        // Disable reporting errors as raw exceptions to prevent sensitive data leak in production
        mysqli_report(MYSQLI_REPORT_OFF);

        $conn = @new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);

        if ($conn->connect_error) {
            // Check if application is running in browser context
            if (php_sapi_name() !== 'cli') {
                die("<div style='font-family:sans-serif; text-align:center; padding:50px; background:#f8d7da; color:#721c24; border-radius:8px; margin:50px auto; max-width:600px;'>
                    <h2>Database Connection Error</h2>
                    <p>Could not connect to MariaDB database '<strong>" . DB_NAME . "</strong>'.</p>
                    <p>Please check your <code>config/database.php</code> credentials and ensure MariaDB service is running.</p>
                    <small>Details: " . htmlspecialchars($conn->connect_error) . "</small>
                </div>");
            }
        }

        $conn->set_charset("utf8mb4");
    }

    return $conn;
}

// Global DB Connection instance
$conn = get_db_connection();
