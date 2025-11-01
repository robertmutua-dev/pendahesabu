<?php
class Database {
    private static ?PDO $connection = null;

    // private static string $host = "sql100.infinityfree.com";
    // private static string $dbName = "if0_40159767_ph_db";
    // private static string $username = "if0_40159767";
    // private static string $password = "0717721212Eve";

    private static string $host = "localhost";
    private static string $dbName = "ph";
    private static string $username = "root";
    private static string $password = "";

    // Prevent creating instance
    private function __construct() {}

    public static function getConnection(): PDO {
        if (self::$connection === null) {
            try {
                $dsn = "mysql:host=" . self::$host . ";dbname=" . self::$dbName . ";charset=utf8mb4";
                self::$connection = new PDO($dsn, self::$username, self::$password);

                // Set PDO attributes
                self::$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                self::$connection->setAttribute(PDO::ATTR_PERSISTENT, true);
            } catch (PDOException $e) {
                die("Database Connection Failed: " . $e->getMessage());
            }
        }
        return self::$connection;
    }
}

