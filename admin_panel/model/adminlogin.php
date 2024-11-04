<?php
session_start(); // Start the session
include '../includes/dbConnect.php';

	  if ( !isset($_POST['email'], $_POST['password']) ) {
	  echo 'error1';exit;
		// Could not get the data that should have been sent.
		//exit('Please fill both the username and password fields!');
	}
	  $email  = trim($_POST['email']);
	  $pass   = trim($_POST['password']);

	  $password=md5($pass);
	  $response=0;
	 // print_r("test");exit;
//   $result = mysqli_query($conn,"SELECT * FROM login WHERE (email='$email') AND password='$password'");
  $sql = "select id,first_name,email,password from admin_users where email = ? ";  
  //===========================================
  if ($stmtsql = $conn->prepare($sql)) {
            // Bind variables to the prepared statement as parameters
            // Set parameters
            $param_email = $email;
            $stmtsql->bind_param('s', $param_email);            

            // Attempt to execute the prepared statement
            if ($stmtsql->execute()) {                
                // Store result
                $stmtsql->store_result();

                // Check if username exists, if yes then verify password
                if ($stmtsql->num_rows == 1) {                    
                    // Bind the result variables
                    $stmtsql->bind_result($id,$first_name,$adminemail,$hashed_password);
                    if ($stmtsql->fetch()) {                
                       //echo  $hashed_password;exit;
                        if ($password === $hashed_password) {
                            //echo "nnn";exit;
                            // Password is correct, so start a new session
                            session_start();

                            // Store data in session variables                            
                            $_SESSION['adminid'] = $id;  
                            $_SESSION['adminname'] = $first_name;  
                            //=========================
                            // Check if the "Remember Me" checkbox is selected
                            
         if (isset($_POST['check_me']) && ($_POST['check_me'] === 'on')) {             
        // Set a long-lasting cookie for automatic login
        $cookie_name = 'remember_me';
        $cookie_value = $email . '|' . $pass; // Store the username and password in the cookie 
        $cookie_expiry = time() + (60 * 60 * 24 * 30); // 30 days expiration        
        setcookie($cookie_name, $cookie_value, $cookie_expiry, '/');
    }

//============================
                             echo 'success';
                            exit;
                        } else {
                            echo "error3";exit;
                            // Password is not valid                            
                        }
                    }
                } else {
                    echo 'error2';exit;                     
                    // Username doesn't exist
                   // $login_err = 'Invalid username or password.';
                }
            } else {
               
                echo 'Oops! Something went wrong. Please try again later.';
            }

            // Close statement
            $stmtsql->close();
        }
   // Close connection
   exit;
?>