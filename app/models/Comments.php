<?php
$db= __DIR__ ."/../../core/Database.php";
require_once $db;
class Comments
{
    private $pdo;
    public function __construct() {
        $this->pdo=Database::getConnection();
        $this->commentsTable();
    }

    public function commentsTable(){
        try {
             $sql = "
                CREATE TABLE IF NOT EXISTS comments (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    post_id INT NOT NULL,
                    comment TEXT NOT NULL,
                    image_path VARCHAR(255) DEFAULT NULL,
                    user_id INT NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
                    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ";
            $this->pdo->exec($sql);
        } catch (PDOException $e) {
            die("Table creation failed: " . $e->getMessage());
        }
    }

    // comment a post
    public function post($post,$comment,$user_id,$image_path=null){
        try {
            $stmt = $this->pdo->prepare("INSERT INTO comments(post_id, comment, image_path,user_id) VALUES(:post_id, :comment, :image_path,:user_id)");
            $stmt->bindParam(":post_id", $post, PDO::PARAM_INT);
            $stmt->bindParam(":user_id",$user_id);
            $stmt->bindParam(":comment", $comment, PDO::PARAM_STR);
            $stmt->bindParam(":image_path", $image_path, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->rowCount();
        } catch (PDOException $e) {
            error_log("Error during commenting on post:".$e->getMessage());
            return 0;
        }

    }

    // retrieve comments
    public function getComments($post){
        try {
            $sql="SELECT c.*,u.id AS userid,u.name,
            CASE
            WHEN TIMESTAMPDIFF(MINUTE, c.updated_at, NOW())<60
            THEN TIMESTAMPDIFF(MINUTE, c.updated_at, NOW())
            WHEN TIMESTAMPDIFF(HOUR, c.updated_at, NOW())<24
            THEN TIMESTAMPDIFF(HOUR, c.updated_at, NOW())
            WHEN TIMESTAMPDIFF(DAY, c.updated_at, NOW())<30
            THEN TIMESTAMPDIFF(DAY, c.updated_at, NOW())
            WHEN TIMESTAMPDIFF(MONTH, c.updated_at, NOW())<12
            THEN TIMESTAMPDIFF(MONTH, c.updated_at, NOW())

            ELSE TIMESTAMPDIFF(YEAR, c.updated_at, NOW())
            END AS timeDiff,
            CASE
            WHEN TIMESTAMPDIFF(MINUTE, c.updated_at, NOW()) <2
            THEN 'minute'
            WHEN TIMESTAMPDIFF(MINUTE, c.updated_at, NOW()) <60
            THEN 'minutes'
            WHEN TIMESTAMPDIFF(HOUR, c.updated_at, NOW()) <2
            THEN 'hour'
            WHEN TIMESTAMPDIFF(HOUR, c.updated_at, NOW()) <24
            THEN 'hours'
            WHEN TIMESTAMPDIFF(DAY, c.updated_at, NOW()) <2
            THEN 'day'
            WHEN TIMESTAMPDIFF(DAY, c.updated_at, NOW()) <30
            THEN 'days'
            WHEN TIMESTAMPDIFF(MONTH, c.updated_at, NOW()) <2
            THEN 'month'
            WHEN TIMESTAMPDIFF(MONTH, c.updated_at, NOW()) <12
            THEN 'months'
            ELSE
            'years'
            END AS unit
            FROM comments c INNER JOIN users u
            ON c.user_id=u.id
            WHERE c.post_id=:post
            ORDER BY updated_at DESC";
            $stmt=$this->pdo->prepare($sql);
            $stmt->bindParam(":post",$post);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ? : null;
        } catch (PDOException $e) {
            error_log("Error fetching comments!".$e->getMessage());
            return null;
        }
    }

    // delete comment
    public function deleteComment($commentId){
        try {
            $stmt = $this->pdo->prepare("DELETE FROM comments WHERE id = :commentId");
            $stmt->bindParam(":commentId", $commentId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Error deleting comment: " . $e->getMessage());
            return false;
        }
}

    // get comment by id
    public function getCommentById($commentId)
    {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM comments WHERE id = :commentId");
            $stmt->bindParam(":commentId", $commentId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (PDOException $e) {
            error_log("Error fetching comment by ID: " . $e->getMessage());
            return null;
        }
}
}

