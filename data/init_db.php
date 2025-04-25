<?php
$db = new SQLite3(__DIR__ . '/exam.db');
$db->exec('CREATE TABLE IF NOT EXISTS configs (
    id TEXT PRIMARY KEY,
    content TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)');
$db->exec('CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT UNIQUE NOT NULL,
    password TEXT NOT NULL,
    role TEXT NOT NULL
)');

// 插入管理员账号（如不存在）
$adminExists = $db->querySingle("SELECT COUNT(*) FROM users WHERE username='admin'");
if (!$adminExists) {
    $plainPass = bin2hex(random_bytes(4)); // 8位随机密码
    $adminPass = password_hash($plainPass, PASSWORD_DEFAULT);
    $db->exec("INSERT INTO users (username, password, role) VALUES ('admin', '$adminPass', 'admin')");
} else {
    $plainPass = '(已存在，密码未知)';
}

// 插入示例配置（如不存在）
$sampleConfigId = 'demo';
$sampleConfig = [
    "examName" => "期末考试",
    "message" => "请提前10分钟进入考场",
    "room" => "room301",
    "examInfos" => [
        [
            "name" => "数学",
            "start" => "2023-12-01T09:00:00",
            "end" => "2023-12-01T11:00:00"
        ],
        [
            "name" => "英语",
            "start" => "2023-12-01T13:00:00",
            "end" => "2023-12-01T15:00:00"
        ]
    ]
];
$configExists = $db->querySingle("SELECT COUNT(*) FROM configs WHERE id='$sampleConfigId'");
if (!$configExists) {
    $content = $db->escapeString(json_encode($sampleConfig, JSON_UNESCAPED_UNICODE));
    $db->exec("INSERT INTO configs (id, content) VALUES ('$sampleConfigId', '$content')");
}

echo "数据库初始化完成<br>";
echo "管理员账号：admin<br>";
echo "管理员密码：$plainPass<br>";
echo "示例配置ID：demo<br>";
