<?php
// admin_reservations.php
require 'db.php';
session_start();

if ($_SESSION['role'] != 'admin') {
    echo 'Accès refusé';
    exit;
}

// Handle status updates
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_reservation = $_POST['id_reservation'];
    $statut = $_POST['statut'];

    // Validate status
    $valid_statuses = ['confirmée', 'en attente', 'annulée'];
    if (!in_array($statut, $valid_statuses)) {
        echo 'Statut invalide';
        exit;
    }

    // Update reservation status
    $stmt = $pdo->prepare('UPDATE reservation SET statut = ? WHERE id_reservation = ?');
    $stmt->execute([$statut, $id_reservation]);

    echo 'Statut mis à jour avec succès';
}

// Fetch all reservations
$stmt = $pdo->query('SELECT r.*, u.nom_utilisateur FROM reservation r JOIN utilisateur u ON r.id_utilisateur = u.id_utilisateur ORDER BY date_reservation DESC');
$reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Gestion des réservations</title>
    <link rel="stylesheet" href="css/style.css" />
</head>
<body>
    <h1>Gestion des réservations</h1>
    <table border="1">
        <tr>
            <th>ID Réservation</th>
            <th>Client</th>
            <th>Date</th>
            <th>Heure</th>
            <th>Personnes</th>
            <th>Statut</th>
            <th>Action</th>
        </tr>
        <?php foreach ($reservations as $reservation): ?>
            <tr>
                <form action="admin_reservations.php" method="post">
                    <td><?php echo $reservation['id_reservation']; ?></td>
                    <td><?php echo htmlspecialchars($reservation['nom_utilisateur']); ?></td>
                    <td><?php echo $reservation['date_reservation']; ?></td>
                    <td><?php echo $reservation['heure_reservation']; ?></td>
                    <td><?php echo $reservation['nombre_personnes']; ?></td>
                    <td>
                        <select name="statut">
                            <option value="confirmée" <?php if ($reservation['statut'] == 'confirmée') echo 'selected'; ?>>Confirmée</option>
                            <option value="en attente" <?php if ($reservation['statut'] == 'en attente') echo 'selected'; ?>>En attente</option>
                            <option value="annulée" <?php if ($reservation['statut'] == 'annulée') echo 'selected'; ?>>Annulée</option>
                        </select>
                    </td>
                    <td>
                        <input type="hidden" name="id_reservation" value="<?php echo $reservation['id_reservation']; ?>">
                        <button type="submit">Mettre à jour</button>
                    </td>
                </form>
            </tr>
        <?php endforeach; ?>
    </table>
    <p><a href="dashboard.php">Retour au tableau de bord</a></p>
</body>
</html>