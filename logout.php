<?php
require_once 'includes/functions.php';
secureLogout();
$lang = $_GET['lang'] ?? $_SESSION['lang'] ?? 'en';
header("Location: index.php?lang=" . $lang . "&logout=1");
exit;
?>