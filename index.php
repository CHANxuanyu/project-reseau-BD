<?php
// index.php
session_start();

if (isset($_SESSION['id_utilisateur'])) {
    // If the user is already logged in, redirect to the dashboard
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Bienvenue au Restaurant</title>
    <link rel="stylesheet" href="css/style.css" />
</head>
<body>
    <h1>Bienvenue au Restaurant</h1>
    <p>Veuillez vous connecter ou vous inscrire pour continuer.</p>
    <a href="login.php">Se connecter</a> | <a href="register.php">S'inscrire</a>
</body>
</html>