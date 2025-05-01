<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: dashboard.php');
    exit;
}
$db = new SQLite3(__DIR__ . '/../data/exam.db');
$res = $db->query('SELECT id, username, role FROM users ORDER BY id ASC');
$users = [];
while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
    $users[] = $row;
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>用户管理</title>
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
        .container { max-width: 700px; margin: 40px auto; background: #fff; border-radius: 8px; box-shadow: 0 2px 8px #0001; padding: 32px; }
        .md-btn { background: #1976d2; color: #fff !important; border: none; border-radius: 4px; padding: 8px 18px; font-size: 15px; cursor: pointer; text-decoration: none !important; }
        .md-btn:hover { background: #1565c0; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 24px; table-layout: fixed; }
        th, td { border: 1px solid #e3eaf2; padding: 8px 12px; }
        th { background: #e3f0fc; }
        .actions { display: flex; gap: 8px; }
        .back-btn {
            background: #fff;
            color: #1976d2 !important;
            border: 1px solid #1976d2;
            border-radius: 4px;
            padding: 8px 20px;
            font-size: 15px;
            font-weight: 500;
            cursor: pointer;
            margin-bottom: 24px;
            margin-right: 16px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            text-decoration: none !important;
        }
        .back-btn:hover { background: #e3f0fc; }
        a, a:visited, a:active { text-decoration: none !important; color: inherit; }
        @media (max-width: 900px) {
            .container { max-width: 99vw; padding: 10px; }
            table { font-size: 13px; }
        }
    </style>
</head>
<body>
    <div class="navbar">
        <span class="nav-title">
            <span class="material-icons">group</span>
            用户管理
        </span>
        <a href="../index.php" class="home-btn"><span class="material-icons">home</span>主页</a>
    </div>
    <div class="container">
        <a href="dashboard.php" class="back-btn"><span class="material-icons">arrow_back</span>返回</a>
        <a href="edit_user.php" class="md-btn"><span class="material-icons" style="vertical-align:middle;">person_add</span> 新建用户</a>
        <table>
            <thead>
                <tr>
                    <th>用户名</th>
                    <th>角色</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($users as $row): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['username']); ?></td>
                    <td><?php echo $row['role'] === 'admin' ? '管理员' : '普通用户'; ?></td>
                    <td class="actions">
                        <a href="edit_user.php?id=<?php echo urlencode($row['id']); ?>" class="md-btn"><span class="material-icons">edit</span></a>
                        <?php if ($row['username'] !== 'admin'): ?>
                        <a href="delete_user.php?id=<?php echo urlencode($row['id']); ?>" class="md-btn" onclick="return confirm('确定删除该用户？');"><span class="material-icons">delete</span></a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
