<?php
session_start();

// Destruir sessão
session_destroy();

// Remover cookies
if (isset($_COOKIE['user_id'])) {
    setcookie('user_id', '', time() - 3600, '/');
}

header('Location: ../pages/home.php?logout=true');
exit;
