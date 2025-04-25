<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}
$db = new SQLite3(__DIR__ . '/../data/exam.db');
$id = $_GET['id'] ?? '';
$isEdit = false;
$examName = '';
$message = '';
$room = '';
$examInfos = [['name'=>'','start'=>'','end'=>'']];

if ($id) {
    $stmt = $db->prepare('SELECT content FROM configs WHERE id = :id');
    $stmt->bindValue(':id', $id, SQLITE3_TEXT);
    $result = $stmt->execute();
    $row = $result->fetchArray(SQLITE3_ASSOC);
    if ($row) {
        $content = json_decode($row['content'], true);
        $examName = $content['examName'] ?? '';
        $message = $content['message'] ?? '';
        $room = $content['room'] ?? '';
        $examInfos = $content['examInfos'] ?? [['name'=>'','start'=>'','end'=>'']];
        $isEdit = true;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = trim($_POST['id']);
    $examName = trim($_POST['examName']);
    $message = trim($_POST['message']);
    $room = trim($_POST['room']);
    $names = $_POST['subject_name'] ?? [];
    $starts = $_POST['subject_start'] ?? [];
    $ends = $_POST['subject_end'] ?? [];
    $examInfos = [];
    foreach ($names as $i => $name) {
        if (trim($name) !== '' && isset($starts[$i]) && isset($ends[$i])) {
            // 转换为YYYY-MM-DDTHH:MM:SS格式
            $start = date('Y-m-d\TH:i:s', strtotime($starts[$i]));
            $end = date('Y-m-d\TH:i:s', strtotime($ends[$i]));
            $examInfos[] = [
                'name' => trim($name),
                'start' => $start,
                'end' => $end
            ];
        }
    }
    if (!preg_match('/^[a-zA-Z0-9_\-]+$/', $id)) {
        $msg = "ID格式错误";
    } elseif (!$examName || !$examInfos) {
        $msg = "考试名称和科目不能为空";
    } else {
        $content = [
            'examName' => $examName,
            'message' => $message,
            'room' => $room,
            'examInfos' => $examInfos
        ];
        $stmt = $db->prepare('REPLACE INTO configs (id, content) VALUES (:id, :content)');
        $stmt->bindValue(':id', $id, SQLITE3_TEXT);
        $stmt->bindValue(':content', json_encode($content, JSON_UNESCAPED_UNICODE), SQLITE3_TEXT);
        $stmt->execute();
        header('Location: dashboard.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title><?php echo $isEdit ? '编辑' : '新建'; ?>考试配置</title>
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
        .container {
            max-width: 820px;
            margin: 40px auto;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 8px #0001;
            padding: 32px 32px 32px 32px;
        }
        .md-btn {
            background: #1976d2;
            color: #fff;
            border: none;
            border-radius: 4px;
            padding: 10px 24px;
            font-size: 16px;
            cursor: pointer;
            text-decoration: none !important;
        }
        .md-btn:hover { background: #1565c0; }
        .md-btn, .md-btn span, .md-btn .material-icons {
            color: #fff !important;
        }
        .add-btn, .add-btn span, .add-btn .material-icons {
            color: #fff !important;
        }
        .del-btn, .del-btn span, .del-btn .material-icons {
            color: #fff !important;
        }
        .back-btn, .back-btn span, .back-btn .material-icons {
            color: #fff !important;
        }
        .input-group { margin-bottom: 24px; }
        label { display: block; margin-bottom: 8px; color: #1976d2; }
        input[type="text"], input[type="datetime-local"] {
            width: 96%;
            padding: 10px;
            border: 1px solid #b3c6e0;
            border-radius: 4px;
            font-size: 16px;
            margin: 0 2%;
            box-sizing: border-box;
        }
        table { width: 100%; border-collapse: collapse; margin-bottom: 16px; table-layout: fixed; }
        th, td { border: 1px solid #e3eaf2; padding: 8px 6px; text-align: center; }
        th { background: #e3f0fc; }
        .actions { display: flex; gap: 8px; justify-content: center; }
        .msg { color: red; margin-bottom: 16px; }
        .add-btn { background: #43a047; }
        .add-btn:hover { background: #2e7031; }
        .del-btn { background: #e53935; }
        .del-btn:hover { background: #b71c1c; }
        a, a:visited, a:active { text-decoration: none !important; color: inherit; }
        /* 科目表格输入框宽度适配 */
        .subject-input { width: 90%; min-width: 60px; max-width: 180px; margin: 0 auto; }
        .subject-time-input { width: 90%; min-width: 120px; max-width: 180px; margin: 0 auto; }
        .card-title { display: flex; align-items: center; gap: 8px; }
        @media (max-width: 900px) {
            .container { max-width: 99vw; padding: 10px; }
            input[type="text"], input[type="datetime-local"] { width: 98%; margin: 0 1%; }
        }
    </style>
    <script>
    let isDirty = false;
    function addRow() {
        const tbody = document.getElementById('subjects-tbody');
        const row = document.createElement('tr');
        row.innerHTML = `
            <td><input type="text" name="subject_name[]" required class="subject-input"></td>
            <td><input type="datetime-local" name="subject_start[]" required class="subject-time-input"></td>
            <td><input type="datetime-local" name="subject_end[]" required class="subject-time-input"></td>
            <td class="actions">
                <button type="button" class="md-btn del-btn" onclick="this.closest('tr').remove(); isDirty=true;">
                    <span class="material-icons">delete</span>
                </button>
            </td>
        `;
        tbody.appendChild(row);
        isDirty = true;
    }
    window.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('input,textarea').forEach(el => {
            el.addEventListener('input', () => { isDirty = true; });
        });
        document.getElementById('edit-config-form').addEventListener('submit', () => { isDirty = false; });
        document.getElementById('back-dashboard-btn').addEventListener('click', function(e) {
            if (isDirty) {
                if (!confirm('有未保存的更改，确定要返回吗？')) {
                    e.preventDefault();
                }
            }
        });
    });
    </script>
</head>
<body>
    <div class="navbar">
        <span class="nav-title">
            <span class="material-icons">edit</span>
            <?php echo $isEdit ? '编辑' : '新建'; ?>考试配置
        </span>
        <a href="../index.php" class="home-btn"><span class="material-icons">home</span>主页</a>
    </div>
    <div class="container">
        <?php if (!empty($msg)): ?>
            <div class="msg"><?php echo htmlspecialchars($msg); ?></div>
        <?php endif; ?>
        <form method="post" id="edit-config-form">
            <div class="input-group">
                <label for="id">配置ID（英文字母、数字、下划线、短横线）</label>
                <input type="text" id="id" name="id" required value="<?php echo htmlspecialchars($id); ?>" <?php if ($isEdit) echo 'readonly'; ?>>
            </div>
            <div class="input-group">
                <label for="examName">考试名称</label>
                <input type="text" id="examName" name="examName" required value="<?php echo htmlspecialchars($examName); ?>">
            </div>
            <div class="input-group">
                <label for="message">提示语</label>
                <input type="text" id="message" name="message" value="<?php echo htmlspecialchars($message); ?>">
            </div>
            <div class="input-group">
                <label for="room">考场号</label>
                <input type="text" id="room" name="room" value="<?php echo htmlspecialchars($room); ?>">
            </div>
            <div class="input-group">
                <label>考试科目安排</label>
                <table>
                    <thead>
                        <tr>
                            <th>科目名称</th>
                            <th>开始时间</th>
                            <th>结束时间</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody id="subjects-tbody">
                        <?php foreach ($examInfos as $i => $info): ?>
                        <tr>
                            <td><input type="text" name="subject_name[]" required class="subject-input" value="<?php echo htmlspecialchars($info['name']); ?>"></td>
                            <td><input type="datetime-local" name="subject_start[]" required class="subject-time-input" value="<?php echo $info['start'] ? date('Y-m-d\TH:i:s', strtotime($info['start'])) : ''; ?>"></td>
                            <td><input type="datetime-local" name="subject_end[]" required class="subject-time-input" value="<?php echo $info['end'] ? date('Y-m-d\TH:i:s', strtotime($info['end'])) : ''; ?>"></td>
                            <td class="actions">
                                <button type="button" class="md-btn del-btn" onclick="this.closest('tr').remove(); isDirty=true;">
                                    <span class="material-icons">delete</span>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <button type="button" class="md-btn add-btn" onclick="addRow()">
                    <span class="material-icons" style="vertical-align:middle;">add</span> 添加科目
                </button>
            </div>
            <button type="submit" class="md-btn">
                <span class="material-icons" style="vertical-align:middle;">save</span> 保存
            </button>
            <a href="dashboard.php" class="md-btn" style="background:#888;margin-left:16px;" id="back-dashboard-btn">
                <span class="material-icons" style="vertical-align:middle;">arrow_back</span> 返回
            </a>
        </form>
    </div>
</body>
</html>
