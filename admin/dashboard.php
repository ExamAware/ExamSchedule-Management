<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}
$user = $_SESSION['user'];
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>后台管理</title>
    <link rel="stylesheet" href="../assets/md2-blue.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <style>
        body { font-family: Roboto, Arial, sans-serif; background: #f5f7fa; margin: 0; }
        .navbar { background: #1976d2; color: #fff; padding: 16px 24px; display: flex; align-items: center; position: relative; }
        .navbar .material-icons { margin-right: 8px; }
        .navbar .nav-title { display: flex; align-items: center; gap: 8px; }
        .home-btn {
            background: #fff;
            color: #1976d2 !important;
            border: 1px solid #1976d2;
            border-radius: 4px;
            padding: 6px 18px;
            font-size: 15px;
            font-weight: 500;
            cursor: pointer;
            margin-left: 24px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            text-decoration: none !important;
        }
        .home-btn:hover { background: #e3f0fc; }
        .container { max-width: 900px; margin: 48px auto; }
        .card-group { display: flex; gap: 32px; justify-content: center; margin-top: 64px; }
        .md2-card {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 8px #0001;
            padding: 36px 40px;
            min-width: 320px;
            display: flex;
            flex-direction: column;
            align-items: center;
            transition: box-shadow 0.2s;
        }
        .md2-card:hover { box-shadow: 0 6px 24px #1976d233; }
        .md-btn {
            background: #1976d2;
            color: #fff !important;
            border: none;
            border-radius: 4px;
            padding: 12px 28px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            margin-top: 18px;
            box-shadow: 0 2px 8px #1976d233;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: background 0.2s;
            text-decoration: none !important;
        }
        .md-btn:hover { background: #1565c0; }
        .logout { float: right; color: #fff; cursor: pointer; margin-left: 32px; text-decoration: none !important; }
        .user-info { margin-left: auto; }
        a, a:visited, a:active { text-decoration: none !important; color: inherit; }
        @media (max-width: 900px) {
            .container { max-width: 99vw; padding: 10px; }
        }
    </style>
</head>
<body>
    <div class="navbar">
        <span class="nav-title">
            <span class="material-icons">admin_panel_settings</span>
            后台管理
        </span>
        <a href="../index.php" class="home-btn"><span class="material-icons">home</span>主页</a>
        <span class="user-info">
            当前用户：<?php echo htmlspecialchars($user['username']); ?> (<?php echo $user['role'] === 'admin' ? '管理员' : '普通用户'; ?>)
        </span>
        <a href="logout.php" class="logout"><span class="material-icons" style="vertical-align:middle;">logout</span> 退出</a>
    </div>
    <div class="container">
        <div class="card-group">
            <div class="md2-card">
                <span class="material-icons" style="font-size:44px;color:#1976d2;">dashboard_customize</span>
                <h2 style="margin:20px 0 8px 0;">配置管理</h2>
                <div style="color:#555;">创建、编辑、删除考试配置</div>
                <a href="manage_configs.php" class="md-btn">
                    <span class="material-icons">edit</span> 进入配置管理
                </a>
            </div>
            <?php if ($user['role'] === 'admin'): ?>
            <div class="md2-card">
                <span class="material-icons" style="font-size:44px;color:#1976d2;">group</span>
                <h2 style="margin:20px 0 8px 0;">用户管理</h2>
                <div style="color:#555;">管理后台用户账号</div>
                <a href="manage_users.php" class="md-btn">
                    <span class="material-icons">manage_accounts</span> 进入用户管理
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
