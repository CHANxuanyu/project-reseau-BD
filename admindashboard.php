<?php
// admin_dashboard.php
require 'db.php';
session_start();

if ($_SESSION['role'] != 'admin') {
    echo 'Accès interdit';
    exit;
}

// Fetch reservations
$stmt = $pdo->query('SELECT * FROM reservation');
$reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Tableau de bord Admin</title>
</head>
<body>
    <h1>Réservations</h1>
    <table border="1">
        <tr>
            <th>ID</th>
            <th>ID Utilisateur</th>
            <th>Date</th>
            <th>Heure</th>
            <th>Statut</th>
            <th>Personnes</th>
        </tr>
        <?php foreach ($reservations as $res): ?>
            <tr>
                <td><?php echo $res['id_reservation']; ?></td>
                <td><?php echo $res['id_utilisateur']; ?></td>
                <td><?php echo $res['date_reservation']; ?></td>
                <td><?php echo $res['heure_reservation']; ?></td>
                <td><?php echo $res['statut']; ?></td>
                <td><?php echo $res['nombre_personnes']; ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>