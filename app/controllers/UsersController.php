<?php
$model= __DIR__ ."/../models/Users.php";
require_once $model;

class UsersController
{
    private $model;
    public function __construct() {
        // Session start
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->model = new Users();
    }

    // show function
    public function index()
    {
        if ($_SESSION['user']['role']==='school') {
            $school=$_SESSION['user']['id'];
            $role='teacher';
        }else {
            $school=$_SESSION['user']['school_id'];
            $role='student';
        }
        return $this->model->index($role,$school);
    }

    // register function
    // Register function
public function create()
{
    // Sanitize inputs
    $name  = ucwords(strtolower(trim($_POST["name"] ?? '')));
    $email = strtolower(trim($_POST["email"] ?? ''));
    $role  = $_POST['role'] ?? null;
    $isLogged = isset($_SESSION['user']['role']);
    if ($isLogged) {
        $redirectBase = "/pendahesabu/{$_SESSION['user']['role']}/users";
    }

    // Determine which school the new user belongs to
    if ($_SESSION['user']['role'] === 'school') {
        // If logged in as a school
        $school_id = $_SESSION['user']['id'];
    } else {
        // If logged in as teacher or student, inherit the school's ID
        $school_id = $_SESSION['user']['school_id'];
    }

    if ($isLogged) {
        $schoolDetails = $this->model->getById($school_id);
        if (!$schoolDetails) {
        $_SESSION["error"] = "Fetching school info failed.";
        header("Location: $redirectBase");
        exit;
    }
    }


    // Extract domain part of the school's email (e.g. "schoola" from "schoola@mail.com")
    if ($_SESSION['user']['role']==='student' || $_SESSION['user']['role']==='teacher') {
        if (empty($email) || $email===null) {
            $_SESSION['error']='Admission number required!';
            header("Location:$redirectBase");
            exit;
        }else {
            $schoolEmail = $schoolDetails['email'];
            $adm = explode('@', $schoolEmail)[0]; // before the '@'
            $email = $email . '@' . $adm;
        }
    }

    // Password handling
    if ($isLogged) {
        // Logged in users (schools, teachers) => auto password = email
        $password  = $email;
        $password2 = $email;
    } else {
        // Public registration
        $password  = $_POST["password"] ?? null;
        $password2 = $_POST["password2"] ?? null;
    }

    // Validation
    if (empty($name) || empty($email) || empty($password) || empty($role)) {
        $_SESSION["error"] = "All fields are required.".$role;
        $redirect = $isLogged ? $redirectBase : "/pendahesabu/register";
        header("Location: $redirect");
        exit;
    }

    if (!$isLogged && $password !== $password2) {
        $_SESSION["error"] = "Passwords do not match.";
        header("Location: /pendahesabu/register");
        exit();
    }

    // Create the new user
    $userCreated = $this->model->createUser($name, $email, $password, $role, $school_id);

    if ($userCreated) {
        $_SESSION["success"] = ucfirst($role) . " registered successfully.";
    } else {
        $_SESSION["error"] = "Failed to register $role.";
    }

    // Redirect appropriately
    if ($isLogged) {
        header("Location: $redirectBase");
    } else {
        header("Location: /pendahesabu/register");
    }
    exit;
}


    // login function
    public function login()
    {
        $email=strtolower(trim($_POST["email"]));
        $password=$_POST["password"];

        if (empty($email) || empty($password)) {
            $_SESSION["error"] ="All fields are required!";
            header("location:/pendahesabu/login");
            exit();
        }

        $user=$this->model->getUserByEmail($email);
        if ($user === null) {
            $_SESSION["error"] ="User not found!";
            header("location:/pendahesabu/register");
            exit();
        }

        if (password_verify($password,$user['password'])) {
            if($user['status']==='pending' && $user['role']==='school'){
                $_SESSION["error"] = "Successful Login. Account not yet approved!";
           	 	header("location:/pendahesabu/login");
            	exit();
            }
            $_SESSION['success'] = 'Login successful!';
            $_SESSION['user']= $user;
            $_SESSION['token']= bin2hex(random_bytes(16));
            header("location:/pendahesabu/".$user['role']."/");
            exit();
        }else {
            $_SESSION["error"] = "Incorrect password!";
            header("location:/pendahesabu/login");
            exit();
        }  
    }

    // logout function
    public function logout()
    {
        session_unset();
        session_destroy();
        session_start();
        $_SESSION["success"] = "Logged out successfully.";
        header("Location: /pendahesabu/login");
        exit();
    }

    // get user by id function
    public function edit($id)
    {
        $user=$this->model->getById($id);
        $role = $_SESSION['user']['role'] ?? null;
        if ($user === null) {
            $_SESSION["error"] = "User not found!";
            header("Location: /pendahesabu/$role"."s/");
            exit();
        }
        return $user;
    }



    // drop user function
    public function delete(){
        if ($_SERVER['REQUEST_METHOD']==='POST') {
            $id=trim($_POST["user_id"]);
        if (empty($id)) {
            $_SESSION["error"] = "User ID is required!";
            $role = $_SESSION['user']['role'] ?? null;
            header("Location: /pendahesabu/{$role}/users");
            exit();
        }

        $response=$this->model->delete($id);
        if ($response > 0) {
            $_SESSION["success"] = "User deleted successfully.";
            $role = $_SESSION['user']['role'] ?? null;
            header("Location: /pendahesabu/{$role}/users");
            exit();
        } else {
            $_SESSION["error"] = "Delete failed. User may not exist.";
            $role = $_SESSION['user']['role'] ?? null;
            header("Location: /pendahesabu/{$role}/users");
            exit();
        }
        }  
    }

    // search users function
    public function search(){
        $term=trim($_POST["term"]);
        if (empty($term)) {
            $_SESSION["error"] = "Search term is required!";
            $role = $_SESSION['user']['role'] ?? null;
            header("Location: /pendahesabu/$role"."s/");
            exit();
        }
        $users=$this->model->search($term);
        if ($users === null) {
            $_SESSION["error"] = "No users found matching '$term'.";
            $role = $_SESSION['user']['role'] ?? null;
            header("Location: /pendahesabu/$role"."s/");
            exit();
        }
        return $users;
    }

    // settings function
    public function settings()
    {
        $userId = $_SESSION['user']['id'] ?? null;
        if ($userId === null) {
            $_SESSION["error"] = "User not logged in.";
            header("Location: /pendahesabu/login");
            exit();
        }

        $user = $this->model->getById($userId);
        if ($user === null) {
            $_SESSION["error"] = "User not found.";
            header("Location: /pendahesabu/login");
            exit();
        }
                require __DIR__ . '/../views/users/settings.php';
    }

    public function update() {
    $userId = $_SESSION['user']['id'] ?? null;
    if ($userId === null) {
        $_SESSION["error"] = "User not logged in.";
        header("Location: /pendahesabu/login");
        exit();
    }

    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? null;

    // 🔁 Corrected this line to avoid recursive call
    $results = $this->model->update($userId, $name, $email, $password);
    if ($results>0) {
        $_SESSION['success']='Update successfull!';
    }else {
        $_SESSION['error']='Update failed or no changes made.';
    }
    header("Location: /pendahesabu/users/settings");
}

 function sendOneSignalNotification($user_ids, $message, $comment_id) {
    $fields = array(
        'app_id' => "Y2973e886-8b40-46d9-b57f-2168d8469650",
        'include_external_user_ids' => $user_ids,
        'headings' => array("en" => "New Update"),
        'contents' => array("en" => $message),
        'url' => "http://localhost/pendahesabu/student/post/$comment_id" // URL to redirect on click
    );

    $fields = json_encode($fields);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://onesignal.com/api/v1/notifications");
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json; charset=utf-8',
        'Authorization: msif3jmfyep6ehhkc5jbqzmb5'
    ));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_POST, TRUE);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
    $response = curl_exec($ch);
    curl_close($ch);

    return $response;
}

}
