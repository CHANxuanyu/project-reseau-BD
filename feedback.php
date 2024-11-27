<?php
// feedback.php
require 'db.php';
session_start();

// Check if the user is logged in
if (!isset($_SESSION['id_utilisateur'])) {
    echo 'Veuillez vous connecter pour laisser un avis';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_utilisateur = $_SESSION['id_utilisateur'];
    $note = $_POST['note'];
    $commentaire = $_POST['commentaire'];

    // Validate inputs
    if (!filter_var($note, FILTER_VALIDATE_INT, ["options" => ["min_range" => 1, "max_range" => 5]])) {
        echo 'Veuillez entrer une note valide entre 1 et 5';
        exit;
    }
    if (empty($commentaire)) {
        echo 'Le commentaire ne peut pas être vide';
        exit;
    }

    // Insert feedback into the database
    $stmt = $pdo->prepare('INSERT INTO avis_clients (id_utilisateur, note, commentaire, date_avis)
                           VALUES (?, ?, ?, NOW())');
    $stmt->execute([$id_utilisateur, $note, $commentaire]);

    echo 'Merci pour votre avis';
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Laisser un avis</title>
</head>
<body>
    <h1>Laisser un avis</h1>
    <form action="feedback.php" method="post">
        <label for="note">Note (1-5):</label>
        <input type="number" name="note" id="note" min="1" max="5" required>
        <br><br>
        <label for="commentaire">Commentaire:</label><br>
        <textarea name="commentaire" id="commentaire" rows="5" cols="50" required></textarea>
        <br><br>
        <button type="submit">Soumettre</button>
    </form>
</body>
</html>