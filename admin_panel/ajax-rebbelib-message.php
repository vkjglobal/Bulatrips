<?php
    include 'includes/dbConnect.php';
    $title = $_POST['message-from'];
    $content = $_POST['content']; 
    $name = $_POST['name'];
    $position =$_POST['position'];
    $currentDate = date('Y-m-d'); 
   
    if(isset($_FILES['image'])){
        $pic=$_FILES["image"]["name"];
    }else{
        $pic='';
    }
    $id=$_POST['id'];
    if($id==''){
        $extension = substr($pic,strlen($pic)-4,strlen($pic));
        $allowed_extensions = array(".jpg","jpeg",".png");
        if($pic){
            if(!in_array($extension,$allowed_extensions))
            {
                echo "<script>alert('Invalid format. Only jpg / jpeg/ png /gif format allowed');</script>";
            }
        }
        if($pic){
            $imgnewfile=md5($pic).time().$extension;
            move_uploaded_file($_FILES["image"]["tmp_name"],"uploads/about/rebbelib/".$imgnewfile);          
        }else{
            $imgnewfile='';
        }
        $sql_statement="INSERT INTO messages (message_from, content, imagefile, name, position) VALUES ('$title', '$content', '$imgnewfile', '$name', '$position')";
        
        if(mysqli_query($conn,$sql_statement)) {
            echo "Data added Successfully";
        } else {
            echo "Error: " . $sql_statement . "<br>" . mysqli_error($conn);
        }
        
    }else{
        $extension = substr($pic,strlen($pic)-4,strlen($pic));
        $allowed_extensions = array(".jpg","jpeg",".png");
        if($pic){
            if(!in_array($extension,$allowed_extensions))
            {
                echo "<script>alert('Invalid format. Only jpg / jpeg/ png /gif format allowed');</script>";
            }
        }
        if($pic){
            $imgnewfile=md5($pic).time().$extension;
            move_uploaded_file($_FILES["image"]["tmp_name"],"uploads/about/rebbelib/".$imgnewfile);          
        }else{
            $sql = "SELECT * FROM messages WHERE id = $id and status='active'";

            // Execute query and fetch record data
            $result = $conn->query($sql);
            $row = $result->fetch_assoc();
            $imgnewfile=$row['imagefile'];
        }
        // $sql_statement="INSERT INTO history_histogram (title, content, image, imagepath) VALUES ('$title', '$content', '$imgnewfile', '')";
        $update="update messages set message_from='$title',content='$content',imagefile='$imgnewfile',name='$name',position='$position',updted_at='$currentDate' where id='$id'";	

        if(mysqli_query($conn,$update)) {
            echo "Data added Successfully";
        } else {
            echo "Error: " . $update . "<br>" . mysqli_error($conn);
        }
    }
    
    

?>