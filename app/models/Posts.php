<?php
$db= __DIR__ ."/../../core/Database.php";
require_once $db;
class Posts
{
    private $pdo;
    public function __construct() {
        $this->pdo=Database::getConnection();
        $this->createPostsTable();
        $this->notifications();
    }

    private function createPostsTable(){
        try {
             $sql = "
                CREATE TABLE IF NOT EXISTS posts (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    user_id INT NOT NULL,
                    post TEXT NOT NULL,
                    image_path VARCHAR(255) DEFAULT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ";
            $this->pdo->exec($sql);
        } catch (PDOException $e) {
            die("Table creation failed: " . $e->getMessage());
        }
    }

    // post content
    public function postContent($user_id, $content, $image_path = null)
{
    try {
        $stmt = $this->pdo->prepare("
            INSERT INTO posts (user_id, post, image_path, created_at, updated_at)
            VALUES (:user_id, :content, :image_path, NOW(), NOW())
        ");
        $stmt->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $stmt->bindValue(":content", $content, PDO::PARAM_STR);
        $stmt->bindValue(":image_path", $image_path, PDO::PARAM_STR);
        if ($stmt->execute()) {
            return $this->pdo->lastInsertId(); // ✅ always return success so controller logic works
        }
        return 0;
    } catch (PDOException $e) {
        error_log("Error during posting content: " . $e->getMessage());
        return 0;
    }
}


    // retrieve posts
    public function retrieve($school)
{
    try {
        $sql = "
        SELECT 
            p.*,
            CASE
                WHEN TIMESTAMPDIFF(MINUTE, p.updated_at, NOW()) < 60
                    THEN TIMESTAMPDIFF(MINUTE, p.updated_at, NOW())
                WHEN TIMESTAMPDIFF(HOUR, p.updated_at, NOW()) < 24
                    THEN TIMESTAMPDIFF(HOUR, p.updated_at, NOW())
                WHEN TIMESTAMPDIFF(DAY, p.updated_at, NOW()) < 30
                    THEN TIMESTAMPDIFF(DAY, p.updated_at, NOW())
                WHEN TIMESTAMPDIFF(MONTH, p.updated_at, NOW()) < 12
                    THEN TIMESTAMPDIFF(MONTH, p.updated_at, NOW())
                ELSE TIMESTAMPDIFF(YEAR, p.updated_at, NOW())
            END AS timeDiff,
            
            CASE
                WHEN TIMESTAMPDIFF(MINUTE, p.updated_at, NOW()) < 2 THEN 'minute'
                WHEN TIMESTAMPDIFF(MINUTE, p.updated_at, NOW()) < 60 THEN 'minutes'
                WHEN TIMESTAMPDIFF(HOUR, p.updated_at, NOW()) < 2 THEN 'hour'
                WHEN TIMESTAMPDIFF(HOUR, p.updated_at, NOW()) < 24 THEN 'hours'
                WHEN TIMESTAMPDIFF(DAY, p.updated_at, NOW()) < 2 THEN 'day'
                WHEN TIMESTAMPDIFF(DAY, p.updated_at, NOW()) < 30 THEN 'days'
                WHEN TIMESTAMPDIFF(MONTH, p.updated_at, NOW()) < 2 THEN 'month'
                WHEN TIMESTAMPDIFF(MONTH, p.updated_at, NOW()) < 12 THEN 'months'
                ELSE 'years'
            END AS unit,

            u.id AS user_id,
            u.name AS username,
            u.role AS user_role
        FROM posts p
        LEFT JOIN users u ON p.user_id = u.id
        WHERE 
            (
                (u.role = 'school' AND u.id = :school)
                OR 
                (u.role != 'school' AND u.school_id = :school)
            )
        ORDER BY p.created_at DESC
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':school', $school, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        error_log("Error retrieving posts: " . $e->getMessage());
        return null;
    }
}


// get post by id
public function getById($id)
{
    try {
        $stmt = $this->pdo->prepare("SELECT * FROM posts WHERE id=:id");
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
}catch (PDOException $e) {
    error_log("Error fetching the post".$e->getMessage());
    return null;
}
}

// update post
public function updateById($id, $content, $image_path){
    try {
        $stmt = $this->pdo->prepare("UPDATE posts SET
        content=:content,
        image_path=:image
        WHERE id=:id
        ");
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        $stmt->bindParam(":content", $content, PDO::PARAM_STR);
        $stmt->bindParam(":image", $image_path, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->rowCount();
    } catch (PDOException $e) {
        error_log("Error Updating image".$e->getMessage());
        return 0;
    }
}


// delete post
public function deleteById($id){
    try {
        $stmt = $this->pdo->prepare("DELETE FROM posts WHERE id=:id");
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->rowCount();
    } catch (PDOException $e) {
        error_log("Error deleting post!".$e->getMessage());
        return 0;
    }
}

// search  posts by keywords
public function searchPost($keyword) {
    try {
        $sql = "SELECT 
                    p.id, 
                    p.content, 
                    p.created_at, 
                    u.name, 
                    u.email
                FROM posts p
                INNER JOIN users u ON p.user_id = u.id
                WHERE p.content LIKE :keyword
                   OR u.name LIKE :keyword
                   OR u.email LIKE :keyword
                ORDER BY p.created_at DESC";

        $stmt = $this->pdo->prepare($sql);

        $search = "%" . $keyword . "%";
        $stmt->bindParam(":keyword", $search, PDO::PARAM_STR);

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo "Search error: " . $e->getMessage();
        return null;
    }
}

// notifications
private function notifications(){
    $sql="CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,        -- who receives the notification
    from_user_id INT NOT NULL,   -- who triggered it
    post_id INT NOT NULL,        -- which post
    type ENUM('comment','post') DEFAULT 'comment',
    message VARCHAR(255) NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (from_user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
$this->pdo->exec($sql);
}
}

$post= new Posts();
