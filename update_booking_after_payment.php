<?php
//User booking call 
include_once('includes/common_const.php');
require_once('includes/dbConnect.php');
include_once('includes/class.Markup.php');
include_once('includes/class.Users.php');
include_once('includes/class.BookScript.php');
$objBook    =   new BookScript();


$data   =   $_SESSION['revalidationApi'];
$contactFirstName = $data['contactfirstname'] ?? '';
$contactLastName = $data['contactlastname'] ?? '';
$contactPhone = $data['contactnumber'] ?? '';
$contactEmail = $data['contactemail'] ?? '';
$contactPhonecode = $data['contactcountry'] ?? '';
$contactPostcode = $data['contactpostcode'] ?? '';
$adultCount = $data['adultCount'] ?? '';
$childCount = $data['childCount'] ?? '';
$infantCount = $data['infantCount'] ?? '';
$pricedItineraries = $data['pricedItineraries'];
$userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : "";
$extra_service_total = $_SESSION['totalService'] ?? '';
$total_paid = $_SESSION['Totalamount'] ?? '';
$total_paid = str_replace(',', '', $total_paid);
$adminToemail   =   "no-reply@bulatrips.com";
$pricedItineraries = json_decode($data['pricedItineraries'], true);
$errors = [];

$tempBookingId = $responseArray['merchantReference'];

$objMarkup = new Markup();
$markupDetails = $objMarkup->getMarkupDetails(1);
$markupIPGDetails = $objMarkup->getSettingDetails();
$markup =  (($markupIPGDetails['value']+$markupDetails['commission_percentage']) / 100) * $totalFare;

    // Breaking the Code for after payment Starts.
        $stmtbookingid = $conn->prepare('SELECT * FROM temp_booking WHERE fare_source_code = :farecode and id= :id');
        $stmtbookingid->execute(array('farecode' => $_SESSION['fsc'], 'id' => $tempBookingId));
        $bookingData = $stmtbookingid->fetch(PDO::FETCH_ASSOC);
        $fsc = $bookingData['fare_source_code'];
    
        if (isset($fsc)) {
            $codeWithoutPlus = substr($bookingData['contact_phonecode'], 1);
            $stmt = $conn->prepare("SELECT * FROM travellers_details Where flight_booking_id = :bookingId");
            $stmt->execute(array('bookingId' => $bookingData['id']));
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $endpoint   =   'v1/Book/Flight';
            $apiEndpoint = APIENDPOINT . $endpoint;
            $bearerToken   =   BEARER;

            foreach ($result as $row) {
                $extraMealId = $row['extrameal_id'];
                $extraBaggageId = $row['extrabaggage_id'];
                $extraMealReturnId = $row['extrameal_return_id'];
                $extraBaggageReturnId = $row['extrabaggage_return_id'];
                $extraServices = [];

                if ($extraMealId != 0) {
                    $extraServices[] = array(
                        "ExtraServiceId" => $extraMealId,
                        "Quantity" => 1,
                        "Key" => "string"
                    );
                }

                if ($extraBaggageId != 0) {
                    $extraServices[] = array(
                        "ExtraServiceId" => $extraBaggageId,
                        "Quantity" => 1,
                        "Key" => "string"
                    );
                }
                if ($extraMealReturnId != 0) {
                    $extraServices[] = array(
                        "ExtraServiceId" => $extraMealReturnId,
                        "Quantity" => 1,
                        "Key" => "string"
                    );
                }
                if ($extraBaggageReturnId != 0) {
                    $extraServices[] = array(
                        "ExtraServiceId" => $extraBaggageReturnId,
                        "Quantity" => 1,
                        "Key" => "string"
                    );
                }

                $passenger = array(
                    "PassengerType" => $row['passenger_type'],
                    "Gender" => $row['gender'],
                    // "Gender" => "F",
                    "PassengerName" => array(
                        "PassengerTitle" => $row['title'],
                        "PassengerFirstName" => $row['first_name'],
                        "PassengerLastName" => $row['last_name']
                    ),
                    "DateOfBirth" => $row['dob'],
                    "Passport" => array(
                        "PassportNumber" => $row['passport_number'],
                        "ExpiryDate" => $row['passport_expiry_date'],
                        "Country" => $row['issuing_country'],
                        // "Country" => "IN",
                    ),
                    // "ExtraServices1_1"=> array(
                    //     array(
                    //       "ExtraServiceId"=> 11,
                    //       "Quantity"=> 1,
                    //       "Key"=> "string"
                    //     )
                    // ),

                    // "ExtraServices1_1" => $extraServices,
                    // "PassengerNationality" => "IN",
                    "PassengerNationality" => $row['nationality'],
                );
                if (!empty($extraServices) &&  $bookingData['fare_type'] == "WebFare") {
                    $passenger["ExtraServices1_1"] = $extraServices;
                }
                $passengerDetails[] = $passenger;
            }

            $requestData = array(
                "FareSourceCode" =>  $fsc,
                "ClientMarkup" => $markup,
                "TravelerInfo" => array(
                    "AirTravelers" => $passengerDetails,
                    "CountryCode" => $codeWithoutPlus,
                    "PhoneNumber" => $bookingData['contact_number'],
                    "Email" => $bookingData['contact_email'],
                    "PostCode" => $bookingData['contact_postcode']
                ),
                "Target" => TARGET,
            );

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
            if ($response) {
                $responseData = json_decode($response, true);
            }
        }
        
        $resSuccess  = $responseData['Data']['Success'];
        $fairtype = $bookingData['fare_type'];
        
        if (!empty($responseData['Data']['Errors'])) {
            $errMsg = $responseData['Data']['Errors'][0]['Message'];
            $errCDE = $responseData['Data']['Errors'][0]['Code'];
            if (empty($errMsg)) {
                $errMsg = $responseData['Data']['Errors']['Message'];
                $errCDE = $responseData['Data']['Errors']['Code'];
            }
            
            $booking_date = date('Y-m-d H:i:s');
            $mfreference = $responseData['Data']['UniqueID'];
            $traceId = $responseData['Data']['TraceId'];
            $booking_status = $responseData['Data']['Status'];
            $TktTimeLimit = $responseData['Data']['TktTimeLimit'];
            $id = $bookingData['id'];
            
            $stmtupdate = $conn->prepare('UPDATE temp_booking SET mf_reference = :mfreference, trace_id = :traceId ,booking_status = :booking_status, booking_date = :booking_date , markup = :markup ,ticket_time_limit = :ticketTimeLimit WHERE id = :id');
            $stmtupdate->bindParam(':mfreference', $mfreference);
            $stmtupdate->bindParam(':traceId', $traceId);
            $stmtupdate->bindParam(':booking_status', $booking_status);
            $stmtupdate->bindParam(':booking_date', $booking_date);
            $stmtupdate->bindParam(':markup', $markup);
            $stmtupdate->bindParam(':ticketTimeLimit', $TktTimeLimit);
            $stmtupdate->bindParam(':id', $id);
            $stmtupdate->execute();

            $err_code = $errCDE;
            $fairtype = $bookingData['fare_type'];
            $booking_status = $responseData['Data']['Status'];
            $ticket_status = $responseData['Data']['Status'];
            $id = $bookingData['id'];

            $stmtInsert = $conn->prepare('INSERT INTO `booking_errors` (`id`, `booking_Id`, `err_code`, `err_msg`, `fare_type`, `book_status`, `ticket_sts`, `created_date`, `update_at`) VALUES (NULL, :book_id, :err_code, :err_msg,:fare_type ,:book_status, :ticket_sts, current_timestamp(), current_timestamp());');
            $stmtInsert->bindParam(':book_id', $id);
            $stmtInsert->bindParam(':err_code', $err_code);
            $stmtInsert->bindParam(':err_msg', $errMsg);
            $stmtInsert->bindParam(':fare_type', $fairtype);
            $stmtInsert->bindParam(':book_status', $booking_status);
            $stmtInsert->bindParam(':ticket_sts', $ticket_status);
            $stmtInsert->execute();
            $response = array(
                'BookStatus' => "Failed",
                'ticketstatus' => "Failed",
                'faretype' => $fairtype,
                'errors' => $errMsg,
                'errCde' => $errCDE
            );
            echo json_encode($response);
            exit;
        } elseif (($responseData['Data']['Success']) && ($responseData['Data']['Status'] == "CONFIRMED")) { //"Confirmed" with MF Number: Please consider it as a successful transaction
            
            $booking_date = date('Y-m-d H:i:s');
            $mfreference = $responseData['Data']['UniqueID'];
            $traceId = $responseData['Data']['TraceId'];
            $booking_status = $responseData['Data']['Status'];
            $TktTimeLimit = $responseData['Data']['TktTimeLimit'];
            $id = $bookingData['id'];
            
            $stmtupdate = $conn->prepare('UPDATE temp_booking SET mf_reference = :mfreference, trace_id = :traceId ,booking_status = :booking_status, booking_date = :booking_date , markup = :markup ,ticket_time_limit = :ticketTimeLimit WHERE id = :id');
            $stmtupdate->bindParam(':mfreference', $mfreference);
            $stmtupdate->bindParam(':traceId', $traceId);
            $stmtupdate->bindParam(':booking_status', $booking_status);
            $stmtupdate->bindParam(':booking_date', $booking_date);
            $stmtupdate->bindParam(':markup', $markup);
            $stmtupdate->bindParam(':ticketTimeLimit', $TktTimeLimit);
            $stmtupdate->bindParam(':id', $id);
            $stmtupdate->execute();
            $orderstatus = $responseData['Data']['Success'];
        } elseif (($responseData['Data']['Success']) && ($responseData['Data']['Status'] == "BOOKINGINPROCESS")) { //"BOOKINGINPROCESS" and the booking might get conformed or unconfirmed
            $stmtupdate = $conn->prepare('UPDATE temp_booking SET mf_reference = :mfreference, trace_id = :traceId ,booking_status = :booking_status, booking_date = :booking_date , markup = :markup ,ticket_time_limit = :ticketTimeLimit WHERE id = :id');
            $booking_date = date('Y-m-d H:i:s');
            $mfreference = $responseData['Data']['UniqueID'];
            $traceId = $responseData['Data']['TraceId'];
            $booking_status = $responseData['Data']['Status'];
            $TktTimeLimit = $responseData['Data']['TktTimeLimit'];
            $id = $bookingData['id'];

            $stmtupdate->bindParam(':mfreference', $mfreference);
            $stmtupdate->bindParam(':traceId', $traceId);
            $stmtupdate->bindParam(':booking_status', $booking_status);
            $stmtupdate->bindParam(':booking_date', $booking_date);
            $stmtupdate->bindParam(':markup', $markup);
            $stmtupdate->bindParam(':ticketTimeLimit', $TktTimeLimit);
            $stmtupdate->bindParam(':id', $id);
            $stmtupdate->execute();
            $orderstatus = $responseData['Data']['Success'];
        } elseif (empty($responseData['Data']['Success'])) { //failure case
            $errCDE = '';
            $errMsg = '';
            if (!empty($responseData['Data']['Errors'])) {
                $errMsg = $responseData['Data']['Errors'][0]['Message'];
                $errCDE = $responseData['Data']['Errors'][0]['Code'];
                if (empty($errMsg)) {
                    $errMsg = $responseData['Data']['Message'];
                }
            }
            
            $booking_date = date('Y-m-d H:i:s');
            $mfreference = $responseData['Data']['UniqueID'];
            $traceId = $responseData['Data']['TraceId'];
            $booking_status = $responseData['Data']['Status'];
            $TktTimeLimit = $responseData['Data']['TktTimeLimit'];
            $id = $bookingData['id'];
            
            $stmtupdate = $conn->prepare('UPDATE temp_booking SET mf_reference = :mfreference, trace_id = :traceId ,booking_status = :booking_status, booking_date = :booking_date , markup = :markup ,ticket_time_limit = :ticketTimeLimit WHERE id = :id');
            $stmtupdate->bindParam(':mfreference', $mfreference);
            $stmtupdate->bindParam(':traceId', $traceId);
            $stmtupdate->bindParam(':booking_status', $booking_status);
            $stmtupdate->bindParam(':booking_date', $booking_date);
            $stmtupdate->bindParam(':markup', $markup);
            $stmtupdate->bindParam(':ticketTimeLimit', $TktTimeLimit);
            $stmtupdate->bindParam(':id', $id);
            $stmtupdate->execute();

            $stmtInsert = $conn->prepare('INSERT INTO `booking_errors` (`id`, `booking_Id`, `err_code`, `err_msg`, `fare_type`, `book_status`, `ticket_sts`, `created_date`, `update_at`) VALUES (NULL, :book_id, :err_code, :err_msg,:fare_type ,:book_status, :ticket_sts, current_timestamp(), current_timestamp());');
            $err_code = $errCDE;
            $fairtype = $bookingData['fare_type'];

            $booking_status = $responseData['Data']['Status'];
            $ticket_status = $responseData['Data']['Status'];

            $id = $bookingData['id'];
            if (empty($errMsg)) {
                $errMsg = "Null Status Received from Airline";
                $errCDE = "000";
            }
            $stmtInsert->bindParam(':book_id', $id);
            $stmtInsert->bindParam(':err_code', $err_code);
            $stmtInsert->bindParam(':err_msg', $errMsg);
            $stmtInsert->bindParam(':fare_type', $fairtype);
            $stmtInsert->bindParam(':book_status', $booking_status);
            $stmtInsert->bindParam(':ticket_sts', $ticket_status);
            $stmtInsert->execute();
            $response = array(
                'BookStatus' => "Failed",
                'ticketstatus' => "Failed",
                'faretype' => $fairtype,
                'errors' => $errMsg,
                'errCde' => $errCDE
            );
            header('Content-Type: application/json');
            echo json_encode($response);
            exit;
        } else {
            $booking_date = date('Y-m-d H:i:s');
            $mfreference = $responseData['Data']['UniqueID'];
            $traceId = $responseData['Data']['TraceId'];
            $booking_status = $responseData['Data']['Status'];
            $TktTimeLimit = $responseData['Data']['TktTimeLimit'];
            $id = $bookingData['id'];

            if (empty($mfreference) && ($booking_status == "PENDING")) {
                $stmtupdate = $conn->prepare('UPDATE temp_booking SET booking_date = :booking_date , markup = :markup, booking_status = :booking_status WHERE id = :id');
                $stmtupdate->bindParam(':booking_date', $booking_date);
                $stmtupdate->bindParam(':markup', $markup);
                $stmtupdate->bindParam(':booking_status', $booking_status);
                $stmtupdate->bindParam(':id', $id);
                if (empty($errMsg)) {
                    $errMsg = "Pending without MF number Direct failure as per api Received";
                    $errCDE = "001";
                }
                $stmtupdate->execute();
                $response = array(
                    'BookStatus' => "Failed",
                    'ticketstatus' => "Failed",
                    'faretype' => $fairtype,
                    'errors' => $errMsg,
                    'errCde' => $errCDE
                );
                
                $err_code = $errCDE;
                $fairtype = $bookingData['fare_type'];
                $booking_status = $responseData['Data']['Status'];
                $ticket_status = $responseData['Data']['Status'];
                $id = $bookingData['id'];
                
                $stmtInsert = $conn->prepare('INSERT INTO `booking_errors` (`id`, `booking_Id`, `err_code`, `err_msg`, `fare_type`, `book_status`, `ticket_sts`, `created_date`, `update_at`) VALUES (NULL, :book_id, :err_code, :err_msg,:fare_type ,:book_status, :ticket_sts, current_timestamp(), current_timestamp());');
                $stmtInsert->bindParam(':book_id', $id);
                $stmtInsert->bindParam(':err_code', $err_code);
                $stmtInsert->bindParam(':err_msg', $errMsg);
                $stmtInsert->bindParam(':fare_type', $fairtype);
                $stmtInsert->bindParam(':book_status', $booking_status);
                $stmtInsert->bindParam(':ticket_sts', $ticket_status);
                $stmtInsert->execute();
                
                header('Content-Type: application/json');
                echo json_encode($response);
                exit;
            } elseif (empty($mfreference) && ($booking_status == "NotBooked")) {
                $stmtupdate = $conn->prepare('UPDATE temp_booking SET booking_date = :booking_date , markup = :markup, booking_status = :booking_status WHERE id = :id');
                $stmtupdate->bindParam(':booking_date', $booking_date);
                $stmtupdate->bindParam(':markup', $markup);
                $stmtupdate->bindParam(':booking_status', $booking_status);
                $stmtupdate->bindParam(':id', $id);
                if (empty($errMsg)) {
                    $errMsg = "Not Booked, Direct failure as per api Received";
                    $errCDE = "002";
                }
                $stmtupdate->execute();
                $response = array(
                    'BookStatus' => "Failed",
                    'ticketstatus' => "Failed",
                    'faretype' => $fairtype,
                    'errors' => $errMsg,
                    'errCde' => $errCDE
                );
                
                $err_code = $errCDE;
                $fairtype = $bookingData['fare_type'];
                $booking_status = $responseData['Data']['Status'];
                $ticket_status = $responseData['Data']['Status'];
                $id = $bookingData['id'];

                $stmtInsert = $conn->prepare('INSERT INTO `booking_errors` (`id`, `booking_Id`, `err_code`, `err_msg`, `fare_type`, `book_status`, `ticket_sts`, `created_date`, `update_at`) VALUES (NULL, :book_id, :err_code, :err_msg,:fare_type ,:book_status, :ticket_sts, current_timestamp(), current_timestamp());');
                
                $stmtInsert->bindParam(':book_id', $id);
                $stmtInsert->bindParam(':err_code', $err_code);
                $stmtInsert->bindParam(':err_msg', $errMsg);
                $stmtInsert->bindParam(':fare_type', $fairtype);
                $stmtInsert->bindParam(':book_status', $booking_status);
                $stmtInsert->bindParam(':ticket_sts', $ticket_status);
                // Execute the query
                $stmtInsert->execute();
                //====================email send code to admin regrding booking failure and amount need to repay======

                //  include_once('mail_send.php');

                $subject = "Bulatrips User Booking attempt Failure and Balance need to credit Info";

                $email =   $userDetails['email'];
                $name   =   $userDetails['first_name'] . " " . $userDetails['last_name'];
                $content    =   '<p>Hello,</p>
                                                    <p>This user , ' . $name . ', with email ' . $userDetails['email'] . ' had a  transaction for booking id:' . $tempBookingId . '.The amount used is :$' . $amountToDebit . '</p>
                                                    <p>Since this booking attempt failed due to :' . $errMsg . ',Please credit back the same amount </p>';
                $messageData =   $objBook->getEmailContent($content);
                // print_r($messageData);exit;
                $headers = "";
                $email = $adminToemail; //Need ADMIN email here

                $contacts = sendMail($email, $subject, $messageData, $headers);
                $logResErr =   print_r($response, true);
                $objBook->_writeLog(' Pending without MF number Direct failure as per api Received\n' . $logResErr, 'booking.txt');
                header('Content-Type: application/json');
                echo json_encode($response);
                exit;
            } elseif (($responseData['Data']['Success']) && in_array($responseData['Data']['Status'], ["Booked", "Ticketed", "Ticket-In Process", "Pending"])) {
                
                $booking_date = date('Y-m-d H:i:s');
                $mfreference = $responseData['Data']['UniqueID'];
                $traceId = $responseData['Data']['TraceId'];
                $booking_status = $responseData['Data']['Status'];
                $TktTimeLimit = $responseData['Data']['TktTimeLimit'];
                $id = $bookingData['id'];

                $stmtupdate = $conn->prepare('UPDATE temp_booking SET mf_reference = :mfreference, trace_id = :traceId ,booking_status = :booking_status, booking_date = :booking_date , markup = :markup ,ticket_time_limit = :ticketTimeLimit WHERE id = :id');
                $stmtupdate->bindParam(':mfreference', $mfreference);
                $stmtupdate->bindParam(':traceId', $traceId);
                $stmtupdate->bindParam(':booking_status', $booking_status); //
                $stmtupdate->bindParam(':booking_date', $booking_date);
                $stmtupdate->bindParam(':markup', $markup);
                $stmtupdate->bindParam(':ticketTimeLimit', $TktTimeLimit);
                $stmtupdate->bindParam(':id', $id);
                $stmtupdate->execute();
                $orderstatus = $responseData['Data']['Success'];
            } else {
                $stmtupdate = $conn->prepare('UPDATE temp_booking SET booking_date = :booking_date , markup = :markup, booking_status = :booking_status WHERE id = :id');
                $stmtupdate->bindParam(':booking_date', $booking_date);
                $stmtupdate->bindParam(':markup', $markup);
                $stmtupdate->bindParam(':booking_status', $booking_status);
                $stmtupdate->bindParam(':id', $id);
                if (empty($errMsg)) {
                    $errMsg = $booking_status;
                    $errCDE = "003";
                }
                $stmtupdate->execute();
                $response = array(
                    'BookStatus' => "Failed",
                    'ticketstatus' => "Failed",
                    'faretype' => $fairtype,
                    'errors' => $errMsg,
                    'errCde' => $errCDE
                );
                
                $err_code = $errCDE;
                $fairtype = $bookingData['fare_type'];
                $booking_status = $responseData['Data']['Status'];
                $ticket_status = $responseData['Data']['Status'];
                $id = $bookingData['id'];

                $stmtInsert = $conn->prepare('INSERT INTO `booking_errors` (`id`, `booking_Id`, `err_code`, `err_msg`, `fare_type`, `book_status`, `ticket_sts`, `created_date`, `update_at`) VALUES (NULL, :book_id, :err_code, :err_msg,:fare_type ,:book_status, :ticket_sts, current_timestamp(), current_timestamp());');
                $stmtInsert->bindParam(':book_id', $id);
                $stmtInsert->bindParam(':err_code', $err_code);
                $stmtInsert->bindParam(':err_msg', $errMsg);
                $stmtInsert->bindParam(':fare_type', $fairtype);
                $stmtInsert->bindParam(':book_status', $booking_status);
                $stmtInsert->bindParam(':ticket_sts', $ticket_status);
                $stmtInsert->execute();
                header('Content-Type: application/json');
                echo json_encode($response);
                exit;
            } 
        }

        if ($bookingData['fare_type'] != "WebFare") {
            $stmtbookingid = $conn->prepare('SELECT * FROM temp_booking WHERE fare_source_code = :farecode');
            $stmtbookingid->execute(array('farecode' => $_SESSION['fsc']));
            $bookingData = $stmtbookingid->fetch(PDO::FETCH_ASSOC);
            $endpoint   =   'v1/OrderTicket';
            $apiEndpoint = APIENDPOINT . $endpoint;
            $bearerToken   =   BEARER;
            $requestData = array(
                'UniqueID' => $bookingData['mf_reference'],
                'Target' => TARGET,
            );
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $apiEndpoint);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestData));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Authorization: Bearer ' . $bearerToken
            ));

            $responseTicket = curl_exec($ch);
            curl_close($ch);
            if ($response) {
                $responseTicketData = json_decode($responseTicket, true);
            }

            if (!empty($responseData['Data']['Errors'])) {
                $errMsg = $responseData['Data']['Errors'][0]['Message'];
                $errCDE = $responseData['Data']['Errors'][0]['Code'];
                if (empty($errMsg)) {
                    $errMsg = $responseData['Data']['Errors']['Message'];
                    $errCDE = $responseData['Data']['Errors']['Code'];
                }
                $stmtupdate = $conn->prepare('UPDATE temp_booking SET ticket_status = :ticket_status WHERE id = :id');
                $ticket_status = $responseTicketData['Data']['Success'];
                $id = $bookingData['id'];
                $stmtupdate->bindParam(':ticket_status', $ticket_status);
                $stmtupdate->bindParam(':id', $id);
                $stmtupdate->execute();
                
                $err_code = $errCDE;
                $fairtype = $bookingData['fare_type'];
                $booking_status = $booking_status;
                $id = $bookingData['id'];
                

                $stmtInsert = $conn->prepare('INSERT INTO `booking_errors` (`id`, `booking_Id`, `err_code`, `err_msg`, `fare_type`, `book_status`, `ticket_sts`, `created_date`, `update_at`) VALUES (NULL, :book_id, :err_code, :err_msg,:fare_type ,:book_status, :ticket_sts, current_timestamp(), current_timestamp());');
                $stmtInsert->bindParam(':book_id', $id);
                $stmtInsert->bindParam(':err_code', $err_code);
                $stmtInsert->bindParam(':err_msg', $errMsg);
                $stmtInsert->bindParam(':fare_type', $fairtype);
                $stmtInsert->bindParam(':book_status', $booking_status);
                $stmtInsert->bindParam(':ticket_sts', $ticket_status);
                $stmtInsert->execute();
                $response = array(
                    'BookStatus' => "Failed",
                    'ticketstatus' => "Failed",
                    'faretype' => $fairtype,
                    'errors' => $errMsg,
                    'errCde' => $errCDE
                );
                header('Content-Type: application/json');
                echo json_encode($response);
                exit;
            } else {
                $stmtupdate = $conn->prepare('UPDATE temp_booking SET ticket_status = :ticket_status WHERE id = :id');
                $ticket_status = $responseTicketData['Data']['Success'];
                $id = $bookingData['id'];
                $stmtupdate->bindParam(':ticket_status', $ticket_status);
                $stmtupdate->bindParam(':id', $id);
                $stmtupdate->execute();
                $ticketstatus = "ticket sucess";
            }
        } else {
            $ticketstatus = "ticket sucess";
            $subject = "Bulatrips booking Success ";
            $email =   $userDetails['email'];
            $name   =   $userDetails['first_name'] . " " . $userDetails['last_name'];
            $content    =   '<p>Hello ' . $name . ',</p>
                                                    <p>Your Booking on Bulatrips from:' . $depLocation . ' to ' . $arrivalLocation . ' is ' . $booking_status . '</p>';
            $messageData =   $objBook->getEmailContent($content);
            $headers = "";
        }

        $fairtype = $bookingData['fare_type'];
        $messageNew = '';
        $response = array(
            'BookStatus' => $booking_status,
            'ticketstatus' => $ticketstatus,
            'faretype' => $fairtype,
            'bookingid' => $mfreference,
            'messageNew' => $messageNew
        );

        echo "<pre>";
            print_r($response);
        echo "</pre>";
        die;

    // Breaking the Code for after payment Ends.