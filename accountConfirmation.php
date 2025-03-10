<?php
error_reporting(1);
require_once("includes/header.php");
include('includes/dbConnect.php');
?>
<div class="container-jumbotron">
                <div class="bodycontant">
                    <div class="content">
<?php
$token = isset($_GET['token']) && $_GET['token'] != '' ? true : false;
if( $token ) {?>

    <style>
        .bodycontant {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 70vh;
            background: url('images/home-banner1.jpg') center center/cover no-repeat; /* Use your background image here */
            color: white;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        .content {
            position: relative;
            z-index: 2;
            max-width: 600px;
            padding: 20px;
            padding: 20px;
            background: #121e7e;
            border-radius: 10px;
        }
        .content h1 {
            font-size: 48px;
            font-weight: bold;
            margin-bottom: 20px;
        }

        /* Subtext Styling */
        .content p {
            font-size: 18px;
            color: #fff;
            margin-bottom: 30px;
        }

        /* Button Styling */
        .content .btn {
            display: inline-block;
            padding: 15px 30px;
            color: white;
            font-size: 16px;
            font-weight: bold;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
        }

        .content .btn:hover {
            background-color: #121E7E;
        }
    </style>

        <?php
        $stmtbookingid = $conn->prepare('SELECT * FROM users WHERE st_token = :st_token');
        $stmtbookingid->execute(array('st_token' => $_GET['token']));
        $bookingData = $stmtbookingid->fetch(PDO::FETCH_ASSOC);

        if( isset($bookingData['st_token']) && $bookingData['st_token'] != '' && $bookingData['st_token'] == $_GET['token'] ) {
            
            $token_update = "";
            $statuss = "active";
            $stmtupdate = $conn->prepare('UPDATE users SET st_token = :st_token, status = :status WHERE id = :id');
            $stmtupdate->bindParam(':st_token', $token_update);
            $stmtupdate->bindParam(':status', $statuss);
            $stmtupdate->bindParam(':id', $bookingData['id']);
            $stmtupdate->execute();
            
            ?>
            <script>
                Swal.fire({
                    title: "Congratulations!",
                    text: "Your account has been successfully activated.",
                    icon: "success",
                    
                    confirmButtonText: "Close", // Login button
                    
                    confirmButtonColor: "#f57c00", // Green color for login
                    
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                }).then((result) => {
                        window.location.href = "index";
                });
            </script>
        <?php
        } else {?>
            <script>
                Swal.fire({
                    title: "Invalid Token",
                    text: "Your email confirmation token is invalid or expired.",
                    icon: "error",
                    confirmButtonText: "Close",
                    confirmButtonColor: "#f57c00",
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = "index";
                    }
                });

            </script>
            
                        
                    
        <?php
        }
        
} else {?>
    <script>
        window.location = "index"
    </script>    
<?php
}?>
</div>
                </div>
            </div>

</div>

<?php
require_once("includes/login-modal.php");
require_once("includes/forgot-modal.php");
require_once("includes/footer.php");
?>
</body>
</html>