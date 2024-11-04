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

            $stmt = $conn->prepare('UPDATE users SET first_name = :fname, last_name = :lname,mobile = :mobile,image = :image,contact_address = :address,country = :country,state = :state,city = :city,zip_code = :zip_code WHERE id = :id');

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

            // Set the parameters
            
            // $id = 26;
            // Execute the statement
            
            if ($stmt->execute()) {
                echo "success";
              } else {
                echo "Eerror in updation";
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