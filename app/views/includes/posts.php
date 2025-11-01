<!-- Post form section -->
     <div class="post-box">
        <textarea name="postInput" id="postInput" placeholder="Share with us ..." onclick="showForm()"></textarea>

        <form action="/pendahesabu/<?php echo $_SESSION['user']['role']?>/create-post" id="postForm" method="post"  enctype="multipart/form-data" style="display:none">
            <textarea name="content" id="post_text" placeholder="Write something..." required ></textarea>
            <input type="file" name="file" id="file" accept="image/*">
            <button type="submit">Post</button>
        </form>
        <?php require_once __DIR__."/../../../public/alerts.php";?>
     </div>

     <!-- Recent posts -->
      <div class="posts">
        <?php if($posts->index() !==null && !empty($posts->index())):?>
            <?php foreach ($posts->index() as $key => $post):?>
                <div class="post">
                    <p><strong><?php echo $post['username'];?></strong></p>
                    <p><?php echo $post['post'];?></p>
                    <?php if ($post['image_path']!==null):?>
                        <img src="/pendahesabu/public<?php echo $post['image_path'];?>" alt="photo">
                    <?php endif;?>
                    <span class="timestamp">Last updated: <?php echo $post['timeDiff']." ".$post['unit']." ago.";?></span>
                    <a href="<?php echo "/pendahesabu/{$_SESSION['user']['role']}/post/".$post['id'];?>">View Comments</a>
                    <?php if($post['user_id']===$_SESSION['user']['id'] || $_SESSION['user']['role']==='school'):?>
                    <a href="<?php echo "/pendahesabu/{$_SESSION['user']['role']}/post/delete/".$post['id'];?>" class="delete-btn" id="confirmBtn">Delete Post</a>
                    <?php endif;?>
                </div>
            <?php endforeach;?>
        <?php else:?>
        <div class="post">
            <p><strong>No Posts Yet</strong></p>
        </div>
        <?php endif;?>
      </div>