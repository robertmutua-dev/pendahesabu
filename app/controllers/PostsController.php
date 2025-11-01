<?php
$model= __DIR__ ."/../models/Posts.php";
require_once $model;
require_once __DIR__."/../models/Users.php";
$userDir=$_SESSION['user']['role'] ?? '';

class PostsController
{
    private $model;
    private $users;
    public function __construct() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        $this->model = new Posts();
        $this->users=new Users();
    }

    
    
    
   
    // index function
    public function index() {
        if ($_SESSION['user']['role']==='school') {
            $school=$_SESSION['user']['id'];
        }else {
            $school=$_SESSION['user']['school_id'];
        }
        return $this->model->retrieve($school);
    }

    // create post function
public function create() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $userDir = $_SESSION['user']['role'] ?? 'user';
    $user_id = $_SESSION['user']['id'] ?? null;

    if (!$user_id) {
        $_SESSION["error"] = "You must be logged in to create a post.";
        header("Location: /pendahesabu/login");
        exit();
    }

    $content = trim($_POST["content"] ?? '');
    $file_path = null;

    // ✅ File upload check
    if (!empty($_FILES["file"]["name"])) {
        $file_size = $_FILES["file"]["size"];
        $file_type = $_FILES["file"]["type"];
        $file_tmp  = $_FILES["file"]["tmp_name"];
        $file_name = time() . "_" . preg_replace("/[^a-zA-Z0-9._-]/", "_", basename($_FILES["file"]["name"]));

        // 🔹 Size validation (5MB)
        if ($file_size > 5 * 1024 * 1024) {
            $_SESSION["error"] = "File size exceeds 5MB limit.";
            header("Location: /pendahesabu/{$userDir}");
            exit();
        }

        // 🔹 Type validation (images + PDF)
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];
        if (!in_array($file_type, $allowed_types)) {
            $_SESSION["error"] = "Only JPG, PNG, GIF images and PDF files are allowed.";
            header("Location: /pendahesabu/{$userDir}");
            exit();
        }

        // 🔹 Upload directory
        $target_dir = __DIR__ . "/../../public/uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0755, true);
        }

        // 🔹 Final path
        $target_file = $target_dir . $file_name;

        if (move_uploaded_file($file_tmp, $target_file)) {
            $file_path = "/uploads/" . $file_name;
        } else {
            $_SESSION["error"] = "Sorry, there was an error uploading your file.";
            header("Location: /pendahesabu/{$userDir}");
            exit();
        }
    }

    // ✅ Either text OR file must exist
    if (empty($content) && !$file_path) {
        $_SESSION["error"] = "Post cannot be empty. Please add text or upload a file.";
        header("Location: /pendahesabu/{$userDir}");
        exit();
    }

    // ✅ Save post
    $response = $this->model->postContent($user_id, $content, $file_path);
    if ($response > 0) {
        $post_id=$response;
        $school_id=$_SESSION['user']['role']==='school' ? $user_id : $_SESSION['user']['school_id'];
        $users=$this->users->notifyUsers($school_id,$user_id);
        $message=$_SESSION['user']['name']." posted a new update.";
        $this->sendOneSignalNotification($users,$message,$post_id);
        $_SESSION["success"] = "Post created successfully.";
        header("Location: /pendahesabu/{$userDir}/posts");
        exit();
    } else {
        $_SESSION["error"] = "Failed to create post.";
        header("Location: /pendahesabu/{$userDir}");
        exit();
    }
}


    // edit post function
    public function getPost($code) {
        global $userDir;
        // $post=$_POST['post'] ?? null;
        $post=$this->model->getById($code);
        if ($post === null) {
            $_SESSION["error"] = "Post not found!";
            header("Location: /pendahesabu/{$userDir}/posts");
            exit();
        }
        return $post;
    }

    // update post function
    public function update()
    {
        global $userDir;
        $post=$_POST["post"] ?? null;
        $content=trim($_POST["content"]);
        $image_path=$_POST["file"] ?? null;

        if (empty($content)) {
            $_SESSION["error"] = "Post content cannot be empty.";
            header("Location: /pendahesabu/{$userDir}/edit-post?post=$post");
            exit();
        }

        // check if a new file is uploaded, and check its size and type
        if (!empty($_FILES["file"]["name"])) {
            if ($_FILES["file"]["size"] > 5 * 1024 * 1024) { // 5MB limit
                $_SESSION["error"] = "File size exceeds 5MB limit.";
                header("Location: /pendahesabu/{$userDir}/edit-post?post=$post");
                exit();
            }
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];
            if (!in_array($_FILES['file']['type'], $allowed_types)) {
                $_SESSION["error"] = "Only JPG, PNG, GIF images and PDF files are allowed.";
                header("Location: /pendahesabu/{$userDir}/edit-post?post=$post");
                exit();
            }
            $target_dir = __DIR__ . "/../../public/uploads/";
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0755, true);
            }
            $target_file = $target_dir . basename($_FILES["file"]["name"]);
            if (move_uploaded_file($_FILES["file"]["tmp_name"], $target_file)) {
                $image_path = "/uploads/" . basename($_FILES["file"]["name"]);
            } else {
                $_SESSION["error"] = "Sorry, there was an error uploading your file.";
                header("Location: /pendahesabu/{$userDir}/edit-post?post=$post");
                exit();
            }
        } else {
            // retain existing image path if no new file is uploaded
            $existing_post = $this->model->getById($post);
            $image_path = $existing_post['image_path'] ?? null;
        }
    }

    // delete post and its file if exists in server
    public function delete($post)
    {
        $userDir = $_SESSION['user']['role'];
        if (empty($post)) {
            $_SESSION["error"] = "Post ID is required!";
            header("Location: /pendahesabu/{$userDir}/posts");
            exit();
        }

        // fetch post to get image path
        $existing_post = $this->model->getById($post);
        if ($existing_post && !empty($existing_post['image_path'])) {
            $file_path = __DIR__ . "/../../public" . $existing_post['image_path'];
            if (file_exists($file_path)) {
                unlink($file_path); // delete the file
            }
        }

        $response=$this->model->deleteById($post);
        if ($response > 0) {
            $_SESSION["success"] = "Post deleted successfully.";
            header("Location: /pendahesabu/{$userDir}/posts");
            exit();
        } else {
            $_SESSION["error"] = "Failed to delete post.";
            header("Location: /pendahesabu/{$userDir}/posts");
            exit();
        }
    }

    private  function sendOneSignalNotification($user_ids, $message, $post_id) {
    $fields = array(
        'app_id' => "Y2973e886-8b40-46d9-b57f-2168d8469650",
        'include_external_user_ids' => $user_ids,
        'headings' => array("en" => "New Update"),
        'contents' => array("en" => $message),
        'url' => "http://localhost/pendahesabu/student/post/$post_id" // URL to redirect on click
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