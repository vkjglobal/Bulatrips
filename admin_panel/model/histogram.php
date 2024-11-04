<?php
    include '../includes/dbConnect.php';
    $title = $_POST['histogram-title'];
    $content = $_POST['content'];
   
    $pic=$_FILES["image"]["name"];
    

    // if (isset($_FILES['image'])) {
    //     $temp_name = $_FILES['image']['tmp_name'];
    //     $destination = 'https://localhost:8080/Reubro/mcst/admin_panel/uploads/' . $_FILES['image']['name'];
    //     move_uploaded_file($temp_name, $destination);
    //     echo 'File uploaded successfully';
    //   } else {
    //     echo 'Error uploading file';
    //   }
    //         $extension = substr($pic,strlen($pic)-4,strlen($pic));
    //         $allowed_extensions = array(".jpg","jpeg",".png");
    //         if($pic){
    //             if(!in_array($extension,$allowed_extensions))
    //             {
    //                 echo "<script>alert('Invalid format. Only jpg / jpeg/ png /gif format allowed');</script>";
    //             }

    //         }
            
            
           
    //             if($pic){
    //                 $imgnewfile = uniqid('', true) '.' $extension;
    //                 $file_tmp = $_FILES['image']['tmp_name'];
    //                 print_r( $file_tmp);
    //                 $file_destination = 'https://localhost:8080/Reubro/mcst/admin_panel/uploads/about/histogram/' . $imgnewfile;
    //                 move_uploaded_file($file_tmp, $file_destination);

    //             }else{
    //                 $imgnewfile='';
    //             }

  
    // $sql_statement="INSERT INTO history_histogram (title, content, image, imagepath) VALUES ('$title', '$content', '$imgnewfile', '')";
    
    // if(mysqli_query($conn,$sql_statement)) {
    //     echo "Quotation Successfully submitted";
    // } else {
    //     echo "Error: " . $sql_statement . "<br>" . mysqli_error($conn);
    // }

    $target_dir = "http://localhost:8080/Reubro/mcst/admin_panel/uploads/about/histogram/"; // specify the directory where the uploaded file will be stored
    $target_file = $target_dir . basename($_FILES["image"]["name"]); // specify the path of the uploaded file
    $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION)); // get the file extension of the uploaded file
    $extensions_arr = array("jpg","jpeg","png","gif"); // set the allowed file types
    
    // check if the uploaded file is a valid image file
    if(in_array($imageFileType,$extensions_arr)) {
        // move the uploaded file to the specified directory
        if(move_uploaded_file($_FILES["image"]["tmp_name"],$target_file)) {
            // echo "The file ". basename( $_FILES["image"]["name"]). " has been uploaded.";
            
            // Save file details to database
            $filename = basename($_FILES["image"]["name"]);
            $filesize = $_FILES["image"]["size"];
            $filetype = $_FILES["image"]["type"];
            
          
         
            // Insert file details into database
            // $sql = "INSERT INTO files (filename, filesize, filetype) VALUES ('$filename', '$filesize', '$filetype')";
            $sql="INSERT INTO history_histogram (title, content, image, imagepath) VALUES ('$title', '$content', '$filename', '')";

            if (mysqli_query($conn, $sql)) {
                echo "File details saved to database.";
            } else {
                echo "Error: " . $sql . "<br>" . mysqli_error($conn);
            }
            
            
        } else {
            echo "Sorry, there was an error uploading your file.";
        }
    } else {
        echo "Sorry, only JPG, JPEG, PNG, & GIF files are allowed.";
    }
 
?>