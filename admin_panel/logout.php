<?php
ob_start(); // Start output buffering
if (isset($_COOKIE['remember_me'])) {
    unset($_COOKIE['remember_me']); 
    setcookie('remember_me', '', -1, '/'); 
}

session_start();
session_unset();
session_destroy();
ob_end_flush(); // Flush output buffer

// Redirect to login page using PHP header
header("Location: https://bulatrips.com/admin_panel/index.php");
exit(); // Ensure script stops executing after redirection
?>
