<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Penda Hesabu - Register</title>
  <link rel="stylesheet" href="/pendahesabu/public/css/auth.css">
  <link rel="stylesheet" href="/pendahesabu/public/css/alerts.css">
</head>
<body>
  <div class="auth-container">
    <h1>Penda Hesabu</h1>
    <h2>Create a new account</h2>
    <form id="registerForm" method="post" action="/pendahesabu/signup">
      <input type="text" name="name" placeholder="School Name" required>
      <input type="email" name="email" placeholder="School Email address" required>
      <input type="password" name="password" placeholder="Password" required>
      <input type="password" name="password2" placeholder="Confirm Password" required>
      <input type="hidden" name="role" value="school">
      <?php require_once __DIR__ . "/alerts.php";?>
      <button type="submit">Sign Up</button>
    </form>
    <div class="switch-link">
      <span>Already have an account?</span>
      <a href="/pendahesabu">Login</a>
    </div>
  </div>
</body>
</html>
