<?php
/**************************************************************************** 
   Project Name	::> TravelSite
   Module 	::> Class for admin user management
   Programmer	::> Soumya
   Date		::> 27-06-2023
   DESCRIPTION::::>>>>
   This is a Class code used to manage admin users 
*****************************************************************************/
include "class.DbAction.php";
class Member extends DbAction{
	public function __construct(){
       $this->db = new DbAction();
				
	}
	public function getAdminProfile($id){			
		$tableName = "admin_users";        
		$result = $this->db->selectById($tableName, $id);        
		return $result;
		
	}
    public function getAdminByEmail($email){	
		//$db = new DbAction();
		$tableName = "admin_users";
		$result = $this->db->selectByEmail($tableName, $email);
		return $result;
		
	}
     public function updateAdminById($adminid,$newpw){	
		//$db = new DbAction();
		$tableName = "admin_users";
		$result = $this->db->updatePassword($adminid,$newpw);
		return $result;
		
	}
     public function updateAdminProfile($adminid,$email,$fname,$lname,$phone,$image,$address){	
		
		$tableName = "admin_users";
		$result = $this->db->updateProfile($adminid,$email,$fname,$lname,$phone,$image,$address);
		return $result;
		
	}
    public function sanitizeInput($input) {
    // Trim whitespace from the beginning and end of the input
    $input = trim($input);

    // Remove backslashes
    $input = stripslashes($input);

    // Convert special characters to HTML entities
    $input = htmlspecialchars($input, ENT_QUOTES);
    return $input;
    }
    
   public function validateInputStrings($input) {
    // Perform your validation rules on the input
    // Return true if the input is valid, otherwise return false
    //  validation for a name field
    if($input == ""){
        return false;
    }
    if (strlen($input) < 2 || strlen($input) > 50) {
        // Invalid name length
        return false;
    }
    return true;
   }
   public function validateInputEmail($input) {
    //  validation for an email field
    if (!filter_var($input, FILTER_VALIDATE_EMAIL)) {
        // Invalid email format
        return false;
    }
   
    return true;
    }   
    public function validateInputEmailExist($email,$adminid) {
    //  validation for an email exist for any other users
        $tableName = "admin_users";
		$result = $this->db->selectByEmailExist($tableName,$email,$adminid);
		return $result;   
    }   
   public  function validatePhoneNumber($phoneNumber) {
    $pattern = '/^\d{7}$/'; // Assumes a 7-digit phone number without any formatting

  // Perform the validation
  return preg_match($pattern, $phoneNumber);
    }

    //====
    public function validateImage($imageSize,$imageName,$imageType){
        $maxFileSize = 1000000; // 2MB (example maximum file size)
        if ($imageSize > $maxFileSize) {
        echo "Invalid file size";
        return false;
        }
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif']; // Example allowed file extensions
    $fileExtension = strtolower(pathinfo($imageName, PATHINFO_EXTENSION));
        if (!in_array($fileExtension, $allowedExtensions)) {
            echo "Invalid file type";
            return false;
        }
    $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif']; // Example allowed MIME types
        $fileMimeType = $imageType;
        if (!in_array($fileMimeType, $allowedMimeTypes)) {
            echo "Invalid MIME type";
            return false;
        }
    /*$maxWidth = 1920; // Example maximum width
    $maxHeight = 1080; // Example maximum height
    $imageDimensions = getimagesize($_FILES['image']['tmp_name']);
    if ($imageDimensions[0] > $maxWidth || $imageDimensions[1] > $maxHeight) {
        // Invalid image dimensions
        return false;
    }
    */
    return true;

    }
	//store token for forgot password page secure code
	public function storePasswordResetToken($userId, $token) {
        // Calculate the expiration time for the token (e.g., 1 hour from the current time)
        $expiryTime = date('Y-m-d H:i:s', strtotime('+1 hour'));   
        // Store the token and its expiration time in the database  
        $tableName = "admin_forgot_pw_reset_tokens";    
        $params = ['admin_id'=>$userId, 'tocken'=>$token, 'expiry_date'=>$expiryTime];
        //print_r($params);exit;
        $result =   $this->db->insertInto($tableName, $params) ;
       return $result;
    }
    //check token valid for reset password page
    public function isTokenValid($token) {  

    // Retrieve the token and its expiration time from the database
        $tableName = "admin_forgot_pw_reset_tokens";
		$result = $this->db->selectByToken($tableName, $token);
		//return $result;  
        //echo count($result);exit;
    // Check if the token exists in the database
    if (count($result) > 0) {
        $tokenData = $result[0];
        $expirationTime = strtotime($tokenData['expiry_date']);
        $currentTime = time();
        // Check if the token has expired
        if ($currentTime <= $expirationTime) {
            return $tokenData['admin_id']; // Token is valid and has not expired
        }
        else{
            return false; // Token is invalid or has expired
        }
    }

    return false; // Token is invalid or has expired
}

}

?>