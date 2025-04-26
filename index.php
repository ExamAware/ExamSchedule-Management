<?php
session_start();
$user = $_SESSION['user'] ?? null;

// 检查数据库是否存在，不存在则跳转初始化
$dbFile = __DIR__ . '/data/exam.db';
if (!file_exists($dbFile)) {
    header('Location: data/init_db.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>考试看板配置查询</title>
    <link rel="stylesheet" href="assets/md2-blue.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/highlight.js@11.8.0/styles/github.min.css">
    <style>
        body { font-family: Roboto, Arial, sans-serif; background: #f5f7fa; margin: 0; }
        .navbar { background: #1976d2; color: #fff; padding: 16px 24px; display: flex; align-items: center; }
        .navbar .material-icons { margin-right: 8px; }
        .container { max-width: 440px; margin: 60px auto; background: #fff; border-radius: 8px; box-shadow: 0 2px 8px #0001; padding: 32px; }
        .md-btn { background: #1976d2; color: #fff; border: none; border-radius: 4px; padding: 10px 24px; font-size: 16px; cursor: pointer; }
        .md-btn:hover { background: #1565c0; }
        .input-group { margin-bottom: 24px; }
        label { display: block; margin-bottom: 8px; color: #1976d2; }
        input[type="text"] { width: 100%; padding: 10px; border: 1px solid #b3c6e0; border-radius: 4px; font-size: 16px; }
        .footer { text-align: center; color: #888; margin-top: 40px; }
        .json-preview-wrap { margin-bottom: 24px; display: none; }
        .json-preview-title { font-size: 17px; color: #1976d2; margin-bottom: 8px; }
        pre code.hljs { font-size: 15px; border-radius: 6px; }
        .show-btn { margin-top: 12px; width: 100%; }
        .align-center { display: flex; flex-direction: column; align-items: center; }
    </style>
</head>
<body>
    <div class="navbar">
        <span class="material-icons">dashboard</span>
        考试看板配置查询
        <span style="flex:1"></span>
        <?php if ($user): ?>
            <span style="margin-right:16px;">👤 <?php echo htmlspecialchars($user['username']); ?></span>
            <a href="admin/dashboard.php" class="md-btn" style="background:#1976d2;color:#fff;padding:6px 18px;font-size:15px;text-decoration:none;">进入后台</a>
        <?php else: ?>
            <a href="admin/index.php" class="md-btn" style="background:#fff;color:#1976d2 !important;border:1px solid #1976d2;padding:6px 18px;font-size:15px;text-decoration:none;">登录</a>
        <?php endif; ?>
    </div>
    <div class="container align-center">
        <form id="configForm" autocomplete="off" style="width:100%;">
            <div class="input-group">
                <label for="configId"><span class="material-icons" style="vertical-align:middle;">search</span> 配置ID</label>
                <input type="text" id="configId" name="configId" required placeholder="请输入配置ID">
            </div>
            <div class="json-preview-wrap" id="json-preview-wrap" style="display:none;">
                <div class="json-preview-title">配置文件内容</div>
                <pre style="margin:0;"><code id="json-preview" class="json hljs"></code></pre>
                <button class="md-btn show-btn" id="show-btn" type="button" style="background:#1976d2;color:#fff;">放映</button>
            </div>
            <button type="submit" class="md-btn" id="query-btn" style="width:100%;">
                <span class="material-icons" style="vertical-align:middle;">arrow_forward</span> 查询考试安排
            </button>
        </form>
    </div>
    <div class="footer">
        <span class="material-icons" style="vertical-align:middle;">info</span>
        Powered by ExamSchedule-Management
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.8.0/highlight.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.8.0/languages/json.min.js"></script>
    <script>
    hljs.highlightAll();
    document.getElementById('configForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const configId = document.getElementById('configId').value.trim();
        if (!configId) return;
        fetch('api/get_config.php?id=' + encodeURIComponent(configId))
            .then(res => {
                if (!res.ok) return res.json().then(err => { throw err; });
                return res.json();
            })
            .then(data => {
                const jsonStr = JSON.stringify(data, null, 2);
                const codeElem = document.getElementById('json-preview');
                codeElem.textContent = jsonStr;
                hljs.highlightElement(codeElem);
                document.getElementById('json-preview-wrap').style.display = 'block';
                document.getElementById('show-btn').onclick = function() {
                    window.location.href = 'present/index.html?configId=' + encodeURIComponent(configId);
                };
            })
            .catch(err => {
                document.getElementById('json-preview-wrap').style.display = 'none';
                alert('获取配置失败: ' + (err.error || err.message || err));
            });
    });
    </script>
</body>
</html>