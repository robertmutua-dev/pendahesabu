<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student</title>
    <link rel="stylesheet" href="/pendahesabu/public/css/master.css">
    <link rel="stylesheet" href="/pendahesabu/public/css/alerts.css">
</head>
<body>
    <?php $role="student"; include_once __DIR__."/../includes/topbar.php";?>
    <?php require_once __DIR__."/../includes/comments.php";?>
    <?php include_once __DIR__."/../includes/bottombar.php";?>
    <script>
        function showForm() {
            document.getElementById('postForm').style.display='block';
            document.getElementById('postInput').style.display='none';
        }
        setInterval(() => {
            loction.reload();
        }, 1000);
    </script>
</body>
</html>