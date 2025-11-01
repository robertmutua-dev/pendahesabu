<?php
$model= __DIR__ ."/../models/Comments.php";
require_once $model;
class CommentsController
{
    private $model;
    public function __construct() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        $this->model = new Comments();
    }
    
    private function sendNotification($message) {
    $content = array("en" => $message);
    $fields = array(
        'app_id' => "2973e886-8b40-46d9-b57f-2168d8469650",
        'included_segments' => array('All'),
        'contents' => $content
    );
    $fields = json_encode($fields);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://onesignal.com/api/v1/notifications");
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json; charset=utf-8',
        'Authorization: Basic msif3jmfyep6ehhkc5jbqzmb5'
    ));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
    curl_setopt($ch, CURLOPT_POST, TRUE);

    $response = curl_exec($ch);
    curl_close($ch);

    return $response;
}

    // comment a post
    public function comment()
{

    $comment  = trim($_POST["comment"] ?? '');
    $post     = trim($_POST['post'] ?? '');
    $userDir  = $_SESSION['user']['role'] ?? 'user';
    $image_path = null;
    $commenter=$_POST['commenter'];

    // ✅ Check if a file was uploaded
    if (!empty($_FILES['file']['name'])) {
        // Max size: 5MB
        if ($_FILES['file']['size'] > 5 * 1024 * 1024) {
            $_SESSION["error"] = "File too large. Max 5MB allowed.";
            header("Location: /pendahesabu/{$userDir}/post/{$post}");
            exit();
        }

        // Allowed file types
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($_FILES['file']['type'], $allowed_types)) {
            $_SESSION["error"] = "Only JPG, PNG, GIF files are allowed.";
            header("Location: /pendahesabu/{$userDir}/post/{$post}");
            exit();
        }

        // Target directory
        $target_dir = __DIR__ . "/../../public/uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        // Generate unique filename to prevent overwrite
        $filename = time() . "_" . preg_replace("/[^a-zA-Z0-9\.\-_]/", "", basename($_FILES["file"]["name"]));
        $target_file = $target_dir . $filename;

        if (move_uploaded_file($_FILES["file"]["tmp_name"], $target_file)) {
            $image_path = "/uploads/" . $filename;
        } else {
            $_SESSION["error"] = "Sorry, there was an error uploading your file.";
            header("Location: /pendahesabu/{$userDir}/post/{$post}");
            exit();
        }
    }

    // ✅ Comment must not be empty
    if (empty($comment) && $image_path === null) {
        $_SESSION["error"] = "Comment cannot be empty.";
        header("Location: /pendahesabu/{$userDir}/post/{$post}");
        exit();
    }

    // ✅ Save comment via model
    $response = $this->model->post($post, $comment,$commenter, $image_path);
        $this->sendNotification("New post: " . substr($content, 0, 50));
    if ($response > 0) {
        $_SESSION['success'] = "Comment submitted successfully!";
    } else {
        $_SESSION['error'] = "Failed to submit comment.";
    }

    // ✅ Redirect back to the post page (not to comment() again)
    header("Location: /pendahesabu/{$userDir}/post/{$post}");
    exit();
}


    // Get comments
    public function show($postId)
    {
        $comments=$this->model->getComments($postId);
        return $comments;
    }

    // Delete comment and its associated image if any
    public function delete($commentId)
    {
        $userDir=$_SESSION['user']['role'];
        $comment = $this->model->getCommentById($commentId);
        if (!$comment) {
            $_SESSION['error'] = "Comment not found.".$commentId;
            header("Location: /pendahesabu/{$_SESSION['user']['role']}/posts");
            exit();
        }

        // Delete associated image if exists
        if (!empty($comment['image_path'])) {
            $imageFile = __DIR__ . "/../../public" . $comment['image_path'];
            if (file_exists($imageFile)) {
                unlink($imageFile);
            }
        }

        // Delete comment from database
        $response = $this->model->deleteComment($commentId);
        if ($response) {
            $_SESSION['success'] = "Comment deleted successfully.";
        } else {
            $_SESSION['error'] = "Failed to delete comment.";
        }

    //    redirect to the other comments of the post
    header("Location:/pendahesabu/{$userDir}/posts");
        exit();
    }
}