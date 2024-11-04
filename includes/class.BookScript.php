<?php
  include_once('includes/common_const.php');
 include_once('includes/class.Db_client.php');
 class BookScript  extends Db_client{
	public function __construct() {
        parent::__construct(); // Call the constructor of the parent class (MyDatabaseClassPDO)
    }
    public function _writeLog($content	=	"",$filename	=	"log.txt")
	{		
		$fp 	=	fopen('uploads/logFiles/'.$filename, "a+");			
		fputs($fp,$content);		
		fputs($fp, "\r\n");
		fclose($fp);	
	}
    public function calculateHoursFromSLAMinutes($slaMinutes) {
    // Divide the SLA minutes by 60 to get hours
    $hours = $slaMinutes / 60;

    return $hours;
    }
    // Function to debit bookingamount from total balance of agent
    public function debitAmount($totalBalance, $amountToDebit) {
        // Perform the debit operation
            // Remove commas from the amountToDebit

         $amountToDebit = str_replace(',', '', $amountToDebit);

        // Convert the cleaned string to a float
        $amountToDebit = floatval($amountToDebit);
        $newBalance = $totalBalance - $amountToDebit;

        // Ensure precision by formatting to 2 decimal places
        $newBalance = number_format($newBalance, 2, '.', '');

        // Return the new balance
        return $newBalance;
    }
     public function updateInUserAgentCredit($userId,$new_credit_agent){
    
        $tableName = "users"; //cms table name
        $updateData = array(
                    'credit_balance' => $new_credit_agent
                );
                $condition = "`id` = ".$userId;
             //    LIKE '%MF23720823%' 
     
        $result =   $this->update($tableName, $updateData, $condition);
      //print_r($result);exit;
       return $result;		
       }
       //mail content 
    public function getEmailContent($content){

                            $messageDatacontent =   $content;
                                 $messageData      = '
                                <html>
                                <body>
                                <table style="width:100%">
                                    <tbody>
                                        <tr>
                                            <td>
                                                <center>
                                                    <table style="width:80%;margin:0 auto">
    
                                                        <tbody>
                                                            <tr>
                                                                <td style="text-align:center;padding-bottom:15px;padding-top:15px">
                                                                    <h2 style="margin-top:0;margin-bottom:0">
                                                                        <img width="125" height="30"
                                                                            src="https://bulatrips.com/images/bulatrips-logo.png"
                                                                            alt="Bulatrip" title="Bulatrip"
                                                                            style="height:30px;width:125px;display:inline-block;margin-top:0;margin-bottom:0"
                                                                            class="CToWUd" data-bit="iit">
                                                                    </h2>
                                                                </td>
                                                            </tr>
    
    
                                                            <tr>
                                                                <td bgcolor="#ffffff" style="padding-top:20px;text-align:center">
    
                                                                    <div width="100%"
                                                                        style="max-width:480px;padding:5pt 0;background-color:#eff5fc;border-radius:10px;margin:0 auto;width:calc(100% - 32px);margin-bottom:24px">
    
    
    
                                               
    
                                                                    </div>
    
    
    
                                                                    <div align="center" style="padding:0 10px;padding-bottom:5px">
                                                                        <p
                                                                            style="font-family:Arial,sans-serif;color:#000000;letter-spacing:-0.5px;text-align:center;margin-top:0;margin-bottom:0">
                                                                            '.$messageDatacontent.'
                                                                        </p>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </center>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                             </body>
                                </html>';

                                return   $messageData;
     }


     public function insPaySts($fsc,$userId,$payStatus,$amount,$currency){
    
        $tableName = "payment_user"; //payment table name
      //cho "**********************".$TotalRefundAmount;
        $params = ['user_id'=>$userId,'currency'=>$currency,'amount'=>$amount,'fsc'=>$fsc,'payment_status'=>$payStatus];
   // print_r($params);exit;
        $result =   $this->insertInto($tableName, $params) ;
      //print_r($result);exit;
       return $result;		
       }
      public function getExtraserviceAmoount($data){
        $adultCount =   $data['adultCount'];
        $childCount =   $data['childCount'];
        //$adultCount =   $revalidData['adultCount'];
        $TotserviceAmnt =   0;
        for ($i = 1; $i <= $adultCount; $i++) {
            if (isset($data['baggageService' . $i])) {
                    $baggageServiceData = explode('/', $data['baggageService' . $i]);
                    if (isset($baggageServiceData[0]) && isset($baggageServiceData[1]) && isset($baggageServiceData[2])) {
                        $baggageID = $baggageServiceData[0];
                        $baggageDescription = $baggageServiceData[1];
                        $baggageAmount = $baggageServiceData[2];
                       $TotserviceAmnt += isset($baggageServiceData[2]) ? (float) $baggageServiceData[2] : 0;

                    } else {
                        $baggageID = "";
                        $baggageDescription = "";
                        $baggageAmount = "";
                         $TotserviceAmnt +=  0;
                    }
            } else {
                    $baggageID = "";
                    $baggageDescription = "";
                    $baggageAmount = "";
                     $TotserviceAmnt +=  0;
             }
                

                if (isset($data['mealService' . $i])) {
                $mealServiceData = explode('/', $data['mealService' . $i]);
                if (isset($mealServiceData[0]) && isset($mealServiceData[1]) && isset($mealServiceData[2])) {

                    $mealId = $mealServiceData[0];
                    $mealDescription = $mealServiceData[1];
                    $mealAmount = $mealServiceData[2];
                                $TotserviceAmnt += isset($mealServiceData[2]) ? (float) $mealServiceData[2] : 0;

                    } else{
                        $mealId = "";
                        $mealDescription = "";
                        $mealAmount ="";
                        $TotserviceAmnt +=   0;
                    }
                } else {
                    $mealId = "";
                    $mealDescription = "";
                    $mealAmount ="";
                    $TotserviceAmnt +=   0;
                }

                //return extra services
                if (isset($data['baggageServiceReturn' . $i])) {
                    $baggageServiceDataReturn = explode('/', $data['baggageServiceReturn' . $i]);
                    if (isset($baggageServiceDataReturn[0]) && isset($baggageServiceDataReturn[1]) && isset($baggageServiceDataReturn[2])) {
                        $baggageReturnID = $baggageServiceDataReturn[0];
                        $baggageReturnDescription = $baggageServiceDataReturn[1];
                        $baggageReturnAmount = $baggageServiceDataReturn[2];
                                   $TotserviceAmnt += isset($baggageServiceDataReturn[2]) ? (float) $baggageServiceDataReturn[2] : 0;

                    } else {
                        $baggageReturnID = "";
                        $baggageReturnDescription = "";
                        $baggageReturnAmount = "";
                         $TotserviceAmnt +=   0;
                    }
                } else {
                    $baggageReturnID = "";
                    $baggageReturnDescription = "";
                    $baggageReturnAmount = "";
                     $TotserviceAmnt +=   0;
                }
                

                if (isset($data['mealServiceReturn' . $i])) {
                $mealServiceDataReturn = explode('/', $data['mealServiceReturn' . $i]);
                if (isset($mealServiceDataReturn[0]) && isset($mealServiceDataReturn[1]) && isset($mealServiceDataReturn[2])) {

                    $mealReturnId = $mealServiceDataReturn[0];
                    $mealReturnDescription = $mealServiceDataReturn[1];
                    $mealReturnAmount = $mealServiceDataReturn[2];
                                $TotserviceAmnt += isset($mealServiceDataReturn[2]) ? (float) $mealServiceDataReturn[2] : 0;

                    } else{
                        $mealReturnId = "";
                        $mealReturnDescription = "";
                        $mealReturnAmount ="";
                         $TotserviceAmnt +=   0;
                    }
                } else {
                    $mealReturnId = "";
                    $mealReturnDescription = "";
                    $mealReturnAmount ="";
                     $TotserviceAmnt +=   0;
                }
        }//close of adult for loop
        //childservice amnt
        if($childCount > 0){
             for ($i = 1; $i <= $childCount; $i++) {
                  if (isset($data['baggageServiceChild' . $i])) {
                    $baggageServiceData = explode('/', $data['baggageServiceChild' . $i]);
                        if (isset($baggageServiceData[0]) && isset($baggageServiceData[1]) && isset($baggageServiceData[2])) {
                            $baggageID = $baggageServiceData[0];
                            $baggageDescription = $baggageServiceData[1];
                            $baggageAmount = $baggageServiceData[2];
                            $TotserviceAmnt += isset($baggageServiceData[2]) ? (float) $baggageServiceData[2] : 0;
                        } else {
                            $baggageID = "";
                            $baggageDescription = "";
                            $baggageAmount = "";
                             $TotserviceAmnt +=  0;
                        }
                   } else {
                        $baggageID = "";
                        $baggageDescription = "";
                        $baggageAmount = "";
                         $TotserviceAmnt +=  0;
                    }
                

                if (isset($data['mealServiceChild' . $i])) {
                    $mealServiceData = explode('/', $data['mealServiceChild' . $i]);
                    if (isset($mealServiceData[0]) && isset($mealServiceData[1]) && isset($mealServiceData[2])) {

                    $mealId = $mealServiceData[0];
                    $mealDescription = $mealServiceData[1];
                    $mealAmount = $mealServiceData[2];
                     $TotserviceAmnt += isset($mealServiceData[2]) ? (float) $mealServiceData[2] : 0;
                    } else{
                        $mealId = "";
                        $mealDescription = "";
                        $mealAmount ="";
                         $TotserviceAmnt +=  0;
                    }
                } else {
                    $mealId = "";
                    $mealDescription = "";
                    $mealAmount ="";
                     $TotserviceAmnt +=  0;
                }
                //Extra service return

                if (isset($data['baggageServiceChildReturn' . $i])) {
                    $baggageServiceDataReturn = explode('/', $data['baggageServiceChildReturn' . $i]);
                    if (isset($baggageServiceDataReturn[0]) && isset($baggageServiceDataReturn[1]) && isset($baggageServiceDataReturn[2])) {
                        $baggageReturnID = $baggageServiceDataReturn[0];
                        $baggageReturnDescription = $baggageServiceDataReturn[1];
                        $baggageReturnAmount = $baggageServiceDataReturn[2];
                         $TotserviceAmnt += isset($baggageServiceDataReturn[2]) ? (float) $baggageServiceDataReturn[2] : 0;
                    } else {
                        $baggageReturnID = "";
                        $baggageReturnDescription = "";
                        $baggageReturnAmount = "";
                         $TotserviceAmnt +=  0;
                    }
                } else {
                    $baggageReturnID = "";
                    $baggageReturnDescription = "";
                    $baggageReturnAmount = "";
                     $TotserviceAmnt +=  0;
                }
                

                if (isset($data['mealServiceChildReturn' . $i])) {
                $mealServiceDataReturn = explode('/', $data['mealServiceChildReturn' . $i]);
                    if (isset($mealServiceDataReturn[0]) && isset($mealServiceDataReturn[1]) && isset($mealServiceDataReturn[2])) {

                        $mealReturnId = $mealServiceDataReturn[0];
                        $mealReturnDescription = $mealServiceDataReturn[1];
                        $mealReturnAmount = $mealServiceDataReturn[2];
                          $TotserviceAmnt += isset($mealServiceDataReturn[2]) ? (float) $mealServiceDataReturn[2] : 0;
                        } else{
                            $mealReturnId = "";
                            $mealReturnDescription = "";
                            $mealReturnAmount ="";
                             $TotserviceAmnt +=  0;
                        }
                    } else {
                        $mealReturnId = "";
                        $mealReturnDescription = "";
                        $mealReturnAmount ="";
                         $TotserviceAmnt +=  0;
                    }
                }

             }//eod child for loop
         //$amountToDebit = str_replace(',', '', $amountToDebit);
       return  $TotserviceAmnt ;
        // echo "here";exit;
       }
 /*
       //===
       public function insCncelSts_Search($bookingId,$userId,$BookingStatus,$Resolution, $mfreNum,$ProcessingMethod,$PTRId,$PTRType,$CreditNoteNumber,$PTRStatus,$CreditNoteStatus, $ticket_num ,$pax_booking_id_transaction ,$PaxId,$TicketStatus,$TotalRefundAmount,$Currency,$is_active_booking_status,$cancel_status,$message=''){
    
        $tableName = "search_cancel_ptr"; //cms table name
      //  echo $PTRType;
        $params = ['user_agent_id'=>$userId,'booking_id'=>$bookingId,'BookingStatus'=>$BookingStatus,'Resolution'=>$Resolution,'mfref'=>$mfreNum,'ProcessingMethod'=>$ProcessingMethod,'PTRId'=>$PTRId,'PTRtype' =>$PTRType,'CreditNoteNumber' =>$CreditNoteNumber,'PTRStatus'=>$PTRStatus,'CreditNoteStatus'=>$CreditNoteStatus,'ticket_num'=>$ticket_num,'pax_booking_id_transaction'=> $pax_booking_id_transaction ,'PaxId' =>$PaxId,
                    'ticket_status' =>$TicketStatus,'total_refund_amount'=>$TotalRefundAmount,'currency' =>$Currency,'is_active_booking_status'=>$is_active_booking_status,'search_cancel_success_status'=>$cancel_status,'message'=>$message];
   // print_r($params);exit;
        $result =   $this->insertInto($tableName, $params) ;
    //  print_r($result);exit;
       return $result;		
       }


       //====
        public function updateInDB_temp_book($tableName,$mfrefNum){
    
        $tableName = "temp_booking"; //cms table name
        $updateData = array(
                    'ticket_status' => 'cancelled'
                );
                $condition = "`mf_reference` LIKE '%".$mfrefNum."%'";
             //    LIKE '%MF23720823%' 
     
        $result =   $this->update($tableName, $updateData, $condition);
      //print_r($result);exit;
       return $result;		
       }
        public function updateInDB_trav($tableName,$ticketNum){
    
        $updateData = array(
                    'ticket_status' => 'cancelled'
                );
                $condition = "`e_ticket_number` LIKE '%".$ticketNum."%'";
             //    LIKE '%MF23720823%' 
     
        $result =   $this->update($tableName, $updateData, $condition);
      //print_r($result);exit;
       return $result;		
       }
       //update cancelbooking table


        public function updateInDB_cancelbooking($tableName,$ticketNum){
    
        $updateData = array(
                    'ptr_status' => 'completed',
                    'cancel_status' =>1
                );
                $condition = "`ticket_number` LIKE '%".$ticketNum."%'";
             //    LIKE '%MF23720823%' 
     
        $result =   $this->update($tableName, $updateData, $condition);
     
       return $result;		
       }
       //===
       public function count_ticketed__temp_book($tableName,$bookingId){

            $condition = "`flight_booking_id` = $bookingId   AND (`ticket_status` LIKE '%Ticketed%' OR `ticket_status` LIKE '%TktInProcess%' OR `ticket_status` IS NULL)";
           // " `flight_booking_id` = 233 AND `ticket_status` LIKE '%Ticketed%'";
             //    LIKE '%MF23720823%' 
     
        $result =   $this->getCount($tableName,$condition);
      //print_r($result);exit;
       return $result;		
       }
    // Example method that uses the inherited methods from MyDatabaseClassPDO
    public function insertDataIntoDB($name, $email) {
        // You can now use the inherited methods to perform database operations
        if ($this->insertData($name, $email)) {
            echo "Data inserted successfully.";
        } else {
            echo "Failed to insert data.";
        }
    }
        // Example method that uses the inherited getListData method
    public function displaySelectDropdown() {
        $selectData = $this->getListData('temp_booking');

        if (!empty($selectData)) {
            echo '<select name="selectOption">';
            foreach ($selectData as $option) {
                echo '<option value="' . $option['id'] . '">' . $option['dep_location'] . '</option>';
            }
            echo '</select>';
        } else {
            echo 'No data found.';
        }
    }

    // Add more methods to use the inherited database class methods as needed


public function BookCancelUsers($bookingId ,$userId)
 {
    $passengerData = $this->executePassengerQuery($bookingId, $userId);
        return $passengerData;
    }
    public function ReturnDepDate($bookingId, $userId,$arrival_location)
    {
    $depDate = $this->executeDepDateQuery($bookingId, $userId,$arrival_location);
        return $depDate;
    }
    public function MArkup_percentage_value($roleId)
    {
    $MarkupData = $this->executeMarkupQuery($roleId);
        return $MarkupData;
    }
    // Add more methods to use the inherited database class methods as needed

//============================================================
public function callApi($endpoint,$requestData){
        
         $apiEndpoint = APIENDPOINT.$endpoint;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiEndpoint);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestData));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Authorization: Bearer ' . BEARER
        ));
    
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return array(
        'httpCode' => $httpCode,
        'responseData' => $response
    );
}

public function getUSerDetails($tblname,$userId){

     $result =   $this->getUserData($tblname,$userId);
      //print_r($result);exit;
       return $result;	
    
}

    */

}

?>