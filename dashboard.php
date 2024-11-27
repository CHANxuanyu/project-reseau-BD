<?php
// dashboard.php
require('db.php');
session_start();

if (!isset($_SESSION['id_utilisateur'])) {
    header('Location: login.php');
    exit;
}

$role = $_SESSION['role'];
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Dashboard</title>
    <link rel="stylesheet" href="css/style.css" />
</head>
<body>
    <h1>Bienvenue, <?php echo htmlspecialchars($_SESSION['nom_utilisateur']); ?></h1>

    <?php if ($role == 'admin'): ?>
        <h2>Admin Dashboard</h2>
        <ul>
            <li><a href="admin_inventory.php">Gestion de l'inventaire</a></li>
            <li><a href="admin_reservations.php">Voir les réservations</a></li>
            <li><a href="view_feedback.php">Voir les avis</a></li>
        </ul>
    <?php elseif ($role == 'client'): ?>
        <h2>Client Dashboard</h2>
        <ul>
            <li><a href="menu.php">Voir le menu</a></li>
            <li><a href="reservation.php">Faire une réservation</a></li>
            <li><a href="order_history.php">Historique des commandes</a></li>
            <li><a href="feedback.php">Laisser un avis</a></li>
        </ul>
    <?php endif; ?>

    <p><a href="logout.php">Se déconnecter</a></p>
</body>
</html>