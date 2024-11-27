<?php
// login.php
require 'db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $mot_de_passe = $_POST['mot_de_passe'];

    $stmt = $pdo->prepare('SELECT * FROM utilisateur WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Insert var_dump here
    var_dump($user);

    if ($user && password_verify($mot_de_passe, $user['mot_de_passe'])) {
        $_SESSION['id_utilisateur'] = $user['id_utilisateur'];
        $_SESSION['nom_utilisateur'] = $user['nom_utilisateur'];
        $_SESSION['role'] = $user['role'];
        
        header('Location: dashboard.php');  // 假设你有一个仪表盘页面
        exit;
    } else {
        echo 'Email ou mot de passe incorrect';
    }
}
?>

<!-- login form -->
<form action="login.php" method="post">
    <input type="email" name="email" placeholder="Email" required>
    <input type="password" name="mot_de_passe" placeholder="Mot de passe" required>
    <button type="submit">Se connecter</button>
</form>
