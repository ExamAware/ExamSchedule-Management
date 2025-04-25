<?php
session_start();
if (isset($_SESSION['user'])) {
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>管理员登录</title>
    <link rel="stylesheet" href="../assets/md2-blue.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <style>
        body { font-family: Roboto, Arial, sans-serif; background: #f5f7fa; margin: 0; }
        .navbar { background: #1976d2; color: #fff; padding: 16px 24px; display: flex; align-items: center; }
        .navbar .material-icons { margin-right: 8px; }
        .container { max-width: 400px; margin: 60px auto; background: #fff; border-radius: 8px; box-shadow: 0 2px 8px #0001; padding: 32px; }
        .md-btn { background: #1976d2; color: #fff; border: none; border-radius: 4px; padding: 10px 24px; font-size: 16px; cursor: pointer; }
        .md-btn:hover { background: #1565c0; }
        .input-group { margin-bottom: 24px; }
        label { display: block; margin-bottom: 8px; color: #1976d2; }
        input[type="text"], input[type="password"] { width: 100%; padding: 10px; border: 1px solid #b3c6e0; border-radius: 4px; font-size: 16px; }
    </style>
</head>
<body>
    <div class="navbar">
        <span class="material-icons">admin_panel_settings</span>
        管理员后台
    </div>
    <div class="container">
        <form method="post" action="login.php">
            <div class="input-group">
                <label for="username"><span class="material-icons" style="vertical-align:middle;">person</span> 用户名</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="input-group">
                <label for="password"><span class="material-icons" style="vertical-align:middle;">lock</span> 密码</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="md-btn">
                <span class="material-icons" style="vertical-align:middle;">login</span> 登录
            </button>
        </form>
    </div>
</body>
</html>
