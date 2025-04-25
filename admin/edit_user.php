<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: dashboard.php');
    exit;
}
$db = new SQLite3(__DIR__ . '/../data/exam.db');
$id = $_GET['id'] ?? '';
$isEdit = false;
$username = '';
$role = 'user';

if ($id) {
    $stmt = $db->prepare('SELECT username, role FROM users WHERE id = :id');
    $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
    $result = $stmt->execute();
    $row = $result->fetchArray(SQLITE3_ASSOC);
    if ($row) {
        $username = $row['username'];
        $role = $row['role'];
        $isEdit = true;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $role = $_POST['role'];
    $password = $_POST['password'] ?? '';
    if (!$username || !preg_match('/^[a-zA-Z0-9_\-]+$/', $username)) {
        $msg = "用户名格式错误";
    } elseif (!$isEdit && !$password) {
        $msg = "新建用户必须设置密码";
    } else {
        if ($isEdit) {
            if ($password) {
                $stmt = $db->prepare('UPDATE users SET password = :password, role = :role WHERE id = :id');
                $stmt->bindValue(':password', password_hash($password, PASSWORD_DEFAULT), SQLITE3_TEXT);
                $stmt->bindValue(':role', $role, SQLITE3_TEXT);
                $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
            } else {
                $stmt = $db->prepare('UPDATE users SET role = :role WHERE id = :id');
                $stmt->bindValue(':role', $role, SQLITE3_TEXT);
                $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
            }
        } else {
            $stmt = $db->prepare('INSERT INTO users (username, password, role) VALUES (:username, :password, :role)');
            $stmt->bindValue(':username', $username, SQLITE3_TEXT);
            $stmt->bindValue(':password', password_hash($password, PASSWORD_DEFAULT), SQLITE3_TEXT);
            $stmt->bindValue(':role', $role, SQLITE3_TEXT);
        }
        $stmt->execute();
        header('Location: manage_users.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title><?php echo $isEdit ? '编辑' : '新建'; ?>用户</title>
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
        .container { max-width: 400px; margin: 60px auto; background: #fff; border-radius: 8px; box-shadow: 0 2px 8px #0001; padding: 32px; }
        .md-btn { background: #1976d2; color: #fff !important; border: none; border-radius: 4px; padding: 10px 24px; font-size: 16px; cursor: pointer; text-decoration: none !important; }
        .md-btn:hover { background: #1565c0; }
        .input-group { margin-bottom: 24px; }
        label { display: block; margin-bottom: 8px; color: #1976d2; }
        input[type="text"], input[type="password"], select { width: 100%; padding: 10px; border: 1px solid #b3c6e0; border-radius: 4px; font-size: 16px; }
        .msg { color: red; margin-bottom: 16px; }
    </style>
</head>
<body>
    <div class="navbar">
        <span class="nav-title">
            <span class="material-icons">person</span>
            <?php echo $isEdit ? '编辑' : '新建'; ?>用户
        </span>
        <a href="../index.php" class="home-btn"><span class="material-icons">home</span>主页</a>
    </div>
    <div class="container">
        <?php if (!empty($msg)): ?>
            <div class="msg"><?php echo htmlspecialchars($msg); ?></div>
        <?php endif; ?>
        <form method="post">
            <div class="input-group">
                <label for="username">用户名</label>
                <input type="text" id="username" name="username" required value="<?php echo htmlspecialchars($username); ?>" <?php if ($isEdit) echo 'readonly'; ?>>
            </div>
            <div class="input-group">
                <label for="password"><?php echo $isEdit ? '新密码（留空不修改）' : '密码'; ?></label>
                <input type="password" id="password" name="password" <?php if (!$isEdit) echo 'required'; ?>>
            </div>
            <div class="input-group">
                <label for="role">角色</label>
                <select id="role" name="role">
                    <option value="user" <?php if ($role === 'user') echo 'selected'; ?>>普通用户</option>
                    <option value="admin" <?php if ($role === 'admin') echo 'selected'; ?>>管理员</option>
                </select>
            </div>
            <button type="submit" class="md-btn">
                <span class="material-icons" style="vertical-align:middle;">save</span> 保存
            </button>
            <a href="manage_users.php" class="md-btn" style="background:#888;margin-left:16px;">
                <span class="material-icons" style="vertical-align:middle;">arrow_back</span> 返回
            </a>
        </form>
    </div>
</body>
</html>
