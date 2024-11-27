<?php
// reservation.php
require 'db.php';
session_start();

if (!isset($_SESSION['id_utilisateur'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_utilisateur = $_SESSION['id_utilisateur'];
    $date_reservation = $_POST['date_reservation'];
    $heure_reservation = $_POST['heure_reservation'];
    $nombre_personnes = $_POST['nombre_personnes'];
    $statut = 'en attente';

    // Validate inputs
    $errors = [];

    if (empty($date_reservation) || $date_reservation < date('Y-m-d')) {
        $errors[] = 'Veuillez choisir une date valide pour la réservation.';
    }

    if (empty($heure_reservation)) {
        $errors[] = 'Veuillez choisir une heure valide pour la réservation.';
    }

    if (!filter_var($nombre_personnes, FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]])) {
        $errors[] = 'Veuillez entrer un nombre de personnes valide.';
    }

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare('INSERT INTO reservation (id_utilisateur, date_reservation, heure_reservation, statut, nombre_personnes)
                                   VALUES (?, ?, ?, ?, ?)');
            $stmt->execute([$id_utilisateur, $date_reservation, $heure_reservation, $statut, $nombre_personnes]);

            echo 'Réservation effectuée avec succès. <a href="dashboard.php">Retour au tableau de bord</a>';
        } catch (Exception $e) {
            echo 'Erreur lors de la réservation: ' . $e->getMessage();
        }
    } else {
        foreach ($errors as $error) {
            echo '<p>' . htmlspecialchars($error) . '</p>';
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Faire une réservation</title>
    <link rel="stylesheet" href="css/style.css" />
</head>
<body>
    <h1>Faire une réservation</h1>
    <form action="reservation.php" method="post">
        <label for="date_reservation">Date de réservation:</label>
        <input type="date" name="date_reservation" id="date_reservation" required>
        <br><br>
        <label for="heure_reservation">Heure de réservation:</label>
        <input type="time" name="heure_reservation" id="heure_reservation" required>
        <br><br>
        <label for="nombre_personnes">Nombre de personnes:</label>
        <input type="number" name="nombre_personnes" id="nombre_personnes" min="1" required>
        <br><br>
        <button type="submit">Réserver</button>
    </form>
    <p><a href="dashboard.php">Retour au tableau de bord</a></p>
</body>
</html>