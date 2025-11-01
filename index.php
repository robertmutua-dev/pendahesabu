
    <?php
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(E_ALL);

$request = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$request = trim($request, '/');

$base = 'pendahesabu';

// ✅ Check if request starts EXACTLY with /pendahesabu or /pendahesabu/
if ($request === $base) {
    $request = ''; // home
} elseif (strpos($request, $base . '/') === 0) {
    $request = substr($request, strlen($base) + 1); // remove "pendahesabu/"
} else {
    // ❌ Not matching base path → 404
    http_response_code(404);
    require __DIR__ . '/public/404.php';
    exit;
}

// ✅ Switch on TRUE for dynamic routes
switch (true) {
    case $request === '':
    case $request === 'home':
    case $request === 'login':
        require __DIR__ . '/public/login.php';
        break;

    case $request === 'register':
        require __DIR__ . '/public/register.php';
        break;

    case $request === 'signup':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once __DIR__ . '/app/controllers/UsersController.php';
            $controller = new UsersController();
            $controller->create();
        } else {
            header("Location: /{$base}/register");
            exit;
        }
        break;

    case $request === 'authenticate':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once __DIR__ . '/app/controllers/UsersController.php';
            $controller = new UsersController();
            $controller->login();
        } else {
            header("Location:/{$base}/login");
            exit;
        }
        break;

    case $request === 'logout':
        require_once __DIR__."/app/controllers/UsersController.php";
        (new UsersController())->logout();
        break;

    case $request === 'school':
    case $request === 'school/posts':
        require_once __DIR__."/app/controllers/PostsController.php";
        $posts = new PostsController();
        $posts->index();
        require_once __DIR__."/app/views/schools/index.php";
        break;

    case $request === 'teacher':
    case $request === 'teacher/posts':
        require_once __DIR__."/app/controllers/PostsController.php";
        $posts = new PostsController();
        $posts->index();
        require_once __DIR__."/app/views/teachers/index.php";
        break;

         case $request === 'student':
    case $request === 'student/posts':
        require_once __DIR__."/app/controllers/PostsController.php";
        $posts = new PostsController();
        $posts->index();
        require_once __DIR__."/app/views/students/index.php";
        break;

    case $request === 'school/create-post':
        require_once __DIR__."/app/controllers/PostsController.php";
        (new PostsController())->create();
        break;

        case $request === 'teacher/create-post':
        require_once __DIR__."/app/controllers/PostsController.php";
        (new PostsController())->create();
        break;

        case $request === 'student/create-post':
        require_once __DIR__."/app/controllers/PostsController.php";
        (new PostsController())->create();
        break;

    // ✅ Dynamic route: /school/post/{id}
    case preg_match('#^school/post/(\d+)$#', $request, $matches):
        require_once __DIR__."/app/controllers/PostsController.php";
        $posts = new PostsController();
        $post  = $posts->getPost($matches[1]);
        require_once __DIR__."/app/controllers/CommentsController.php";
        $comments = new CommentsController();
        $comments=$comments->show($matches[1]);
        require_once __DIR__."/app/views/schools/comments.php";
        break;

        case preg_match('#^teacher/post/(\d+)$#', $request, $matches):
        require_once __DIR__."/app/controllers/PostsController.php";
        $posts = new PostsController();
        $post  = $posts->getPost($matches[1]);
        require_once __DIR__."/app/controllers/CommentsController.php";
        $comments = new CommentsController();
        $comments=$comments->show($matches[1]);
        require_once __DIR__."/app/views/teachers/comments.php";
        break;

        case preg_match('#^student/post/(\d+)$#', $request, $matches):
        require_once __DIR__."/app/controllers/PostsController.php";
        $posts = new PostsController();
        $post  = $posts->getPost($matches[1]);
        require_once __DIR__."/app/controllers/CommentsController.php";
        $comments = new CommentsController();
        $comments=$comments->show($matches[1]);
        require_once __DIR__."/app/views/students/comments.php";
        break;

    case preg_match('#^school/post/delete/(\d+)$#', $request, $matches):
        require_once __DIR__."/app/controllers/PostsController.php";
        (new PostsController())->delete($matches[1]);
        break;
        case preg_match('#^teacher/post/delete/(\d+)$#', $request, $matches):
        require_once __DIR__."/app/controllers/PostsController.php";
        (new PostsController())->delete($matches[1]);
        break;
        case preg_match('#^student/post/delete/(\d+)$#', $request, $matches):
        require_once __DIR__."/app/controllers/PostsController.php";
        (new PostsController())->delete($matches[1]);
        break;

        // delete comment
    case preg_match('#^school/comment/delete/(\d+)$#', $request, $matches):
        require_once __DIR__."/app/controllers/CommentsController.php";
        (new CommentsController())->delete($matches[1]);
        break;
        case preg_match('#^teacher/comment/delete/(\d+)$#', $request, $matches):
        require_once __DIR__."/app/controllers/CommentsController.php";
        (new CommentsController())->delete($matches[1]);
        break;
        case preg_match('#^student/comment/delete/(\d+)$#', $request, $matches):
        require_once __DIR__."/app/controllers/CommentsController.php";
        (new CommentsController())->delete($matches[1]);
        break;
    

    case $request === 'school/post/comment/send':
        require_once __DIR__."/app/controllers/CommentsController.php";
        (new CommentsController())->comment();
        break;

        case $request === 'teacher/post/comment/send':
        require_once __DIR__."/app/controllers/CommentsController.php";
        (new CommentsController())->comment();
        break;

        case $request === 'student/post/comment/send':
        require_once __DIR__."/app/controllers/CommentsController.php";
        (new CommentsController())->comment();
        break;

    case $request === 'school/users':
        require_once __DIR__."/app/controllers/UsersController.php";
        $users=new UsersController();
        $usersList=$users->index();
        require_once __DIR__."/app/views/schools/teachers.php";
        break;

        case $request === 'teacher/users':
        require_once __DIR__."/app/controllers/UsersController.php";
        $users=new UsersController();
        $usersList=$users->index();
        require_once __DIR__."/app/views/teachers/teachers.php";
        break;

        case $request === 'student/users':
        require_once __DIR__."/app/controllers/UsersController.php";
        $users=new UsersController();
        $usersList=$users->index();
        require_once __DIR__."/app/views/students/users.php";
        break;

    case $request === 'school/new-user':
        require_once __DIR__."/app/controllers/UsersController.php";
        (new UsersController())->create();
        break;
        case $request === 'teacher/new-user':
        require_once __DIR__."/app/controllers/UsersController.php";
        (new UsersController())->create();
        break;

    case $request==='users/settings':
        require_once __DIR__.'/app/controllers/UsersController.php';
        $users=new UsersController();
        $users->settings();
        break;

    case $request==='users/update':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once __DIR__.'/app/controllers/UsersController.php';
            $users=new UsersController();
            $users->update();
        } else {
            header("Location: /{$base}/users/settings");
            exit;
        }
        break;

    case $request==='users/delete':
        require_once __DIR__."/app/controllers/UsersController.php";
        $users=new UsersController();
        $users->delete();
        break;

    

    default:
        http_response_code(404);
        require __DIR__ . '/public/404.php';
        break;
}
?>
        