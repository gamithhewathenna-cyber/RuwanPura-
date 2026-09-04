<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/customer-auth.php';

logout_customer();
header('Location: ' . BASE_URL . 'index.php');
exit;
