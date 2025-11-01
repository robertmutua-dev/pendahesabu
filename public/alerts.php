<?php if(isset($_SESSION["error"])): ?>
    <div class="message error">
        <?php 
            echo $_SESSION["error"]; 
            unset($_SESSION["error"]); // Clear the error after displaying it
        ?>
    </div>
<?php elseif(isset($_SESSION["success"])): ?>
    <div class="message success">
        <?php 
            echo $_SESSION["success"]; 
            unset($_SESSION["success"]); // Clear the success message after displaying it
        ?>
    </div>
<?php else: ?>
    <!-- No messages to display -->
<?php endif; ?>
