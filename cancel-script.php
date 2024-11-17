<?php
    session_start();
    error_reporting(0);
    require_once('includes/dbConnect.php');
    include_once('includes/class.cancel.php');
    include_once('includes/class.Users.php');
    include_once('includes/class.BookScript.php');
    $objBook    =   new BookScript();
    $objCancel    =   new Cancel();
     $bookingId = $_POST['booking-id'];
     $adminToemail = "no-reply@bulatrips.com";
    if(isset($bookingId)){
        $stmtbookingid = $conn->prepare('SELECT * FROM temp_booking WHERE id = :bookingid and user_id = :userid');
        $userId = $_SESSION['user_id'];
        $stmtbookingid->execute(array('bookingid' => $bookingId,'userid' => $userId ));
        $bookingData = $stmtbookingid->fetch(PDO::FETCH_ASSOC);
        $apiEndpoint = 'https://restapidemo.myfarebox.com/api/v1/Booking/Cancel';
        $bearerToken = '18AEA8F0-5B21-41ED-9993-DD7A8123B0D2-1560';
    
        $objUser = new Users();
        $userDetails = $objUser->getUserDetails($userId);
    
        // Construct the API request payload
        $requestData = array(          
            'UniqueID' => $bookingData['mf_reference'],
            'Target' => 'Test',
            // 'ConversationId' => 'string',
        );
       
        
      
       
        // Send the API request
    
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiEndpoint);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestData));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Authorization: Bearer ' . $bearerToken
        ));
    
        $response = curl_exec($ch);
        curl_close($ch);
    
        // Process the API response
        if ($response === false) {
            // Error handling
            echo 'Error: ' . curl_error($ch);
        } else {
            // Process the response data
            $responseData = json_decode($response, true);
            // Handle the response data as needed
        }
        // Handle the API response
    
        if ($response) {
            $responseData = json_decode($response, true);

                if($responseData['Success'] == true){

                    //=================log write for book API ======
                         
                  $logRes =   print_r($responseData, true);
                  $logReQ =   print_r($requestData, true);
                  $objCancel->_writeLog('-------------'.date('l jS \of F Y h:i:s A').'-------------','cancel.txt');
                  $objCancel->_writeLog('Request Received\n'.$logReQ,'cancel.txt');
                  $objCancel->_writeLog('REsponse Received for MF:\n'.$bookingData['mf_reference']. 'OR USERID='.$userId,'cancel.txt');
                  $objCancel->_writeLog('userId is '.$userId. '& MF# recieved '.$responseData['Data']['UniqueID'],'cancel.txt');
                       
      
                  $objCancel->_writeLog('REsponse Received\n'.$logRes,'cancel.txt');   
                 
                  //============ END log write for book API ========== 
             
                  //Update booking status
                  
                  $stmtupdate = $conn->prepare('UPDATE temp_booking SET booking_status = :bookingStatus WHERE id = :id');
                  $bookingStatus = 'Cancelled';
                  $stmtupdate->bindParam(':bookingStatus', $bookingStatus);
                  $stmtupdate->bindParam(':id', $bookingId);
                  // Execute the query
                  $stmtupdate->execute();
      
                  //====================email send code to admin regarding cancellation of email======
      
                  include_once('mail_send.php');
      
                  $subject = "Bulatrips Booking Cancellation Completed";                  
      
                    $email=   $userDetails['email'];
                    $name   =   $userDetails['first_name']." ".$userDetails['last_name'];
                    $content    =   '<p>Hello,</p>
                                        <p>This agent , '. $name .', with email '.$userDetails['email'].' has a Processed the cancellation request successfully transaction for booking id:'.$bookingId;
                    $messageData =   $objBook->getEmailContent($content);
                    // print_r($messageData);exit;
                    $headers="";
                    $email = $adminToemail; //Need ADMIN email here
    
                    $contacts= sendMail($email,$subject, $messageData,$headers);
        
                }
                //=====================email ends for admin=======
        }
        
        echo json_encode($response);
        exit;
    }
    