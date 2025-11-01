<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo strtoupper($user['role']);?></title>
    <link rel="stylesheet" href="/pendahesabu/public/css/master.css">
    <link rel="stylesheet" href="/pendahesabu/public/css/alerts.css">
    <link rel="stylesheet" href="/pendahesabu/public/css/forms.css">
    <link rel="stylesheet" href="/pendahesabu/public/css/settings.css">
</head>
<body>
    <?php $role=$_SESSION['user']['role']; include_once __DIR__."/../includes/topbar.php";?>
    <h2>My Profile Settings</h2>
    <div class="settings-container">
    <form action="/pendahesabu/users/update" method="POST" id="settingsForm">
        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required><br><br>

        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required><br><br>

        <input type="password" id="password" name="password"><br><br>

        <button type="submit">Update Settings</button>
        <?php require_once __DIR__."/../../../public/alerts.php";?>
    </form>
    </div>
    
    <?php include_once __DIR__."/../includes/bottombar.php";?>
</body>
</html>