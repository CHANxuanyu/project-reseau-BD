<!-- register.php -->
<form action="register.php" method="post">
    <input type="text" name="nom_utilisateur" placeholder="Nom d'utilisateur" required>
    <input type="email" name="email" placeholder="Email" required>
    <input type="password" name="mot_de_passe" placeholder="Mot de passe" required>
    <button type="submit">S'inscrire</button>
</form>

<?php
// register.php
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nom_utilisateur = $_POST['nom_utilisateur'];
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