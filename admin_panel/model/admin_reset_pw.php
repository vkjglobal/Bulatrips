<?php
//echo "WWWWWWWWWWWW".$token;exit;
	  if ( !isset($_POST['new_pw'], $_POST['confirm_password']) ) {
	  echo 'error21';exit;
		// Could not get the data that should have been sent.
		//exit('Please fill both the username and password fields!');
	}
	else {
	
	  $token_pw =	trim($_POST['token_pw']);
	  $new_pw	= trim($_POST['new_pw']);
	  $confirm_password   = trim($_POST['confirm_password']);	  
	  // Verify the token and check if it is valid and has not expired
	    include "../includes/class.member.php";
      $objMember		= 	new Member();  	 
      if(!($objMember->isTokenValid($token_pw))){
	   echo "error22";exit; //Token Expired
	  }
	  else{
		 $admin_userID	=	 $objMember->isTokenValid($token_pw);
		  if($new_pw === $confirm_password){
			  
    // Update the user's password in the database
   
	 $res	=	$objMember->updateAdminById($admin_userID, $new_pw);
		  echo "error23";exit;// success
		  }
		  else{
			   echo "error24";exit;// password mismatch new and confirm

		  }
	  }
     
	      // Display a success message to the user
    $success = "Your password has been successfully reset. You can now log in with your new password.";
}
//===============
	 // $password=md5($pass);
	 // $response=0;
	
	  ?>