<?php
session_start();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    $adminPassword = 'admin123';
    if ($password === $adminPassword) {
        $_SESSION['admin_logged_in'] = true;
        header('Location: index.php');
        exit;
    } else {
        $error = 'Falsches Passwort';
    }
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>3D Oberberg Admin Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@700&family=Plus+Jakarta+Sans:wght@400;600&display=swap" rel="stylesheet">
    <style>
        :root { --primary: #8B5CF6; --bg: #FBF8F2; --surface: #FFFFFF; --ink: #1E1B14; --border: rgba(30,27,20,0.09); --r: 8px; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: var(--bg); color: var(--ink); margin: 0; padding: 0; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
        .login-container { background: var(--surface); border-radius: var(--r); padding: 40px; max-width: 400px; width: 90%; border: 1px solid var(--border); text-align: center; }
        .login-logo { font-family: 'Fraunces', sans-serif; font-weight: 900; font-size: 1.5rem; color: var(--ink); margin-bottom: 30px; }
        .login-logo img { height: 40px; width: auto; margin-right: 10px; }
        .login-form { display: flex; flex-direction: column; gap: 20px; }
        .form-group { text-align: left; }
        .form-group label { display: block; font-size: 0.9rem; font-weight: 600; color: #6B6455; margin-bottom: 6px; }
        .form-group input { width: 100%; padding: 12px 15px; border: 1px solid var(--border); border-radius: var(--r); font-size: 1rem; font-family: inherit; background: var(--bg); }
        .form-group input:focus { outline: none; border-color: var(--primary); }
        .btn { padding: 12px 24px; background: var(--primary); color: white; border: none; border-radius: var(--r); cursor: pointer; font-size: 1rem; font-weight: 600; font-family: inherit; transition: all 0.2s; margin-top: 10px; }
        .btn:hover { background: #7C3AED; }
        .error { color: #EF4444; font-size: 0.9rem; margin-top: 10px; }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-logo">
            <img src="../3D%20Oberberg_Logo_kl.png" alt="3D Oberberg Logo">
            <span>Admin-Panel</span>
        </div>
        <form class="login-form" method="POST">
            <div class="form-group">
                <label for="password">Passwort</label>
                <input type="password" id="password" name="password" required>
            </div>
            <?php if (isset($error)): ?>
                <div class="error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <button type="submit" class="btn">Anmelden</button>
        </form>
    </div>
</body>
</html>