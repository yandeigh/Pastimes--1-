<?php

session_start();
require_once 'DBConn.php';


define('ADMIN_EMAIL',    'admin@pastimes.co.za');
define('ADMIN_PASSWORD_HASH', md5('admin123'));

$error       = '';
$stickyEmail = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password']   ?? '';

    $stickyEmail = htmlspecialchars($email);

    if (empty($email) || empty($password)) {
        $error = "Both fields are required.";
    } elseif ($email === ADMIN_EMAIL && md5($password) === ADMIN_PASSWORD_HASH) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_email']     = $email;
        header("Location: AdminDashboard.php");
        exit();
    } else {
        $error = "Invalid admin credentials.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Login – Pastimes</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<style>
  :root {
    --bg: #1C1C1C;
    --card: #272727;
    --rust: #C0533A;
    --rust-hover: #A8432C;
    --muted: #888;
    --border: #383838;
    --text: #F0EDE8;
  }
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body {
    font-family: 'DM Sans', sans-serif;
    background: var(--bg);
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1rem;
  }
  .card {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 2.5rem 2rem;
    width: 100%;
    max-width: 380px;
  }
  .badge {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    background: rgba(192,83,58,0.15);
    color: var(--rust);
    border: 1px solid rgba(192,83,58,0.3);
    border-radius: 20px;
    padding: 0.3rem 0.8rem;
    font-size: 0.78rem;
    font-weight: 500;
    margin-bottom: 1.2rem;
    letter-spacing: 0.06em;
    text-transform: uppercase;
  }
  h1 {
    font-family: 'Playfair Display', serif;
    font-size: 1.5rem;
    color: var(--text);
    margin-bottom: 0.3rem;
  }
  .subtitle { color: var(--muted); font-size: 0.88rem; margin-bottom: 1.8rem; }
  .alert-error {
    background: rgba(192,83,58,0.12);
    color: #F08070;
    border: 1px solid rgba(192,83,58,0.3);
    padding: 0.8rem 1rem;
    border-radius: 8px;
    font-size: 0.88rem;
    margin-bottom: 1.2rem;
  }
  .field { margin-bottom: 1rem; }
  label { display: block; font-size: 0.82rem; color: var(--muted); margin-bottom: 0.4rem; }
  input {
    width: 100%;
    padding: 0.65rem 0.9rem;
    background: #1C1C1C;
    border: 1px solid var(--border);
    border-radius: 7px;
    font-family: 'DM Sans', sans-serif;
    font-size: 0.92rem;
    color: var(--text);
    outline: none;
    transition: border-color 0.2s;
  }
  input:focus { border-color: var(--rust); }
  .btn {
    width: 100%;
    padding: 0.75rem;
    background: var(--rust);
    color: #fff;
    border: none;
    border-radius: 7px;
    font-family: 'DM Sans', sans-serif;
    font-size: 0.95rem;
    font-weight: 500;
    cursor: pointer;
    transition: background 0.2s;
    margin-top: 0.5rem;
  }
  .btn:hover { background: var(--rust-hover); }
  .back { display: block; text-align: center; margin-top: 1.2rem; color: var(--muted); font-size: 0.85rem; text-decoration: none; }
  .back:hover { color: var(--text); }

  /* Demo hint */
  .hint {
    background: #1a1a1a;
    border: 1px solid #333;
    border-radius: 8px;
    padding: 0.75rem 1rem;
    font-size: 0.78rem;
    color: var(--muted);
    margin-bottom: 1.4rem;
    line-height: 1.6;
  }
  .hint code { color: #88BB88; }
</style>
</head>
<body>
<div class="card">
  <span class="badge">🔐 Admin Access</span>
  <h1>Admin Portal</h1>
  <p class="subtitle">Restricted area – authorised personnel only</p>

  <div class="hint">
    Demo credentials:<br>
    Email: <code>admin@pastimes.co.za</code><br>
    Password: <code>admin123</code>
  </div>

  <?php if ($error): ?>
    <div class="alert-error"><?= $error ?></div>
  <?php endif; ?>

  <form method="POST" novalidate>
    <div class="field">
      <label>Admin Email</label>
      <input type="email" name="email" value="<?= $stickyEmail ?>" placeholder="admin@pastimes.co.za" required>
    </div>
    <div class="field">
      <label>Password</label>
      <input type="password" name="password" placeholder="••••••••" required>
    </div>
    <button type="submit" class="btn">Login as Admin</button>
  </form>
  <a class="back" href="login.php">← Back to user login</a>
</div>
</body>
</html>