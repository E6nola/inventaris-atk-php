<?php
session_start();

if (isset($_COOKIE['remember_username'])) {
    $saved_username = htmlspecialchars($_COOKIE['remember_username']);
} else {
    $saved_username = '';
}

if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Inventaris ATK</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #0f0f13 0%, #1a1a2e 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 1rem;
        }

        .login-container {
            max-width: 420px;
            width: 100%;
        }

        .login-card {
            background: #1e1e2a;
            border-radius: 32px;
            padding: 2.5rem;
            box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5);
            border: 1px solid rgba(255,255,255,0.05);
            animation: fadeIn 0.5s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .login-header .icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }

        .login-header h1 {
            font-size: 1.75rem;
            font-weight: 600;
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            margin-bottom: 0.5rem;
        }

        .login-header p {
            color: #a0a0b0;
            font-size: 0.875rem;
        }

        .form-group {
            margin-bottom: 1.25rem;
        }

        .form-group label {
            display: block;
            font-weight: 500;
            margin-bottom: 0.5rem;
            color: #c0c0d0;
            font-size: 0.875rem;
        }

        .form-group input {
            width: 100%;
            padding: 0.875rem 1rem;
            border: 1.5px solid #2a2a35;
            border-radius: 16px;
            font-size: 0.9rem;
            transition: all 0.2s;
            background: #252530;
            color: #e5e5e5;
            font-family: inherit;
        }

        .form-group input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102,126,234,0.15);
        }

        .form-group input::placeholder {
            color: #6b6b7a;
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
        }

        .checkbox-group input {
            width: 18px;
            height: 18px;
            cursor: pointer;
            accent-color: #667eea;
        }

        .checkbox-group label {
            margin-bottom: 0;
            cursor: pointer;
            font-size: 0.875rem;
            color: #a0a0b0;
        }

        .btn-login {
            width: 100%;
            padding: 0.875rem;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            border-radius: 16px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            font-family: inherit;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px -5px rgba(102,126,234,0.4);
        }

        .alert {
            padding: 0.875rem 1rem;
            border-radius: 16px;
            margin-bottom: 1.5rem;
            font-size: 0.875rem;
        }

        .alert-danger {
            background: #3a1a1a;
            color: #e74c3c;
            border: 1px solid #5a2a2a;
        }

        .login-info {
            text-align: center;
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid #2a2a35;
            font-size: 0.75rem;
            color: #6b6b7a;
        }

        .login-info strong {
            color: #a0a0b0;
        }

        .login-info code {
            background: #2a2a35;
            padding: 0.25rem 0.5rem;
            border-radius: 8px;
            font-family: monospace;
            color: #667eea;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <div class="icon">📦</div>
                <h1>Inventaris ATK</h1>
                <p>Silakan login untuk melanjutkan</p>
            </div>

            <?php if(isset($_SESSION['login_error'])): ?>
                <div class="alert alert-danger">
                    <?= htmlspecialchars($_SESSION['login_error']) ?>
                    <?php unset($_SESSION['login_error']); ?>
                </div>
            <?php endif; ?>

            <?php if(isset($_GET['expired'])): ?>
                <div class="alert alert-danger">
                    ⏰ Session Anda telah habis (1 hari). Silakan login kembali.
                </div>
            <?php endif; ?>

            <form action="proses_login.php" method="POST">
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" required autocomplete="off" value="<?= $saved_username ?>" placeholder="Masukkan username">
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" required placeholder="Masukkan password">
                </div>
                <div class="checkbox-group">
                    <input type="checkbox" name="remember" id="remember" <?= $saved_username ? 'checked' : '' ?>>
                    <label for="remember">Ingat username saya</label>
                </div>
                <button type="submit" name="login" class="btn-login">🔐 Login</button>
            </form>

            <div class="login-info">
                <strong>Info:</strong><br>
                Session berlaku 1 hari, setiap hari harus login ulang
            </div>
        </div>
    </div>
</body>
</html>