<?php
// order.php
require 'db.php';
session_start();

if (!isset($_SESSION['id_utilisateur'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_utilisateur = $_SESSION['id_utilisateur'];
    $id_plat = $_POST['id_plat'];
    $quantite = $_POST['quantite'];

    // Validate quantity
    if (!filter_var($quantite, FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]])) {
        echo 'Quantité invalide';
        exit;
    }

    try {
        // Begin transaction
        $pdo->beginTransaction();

        // Create a reservation if not exists
        $date_reservation = date('Y-m-d');
        $heure_reservation = date('H:i:s');
        $statut = 'confirmée';
        $nombre_personnes = 1;

        // Insert reservation
        $stmt = $pdo->prepare('INSERT INTO reservation (id_utilisateur, date_reservation, heure_reservation, statut, nombre_personnes)
                               VALUES (?, ?, ?, ?, ?)
                               RETURNING id_reservation');
        $stmt->execute([$id_utilisateur, $date_reservation, $heure_reservation, $statut, $nombre_personnes]);
        $id_reservation = $stmt->fetchColumn();

        // Insert into 'commande' table
        $stmt = $pdo->prepare('INSERT INTO commande (id_reservation, id_plat, quantite) VALUES (?, ?, ?)');
        $stmt->execute([$id_reservation, $id_plat, $quantite]);

        // Commit transaction
        $pdo->commit();

        echo 'Commande passée avec succès. <a href="order_history.php">Voir vos commandes</a>';
    } catch (Exception $e) {
        // Rollback transaction on failure
        $pdo->rollBack();
        echo 'Erreur lors de la commande: ' . $e->getMessage();
    }
}
?>