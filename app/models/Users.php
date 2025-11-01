<?php
$db= __DIR__ ."/../../core/Database.php";
require_once $db;
class Users
{
    private $pdo;
    public function __construct() {
        $this->pdo=Database::getConnection();
        $this->createUsersTable();
    }

    private function createUsersTable(){
        try {
             $sql = "
                CREATE TABLE IF NOT EXISTS users (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    name VARCHAR(100) NOT NULL,
                    email VARCHAR(150) UNIQUE NOT NULL UNIQUE,
                    password VARCHAR(255) NOT NULL,
                    role ENUM('school','teacher','student') DEFAULT 'student',
                    status ENUM('active', 'pending') NOT NULL DEFAULT 'pending',
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    school_id INT DEFAULT NULL,
                    FOREIGN KEY (school_id) REFERENCES users(id) ON DELETE SET NULL ON UPDATE SET NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ";
            $this->pdo->exec($sql);
        } catch (PDOException $e) {
            die("Table creation failed: " . $e->getMessage());
        }
    }

    //Register user
   public function createUser($name, $email, $password, $role, $school_id)
{
    try {
        $stmt = $this->pdo->prepare("
            INSERT INTO users (name, email, role, password, school_id)
            VALUES (:name, :email, :role, :password, :school)
        ");

        $stmt->bindParam(":name", $name, PDO::PARAM_STR);
        $stmt->bindParam(":email", $email, PDO::PARAM_STR);
        $stmt->bindParam(":role", $role, PDO::PARAM_STR);

        $passwordHash = password_hash($password, PASSWORD_BCRYPT);
        $stmt->bindParam(":password", $passwordHash, PDO::PARAM_STR);

        $stmt->bindParam(":school", $school_id, PDO::PARAM_INT); 
        $stmt->execute();
        return $stmt->rowCount();

    } catch (PDOException $e) {
        error_log("❌ Error during registration: " . $e->getMessage());
        return 0;
    }
}


    // Login user
    public function getUserByEmail($email)
    {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = :email");
            $stmt->bindParam(":email", $email, PDO::PARAM_STR);
            $stmt->execute();
            $user=$stmt->fetch(PDO::FETCH_ASSOC);
            return $user ?:null;
        } catch (PDOException $e) {
            error_log("Error during login:".$e->getMessage());
            return null;
        }
    }
    
    //fetch all users or return null if no users found
    public function index($role,$school)
    {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM users WHERE role= :role AND school_id= :school");
            $stmt->bindParam(':role',$role);
            $stmt->bindParam(':school',$school);
            $stmt->execute();
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $users ?: null;
        } catch (PDOException $e) {
            error_log("Error fetching users!".$e->getMessage());
            return null;
        }
    }

    //get users by id or return null if user not found
    public function getById($id)
    {
        try {
            $stmt = $this->pdo->prepare("SELECT id, name,role, email, created_at, updated_at FROM users WHERE id = :id");
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            return $user ?: null;
        } catch (PDOException $e) {
            return null;
        }
    }

    // update user
    public function update($id,$name,$email,$password=null){
        try {
            if($password){
                $passwordHash = password_hash($password, PASSWORD_BCRYPT);
                $stmt = $this->pdo->prepare("UPDATE users SET name=:name, email=:email, password=:password WHERE id=:id");
                $stmt->bindParam(":password", $passwordHash);
            } else {
                $stmt = $this->pdo->prepare("UPDATE users SET name=:name, email=:email WHERE id=:id");
            }
            $stmt->bindParam(":name", $name, PDO::PARAM_STR);
            $stmt->bindParam(":email", $email, PDO::PARAM_STR);
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->rowCount();
        } catch (PDOException $e) {
            return 0;
        }
    }

    // drop user by id
    public function delete($id){
        try {
            $stmt = $this->pdo->prepare("DELETE FROM users WHERE id=:id");
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->rowCount();
        } catch (PDOException $e) {
            return 0;
        }
    }

    // search users by search term
    public function search($term){
        try {
            $likeTerm = "%$term%";
            $stmt = $this->pdo->prepare("SELECT id, name, email, created_at, updated_at FROM users WHERE name LIKE :term OR email LIKE :term");
            $stmt->bindParam(":term", $likeTerm, PDO::PARAM_STR);
            $stmt->execute();
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $users ?: null;
        } catch (PDOException $e) {
            return null;
        }
    }

    // get users for notifications
    public function notifyUsers($school,$userid)
    {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM users WHERE(school_id= :school OR id=:school) AND id!=:user_id");
            $stmt->bindParam(':user_id',$userid);
            $stmt->bindParam(':school',$school);
            $stmt->execute();
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $users ?: null;
        } catch (PDOException $e) {
            error_log("Error fetching users!".$e->getMessage());
            return null;
        }
    }
}
// $user= new Users();
// $user->createUser('Green Field School', 'gfs@mail.com','school', 123, null);