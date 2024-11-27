<?php
// index.php
session_start();

// Check if the user is logged in
if (isset($_SESSION['id_utilisateur'])) {
    // Redirect to the dashboard
    header('Location: dashboard.php');
    exit;
} else {
    // Redirect to the login page
    header('Location: login.php');
    exit;
}
?>