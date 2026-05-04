<?php

session_start();
require_once 'DBConn.php';


if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header("Location: Adminlogin.php");
    exit();
}

$message = '';
$error   = '';


if (isset($_POST['action']) && $_POST['action'] === 'verify') {
     $uid = (int) $_POST['user_id'];
    $sql = "UPDATE tbluser SET Status = 'verified' WHERE UserID = $uid";
    if (mysqli_query($conn, $sql)) {
        $message = "User verified successfully.";
    } else {
        $error = "Failed to verify user.";
    }
}


if (isset($_POST['action']) && $_POST['action'] === 'delete') {
    $uid = (int) $_POST['user_id'];
    $sql = "DELETE FROM tbluser WHERE UserID = $uid";
    if (mysqli_query($conn, $sql)) {
        $message = "User deleted.";
    } else {
        $error = "Failed to delete user.";
    }
}


if (isset($_POST['action']) && $_POST['action'] === 'add') {
    $name  = mysqli_real_escape_string($conn, trim($_POST['new_name']  ?? ''));
    $email = mysqli_real_escape_string($conn, trim($_POST['new_email'] ?? ''));
    $pass  = trim($_POST['new_password'] ?? '');

    if ($name && $email && $pass) {
        $hash = md5($pass);
        $sql  = "INSERT INTO tbluser (FullName, Email, PasswordHash, Status)
                 VALUES ('$name', '$email', '$hash', 'verified')";
        if (mysqli_query($conn, $sql)) {
            $message = "User '$name' added and verified.";
        } else {
            $error = "Could not add user: " . mysqli_error($conn);
        }
    } else {
        $error = "All fields are required to add a user.";
    }
}


if (isset($_POST['action']) && $_POST['action'] === 'update') {
   $uid = (int) $_POST['user_id'];
    $newName = mysqli_real_escape_string($conn, trim($_POST['new_name'] ?? ''));
    if ($newName) {
        $sql = "UPDATE tbluser SET FullName = '$newName' WHERE UserID = $uid";
        mysqli_query($conn, $sql);
        $message = "Name updated.";
    }
}

// ── Load all users ────────────────────────────────────────────────────────────
$users  = [];
$result = mysqli_query($conn, "SELECT * FROM tbluser ORDER BY Status ASC, CreatedAt DESC");
while ($row = mysqli_fetch_assoc($result)) {
    $users[] = $row;
}

$pendingCount  = count(array_filter($users, fn($u) => $u['Status'] === 'pending'));
$verifiedCount = count($users) - $pendingCount;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard – Pastimes</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<style>
  :root {
    --cream: #F7F3EE;
    --charcoal: #1C1C1C;
    --rust: #C0533A;
    --rust-hover: #A8432C;
    --muted: #7A7065;
    --border: #E2DDD7;
    --card-bg: #FFFFFF;
  }
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: 'DM Sans', sans-serif; background: var(--cream); min-height: 100vh; }

  nav {
    background: var(--charcoal);
    padding: 0 2rem;
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: space-between;
  }
  .logo { font-family: 'Playfair Display', serif; font-size: 1.4rem; color: #fff; text-decoration: none; }
  nav a { color: #aaa; text-decoration: none; font-size: 0.88rem; }
  nav a:hover { color: #fff; }

  .admin-badge {
    background: rgba(192,83,58,0.2);
    color: var(--rust);
    border: 1px solid rgba(192,83,58,0.4);
    border-radius: 20px;
    padding: 0.2rem 0.7rem;
    font-size: 0.75rem;
    font-weight: 500;
  }

  main { max-width: 1000px; margin: 0 auto; padding: 2rem 1rem; }

  h1 {
    font-family: 'Playfair Display', serif;
    font-size: 1.8rem;
    color: var(--charcoal);
    margin-bottom: 0.3rem;
  }
  .sub { color: var(--muted); font-size: 0.88rem; margin-bottom: 2rem; }

  
  .stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
    gap: 1rem;
    margin-bottom: 2rem;
  }
  .stat {
    background: var(--card-bg);
    border: 1px solid var(--border);
    border-radius: 10px;
    padding: 1.2rem 1.5rem;
  }
  .stat-num { font-size: 2rem; font-weight: 700; color: var(--charcoal); }
  .stat-label { font-size: 0.82rem; color: var(--muted); margin-top: 0.2rem; }

  /* ── Alerts ── */
  .alert {
    padding: 0.8rem 1rem;
    border-radius: 8px;
    font-size: 0.88rem;
    margin-bottom: 1.2rem;
  }
  .alert-success { background: #E8F5E9; color: #2E7D32; border: 1px solid #C8E6C9; }
  .alert-error   { background: #FDF0ED; color: var(--rust);  border: 1px solid #F0C4BA; }

  /* ── Add user form ── */
  .add-card {
    background: var(--card-bg);
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 1.5rem;
    margin-bottom: 2rem;
  }
  .add-card h2 {
    font-size: 1rem;
    font-weight: 600;
    color: var(--charcoal);
    margin-bottom: 1rem;
  }
  .add-row { display: flex; gap: 0.7rem; flex-wrap: wrap; }
  .add-row input {
    flex: 1;
    min-width: 150px;
    padding: 0.6rem 0.85rem;
    border: 1px solid var(--border);
    border-radius: 7px;
    font-family: 'DM Sans', sans-serif;
    font-size: 0.88rem;
    color: var(--charcoal);
    background: var(--cream);
    outline: none;
  }
  .add-row input:focus { border-color: var(--rust); }

  /* ── Users table ── */
  .table-card {
    background: var(--card-bg);
    border: 1px solid var(--border);
    border-radius: 12px;
    overflow: hidden;
  }
  .table-card-header {
    padding: 1rem 1.5rem;
    border-bottom: 1px solid var(--border);
    display: flex;
    align-items: center;
    justify-content: space-between;
  }
  .table-card-header h2 { font-size: 1rem; font-weight: 600; color: var(--charcoal); }

  table { width: 100%; border-collapse: collapse; }
  th {
    padding: 0.75rem 1.2rem;
    text-align: left;
    font-size: 0.78rem;
    font-weight: 500;
    color: var(--muted);
    background: #FAFAF8;
    border-bottom: 1px solid var(--border);
    text-transform: uppercase;
    letter-spacing: 0.06em;
  }
  td {
    padding: 0.85rem 1.2rem;
    font-size: 0.88rem;
    color: var(--charcoal);
    border-bottom: 1px solid var(--border);
    vertical-align: middle;
  }
  tr:last-child td { border-bottom: none; }
  tr:hover td { background: #FAFAF8; }

  .badge {
    display: inline-block;
    padding: 0.18rem 0.55rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 500;
  }
  .badge-verified { background: #E8F5E9; color: #2E7D32; }
  .badge-pending  { background: #FFF8E1; color: #F57F17; }

  /* Action buttons */
  .action-group { display: flex; gap: 0.4rem; align-items: center; }
  .btn-sm {
    padding: 0.3rem 0.7rem;
    border-radius: 6px;
    font-size: 0.78rem;
    font-family: 'DM Sans', sans-serif;
    font-weight: 500;
    cursor: pointer;
    border: none;
    transition: background 0.2s;
  }
  .btn-verify  { background: #E8F5E9; color: #2E7D32; }
  .btn-verify:hover { background: #C8E6C9; }
  .btn-delete  { background: #FDF0ED; color: var(--rust); }
  .btn-delete:hover { background: #F8D5CC; }
  .btn-update  { background: #EEF2FF; color: #3730A3; }
  .btn-update:hover { background: #DDE3FF; }

  .btn-add {
    padding: 0.6rem 1.2rem;
    background: var(--rust);
    color: #fff;
    border: none;
    border-radius: 7px;
    font-family: 'DM Sans', sans-serif;
    font-size: 0.88rem;
    font-weight: 500;
    cursor: pointer;
    white-space: nowrap;
  }
  .btn-add:hover { background: var(--rust-hover); }

  .edit-name {
    padding: 0.3rem 0.6rem;
    border: 1px solid var(--border);
    border-radius: 5px;
    font-size: 0.85rem;
    font-family: 'DM Sans', sans-serif;
    width: 140px;
    outline: none;
    background: var(--cream);
  }
  .edit-name:focus { border-color: var(--rust); }
</style>
</head>
<body>

<nav>
  <a class="logo" href="index.php">Pastimes</a>
  <div style="display:flex;gap:1.5rem;align-items:center;">
    <span class="admin-badge">Admin</span>
    <a href="Adminlogout.php">Logout</a>
  </div>
</nav>

<main>
  <h1>Admin Dashboard</h1>
  <p class="sub">Manage users, products, and orders.</p>

  <?php if ($message): ?><div class="alert alert-success">✔ <?= $message ?></div><?php endif; ?>
  <?php if ($error):   ?><div class="alert alert-error">✖ <?= $error ?></div><?php endif; ?>

  <!-- Stats -->
  <div class="stats">
    <div class="stat">
      <div class="stat-num"><?= count($users) ?></div>
      <div class="stat-label">Total Users</div>
    </div>
    <div class="stat">
      <div class="stat-num" style="color:#C0533A"><?= $pendingCount ?></div>
      <div class="stat-label">Pending Verification</div>
    </div>
    <div class="stat">
      <div class="stat-num" style="color:#2E7D32"><?= $verifiedCount ?></div>
      <div class="stat-label">Verified Users</div>
    </div>
  </div>

  <!-- Add User -->
  <div class="add-card">
    <h2>➕ Add New Customer</h2>
    <form method="POST">
      <input type="hidden" name="action" value="add">
      <div class="add-row">
        <input type="text"     name="new_name"     placeholder="Full Name"     required>
        <input type="email"    name="new_email"    placeholder="Email"         required>
        <input type="password" name="new_password" placeholder="Password"      required>
        <button type="submit" class="btn-add">Add & Verify</button>
      </div>
    </form>
  </div>

  <!-- Users table -->
  <div class="table-card">
    <div class="table-card-header">
      <h2>All Users</h2>
      <span style="font-size:0.82rem;color:var(--muted)"><?= count($users) ?> records</span>
    </div>
    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Name</th>
          <th>Email</th>
          <th>Status</th>
          <th>Joined</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($users as $u): ?>
        <tr>
          <td><?= $u['UserID'] ?></td>
          <td><?= htmlspecialchars($u['FullName']) ?></td>
          <td><?= htmlspecialchars($u['Email']) ?></td>
          <td>
            <?php if ($u['Status'] === 'verified'): ?>
              <span class="badge badge-verified">✔ Verified</span>
            <?php else: ?>
              <span class="badge badge-pending">⏳ Pending</span>
            <?php endif; ?>
          </td>
          <td><?= date('d M Y', strtotime($u['CreatedAt'])) ?></td>
          <td>
            <div class="action-group">
              <!-- Update name inline -->
              <form method="POST" style="display:flex;gap:4px">
                <input type="hidden" name="action"  value="update">
                <input type="hidden" name="user_id" value="<?= $u['UserID'] ?>">
                <input type="text" name="new_name" class="edit-name"
                       value="<?= htmlspecialchars($u['FullName']) ?>">
                <button type="submit" class="btn-sm btn-update">Save</button>
              </form>

              <!-- Verify (only show if pending) -->
              <?php if ($u['Status'] === 'pending'): ?>
              <form method="POST">
                <input type="hidden" name="action"  value="verify">
                <input type="hidden" name="user_id" value="<?= $u['UserID'] ?>">
                <button type="submit" class="btn-sm btn-verify">✔ Verify</button>
              </form>
              <?php endif; ?>

              <!-- Delete -->
              <form method="POST" onsubmit="return confirm('Delete this user?')">
                <input type="hidden" name="action"  value="delete">
                <input type="hidden" name="user_id" value="<?= $u['UserID'] ?>">
                <button type="submit" class="btn-sm btn-delete">✖ Delete</button>
              </form>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</main>
</body>
</html>