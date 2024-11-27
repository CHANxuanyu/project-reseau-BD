<?php
// After successful login
if ($user['role'] == 'admin') {
    header('Location: admin_dashboard.php');
    exit;
}