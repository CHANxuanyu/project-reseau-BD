<?php
// 引入数据库连接
require 'db.php';  // 确保你的 db.php 文件包含正确的数据库连接

// 获取所有用户的明文密码
$stmt = $pdo->prepare('SELECT id_utilisateur, mot_de_passe FROM utilisateur');
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 遍历每个用户，将明文密码转换为哈希并更新数据库
foreach ($users as $user) {
    // 将明文密码转换为哈希
    $hashed_password = password_hash($user['mot_de_passe'], PASSWORD_BCRYPT);

    // 更新数据库中的密码为哈希
    $updateStmt = $pdo->prepare('UPDATE utilisateur SET mot_de_passe = ? WHERE id_utilisateur = ?');
    $updateStmt->execute([$hashed_password, $user['id_utilisateur']]);

    echo "Password for user ID {$user['id_utilisateur']} updated.\n";
}

echo "All passwords have been hashed and updated successfully.\n";
?>