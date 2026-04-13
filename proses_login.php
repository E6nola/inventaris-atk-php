<?php
session_start();

if (!isset($_POST['login'])) {
    header("Location: login.php");
    exit;
}

$username = trim($_POST['username']);
$password = trim($_POST['password']);
$remember = isset($_POST['remember']) ? true : false;

if ($username === 'admin' && $password === 'admin') {
    $_SESSION['logged_in'] = true;
    $_SESSION['username'] = 'admin';
    $_SESSION['login_time'] = time();
    
    ini_set('session.gc_maxlifetime', 86400);
    session_set_cookie_params(86400);
    
    if ($remember) {
        setcookie('remember_username', $username, time() + (30 * 24 * 3600), "/");
    } else {
        if (isset($_COOKIE['remember_username'])) {
            setcookie('remember_username', '', time() - 3600, "/");
        }
    }
    
    header("Location: index.php");
    exit;
} else {
    $_SESSION['login_error'] = "Username atau password salah!";
    header("Location: login.php");
    exit;
}
?>