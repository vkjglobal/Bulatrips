<?php

// Start session
session_start();
require_once('includes/dbConnect.php');

    $ppic=$_FILES["image"]["name"];
    $id=$_POST['id'];

    // Function to sanitize form data
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

$fnameErr = $lnameErr = $phoneErr = $addressErr = $countryErr = $stateErr = $cityErr = $postalErr = $agencynameErr = $agencyaddressErr = $agencycountryErr = $agencystateErr = $agencycityErr = $agencyzipErr = $ownernameErr = $dobErr = $kycidErr = $kycnumberErr = $tanErr = '';
$firstname = $lastname = $phone = $address = $country = $state = $city = $postal = $agencyname = $agencyaddress = $agencycountry = $agencystate = $agencycity = $agencyzip = $ownername = $dob = $kycid = $kycnumber = $tan = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate First Name
    // echo $_POST["firstname"].' '.$_POST["lastname"];exit;
    if (empty($_POST["firstname"])) {
        $fnameErr = "First Name is required";
    } else {
        $firstname = sanitize_input($_POST["firstname"]);
        // Check if name only contains letters and whitespace
        if (!preg_match("/^[a-zA-Z ]*$/", $firstname)) {
            $fnameErr = "Only letters and white space allowed in First Name";
        }
    }

    // Validate Last Name
    if (empty($_POST["lastname"])) {
        $lnameErr = "Last Name is required";
    } else {
        $lastname = sanitize_input($_POST['lastname']);
        // Check if name only contains letters and whitespace
        if (!preg_match("/^[a-zA-Z ]*$/", $lastname)) {
            $lnameErr = "Only letters and white space allowed in Last Name";
        }
    }

    // Validate Phone Number
    if (empty($_POST['phone'])) {
        $phoneErr = "Phone Number is required";
    } else {
        $phone = sanitize_input($_POST['phone']);
        // Check if phone number is valid
        if (!preg_match("/^\d{10}$/", $phone)) {
            $phoneErr = "Invalid Phone Number";
        }
    }

    // Validate address
    if (empty($_POST['address'])) {
        $addressErr = "Address is required";
    } else {
        $address = sanitize_input($_POST['address']);
        // Check if address is valid
        if (!preg_match("/^[a-zA-Z, ]*$/", $address)) {
            $addressErr = "Only letters, comma and white space allowed in address";
        }
    }

    // Validate country
    if (empty($_POST['country'])) {
        $countryErr = "Country is required";
    } else {
        $country = sanitize_input($_POST['country']);
    }

    // Validate state
    if (empty($_POST['state'])) {
        $stateErr = "State is required";
    } else {
        $state = sanitize_input($_POST['state']);
    }

    // Validate city
    if (empty($_POST['city'])) {
        $cityErr = "City is required";
    } else {
        $city = sanitize_input($_POST['city']);
    }

    // Validate zipcode
    if (empty($_POST['zipcode'])) {
        $postalErr = "Zipcode is required";
    } else {
        $postal = sanitize_input($_POST['zipcode']);
    }
    
    //validate agencyname
    if (empty($_POST['agencyname'])) {
        $agencynameErr = "Agency name is required";
    } else {
        $agencyname = sanitize_input($_POST['agencyname']);
    }

    // Validate agencyaddress
    if (empty($_POST['agencyaddress'])) {
        $agencyaddressErr = "Agency address is required";
    } else {
        $agencyaddress = sanitize_input($_POST['agencyaddress']);
        // Check if agency address is valid
        if (!preg_match("/^[a-zA-Z, ]*$/", $address)) {
            $agencyaddressErr = "Only letters, comma and white space allowed in address";
        }
    }

    // Validate agencycountry
    if (empty($_POST['agencycountry'])) {
        $agencycountryErr = "Agency country is required";
    } else {
        $agencycountry = sanitize_input($_POST['agencycountry']);
    }

    // Validate agencystate
    if (empty($_POST['agencystate'])) {
        $agencystateErr = "Agency state is required";
    } else {
        $agencystate = sanitize_input($_POST['agencystate']);
    }

    // Validate agencycity
    if (empty($_POST['agencycity'])) {
        $agencycityErr = "Agency city is required";
    } else {
        $agencycity = sanitize_input($_POST['agencycity']);
    }

    // Validate agencyzipcode
    if (empty($_POST['agencyzip'])) {
        $agencyzipErr = "Agency zipcode is required";
    } else {
        $agencyzip = sanitize_input($_POST['agencyzip']);
    }

     // Validate ownername
     if (empty($_POST['ownername'])) {
        $ownernameErr = "Owner Name is required";
    } else {
        $ownername = sanitize_input($_POST['ownername']);
    }

     // Validate dob
     if (empty($_POST['dob'])) {
        $dobErr = "Agency zipcode is required";
    } else {
        $dob = sanitize_input($_POST['dob']);
    }
    
     // Validate kycid
     if (empty($_POST['kycid'])) {
        $kycidErr = "kyc id is required";
    } else {
        $kycid = sanitize_input($_POST['kycid']);
    }
    $kycid=$_POST['kycid'];

    // Validate kycnumber
    if (empty($_POST['kycnumber'])) {
        $kycnumberErr = "kycnumber is required";
    } else {
        $kycnumber = sanitize_input($_POST['kycnumber']);
    }

    // Validate tan
    if (empty($_POST['tan'])) {
        $tanErr = "Tan is required";
    } else {
        $tan = sanitize_input($_POST['tan']);
    }

}

    $stmt = $conn->prepare('SELECT * FROM users WHERE id = :id');
    $stmt->execute(array('id' => $id));
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
   
    $ppic=$_FILES["image"]["name"];
    $extension = substr($ppic,strlen($ppic)-4,strlen($ppic));
    $allowed_extensions = array(".jpg","jpeg",".png");
    if($ppic){
        if(!in_array($extension,$allowed_extensions))
        {
            echo "<script>alert('Invalid format. Only jpg / jpeg/ png /gif format allowed');</script>";
        }

    }
    if($ppic){
        $imgnewfile=md5($imgfile).time().$extension;
        move_uploaded_file($_FILES["image"]["tmp_name"],"uploads/profile/".$imgnewfile);

    }else{
        $imgnewfile=$user['image'];
    }

    $kpic=$_FILES["kycimage"]["name"];
    // echo $imgfile;
    $extension = substr($kpic,strlen($kpic)-4,strlen($kpic));
    $allowed_extensions = array(".jpg","jpeg",".png");
    if($kpic){
        if(!in_array($extension,$allowed_extensions))
        {
            echo "<script>alert('Invalid format. Only jpg / jpeg/ png /gif format allowed');</script>";
        }

    }
    
    if($kpic){
        $imgnewfilekyc=md5($imgfile).time().$extension;
        move_uploaded_file($_FILES["kycimage"]["tmp_name"],"uploads/profile/kyc/".$imgnewfilekyc);

    }else{
        $imgnewfilekyc=$user['kyc_image'];
    }

    $stmt = $conn->prepare('UPDATE users SET first_name = :fname, last_name = :lname,mobile = :mobile,
        image = :image,contact_address = :address,country = :country,state = :state,city = :city,
        zip_code = :zip_code,agency_name =:agencyname ,agency_address=:agencyaddress,	agency_country=:agencycountry,	agency_state=:agencystate,
        agency_city=:agencycity,agency_zip_code=:agencyzip,owner_name=:ownername,kyc_id=:kycid,
        kyc_number=:kycnumber,dob=:dob,tan=:tan,kyc_image=:kycimage
        WHERE id = :id');

        // Bind parameters to the statement
        $stmt->bindParam(':fname', $firstname);
        $stmt->bindParam(':lname', $lastname);
        $stmt->bindParam(':mobile', $phone);
        $stmt->bindParam(':image', $imgnewfile);
        $stmt->bindParam(':address', $address);
        $stmt->bindParam(':country', $country);
        $stmt->bindParam(':state', $state);
        $stmt->bindParam(':city', $city);
        $stmt->bindParam(':zip_code', $postal);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':agencyname', $agencyname);
        $stmt->bindParam(':agencyaddress', $agencyaddress);
        $stmt->bindParam(':agencycountry', $agencycountry);
        $stmt->bindParam(':agencystate', $agencystate);
        $stmt->bindParam(':agencycity', $agencycity);
        $stmt->bindParam(':agencyzip', $agencyzip);

        $stmt->bindParam(':ownername', $ownername);
        $stmt->bindParam(':kycid', $kycid);
        $stmt->bindParam(':kycnumber', $kycnumber);
        $stmt->bindParam(':dob', $dob);
        $stmt->bindParam(':tan', $tan);
        $stmt->bindParam(':kycimage', $imgnewfilekyc);

        // Set the parameters
            
        // $id = 26;
        // Execute the statement
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            echo "success";
        } else {
            echo "error";
        }

        // $update="update login set first_name='$firstName',last_name='$lastName',image='$imgnewfile',phone='$phone',email='$email',address='$address',country='$country',city='$city',zipcode='$postal' where id='$id'";	

        // $query=mysqli_query($conn, $update);
        // if ($query) {
        // echo "<script>alert('You have successfully updated the data');</script>";
        // echo "<script type='text/javascript'> document.location ='home.php'; </script>";
        // } else{
        // echo "<script>alert('Something Went Wrong. Please try again');</script>";
        // }
  
?>