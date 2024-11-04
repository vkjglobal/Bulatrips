<?php

// Start session
session_start();
require_once('includes/dbConnect.php');
        $firstName=$_POST['firstname'];
        $lastName=$_POST['lastname'];
        $phone=$_POST['phone'];
        $address=$_POST['address'];
        $country=$_POST['country'];
        $state=$_POST['state'];
        $city=$_POST['city'];
        $postal=$_POST['zipcode'];
        // $image=$_POST['image'];
        $ppic=$_FILES["image"]["name"];
        $id=$_POST['id'];
        $agencyname=$_POST['agencyname'];
        $agencyaddress=$_POST['agencyaddress'];
        $agencycountry=$_POST['agencycountry'];
        $agencystate=$_POST['agencystate'];
        $agencycity=$_POST['agencycity'];
        $agencyzip=$_POST['agencyzip'];

        $ownername=$_POST['ownername'];
        $dob=$_POST['dob'];
        $kycid=$_POST['kycid'];
        $kycnumber=$_POST['kycnumber'];
        $tan=$_POST['tan'];
        // $kycimage=$_POST['kycimage'];

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
            $stmt->bindParam(':fname', $firstName);
            $stmt->bindParam(':lname', $lastName);
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