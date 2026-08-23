<?php
require_once __DIR__ . '/auth.php';
logout_admin();
header('Location: ' . BASE_URL . 'admin/login.php');
exit;
