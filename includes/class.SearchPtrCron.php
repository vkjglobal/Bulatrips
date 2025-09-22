<?php
include_once __DIR__ . '/class.MyConnection.php';
include_once __DIR__ . '/class.Db_clientCron.php';
 class SearchPtrCron  extends Db_clientCron{
	public function __construct() {
        parent::__construct(); // Call the constructor of the parent class (MyDatabaseClassPDO)
       
    }
    public function _writeLog($content	=	"",$filename	=	"log.txt")
	{		
		$path = __DIR__ . '/../uploads/logFiles/' . $filename;
		$fp = @fopen($path, "a+");
		if ($fp === false) {
			// Fallback to project root path as best effort
			$alt = dirname(__DIR__) . '/uploads/logFiles/' . $filename;
			$fp = @fopen($alt, "a+");
		}
		if ($fp !== false) {
			fputs($fp, $content);
			fputs($fp, "\r\n");
			fclose($fp);
		}
	}
    public function callApi($endpoint,$requestData){
        
        $apiEndpoint = APIENDPOINT.$endpoint;

       $ch = curl_init();
       curl_setopt($ch, CURLOPT_URL, $apiEndpoint);
       curl_setopt($ch, CURLOPT_POST, true);
       curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestData));
       curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
       curl_setopt($ch, CURLOPT_TIMEOUT, 60); // 60 second timeout
       curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30); // 30 second connection timeout
       curl_setopt($ch, CURLOPT_HTTPHEADER, array(
           'Content-Type: application/json',
           'Authorization: Bearer ' . BEARER
       ));
   
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        
        // Enhanced logging for debugging
        $this->_writeLog("API Request to: " . $apiEndpoint, 'api_debug.txt');
        $this->_writeLog("Request Data: " . json_encode($requestData), 'api_debug.txt');
        $this->_writeLog("HTTP Code: " . $httpCode, 'api_debug.txt');
        $this->_writeLog("Response: " . $response, 'api_debug.txt');
        
        if ($curlError) {
            $this->_writeLog("CURL Error: " . $curlError, 'api_debug.txt');
        }
        
        return array(
        'httpCode' => $httpCode,
        'responseData' => $response,
        'curlError' => $curlError
        );
    }
    public function updateInDB_cancelbooking($tableName,$ticketNum){
        if (empty($ticketNum)) {
            $this->_writeLog('Skip updateInDB_cancelbooking: empty ticket number', 'searchPtrCron.txt');
            return false;
        }
    
        $updateData = array(
                    'ptr_status' => 'completed',
                    'cancel_status' =>1
                );
                $condition = "`ticket_number` = '".addslashes($ticketNum)."'";
             //    LIKE '%MF23720823%'
     
        $result =   $this->update($tableName, $updateData, $condition);
     
        // Also propagate PTR ID from travellers_details into cancel_booking for consistency
        try {
            $row = $this->getLisQuery("SELECT ptr_id FROM travellers_details WHERE e_ticket_number LIKE '%".$ticketNum."%' ORDER BY id DESC LIMIT 1");
            if (!empty($row) && isset($row[0]['ptr_id']) && $row[0]['ptr_id'] !== null && $row[0]['ptr_id'] !== '') {
                $ptrIdRaw = $row[0]['ptr_id'];
                // ptrIdRaw may look like 'PTR_1757093237_7461' → extract the main numeric segment
                $ptrId = 0;
                if (is_numeric($ptrIdRaw)) {
                    $ptrId = (int)$ptrIdRaw;
                } else {
                    if (preg_match('/(\d{6,})/', $ptrIdRaw, $m)) {
                        $ptrId = (int)$m[1];
                    }
                }
                if ($ptrId > 0) {
                $sql = "UPDATE cancel_booking SET ptr_id = :ptr_id WHERE `ticket_number` = :t";
                $stmt = $this->conn->prepare($sql);
                $stmt->bindValue(':ptr_id', $ptrId);
                $stmt->bindValue(':t', $ticketNum);
                $stmt->execute();
                }
            }
        } catch (\Exception $e) {
            $this->_writeLog('Error updating ptr_id in cancel_booking: '.$e->getMessage(), 'searchPtrCron.txt');
        }

       return $result;		
    }
    public function updateInDB_trav($tableName,$ticketNum){
        if (empty($ticketNum)) {
            $this->_writeLog('Skip updateInDB_trav: empty ticket number', 'searchPtrCron.txt');
            return false;
        }
        // Log BEFORE values
        try {
            $before = $this->getLisQuery("SELECT e_ticket_number, ticket_status, void_status, cancel_type, cancel_date FROM travellers_details WHERE e_ticket_number = '".addslashes($ticketNum)."' LIMIT 1");
            $this->_writeLog('Before traveller update for ticket '.$ticketNum.': '.print_r($before, true), 'searchPtrCron.txt');
        } catch (Exception $e) {
            $this->_writeLog('Before traveller update read failed: '.$e->getMessage(), 'searchPtrCron.txt');
        }

        $updateData = array(
            'status' => 'cancelled', // use status column to avoid auto timestamp on ticket_status
            'ticket_status' => 'cancelled',
            'void_status' => 'Completed',
            'cancel_type' => 'void',
            'cancel_date' => date('Y-m-d H:i:s')
        );
        $condition = "`e_ticket_number` = '".addslashes($ticketNum)."'";
        $result = $this->update($tableName, $updateData, $condition);

        // Log AFTER values
        try {
            $after = $this->getLisQuery("SELECT e_ticket_number, ticket_status, void_status, cancel_type, cancel_date FROM travellers_details WHERE e_ticket_number = '".addslashes($ticketNum)."' LIMIT 1");
            $this->_writeLog('After traveller update for ticket '.$ticketNum.': '.print_r($after, true), 'searchPtrCron.txt');
        } catch (Exception $e) {
            $this->_writeLog('After traveller update read failed: '.$e->getMessage(), 'searchPtrCron.txt');
        }

        return $result;
    }
    public function count_ticketed__temp_book($tableName,$bookingId){

        $condition = "`flight_booking_id` = $bookingId   AND (`ticket_status` LIKE '%Ticketed%' OR `ticket_status` LIKE '%TktInProcess%' OR `ticket_status` IS NULL)";
       // " `flight_booking_id` = 233 AND `ticket_status` LIKE '%Ticketed%'";
         //    LIKE '%MF23720823%' 
 
    $result =   $this->getCount($tableName,$condition);
    return $result;		
   }
   public function updateInDB_temp_book($tableName,$mfrefNum){
    
        $tableName = "temp_booking"; //cms table name
        $updateData = array(
                    'ticket_status' => 'cancelled',
                    'booking_status' => 'cancelled'
                );
            $condition = "`mf_reference` LIKE '%".$mfrefNum."%'";
         //    LIKE '%MF23720823%' 
 
        $result =   $this->update($tableName, $updateData, $condition);
        return $result;		
   }
    public function insCncelSts_Search($bookingId,$userId,$BookingStatus,$Resolution, $mfreNum,$ProcessingMethod,$PTRId,$PTRType,$CreditNoteNumber,$PTRStatus,$CreditNoteStatus, $ticket_num ,$pax_booking_id_transaction ,$PaxId,$TicketStatus,$TotalRefundAmount,$Currency,$is_active_booking_status,$cancel_status,$message=''){
    
        $tableName = "search_cancel_ptr"; //cms table name
        $params = ['user_agent_id'=>$userId,'booking_id'=>$bookingId,'BookingStatus'=>$BookingStatus,'Resolution'=>$Resolution,'mfref'=>$mfreNum,'ProcessingMethod'=>$ProcessingMethod,'PTRId'=>$PTRId,'PTRtype' =>$PTRType,'CreditNoteNumber' =>$CreditNoteNumber,'PTRStatus'=>$PTRStatus,'CreditNoteStatus'=>$CreditNoteStatus,'ticket_num'=>$ticket_num,'pax_booking_id_transaction'=> $pax_booking_id_transaction ,'PaxId' =>$PaxId,
                    'ticket_status' =>$TicketStatus,'total_refund_amount'=>$TotalRefundAmount,'currency' =>$Currency,'is_active_booking_status'=>$is_active_booking_status,'search_cancel_success_status'=>$cancel_status,'message'=>$message];
        $result =   $this->insertInto($tableName, $params) ;
       return $result;		
    }
    public function getBookCronIDs()
    {
       
       
            // Validate the email address
            try {
                // Only fetch truly pending rows. Exclude rows we have already notified or completed
                $query = "SELECT * FROM cancel_booking 
                          WHERE mf_ref_num != '' 
                          AND (ptr_type IN ('Refund','Void','Reissue')) 
                          AND ptr_status = 'InProcess'
                          AND cancel_status = 0
                          AND (message IS NULL OR message = '' OR message NOT LIKE '%email sent%')";

                $stmt = $this->conn->prepare($query);

                $stmt->execute();

                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
               
                return $result;
            } catch (PDOException $e) {
                // Handle the exception (e.g., log the error)
                return null;
            }
    }
    
    public function getSpecificPTR($ptrId = null, $mfRef = null)
    {
        try {
            $conditions = [];
            $params = [];
            
            if ($ptrId) {
                $conditions[] = "ptr_id = :ptr_id";
                $params['ptr_id'] = $ptrId;
            }
            
            if ($mfRef) {
                $conditions[] = "mf_ref_num = :mf_ref";
                $params['mf_ref'] = $mfRef;
            }
            
            if (empty($conditions)) {
                return [];
            }
            
            $whereClause = implode(' OR ', $conditions);
            $query = "SELECT * FROM cancel_booking WHERE ($whereClause) AND (ptr_type = 'Refund' OR ptr_type = 'Void' OR ptr_type = 'Reissue')";

            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);

            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
           
            return $result;
        } catch (PDOException $e) {
            // Handle the exception (e.g., log the error)
            return [];
        }
    }
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
                                                        src="https://bulatrips.com/images/Image-Logo-vec.png"
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
    
    /**
     * Handle PTRs that are stuck and returning "No records found"
     * Mark them as failed after SLA expiry
     */
    public function handleStuckPTR($bookingId, $ptrId, $mfRef, $ptrType, $slaMinutes) {
        try {
            // Calculate if PTR is beyond SLA
            $query = "SELECT created_date FROM cancel_booking WHERE id = :booking_id";
            $stmt = $this->conn->prepare($query);
            $stmt->execute(['booking_id' => $bookingId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result) {
                $createdTime = strtotime($result['created_date']);
                $currentTime = time();
                $elapsedMinutes = ($currentTime - $createdTime) / 60;
                
                // If PTR is beyond SLA + 30 minutes buffer, mark as failed
                if ($elapsedMinutes > ($slaMinutes + 30)) {
                    $updateData = array(
                        'ptr_status' => 'failed',
                        'failure_reason' => 'PTR stuck in system - No records found after SLA expiry',
                        'failed_at' => date('Y-m-d H:i:s')
                    );
                    $condition = "id = " . $bookingId;
                    
                    $this->update('cancel_booking', $updateData, $condition);
                    
                    $this->_writeLog("Marked PTR as failed - Booking: $bookingId, PTR: $ptrId, Elapsed: {$elapsedMinutes}min", 'api_debug.txt');
                    
                    return true;
                }
            }
            
            return false;
        } catch (Exception $e) {
            $this->_writeLog("Error handling stuck PTR: " . $e->getMessage(), 'api_debug.txt');
            return false;
        }
    }
    
    /**
     * Get PTRs that need immediate attention (beyond SLA)
     */
    public function getOverduePTRs() {
        try {
            $query = "SELECT *, 
                      TIMESTAMPDIFF(MINUTE, created_date, NOW()) as elapsed_minutes,
                      sla_minutes
                      FROM cancel_booking 
                      WHERE ptr_status = 'InProcess' 
                      AND TIMESTAMPDIFF(MINUTE, created_date, NOW()) > (sla_minutes + 15)
                      ORDER BY created_date ASC";

            $stmt = $this->conn->prepare($query);
            $stmt->execute();

            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $result;
        } catch (PDOException $e) {
            $this->_writeLog("Error getting overdue PTRs: " . $e->getMessage(), 'api_debug.txt');
            return [];
        }
    }
    
    /**
     * Send alert email for stuck PTRs
     */
    public function sendStuckPTRAlert($ptrDetails) {
        $subject = "URGENT: PTR Stuck Alert - PTR ID " . $ptrDetails['ptr_id'];
        $content = "
        <h3>PTR Processing Alert</h3>
        <p><strong>PTR Details:</strong></p>
        <ul>
            <li>PTR ID: {$ptrDetails['ptr_id']}</li>
            <li>MF Reference: {$ptrDetails['mf_ref_num']}</li>
            <li>PTR Type: {$ptrDetails['ptr_type']}</li>
            <li>Created: {$ptrDetails['created_date']}</li>
            <li>SLA: {$ptrDetails['sla_minutes']} minutes</li>
            <li>Elapsed: {$ptrDetails['elapsed_minutes']} minutes</li>
            <li>Status: STUCK - No records found</li>
        </ul>
        <p><strong>Action Required:</strong></p>
        <p>Please contact Mystifly support immediately for PTR ID {$ptrDetails['ptr_id']}</p>
        ";
        
        $messageData = $this->getEmailContent($content);
        
        // You can implement email sending here
        $this->_writeLog("Alert needed for stuck PTR: " . $ptrDetails['ptr_id'], 'api_debug.txt');
        
        return true;
    }

    /**
     * Get booking contact email and mf reference
     */
    public function getBookingContactEmail($bookingId) {
        try {
            $stmt = $this->conn->prepare("SELECT contact_email, mf_reference FROM temp_booking WHERE id = :id");
            $stmt->execute(['id' => (int)$bookingId]);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $row ?: [];
        } catch (\PDOException $e) {
            $this->_writeLog('Error fetching contact email: ' . $e->getMessage(), 'searchPtrCron.txt');
            return [];
        }
    }
}

?>