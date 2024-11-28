<?php
// order_history.php
require 'db.php';
session_start();

if (!isset($_SESSION['id_utilisateur'])) {
    header('Location: login.php');
    exit;
}

$id_utilisateur = $_SESSION['id_utilisateur'];

$stmt = $pdo->prepare('
    SELECT p.nom_plat, c.quantite, r.date_reservation, r.heure_reservation, (c.quantite * p.prix_plat) AS total
    FROM commande c
    JOIN plat p ON c.id_plat = p.id_plat
    JOIN reservation r ON c.id_reservation = r.id_reservation
    WHERE r.id_utilisateur = ?
    ORDER BY r.date_reservation DESC
');
$stmt->execute([$id_utilisateur]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Historique des commandes</title>
    <link rel="stylesheet" href="css/style.css" />
</head>
<body>
    <h1>Vos commandes passées</h1>
    <table border="1">
        <tr>
            <th>Plat</th>
            <th>Quantité</th>
            <th>Date</th>
            <th>Heure</th>
            <th>Total</th>
        </tr>
        <?php foreach ($orders as $order): ?>
            <tr>
                <td><?php echo htmlspecialchars($order['nom_plat']); ?></td>
                <td><?php echo $order['quantite']; ?></td>
                <td><?php echo $order['date_reservation']; ?></td>
                <td><?php echo $order['heure_reservation']; ?></td>
                <td>€<?php echo number_format($order['total'], 2); ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
    <p><a href="dashboard.php">Retour au tableau de bord</a></p>
</body>
</html>