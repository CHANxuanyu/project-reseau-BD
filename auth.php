<?php
// auth.php
session_start();

if (!isset($_SESSION['id_utilisateur'])) {
    header('Location: login.php');
    exit;
}
?>