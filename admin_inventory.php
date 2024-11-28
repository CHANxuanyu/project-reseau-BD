<?php
// admin_inventory.php
require 'db.php';
session_start();

// Check if the user is an admin
if ($_SESSION['role'] != 'admin') {
    echo 'Accès refusé';
    exit;
}

// Handle inventory updates
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_stock = $_POST['id_stock'];
    $quantite = $_POST['quantite'];
    $seuil_minimum = $_POST['seuil_minimum'];

    // Validate inputs
    if (!filter_var($quantite, FILTER_VALIDATE_FLOAT) || $quantite < 0 ||
        !filter_var($seuil_minimum, FILTER_VALIDATE_FLOAT) || $seuil_minimum < 0) {
        echo 'Valeurs invalides pour la quantité ou le seuil minimum';
        exit;
    }

    // Update inventory
    $stmt = $pdo->prepare('UPDATE inventaire_de_stock SET quantite = ?, seuil_minimum = ? WHERE id_stock = ?');
    $stmt->execute([$quantite, $seuil_minimum, $id_stock]);

    echo 'Inventaire mis à jour avec succès';
}

// Fetch inventory data
$stmt = $pdo->query('SELECT * FROM inventaire_de_stock');
$stocks = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Gestion de l'inventaire</title>
</head>
<body>
    <h1>Gestion de l'inventaire</h1>
    <table border="1">
        <tr>
            <th>ID Stock</th>
            <th>Nom de l'article</th>
            <th>Quantité</th>
            <th>Seuil Minimum</th>
            <th>Unité</th>
            <th>Fournisseur</th>
            <th>Action</th>
        </tr>
        <?php foreach ($stocks as $stock): ?>
            <tr>
                <form action="admin_inventory.php" method="post">
                    <td><?php echo $stock['id_stock']; ?></td>
                    <td><?php echo htmlspecialchars($stock['nom_article']); ?></td>
                    <td>
                        <input type="number" step="0.01" name="quantite" value="<?php echo $stock['quantite']; ?>" min="0" required>
                    </td>
                    <td>
                        <input type="number" step="0.01" name="seuil_minimum" value="<?php echo $stock['seuil_minimum']; ?>" min="0" required>
                    </td>
                    <td><?php echo htmlspecialchars($stock['unite']); ?></td>
                    <td><?php echo $stock['id_fournisseur']; ?></td>
                    <td>
                        <input type="hidden" name="id_stock" value="<?php echo $stock['id_stock']; ?>">
                        <button type="submit">Mettre à jour</button>
                    </td>
                </form>
            </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>