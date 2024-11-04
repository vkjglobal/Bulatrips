<?php
    include 'includes/dbConnect.php';
    $country = $_POST['country'];
    $signedby = $_POST['signed-by'];  
    $signeddate = $_POST['signed-date']; 
    $currentDate = date('Y-m-d'); 

    
    $id=$_POST['id'];
    if($id==''){
       
        $sql_statement="INSERT INTO signatories (country, signed_by, signed_date) VALUES ('$country', '$signedby', '$signeddate')";
        
        if(mysqli_query($conn,$sql_statement)) {
            echo "Data added Successfully";
        } else {
            echo "Error: " . $sql_statement . "<br>" . mysqli_error($conn);
        }
        
    }else{
            
        $update="update signatories set country='$country',signed_by='$signedby',signed_date='$signeddate',updted_at='$currentDate' where id='$id'";	
        if(mysqli_query($conn,$update)) {
            echo "Data added Successfully";
        } else {
            echo "Error: " . $update . "<br>" . mysqli_error($conn);
        }
    }
    
    

?>