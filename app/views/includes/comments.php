<!-- Post form section -->
<div class="post-box">
    <!-- A "fake" textarea to trigger form reveal -->
    <textarea name="postInput" id="postInput" placeholder="Share your comment ..." onclick="showForm()"></textarea>

    <!-- Actual comment form -->
    <form action="/pendahesabu/<?php echo $_SESSION['user']['role']?>/post/comment/send" 
          id="postForm" 
          method="post"  
          enctype="multipart/form-data" 
          style="display:none">

        <!-- Comment text -->
        <textarea name="comment" id="post_text" placeholder="Write something..." required></textarea>
        
        <!-- Hidden post id and commente id -->
        <input type="hidden" name="post" value="<?php echo $post['id']; ?>">
        <input type="hidden" name="commenter" value="<?php echo $_SESSION['user']['id'];?>">

        <!-- Optional image -->
        <input type="file" name="file" id="file" accept="image/*">

        <button type="submit">Comment</button>
    </form>

    <?php require_once __DIR__."/../../../public/alerts.php"; ?>
</div>


<!-- Post and Comments container -->
<div class="posts">

    <!-- Main Post -->
    <div class="post">
        <p><?php echo $post['post'];?></p>
    </div>

    <!-- Comments -->
    <?php if (!empty($comments)): ?>
        <?php foreach ($comments as $comment): ?>
            <div class="post">
                <p><strong><?php echo htmlspecialchars($comment['name'] ?? 'Guest'); ?></strong></p>
                <p><?php echo htmlspecialchars($comment['comment']); ?></p>

                <?php if (!empty($comment['image_path'])): ?>
                    <img src="/pendahesabu/public<?php echo $comment['image_path']; ?>" alt="photo">
                <?php endif; ?>

                <span class="timestamp">
                    Commented <?php echo $comment['timeDiff'] . " " . $comment['unit']; ?> ago.
                </span>

                <?php if($comment['userid'] === $_SESSION['user']['id']): ?>
                    <a href="/pendahesabu/<?php echo $_SESSION['user']['role'] ?>/comment/delete/<?php echo $comment['id']; ?>" class="deleteBtn" id="confirmBtn">Delete Comment</a>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="post">
            <p><strong>No comments Yet</strong></p>
        </div>
    <?php endif; ?>

</div> 