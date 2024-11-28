<?php
// register.php
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nom_utilisateur = trim($_POST['nom_utilisateur']);
    $email = strtolower(trim($_POST['email']));
    $mot_de_passe = password_hash($_POST['mot_de_passe'], PASSWORD_BCRYPT);
    $role = 'client';
    $date_inscription = date('Y-m-d');

    // Check if email already exists
    $stmt = $pdo->prepare('SELECT * FROM utilisateur WHERE email = ?');
    $stmt->execute([$email]);

    if ($stmt->fetch()) {
        $error = 'Un compte avec cet email existe déjà';
    } else {
        $stmt = $pdo->prepare('INSERT INTO utilisateur (nom_utilisateur, email, mot_de_passe, role, date_inscription)
                               VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([$nom_utilisateur, $email, $mot_de_passe, $role, $date_inscription]);
        header('Location: login.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Inscription</title>
    <link rel="stylesheet" href="css/style.css" />
</head>
<body>
    <h1>Inscription</h1>
    <?php if (isset($error)) echo '<p>' . htmlspecialchars($error) . '</p>'; ?>
    <form action="register.php" method="post">
        <label for="nom_utilisateur">Nom d'utilisateur :</label>
        <input type="text" name="nom_utilisateur" id="nom_utilisateur" required>
        <br><br>
        <label for="email">Email :</label>
        <input type="email" name="email" id="email" required>
        <br><br>
        <label for="mot_de_passe">Mot de passe :</label>
        <input type="password" name="mot_de_passe" id="mot_de_passe" required>
        <br><br>
        <button type="submit">S'inscrire</button>
    </form>
    <p>Déjà inscrit ? <a href="login.php">Se connecter</a></p>
</body>
</html>