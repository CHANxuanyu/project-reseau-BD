<?php
// view_feedback.php
require 'db.php';

// Fetch feedback
$stmt = $pdo->query('SELECT u.nom_utilisateur, a.note, a.commentaire, a.date_avis
                     FROM avis_clients a
                     JOIN utilisateur u ON a.id_utilisateur = u.id_utilisateur
                     ORDER BY a.date_avis DESC');
$feedbacks = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Avis des clients</title>
</head>
<body>
    <h1>Avis des clients</h1>
    <?php foreach ($feedbacks as $feedback): ?>
        <div style="border:1px solid #ccc; padding:10px; margin-bottom:10px;">
            <strong><?php echo htmlspecialchars($feedback['nom_utilisateur']); ?></strong>
            <span> - Note: <?php echo $feedback['note']; ?>/5</span>
            <p><?php echo nl2br(htmlspecialchars($feedback['commentaire'])); ?></p>
            <em><?php echo $feedback['date_avis']; ?></em>
        </div>
    <?php endforeach; ?>
</body>
</html>