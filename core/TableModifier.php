<?php
require_once __DIR__ . "/Database.php";

class TableModifier
{
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getConnection();
        $this->modifyUsersTable(); // run modification on construct
    }

    private function modifyUsersTable() {
        try {
            // Check if column already exists
            $stmt = $this->pdo->query("SHOW COLUMNS FROM comments LIKE 'user_id'");
            $columnExists = $stmt->rowCount() > 0;

            if (!$columnExists) {
                $sql = "
                    ALTER TABLE comments
                    ADD COLUMN user_id INT;
                ";
                $this->pdo->exec($sql);
                echo "✅ Column 'user_id' added to 'users' table successfully.";
            } else {
                echo "ℹ️ Column 'user_id' already exists in 'users' table.";
            }

        } catch (PDOException $e) {
            die("❌ Table modification failed: " . $e->getMessage());
        }
    }
}

$tabler = new TableModifier();
