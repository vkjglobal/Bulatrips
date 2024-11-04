<?php
include_once __DIR__ . '/../includes/common_const.php';
include_once __DIR__ . '/../includes/class.Db_clientCron.php';
 class BookingCron  extends Db_clientCron{
	public function __construct() {
        parent::__construct(); // Call the constructor of the parent class (MyDatabaseClassPDO)
       
    }
    public function _writeLog($content	=	"",$filename	=	"log.txt")
	{		
		$fp 	=	fopen('../uploads/logFiles/'.$filename, "a+");			
		fputs($fp,$content);		
		fputs($fp, "\r\n");
		fclose($fp);	
	}
    public function calculateHoursFromSLAMinutes($slaMinutes) {
    // Divide the SLA minutes by 60 to get hours
    $hours = $slaMinutes / 60;

    return $hours;
    }
    public function getBookCronIDs()
    {
       
       
            // Validate the email address
            try {
                $query = "SELECT * FROM temp_booking WHERE mf_reference != '' AND dep_date >= NOW() AND `booking_status`!= 'NotBooked' AND (`ticket_status` NOT LIKE '%Ticketed%' OR `ticket_status` IS NULL)";
                                   // AND ticket_time_limit >= NOW()
                $stmt = $this->conn->prepare($query);

                $stmt->execute();

                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
               
                return $result;
            } catch (PDOException $e) {
                // Handle the exception (e.g., log the error)
                return null;
            }
    }
    public function getUsersbyId($user_id) {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM users WHERE id = :user_id");
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_STR);
            $stmt->execute();

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result;
        } catch (PDOException $e) {
            // Handle the exception (e.g., log the error)
            return null;
        }
    }
     public function getUserDetails($user_id) {
        $result = $this->getUsersbyId($user_id);
        return $result;
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