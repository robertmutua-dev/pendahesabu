<script src="https://cdn.onesignal.com/sdks/web/v16/OneSignalSDK.page.js" defer></script>
<script>
  window.OneSignalDeferred = window.OneSignalDeferred || [];
  OneSignalDeferred.push(async function(OneSignal) {
    await OneSignal.init({
      appId: "2973e886-8b40-46d9-b57f-2168d8469650",
      allowLocalhostAsSecureOrigin: true
    });

    <?php if(isset($_SESSION['user']['id'])): ?>
    // Set external user ID inside the initialized function
    OneSignal.setExternalUserId("<?php echo $_SESSION['user']['id']; ?>");
    <?php endif; ?>
  });
</script>

<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SESSION['user']['role'] !==$role) {
    $_SESSION['error']='You must be logged in as '.$role;
    header('Location:/pendahesabu/login');
    exit;
}
if (!isset($_SESSION['user'])) {
    $_SESSION['error']='You must be logged in';
    header('Location:/pendahesabu/login');
    exit;
}
$appName= $_SESSION['user']['name'] ?? 'School Name';?>

<div class="topbar">
    <div class="app-name"><?php echo $appName;?></div>
    <form action="/pendahesabu/logout" method="post" >
        <button type="submit" class="logout-btn" id="confirmBtn">Logout</button>
    </form>
</div>
<div class="main-content">