<?php
// feedback.php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require 'db.php';
session_start();

if (!isset($_SESSION['id_utilisateur'])) {
    echo 'Veuillez vous connecter pour laisser un avis.';
    exit;
}

// Fetch user reservations
$id_utilisateur = $_SESSION['id_utilisateur'];
$stmt = $pdo->prepare('SELECT id_reservation, date_reservation FROM reservation WHERE id_utilisateur = ? ORDER BY date_reservation DESC');
$stmt->execute([$id_utilisateur]);
$reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);

$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_utilisateur = $_SESSION['id_utilisateur'];
    $id_reservation = $_POST['id_reservation'];
    $note = $_POST['note'];
    $commentaire = $_POST['commentaire'];

    // Validate inputs
    if (empty($id_reservation)) {
        $message = 'Veuillez sélectionner une réservation.';
    } elseif (!filter_var($note, FILTER_VALIDATE_INT, ["options" => ["min_range" => 1, "max_range" => 5]])) {
        $message = 'Veuillez entrer une note valide entre 1 et 5.';
    } elseif (empty($commentaire)) {
        $message = 'Le commentaire ne peut pas être vide.';
    } else {
        // Validate that the reservation belongs to the user
        $stmt = $pdo->prepare('SELECT id_reservation FROM reservation WHERE id_reservation = ? AND id_utilisateur = ?');
        $stmt->execute([$id_reservation, $id_utilisateur]);
        if ($stmt->rowCount() == 0) {
            $message = 'Réservation invalide.';
        } else {
            // Check if feedback already exists for this reservation
            $stmt = $pdo->prepare('SELECT id_avis FROM avis_clients WHERE id_reservation = ? AND id_utilisateur = ?');
            $stmt->execute([$id_reservation, $id_utilisateur]);
            if ($stmt->rowCount() > 0) {
                $message = 'Vous avez déjà laissé un avis pour cette réservation.';
            } else {
                // Proceed with inserting the feedback
                try {
                    $stmt = $pdo->prepare('INSERT INTO avis_clients (id_utilisateur, id_reservation, note, commentaire, date_avis)
                                           VALUES (?, ?, ?, ?, NOW())');
                    $stmt->execute([$id_utilisateur, $id_reservation, $note, $commentaire]);

                    $message = 'Merci pour votre avis.';
                } catch (PDOException $e) {
                    $message = 'Erreur lors de l\'enregistrement de votre avis: ' . $e->getMessage();
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Laisser un avis</title>
</head>
<body>
    <h1>Laisser un avis</h1>
    <?php if (!empty($message)): ?>
        <p><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>
    <?php if (!empty($reservations)): ?>
        <form action="feedback.php" method="post">
            <label for="id_reservation">Sélectionnez votre réservation :</label>
            <select name="id_reservation" id="id_reservation" required>
                <option value="">-- Sélectionnez une réservation --</option>
                <?php foreach ($reservations as $reservation): ?>
                    <option value="<?php echo $reservation['id_reservation']; ?>">
                        Réservation du <?php echo htmlspecialchars($reservation['date_reservation']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <br><br>
            <label for="note">Note (1-5):</label>
            <input type="number" name="note" id="note" min="1" max="5" required>
            <br><br>
            <label for="commentaire">Commentaire:</label><br>
            <textarea name="commentaire" id="commentaire" rows="5" cols="50" required></textarea>
            <br><br>
            <button type="submit">Soumettre</button>
        </form>
    <?php else: ?>
        <p>Vous n'avez aucune réservation pour laquelle laisser un avis.</p>
    <?php endif; ?>
    <p><a href="dashboard.php">Retour au tableau de bord</a></p>
</body>
</html>