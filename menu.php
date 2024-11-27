<?php
// menu.php
require 'db.php';
session_start();


// Fetch menu items from the 'plat' table
$stmt = $pdo->query('SELECT * FROM plat');
$plats = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Menu</title>
    <link rel="stylesheet" href="css/style.css" />
</head>
<body>
    <h1>Menu</h1>
    <div class="menu-container">
        <?php foreach ($plats as $plat): ?>
            <div class="menu-item">
                <h2><?php echo htmlspecialchars($plat['nom_plat']); ?></h2>
                <p><?php echo htmlspecialchars($plat['description']); ?></p>
                <p>Prix: €<?php echo htmlspecialchars($plat['prix_plat']); ?></p>
                <form action="order.php" method="post">
                    <input type="hidden" name="id_plat" value="<?php echo $plat['id_plat']; ?>">
                    <label for="quantite">Quantité:</label>
                    <input type="number" name="quantite" value="1" min="1">
                    <button type="submit">Commander</button>
                </form>
            </div>
        <?php endforeach; ?>
    </div>
    <p><a href="dashboard.php">Retour au tableau de bord</a></p>
</body>
</html>

