<?php
include("includes/dbConnect.php");
if (isset($_COOKIE['remember_me'])) {
	unset($_COOKIE['remember_me']); 
    setcookie('remember_me', '', -1, '/'); 
	//setcookie('remember_me', '', time() - 3600);
}
session_start();
session_unset();
session_destroy();
ob_start();
header("Location: https://bulatrips.com/admin_panel/index.php");exit;
?>
<script>
        window.location = "https://bulatrips.com/admin_panel/index.php";
       
    </script>
    <?php
exit();
?>






<?

/*

header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");
session_start();
session_unset();
session_destroy();
//header("location:https://bulatrips.com/admin_panel/index.php");exit; NOT WORKING ON LIVE
?>
<script>
        window.location = "https://bulatrips.com/admin_panel/index.php";
       
    </script>
    <?
//ob_start();
//header("location:index.php");exit;
//include 'index.php';
exit(); */
?>
