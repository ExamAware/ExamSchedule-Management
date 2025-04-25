<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}
$db = new SQLite3(__DIR__ . '/../data/exam.db');
$res = $db->query('SELECT id, content, created_at FROM configs ORDER BY created_at DESC');
$configs = [];
while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
    $content = json_decode($row['content'], true);
    $configs[] = [
        'id' => $row['id'],
        'examName' => $content['examName'] ?? '',
        'message' => $content['message'] ?? '',
        'room' => $content['room'] ?? '',
        'created_at' => $row['created_at']
    ];
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>配置管理</title>
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
        .container { max-width: 1100px; margin: 48px auto; }
        .card-list { display: flex; flex-wrap: wrap; gap: 32px; margin-top: 32px; }
        .md2-card {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 8px #0001;
            padding: 32px 36px;
            min-width: 320px;
            max-width: 340px;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            transition: box-shadow 0.2s;
            position: relative;
        }
        .md2-card:hover { box-shadow: 0 6px 24px #1976d233; }
        .md-btn {
            background: #1976d2;
            color: #fff !important;
            border: none;
            border-radius: 4px;
            padding: 10px 22px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            margin-top: 14px;
            margin-right: 12px;
            box-shadow: 0 2px 8px #1976d233;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: background 0.2s;
            text-decoration: none !important;
        }
        .md-btn:hover { background: #1565c0; }
        .card-title { font-size: 20px; font-weight: 500; margin-bottom: 8px; color: #1976d2; display: flex; align-items: center; gap: 8px; }
        .card-sub { color: #555; margin-bottom: 6px; }
        .card-id { color: #888; font-size: 13px; margin-bottom: 6px; }
        .card-footer { font-size: 13px; color: #aaa; margin-top: 14px; }
        .add-card {
            border: 2px dashed #1976d2;
            background: #f0f6ff;
            color: #1976d2;
            justify-content: center;
            align-items: center;
            cursor: pointer;
            min-height: 180px;
            min-width: 320px;
            display: flex;
            flex-direction: column;
            border-radius: 8px;
            transition: background 0.2s, border 0.2s;
            text-decoration: none !important;
        }
        .add-card:hover { background: #e3f0fc; border-color: #1565c0; }
        .back-btn {
            background: #fff;
            color: #1976d2;
            border: 1px solid #1976d2;
            border-radius: 4px;
            padding: 8px 20px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            margin-bottom: 24px;
            margin-top: 24px;
            margin-right: 16px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            text-decoration: none !important;
        }
        .back-btn:hover { background: #e3f0fc; }
        .delete-btn { background: #e53935; }
        .delete-btn:hover { background: #b71c1c; }
        a, a:visited, a:active { text-decoration: none !important; color: inherit; }
        @media (max-width: 1200px) {
            .container { max-width: 99vw; padding: 10px; }
            .md2-card, .add-card { min-width: 90vw; max-width: 99vw; }
        }
    </style>
</head>
<body>
    <div class="navbar">
        <span class="nav-title">
            <span class="material-icons">dashboard_customize</span>
            配置管理
        </span>
        <a href="../index.php" class="home-btn"><span class="material-icons">home</span>主页</a>
    </div>
    <div class="container">
        <a href="dashboard.php" class="back-btn"><span class="material-icons">arrow_back</span>返回</a>
        <div class="card-list">
            <a href="edit_config.php" class="add-card">
                <span class="material-icons" style="font-size:44px;">add_circle</span>
                <div style="font-size:17px;margin-top:8px;">新建配置</div>
            </a>
            <?php foreach ($configs as $cfg): ?>
            <div class="md2-card">
                <div class="card-title">
                    <span class="material-icons" style="vertical-align:middle;">event_note</span>
                    <?php echo htmlspecialchars($cfg['examName']); ?>
                </div>
                <div class="card-id">ID: <?php echo htmlspecialchars($cfg['id']); ?></div>
                <div class="card-sub">考场号: <?php echo htmlspecialchars($cfg['room']); ?></div>
                <div class="card-sub"><?php echo htmlspecialchars($cfg['message']); ?></div>
                <div class="card-footer">创建时间: <?php echo htmlspecialchars($cfg['created_at']); ?></div>
                <div>
                    <a href="edit_config.php?id=<?php echo urlencode($cfg['id']); ?>" class="md-btn">
                        <span class="material-icons">edit</span>编辑
                    </a>
                    <a href="delete_config.php?id=<?php echo urlencode($cfg['id']); ?>" class="md-btn delete-btn" onclick="return confirm('确定删除该配置？');">
                        <span class="material-icons">delete</span>删除
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>
