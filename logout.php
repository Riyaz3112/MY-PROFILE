<?php
require_once __DIR__ . '/includes/functions.php';
// Sign out customer
unset($_SESSION['customer_id'], $_SESSION['customer_name']);
header('Location: index.html');
exit;
