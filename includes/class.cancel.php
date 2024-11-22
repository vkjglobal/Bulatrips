<?php
  include_once('includes/common_const.php');
 include_once('includes/class.Db_client.php');
 class Cancel  extends Db_client{
	public function __construct() {
        parent::__construct(); // Call the constructor of the parent class (MyDatabaseClassPDO)
    }

      public function insCncelSts($bookingId,$userId,$precancelsts,$errorCode='',$mfreNum="",$traceId='',$http_code_response="",$PTRId='',$PTRType='',$SLAInMinutes='',$PTRStatus='',$VoidingWindow='',$ticket_num ='',$AdminCharges='',$GSTCharge='',$TotalVoidingFee='',$TotalRefundAmount='',$Currency='',$cancel_status='',$meesage_new=''){
    
        $tableName = "cancel_booking"; //cms table name
      //cho "**********************".$TotalRefundAmount;
        $params = ['user_agent_id'=>$userId,'booking_id'=>$bookingId,'pre_post_ticket_status'=>$precancelsts,'err_code'=>$errorCode,'mf_ref_num'=>$mfreNum,'trace_id'=>$traceId,'http_code_response' =>$http_code_response,'ptr_id'=>$PTRId,'ptr_type' =>$PTRType,'sla_minutes'=>$SLAInMinutes,'ptr_status'=>$PTRStatus,'void_window'=> $VoidingWindow ,'ticket_number'=>$ticket_num,'admin_charge' =>$AdminCharges,
                    'gst_charge' =>$GSTCharge,'total_void_fee'=>$TotalVoidingFee,'total_refund_amount'=>$TotalRefundAmount,'currency'=>$Currency,'cancel_status'=>$cancel_status,'message'=>$meesage_new];
  //  print_r($params);exit;
        $result =   $this->insertInto($tableName, $params) ;
      //print_r($result);exit;
       return $result;		
       }

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
public function getUSerDetails($tblname,$userId){

     $result =   $this->getUserData($tblname,$userId);
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

}

?>