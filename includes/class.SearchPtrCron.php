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
        $ptr_id = $ptrDetails['ptr_id'];
        $mf_ref = $ptrDetails['mf_ref_num'];
        $ptr_type = $ptrDetails['ptr_type'];
        $elapsed = $ptrDetails['elapsed_minutes'];
        $sla = $ptrDetails['sla_minutes'];
        $booking_id = $ptrDetails['booking_id'];
        
        // Format times for better readability
        $createdTime = date('d M Y, H:i:s', strtotime($ptrDetails['created_date']));
        $expectedCompletionTime = date('d M Y, H:i:s', strtotime($ptrDetails['created_date']) + ($sla * 60));
        $currentTime = date('d M Y, H:i:s');
        
        // Calculate delay in hours and minutes
        $delayMinutes = $elapsed - $sla;
        $delayHours = floor($delayMinutes / 60);
        $delayMins = $delayMinutes % 60;
        $delayText = ($delayHours > 0 ? $delayHours . " hours " : "") . $delayMins . " minutes";
        
        // Get customer email for reference
        $contact = $this->getBookingContactEmail($booking_id);
        $customerEmail = isset($contact['contact_email']) ? trim($contact['contact_email']) : 'Not Available';
        
        // Admin alert email content
        $adminSubject = "🚨 URGENT: PTR Stuck Alert - PTR ID " . $ptr_id;
        $adminContent = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <style>
                .critical-alert { background: #f8d7da; border: 3px solid #dc3545; padding: 20px; border-radius: 8px; margin: 20px; }
                .info-table { width: 100%; border-collapse: collapse; margin: 15px 0; }
                .info-table td { padding: 10px; border-bottom: 1px solid #ddd; }
                .info-table tr:nth-child(even) { background: #f9f9f9; }
                .label { font-weight: bold; width: 220px; color: #555; }
                .action-box { background: #dc3545; color: white; padding: 20px; margin: 20px 0; border-radius: 5px; }
            </style>
        </head>
        <body style="font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5;">
            <div class="critical-alert">
                <h2 style="color: #dc3545; margin-top: 0; font-size: 24px;">⚠️ CRITICAL: PTR STUCK ALERT</h2>
                
                <h3 style="color: #333; border-bottom: 2px solid #dc3545; padding-bottom: 5px;">PTR Information:</h3>
                <table class="info-table">
                    <tr>
                        <td class="label">PTR ID:</td>
                        <td><strong style="color: #dc3545; font-size: 18px;">' . htmlspecialchars($ptr_id) . '</strong></td>
                    </tr>
                    <tr>
                        <td class="label">MF Reference:</td>
                        <td><strong>' . htmlspecialchars($mf_ref) . '</strong></td>
                    </tr>
                    <tr>
                        <td class="label">Booking ID:</td>
                        <td>' . htmlspecialchars($booking_id) . '</td>
                    </tr>
                    <tr>
                        <td class="label">PTR Type:</td>
                        <td>' . htmlspecialchars($ptr_type) . '</td>
                    </tr>
                    <tr>
                        <td class="label">Customer Email:</td>
                        <td>' . htmlspecialchars($customerEmail) . '</td>
                    </tr>
                </table>
                
                <h3 style="color: #333; border-bottom: 2px solid #dc3545; padding-bottom: 5px;">Timing Analysis (UTC):</h3>
                <table class="info-table">
                    <tr>
                        <td class="label">Created At:</td>
                        <td>' . $createdTime . '</td>
                    </tr>
                    <tr>
                        <td class="label">Expected Completion:</td>
                        <td>' . $expectedCompletionTime . '</td>
                    </tr>
                    <tr>
                        <td class="label">Current Time:</td>
                        <td>' . $currentTime . '</td>
                    </tr>
                    <tr>
                        <td class="label">SLA Time:</td>
                        <td>' . $sla . ' minutes (' . round($sla/60, 1) . ' hours)</td>
                    </tr>
                    <tr>
                        <td class="label">Elapsed Time:</td>
                        <td>' . $elapsed . ' minutes (' . round($elapsed/60, 1) . ' hours)</td>
                    </tr>
                    <tr style="background: #f8d7da;">
                        <td class="label">⚠️ DELAY:</td>
                        <td><strong style="color: #dc3545; font-size: 18px;">' . $delayText . ' OVERDUE</strong></td>
                    </tr>
                </table>
                
                <div class="action-box">
                    <h3 style="margin: 0 0 15px 0;">🚨 IMMEDIATE ACTIONS REQUIRED:</h3>
                    <ol style="margin: 0; padding-left: 20px; line-height: 2;">
                        <li><strong>Contact Mystifly Support:</strong> support@mystifly.com</li>
                        <li><strong>Provide PTR ID:</strong> ' . htmlspecialchars($ptr_id) . '</li>
                        <li><strong>Provide MF Reference:</strong> ' . htmlspecialchars($mf_ref) . '</li>
                        <li><strong>Check Mystifly Dashboard</strong> for manual intervention</li>
                        <li><strong>Customer Has Been Notified:</strong> ' . htmlspecialchars($customerEmail) . '</li>
                        <li><strong>Manual Cron Check:</strong> <a href="' . (defined('ENVIRONMENT_VAR') ? ENVIRONMENT_VAR : 'http://localhost/bulatrips/') . 'CronJob/cronSearchPtr?ptr_id=' . $ptr_id . '" style="color: white; text-decoration: underline;">Run Manual Check</a></li>
                    </ol>
                </div>
                
                <p style="margin-top: 20px; padding: 15px; background: #fff3cd; border-radius: 5px; border-left: 4px solid #ffc107;">
                    <strong>⚡ Quick Link:</strong> 
                    <a href="' . (defined('ENVIRONMENT_VAR') ? ENVIRONMENT_VAR : 'http://localhost/bulatrips/') . 'cancel_user?booking_id=' . $booking_id . '" style="color: #0d6efd; text-decoration: none; font-weight: bold;">→ View Booking Details</a>
                </p>
                
                <p style="font-size: 11px; color: #999; margin-top: 30px; border-top: 1px solid #ddd; padding-top: 15px;">
                    This alert was automatically generated by Bulatrips PTR Monitoring System<br>
                    Alert Time: ' . gmdate('d M Y, H:i:s') . ' UTC<br>
                    System: Production | Environment: Live | Severity: CRITICAL
                </p>
            </div>
        </body>
        </html>';
        
        // Send to admin and monitoring email
        $adminEmails = [
            'admin@bulatrips.com',
            'mindinstructions@gmail.com'
        ];
        
        foreach ($adminEmails as $adminEmail) {
            try {
                if (function_exists('sendMail')) {
                    sendMail($adminEmail, $adminSubject, $adminContent);
                    $this->_writeLog("Stuck PTR alert sent to: " . $adminEmail . " for PTR: " . $ptr_id, 'searchPtrCron.txt');
                }
            } catch (Exception $e) {
                $this->_writeLog("Failed to send alert email to {$adminEmail}: " . $e->getMessage(), 'searchPtrCron.txt');
            }
        }
        
        return true;
    }
    
    /**
     * Send delay notification to customer
     * Triggered when PTR processing takes longer than expected
     */
    public function sendCustomerDelayNotification($ptrDetails) {
        $booking_id = $ptrDetails['booking_id'];
        $ptr_id = $ptrDetails['ptr_id'];
        $ptr_type = $ptrDetails['ptr_type'];
        $sla = $ptrDetails['sla_minutes'];
        
        // Get customer email
        $contact = $this->getBookingContactEmail($booking_id);
        $customerEmail = isset($contact['contact_email']) ? trim($contact['contact_email']) : '';
        $mf_ref = isset($contact['mf_reference']) ? $contact['mf_reference'] : $ptrDetails['mf_ref_num'];
        
        if (empty($customerEmail)) {
            $this->_writeLog("No customer email found for booking: " . $booking_id, 'searchPtrCron.txt');
            return false;
        }
        
        // Get customer name
        $contactName = 'Customer';
        try {
            $stmt = $this->conn->prepare("SELECT contact_first_name, contact_last_name FROM temp_booking WHERE id = :id LIMIT 1");
            $stmt->execute(['id' => (int)$booking_id]);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            if ($row) {
                $first = trim($row['contact_first_name'] ?? '');
                $last = trim($row['contact_last_name'] ?? '');
                $full = trim($first . ' ' . $last);
                if ($full !== '') {
                    $contactName = $full;
                }
            }
        } catch (\PDOException $e) {
            // Use default name
        }
        
        // Calculate expected completion time in UTC
        $createdTimestamp = strtotime($ptrDetails['created_date']);
        $originalExpectedUTC = gmdate('d M Y, H:i', $createdTimestamp + ($sla * 60)) . ' UTC';
        $newEstimatedUTC = gmdate('d M Y, H:i', time() + 1800) . ' UTC'; // +30 mins from now
        
        // Determine process type text
        $processText = ($ptr_type === 'Void') ? 'cancellation' : strtolower($ptr_type);
        
        $customerSubject = "Processing Update - Your " . ucfirst($processText) . " Request (Booking #" . $booking_id . ")";
        $customerContent = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
        </head>
        <body style="margin:0; padding:20px; background-color:#f5f7fb; font-family:Arial,sans-serif;">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:600px; margin:0 auto; background-color:#ffffff; border-radius:8px; box-shadow:0 0 10px rgba(0,0,0,0.08);">
                <tr>
                    <td align="center" style="padding:20px 0 10px 0;">
                        <img src="https://bulatrips.com/images/Image-Logo-vec.png" alt="Bulatrips" style="height:50px; width:auto; display:block; margin:10px auto;">
                    </td>
                </tr>
                
                <tr>
                    <td align="center" style="background-color:#ffc107; color:#000; font-size:18px; font-weight:bold; padding:14px;">
                        ⏳ Processing Update
                    </td>
                </tr>
                
                <tr>
                    <td style="padding:22px; font-size:15px; line-height:1.6; color:#333;">
                        <p style="margin:0 0 12px 0;">Dear ' . htmlspecialchars($contactName) . ',</p>
                        
                        <p style="margin:0 0 18px 0;">
                            Thank you for your patience. Your ' . $processText . ' request is being processed by the airline.
                        </p>
                        
                        <div style="background:#fff3cd; border-left:4px solid #ffc107; padding:15px; margin:0 0 20px 0; border-radius: 4px;">
                            <p style="margin:0 0 8px 0; font-weight:bold; color:#856404; font-size: 16px;">
                                ⏰ Processing is taking longer than expected
                            </p>
                            <p style="margin:0; color:#856404; font-size:14px;">
                                The airline is experiencing high volume. We appreciate your patience and will update you as soon as the process is complete.
                            </p>
                        </div>
                        
                        <div style="background:#f1f1f1; border-radius:6px; padding:14px; margin:0 0 20px 0;">
                            <div style="margin:0 0 8px 0;">
                                <span style="font-weight:bold;">Booking Reference:</span> 
                                <span>' . htmlspecialchars($mf_ref) . '</span>
                            </div>
                            <div style="margin:0 0 8px 0;">
                                <span style="font-weight:bold;">PTR ID:</span> 
                                <span>' . htmlspecialchars($ptr_id) . '</span>
                            </div>
                            <div style="margin:0;">
                                <span style="font-weight:bold;">Request Type:</span> 
                                <span>' . ucfirst($processText) . '</span>
                            </div>
                        </div>
                        
                        <div style="background:#e7f3ff; border-left:4px solid #0d6efd; padding:15px; margin:0 0 20px 0; border-radius: 4px;">
                            <p style="margin:0 0 12px 0; font-weight:bold; color:#084298; font-size: 16px;">📅 Timeline Information</p>
                            <div style="margin:0 0 8px 0; font-size:14px; color:#084298;">
                                <strong>Original Expected Time:</strong> ' . $originalExpectedUTC . '
                            </div>
                            <div style="margin:0 0 12px 0; font-size:14px; color:#084298;">
                                <strong>New Estimated Time:</strong> ' . $newEstimatedUTC . '
                            </div>
                            <p style="margin:0; font-size:13px; color:#666; font-style: italic;">
                                Note: Times shown in UTC (Universal Time). Please adjust for your local timezone.
                            </p>
                        </div>
                        
                        <p style="margin:0 0 18px 0; font-size: 15px;">
                            We are monitoring your request closely and will send you a confirmation email as soon as it is completed by the airline.
                        </p>
                        
                        <p style="margin:0; color:#555; font-size:14px;">
                            If you have any questions, please contact our support team.
                        </p>
                        
                        <p style="margin:18px 0 0 0; color:#555;">
                            Thank you for choosing Bulatrips.
                        </p>
                    </td>
                </tr>
                
                <tr>
                    <td style="padding:15px; background-color:#f8f9fa; text-align:center; font-size:12px; color:#666;">
                        <p style="margin:0;">This is an automated notification from Bulatrips PTR Monitoring System</p>
                        <p style="margin:5px 0 0 0;">Alert generated at: ' . gmdate('d M Y, H:i:s') . ' UTC</p>
                    </td>
                </tr>
            </table>
        </body>
        </html>';
        
        // Send to customer
        try {
            if (function_exists('sendMail')) {
                sendMail($customerEmail, $customerSubject, $customerContent);
                $this->_writeLog("Customer delay notification sent to: " . $customerEmail . " for PTR: " . $ptr_id, 'searchPtrCron.txt');
            }
        } catch (Exception $e) {
            $this->_writeLog("Failed to send customer notification: " . $e->getMessage(), 'searchPtrCron.txt');
        }
        
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