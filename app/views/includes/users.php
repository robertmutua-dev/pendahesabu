<div class="container">
  <?php $isSchool = $_SESSION['user']['role'] === 'school'; ?>

  <h3>Registered Users</h3>

  <!-- Add User Form -->
  <form method="POST" action="/pendahesabu/school/new-user">
    <input type="text" name="name" placeholder="Full Name">
    <input type="text" name="email" placeholder="<?= $isSchool ? 'Email Address' : 'Admission Number' ?>">
    <input type="text" name="role" id="role" value="<?= $isSchool ? 'teacher' : 'student' ?>" readonly>
    <input type="hidden" name="school" value="<?= htmlspecialchars($_SESSION['user']['id']) ?>">
    <button type="submit" class="btn btn-facebook">Add</button>
  </form>

  <?php require_once __DIR__ . "/../../../public/alerts.php"; ?>

  <!-- Users Table -->
  <div class="table-responsive">
    <table>
      <thead>
        <tr>
          <th>#</th>
          <th>Full Name</th>
          <th>Email Address</th>
          <th>Password</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($usersList)): ?>
          <?php foreach ($usersList as $index => $user): ?>
            <tr>
              <td><?= $index + 1 ?></td>
              <td><?= htmlspecialchars($user['name']) ?></td>
              <td><?= htmlspecialchars($user['email']) ?></td>
              <td>
                <?php if ($user['password'] !== null || !empty($user['password'])): ?>
                  ••••••••
                <?php else: ?>
                  No Password
                <?php endif; ?>
              </td>
              <td>
                <form method="POST" action="/pendahesabu/users/delete">
                  <input type="hidden" name="user_id" value="<?= htmlspecialchars($user['id']) ?>">
                  <button type="submit" class="btn btn-danger btn-sm" id="confirmBtn"
                  <?php if ($user['role']==='student') {
                    echo 'Disabled';
                  }?>
                  >Delete</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="5" style="text-align:center;">No users registered.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
