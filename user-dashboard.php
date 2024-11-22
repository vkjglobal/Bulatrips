<?php
session_start();
error_reporting(0);

if (!isset($_SESSION['user_id'])) {
    ?>
    <script>
        window.location = "index.php"
    </script>
    <?php
} else {
    require_once("includes/header.php");
    include('includes/dbConnect.php');

    $id = $_SESSION['user_id'];
    $stmt = $conn->prepare('SELECT * FROM users WHERE id = :id');
    $stmt->execute(array('id' => $id));

    // Fetch the user details as an associative array
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $pimage = $user['image'];
    } else {
        $pimage = 'logo.png';
    }
    //nimmi - 23-05-2024

    $cDate = date('Y-m-d');
    //fetch completed booking details - booking status: booked , ticket status: ticketed
    // $comp = $conn->prepare("SELECT count(id) as count FROM temp_booking WHERE booking_status = 'Booked' AND ticket_status = 'Ticketed' AND user_id = :id");
    $comp = $conn->prepare("SELECT count(id) FROM temp_booking WHERE dep_date >= :cDate AND user_id = :id AND mf_reference != ''");
    $result = $comp->execute(array('cDate' => $cDate, 'id' => $id));
    // $result = $comp->execute(array('id' => $id));
    $result = $comp->fetch();
    $completed = $result['count(id)'];

    // fetch confirmed flight count
    $conf = $conn->prepare("SELECT count(id) as count FROM temp_booking WHERE booking_status = 'Booked' AND ticket_status = 'Ticketed' AND dep_date >= :cDate AND user_id = :id");
    $result = $conf->execute(array('cDate' => $cDate, 'id' => $id));
    $result = $conf->fetch();
    $confirm = $result['count'];

    //fetch pending booking details -case1
    $pend1 = $conn->prepare("SELECT count(id) as count FROM temp_booking WHERE booking_status = 'Booked' AND mf_reference != NULL AND ticket_status != NULL AND user_id = :id");
    $pend1->execute(array('id' => $id));
    $result1 = $pend1->fetch();
    $pending1 = $result1['count'];

    //fetch pending booking details -case2
    $pend2 = $conn->prepare("SELECT count(id) as count FROM temp_booking WHERE booking_status = :booked AND ticket_status = :ticket AND user_id = :id");
    $pend2->execute(array('booked' => 'Booked', 'ticket' => 'TktInProcess', 'id' => $id));
    $result2 = $pend2->fetch();
    $pending2 = $result2['count'];

    //fetch pending booking details -case3
    $pend3 = $conn->prepare("SELECT count(id) as count FROM temp_booking WHERE booking_status = 'BookingInProcess' AND dep_date >= :cDate AND user_id = :id");
    $pend3->execute(array('cDate' => $cDate, 'id' => $id));
    $result3 = $pend3->fetch();
    $pending3 = $result3['count'];

    //fetch pending booking details -case4
    $pend4 = $conn->prepare("SELECT count(id) as count FROM temp_booking WHERE booking_status = ' Pending' AND mf_reference != NULL AND user_id = :id");
    $pend4->execute(array('id' => $id));
    $result4 = $pend4->fetch();
    $pending4 = $result4['count'];

    // add into one variable
    $pending = $pending1 + $pending2 + $pending3 + $pending4;
    // echo $pending;
    //fetch waiting booking details
    $wait = $conn->prepare("SELECT count(id) as count FROM temp_booking WHERE booking_status = 'unconfirmed' AND mf_reference != NULL AND user_id = :id");
    $wait->execute(array('id' => $id));
    $result3 = $wait->fetch();
    $waiting = $result3['count'];

    //fetch not booked booking details
    $notbook = $conn->prepare("SELECT count(id) as count FROM temp_booking WHERE booking_status = 'NotBooked' AND mf_reference != NULL AND user_id = :id");
    $notbook->execute(array('id' => $id));
    $result5 = $notbook->fetch();
    $notbook = $result5['count'];

    //fetch canceled flight details
    $cancel = $conn->prepare("SELECT count(id) as count FROM cancel_booking WHERE cancel_status = '1' AND user_agent_id = :id");
    $cancel->execute(array('id' => $id));
    $result4 = $cancel->fetch();
    $canceled = $result4['count'];
    ?>
    <section class="">
        <div class="container">

            <div class="row user-dash-main-tab">
                <div class="col-md-3 pr-md-3 mb-3">
                    <div class="top-tabs-container">
                        <div class="d-flex flex-column align-items-center mb-3">
                            <span class="usr-pic">
                                <!-- <img src="images/Ellipse-37.png" alt=""> -->
                                <img src="uploads/profile/<?php echo $pimage; ?>" alt="">

                            </span>
                            <span class="usr-nm fw-500 light-blue-txt"><?php echo $user['first_name'] ?></span>
                            <!--<button type="button" class="btn btn-typ4 fs-14 fw-500 pl-lg-4 pr-lg-4 pl-3 pr-3">My Account</button>-->
                        </div>
                        <label for="main-tab-1" class="maintab-label w-100 active">Manage Booking</label>
                        <label for="main-tab-2" class="maintab-label w-100">Cancellation</label>
                        <label for="main-tab-3" class="maintab-label w-100">Profile</label>
                        <label for="main-tab-5" class="maintab-label w-100">Reviews</label>
                        <label for="main-tab-4" class="maintab-label w-100">Settings</label>
                        <!-- <button type="button" class="btn maintab-label w-100 text-left justify-content-start">Logout</button> -->
                        <a href="logout.php" class="btn maintab-label w-100 text-left justify-content-start">Logout</a>

                    </div>
                </div>

                <!-- Tab Container 1 -->

                <input class="tab-radio" id="main-tab-1" name="main-group" type="radio" checked="checked" />
                <div class="tab-content col-md-9 pl-md-0">
                    <div class="row my-4">
                        <div class="col-12">
                            <h2 class="title-typ2 mb-0">Dashboard</h2>
                        </div>
                        <div class="col-12">
                            <div class="form-row mt-4">
                                <div class="col-xl-3 col-sm-6 mb-2">
                                    <div class="card1">
                                        <div class="d-flex align-items-center">
                                            <div class="img-wrp">
                                                <img src="images/completed-icon.svg" alt="">
                                            </div>
                                            <strong class="text-uppercase">UPCOMING</strong>
                                        </div>
                                        <div class="count">
                                            <?php
                                            if ($completed) {
                                                print_r($completed);
                                            } else {
                                                echo '0';
                                            }
                                            ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-3 col-sm-6 mb-2">
                                    <div class="card1">
                                        <div class="d-flex align-items-center">
                                            <div class="img-wrp">
                                                <img src="images/pending-icon.svg" alt="">
                                            </div>
                                            <strong class="text-uppercase">CONFIRMED</strong>
                                        </div>
                                        <div class="count">
                                            <?php
                                            if ($confirm) {
                                                print_r($confirm);
                                            } else {
                                                echo '0';
                                            }
                                            ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-3 col-sm-6 mb-2">
                                    <div class="card1">
                                        <div class="d-flex align-items-center">
                                            <div class="img-wrp">
                                                <img src="images/cancellation-icon.svg" alt="">
                                            </div>
                                            <strong class="text-uppercase">PENDING</strong>
                                        </div>
                                        <div class="count">
                                            <?php
                                            if ($pending) {
                                                print_r($pending);
                                            } else {
                                                echo '0';
                                            }
                                            ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-3 col-sm-6 mb-2">
                                    <div class="card1" data-bs-toggle="tooltip"
                                        title="Booking failed after payment process.">
                                        <div class="d-flex align-items-center">
                                            <div class="img-wrp">
                                                <img src="images/cancellation-icon.svg" alt="">
                                            </div>
                                            <strong class="text-uppercase">BOOKING FAILED</strong>
                                        </div>
                                        <div class="count">
                                            <?php
                                            if ($notbook) {
                                                print_r($notbook);
                                            } else {
                                                echo '0';
                                            }
                                            ?>
                                        </div>
                                    </div>
                                </div>
                                <!-- <div class="col-xl-3 col-sm-6 mb-2">
                                    <div class="card1">
                                        <div class="d-flex align-items-center">
                                            <div class="img-wrp">
                                                <img src="images/incompleted-icon.svg" alt="">
                                            </div>
                                            <strong class="text-uppercase">CANCELLATION</strong>
                                        </div>
                                        <div class="count">
                                            <?php
                                            if ($canceled) {
                                                print_r($canceled);
                                            } else {
                                                echo '0';
                                            }
                                            ?>
                                        </div>
                                    </div>
                            </div> -->
                        </div>
                    </div>
                </div>
                <div class="sub-tabs-container">
                    <div class="subtab-btn-wrp">
                        <!--<label class="subtab-label active" id="all">ALL</label>-->
                        <label class="subtab-label active" id="a">UPCOMING</label>
                        <label class="subtab-label" id="b">CONFIRMED</label>
                        <label class="subtab-label" id="c">PENDING</label>
                        <!-- <label class="subtab-label" id="e">NOT BOOKED</label> -->
                        <label class="subtab-label" id="d">CANCELLED</label>
                    </div>
                    <div class="subtab-content">
                        <div class="d-md-block d-none w-100">
                            <table>
                                <tr>
                                    <th class="id">#ID</th>
                                    <th class="title">Title</th>
                                    <!-- <th class="type">Type</th>
                                        <th class="coat">Coat</th> -->
                                    <th class="status">Status</th>
                                    <th class="action">Action</th>
                                </tr>
                            </table>
                        </div>
                        <div id="parent" class="d-flex flex-column">

                            <div class="a box booking-pending">

                                <table>
                                    <?php
                                    $today = date('Y-m-d');

                                    // $stmtbookingid = $conn->prepare('SELECT * FROM temp_booking WHERE user_id = :userid and booking_status = :bookingStatus and mf_reference != NULL and dep_date >= :today');
                                    $stmtbookingid = $conn->prepare("SELECT * FROM temp_booking WHERE dep_date >= :today AND user_id = :id AND mf_reference != ''");
                                    $stmtbookingid->execute(array('today' => $today, 'id' => $id));
                                    // $stmtbookingid->execute(array('userid' => $id, 'bookingStatus' => "Booked", 'today' => $today));
                                    $bookingData = $stmtbookingid->fetchAll(PDO::FETCH_ASSOC);
                                    foreach ($bookingData as $bookingDatas) {
                                        $dateTime = new DateTime($bookingDatas['dep_date']);

                                        $formattedDate = $dateTime->format('d F Y, H:i');


                                        ?>

                                        <tr>
                                            <td class="id"> <?php echo $bookingDatas['id']; ?></td>
                                            <td class="title">
                                                <?php echo $bookingDatas['dep_location'] . " -> " . $bookingDatas['arrival_location'] . " " . $formattedDate ?>
                                            </td>
                                            <!-- <td class="type">Stanard</td> -->
                                            <!-- <td class="coat">Rs. 35 213</td> -->
                                            <td class="status"><?php echo $bookingDatas['booking_status']; ?></td>
                                            <td class="action">
                                                <!-- <button type="button" class="btn btn-typ3 w-100 mb-2"> -->
                                                <?php
                                                if($bookingDatas['booking_status'] == "Cancelled"){
                                                    ?>
                                                <button type="button" class="btn btn-typ3 w-100 mb-2" disabled><svg width="12"
                                                    height="12" viewBox="0 0 12 12" fill="none"
                                                    xmlns="http://www.w3.org/2000/svg">
                                                    <path
                                                        d="M6 0C4.81331 0 3.65328 0.351894 2.66658 1.01118C1.67989 1.67047 0.910851 2.60754 0.456725 3.7039C0.0025997 4.80025 -0.11622 6.00665 0.115291 7.17054C0.346802 8.33443 0.918247 9.40352 1.75736 10.2426C2.59648 11.0818 3.66557 11.6532 4.82946 11.8847C5.99335 12.1162 7.19975 11.9974 8.2961 11.5433C9.39246 11.0891 10.3295 10.3201 10.9888 9.33342C11.6481 8.34672 12 7.18669 12 6C12 4.4087 11.3679 2.88258 10.2426 1.75736C9.11742 0.632141 7.5913 0 6 0V0ZM1.2 6C1.20236 4.93462 1.5591 3.90031 2.214 3.06L8.94 9.786C8.23085 10.3355 7.3819 10.6752 6.48945 10.7667C5.597 10.8582 4.69677 10.6977 3.89089 10.3035C3.085 9.90935 2.40572 9.2972 1.9301 8.53653C1.45447 7.77586 1.20155 6.89712 1.2 6ZM9.786 8.94L3.06 2.214C3.9841 1.51165 5.1314 1.16794 6.28945 1.24652C7.4475 1.3251 8.53785 1.82065 9.3586 2.6414C10.1794 3.46215 10.6749 4.5525 10.7535 5.71055C10.8321 6.8686 10.4883 8.0159 9.786 8.94Z"
                                                        fill="white" />
                                                </svg> Cancel Booking</button>
                                                <button type="button" class="btn btn-typ4 w-100" disabled><svg width="12"
                                                        height="12" viewBox="0 0 12 12" fill="none"
                                                        xmlns="http://www.w3.org/2000/svg">
                                                        <path
                                                            d="M6 0C4.81331 0 3.65328 0.351894 2.66658 1.01118C1.67989 1.67047 0.910851 2.60754 0.456725 3.7039C0.0025997 4.80025 -0.11622 6.00665 0.115291 7.17054C0.346802 8.33443 0.918247 9.40352 1.75736 10.2426C2.59648 11.0818 3.66557 11.6532 4.82946 11.8847C5.99335 12.1162 7.19975 11.9974 8.2961 11.5433C9.39246 11.0891 10.3295 10.3201 10.9888 9.33342C11.6481 8.34672 12 7.18669 12 6C12 4.4087 11.3679 2.88258 10.2426 1.75736C9.11742 0.632141 7.5913 0 6 0V0ZM1.2 6C1.20236 4.93462 1.5591 3.90031 2.214 3.06L8.94 9.786C8.23085 10.3355 7.3819 10.6752 6.48945 10.7667C5.597 10.8582 4.69677 10.6977 3.89089 10.3035C3.085 9.90935 2.40572 9.2972 1.9301 8.53653C1.45447 7.77586 1.20155 6.89712 1.2 6ZM9.786 8.94L3.06 2.214C3.9841 1.51165 5.1314 1.16794 6.28945 1.24652C7.4475 1.3251 8.53785 1.82065 9.3586 2.6414C10.1794 3.46215 10.6749 4.5525 10.7535 5.71055C10.8321 6.8686 10.4883 8.0159 9.786 8.94Z"
                                                            fill="white" />
                                                    </svg> Details</button>
                                                <?php
                                                }
                                                else{
                                                    ?>
                                                   <a href="cancel_user.php?booking_id=<?php echo $bookingDatas['id']; ?>"
                                                        class="btn btn-typ4 w-100 mb-2 btn-primary">
                                                        <!-- btn btn-typ7 ml-3  -->

                                                        <svg width="12" height="12" viewBox="0 0 12 12" fill="none"
                                                            xmlns="http://www.w3.org/2000/svg">

                                                            <path
                                                                d="M6 0C4.81331 0 3.65328 0.351894 2.66658 1.01118C1.67989 1.67047 0.910851 2.60754 0.456725 3.7039C0.0025997 4.80025 -0.11622 6.00665 0.115291 7.17054C0.346802 8.33443 0.918247 9.40352 1.75736 10.2426C2.59648 11.0818 3.66557 11.6532 4.82946 11.8847C5.99335 12.1162 7.19975 11.9974 8.2961 11.5433C9.39246 11.0891 10.3295 10.3201 10.9888 9.33342C11.6481 8.34672 12 7.18669 12 6C12 4.4087 11.3679 2.88258 10.2426 1.75736C9.11742 0.632141 7.5913 0 6 0V0ZM1.2 6C1.20236 4.93462 1.5591 3.90031 2.214 3.06L8.94 9.786C8.23085 10.3355 7.3819 10.6752 6.48945 10.7667C5.597 10.8582 4.69677 10.6977 3.89089 10.3035C3.085 9.90935 2.40572 9.2972 1.9301 8.53653C1.45447 7.77586 1.20155 6.89712 1.2 6ZM9.786 8.94L3.06 2.214C3.9841 1.51165 5.1314 1.16794 6.28945 1.24652C7.4475 1.3251 8.53785 1.82065 9.3586 2.6414C10.1794 3.46215 10.6749 4.5525 10.7535 5.71055C10.8321 6.8686 10.4883 8.0159 9.786 8.94Z"
                                                                fill="white" />
                                                        </svg> Void/Refund Booking
                                                        <!-- </button> -->
                                                    </a>
                                                    <a href="cancel.php?booking_id=<?php echo $bookingDatas['id']; ?>"
                                                        class="btn btn-typ3 w-100 mb-2">

                                                        <svg width="12" height="12" viewBox="0 0 12 12" fill="none"
                                                            xmlns="http://www.w3.org/2000/svg">

                                                            <path
                                                                d="M6 0C4.81331 0 3.65328 0.351894 2.66658 1.01118C1.67989 1.67047 0.910851 2.60754 0.456725 3.7039C0.0025997 4.80025 -0.11622 6.00665 0.115291 7.17054C0.346802 8.33443 0.918247 9.40352 1.75736 10.2426C2.59648 11.0818 3.66557 11.6532 4.82946 11.8847C5.99335 12.1162 7.19975 11.9974 8.2961 11.5433C9.39246 11.0891 10.3295 10.3201 10.9888 9.33342C11.6481 8.34672 12 7.18669 12 6C12 4.4087 11.3679 2.88258 10.2426 1.75736C9.11742 0.632141 7.5913 0 6 0V0ZM1.2 6C1.20236 4.93462 1.5591 3.90031 2.214 3.06L8.94 9.786C8.23085 10.3355 7.3819 10.6752 6.48945 10.7667C5.597 10.8582 4.69677 10.6977 3.89089 10.3035C3.085 9.90935 2.40572 9.2972 1.9301 8.53653C1.45447 7.77586 1.20155 6.89712 1.2 6ZM9.786 8.94L3.06 2.214C3.9841 1.51165 5.1314 1.16794 6.28945 1.24652C7.4475 1.3251 8.53785 1.82065 9.3586 2.6414C10.1794 3.46215 10.6749 4.5525 10.7535 5.71055C10.8321 6.8686 10.4883 8.0159 9.786 8.94Z"
                                                                fill="white" />
                                                        </svg> Cancel Booking
                                                        <!-- </button> -->
                                                    </a>
                                                    <!-- <button type="button" class="btn btn-typ4 w-100"><svg width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg"> -->
                                                    <a href="flight-booking-details.php?booking_id=<?php echo $bookingDatas['id']; ?>"
                                                        class="btn btn-typ4 w-100">
                                                        <path
                                                            d="M6 0C4.81331 0 3.65328 0.351894 2.66658 1.01118C1.67989 1.67047 0.910851 2.60754 0.456725 3.7039C0.0025997 4.80025 -0.11622 6.00665 0.115291 7.17054C0.346802 8.33443 0.918247 9.40352 1.75736 10.2426C2.59648 11.0818 3.66557 11.6532 4.82946 11.8847C5.99335 12.1162 7.19975 11.9974 8.2961 11.5433C9.39246 11.0891 10.3295 10.3201 10.9888 9.33342C11.6481 8.34672 12 7.18669 12 6C12 4.4087 11.3679 2.88258 10.2426 1.75736C9.11742 0.632141 7.5913 0 6 0V0ZM1.2 6C1.20236 4.93462 1.5591 3.90031 2.214 3.06L8.94 9.786C8.23085 10.3355 7.3819 10.6752 6.48945 10.7667C5.597 10.8582 4.69677 10.6977 3.89089 10.3035C3.085 9.90935 2.40572 9.2972 1.9301 8.53653C1.45447 7.77586 1.20155 6.89712 1.2 6ZM9.786 8.94L3.06 2.214C3.9841 1.51165 5.1314 1.16794 6.28945 1.24652C7.4475 1.3251 8.53785 1.82065 9.3586 2.6414C10.1794 3.46215 10.6749 4.5525 10.7535 5.71055C10.8321 6.8686 10.4883 8.0159 9.786 8.94Z"
                                                            fill="white" />
                                                        </svg> Details
                                                        <!-- </button> -->
                                                    </a>
                                                <?php
                                                }
                                                ?>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </table>
                            </div>
                            <div class="b box booking-completed" style="display:none;">
                                <table>
                                    <?php
                                    // Get today's date and current time
                                    $todayDate = date('Y-m-d');
                                    $currentTime = date('H:i:s');

                                    $stmtbookingid = $conn->prepare('SELECT * FROM temp_booking WHERE user_id = :userid 
                                                                                                    AND booking_status = :bookingStatus
                                                                                                    AND DATE(dep_date) >= :todayDate
                                                                                                    AND ticket_status = :ticketStatus');

                                    $stmtbookingid->execute(array('userid' => $id, 'bookingStatus' => "Booked", 'todayDate' => $todayDate, 'ticketStatus' => "Ticketed"));
                                    $bookingData = $stmtbookingid->fetchAll(PDO::FETCH_ASSOC);
                                    // print_r($bookingData);
                                    foreach ($bookingData as $bookingDatas) {
                                        $dateTime = new DateTime($bookingDatas['dep_date']);

                                        $formattedDate = $dateTime->format('d F Y, H:i');


                                        ?>

                                        <tr>
                                            <td class="id"> <?php echo $bookingDatas['id']; ?></td>
                                            <td class="title">
                                                <?php echo $bookingDatas['dep_location'] . " -> " . $bookingDatas['arrival_location'] . " " . $formattedDate ?>
                                            </td>
                                            <!-- <td class="type">Stanard</td> -->
                                            <!-- <td class="coat">Rs. 35 213</td> -->
                                            <td class="status"><?php echo $bookingDatas['booking_status']; ?></td>
                                            <td class="action">
                                                <button type="button" class="btn btn-typ3 w-100 mb-2"><svg width="12"
                                                        height="12" viewBox="0 0 12 12" fill="none"
                                                        xmlns="http://www.w3.org/2000/svg">
                                                        <path
                                                            d="M6 0C4.81331 0 3.65328 0.351894 2.66658 1.01118C1.67989 1.67047 0.910851 2.60754 0.456725 3.7039C0.0025997 4.80025 -0.11622 6.00665 0.115291 7.17054C0.346802 8.33443 0.918247 9.40352 1.75736 10.2426C2.59648 11.0818 3.66557 11.6532 4.82946 11.8847C5.99335 12.1162 7.19975 11.9974 8.2961 11.5433C9.39246 11.0891 10.3295 10.3201 10.9888 9.33342C11.6481 8.34672 12 7.18669 12 6C12 4.4087 11.3679 2.88258 10.2426 1.75736C9.11742 0.632141 7.5913 0 6 0V0ZM1.2 6C1.20236 4.93462 1.5591 3.90031 2.214 3.06L8.94 9.786C8.23085 10.3355 7.3819 10.6752 6.48945 10.7667C5.597 10.8582 4.69677 10.6977 3.89089 10.3035C3.085 9.90935 2.40572 9.2972 1.9301 8.53653C1.45447 7.77586 1.20155 6.89712 1.2 6ZM9.786 8.94L3.06 2.214C3.9841 1.51165 5.1314 1.16794 6.28945 1.24652C7.4475 1.3251 8.53785 1.82065 9.3586 2.6414C10.1794 3.46215 10.6749 4.5525 10.7535 5.71055C10.8321 6.8686 10.4883 8.0159 9.786 8.94Z"
                                                            fill="white" />
                                                    </svg> Cancel Booking</button>
                                                <!-- <button type="button" class="btn btn-typ4 w-100"><svg width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M6 0C4.81331 0 3.65328 0.351894 2.66658 1.01118C1.67989 1.67047 0.910851 2.60754 0.456725 3.7039C0.0025997 4.80025 -0.11622 6.00665 0.115291 7.17054C0.346802 8.33443 0.918247 9.40352 1.75736 10.2426C2.59648 11.0818 3.66557 11.6532 4.82946 11.8847C5.99335 12.1162 7.19975 11.9974 8.2961 11.5433C9.39246 11.0891 10.3295 10.3201 10.9888 9.33342C11.6481 8.34672 12 7.18669 12 6C12 4.4087 11.3679 2.88258 10.2426 1.75736C9.11742 0.632141 7.5913 0 6 0V0ZM1.2 6C1.20236 4.93462 1.5591 3.90031 2.214 3.06L8.94 9.786C8.23085 10.3355 7.3819 10.6752 6.48945 10.7667C5.597 10.8582 4.69677 10.6977 3.89089 10.3035C3.085 9.90935 2.40572 9.2972 1.9301 8.53653C1.45447 7.77586 1.20155 6.89712 1.2 6ZM9.786 8.94L3.06 2.214C3.9841 1.51165 5.1314 1.16794 6.28945 1.24652C7.4475 1.3251 8.53785 1.82065 9.3586 2.6414C10.1794 3.46215 10.6749 4.5525 10.7535 5.71055C10.8321 6.8686 10.4883 8.0159 9.786 8.94Z" fill="white"/>
                                                    </svg> Details</button> -->
                                                <a href="flight-booking-details.php?booking_id=<?php echo $bookingDatas['id']; ?>"
                                                    class="btn btn-typ4 w-100">
                                                    <path
                                                        d="M6 0C4.81331 0 3.65328 0.351894 2.66658 1.01118C1.67989 1.67047 0.910851 2.60754 0.456725 3.7039C0.0025997 4.80025 -0.11622 6.00665 0.115291 7.17054C0.346802 8.33443 0.918247 9.40352 1.75736 10.2426C2.59648 11.0818 3.66557 11.6532 4.82946 11.8847C5.99335 12.1162 7.19975 11.9974 8.2961 11.5433C9.39246 11.0891 10.3295 10.3201 10.9888 9.33342C11.6481 8.34672 12 7.18669 12 6C12 4.4087 11.3679 2.88258 10.2426 1.75736C9.11742 0.632141 7.5913 0 6 0V0ZM1.2 6C1.20236 4.93462 1.5591 3.90031 2.214 3.06L8.94 9.786C8.23085 10.3355 7.3819 10.6752 6.48945 10.7667C5.597 10.8582 4.69677 10.6977 3.89089 10.3035C3.085 9.90935 2.40572 9.2972 1.9301 8.53653C1.45447 7.77586 1.20155 6.89712 1.2 6ZM9.786 8.94L3.06 2.214C3.9841 1.51165 5.1314 1.16794 6.28945 1.24652C7.4475 1.3251 8.53785 1.82065 9.3586 2.6414C10.1794 3.46215 10.6749 4.5525 10.7535 5.71055C10.8321 6.8686 10.4883 8.0159 9.786 8.94Z"
                                                        fill="white" />
                                                    </svg> Details
                                                    <!-- </button> -->
                                                </a>
                                            </td>
                                        </tr>
                                        <?php
                                    }
                                    ?>
                                </table>
                            </div>
                            <div class="c box booking-incompleted" style="display:none;">
                                <table>
                                    <?php
                                    $today = date('Y-m-d');

                                    // Prepare and execute the first query
                                    $stmtbookingid = $conn->prepare("SELECT * FROM temp_booking WHERE booking_status = 'Booked' AND ticket_status = 'TktInProcess' AND dep_date >= :cDate AND user_id = :id");
                                    $stmtbookingid->execute(array('cDate'=>$today, 'id' => $id));
                                    $bookingData = $stmtbookingid->fetchAll(PDO::FETCH_ASSOC);

                                    // Prepare and execute the second query
                                    $stmtbookingid1 = $conn->prepare("SELECT * FROM temp_booking WHERE booking_status = 'Booked' AND mf_reference IS NOT NULL AND ticket_status IS NULL AND dep_date >= :cDate AND user_id = :id");
                                    $stmtbookingid1->execute(array('cDate'=>$today, 'id' => $id));
                                    $bookingData1 = $stmtbookingid1->fetchAll(PDO::FETCH_ASSOC);
// echo '<pre/>'; print_r($bookingData1);
                                    // Prepare and execute the third query
                                    $stmtbookingid2 = $conn->prepare("SELECT * FROM temp_booking WHERE booking_status = 'BookingInProcess' AND dep_date >= :cDate AND mf_reference IS NOT NULL AND user_id = :id");
                                    $stmtbookingid2->execute(array('cDate'=>$today, 'id' => $id));
                                    $bookingData2 = $stmtbookingid2->fetchAll(PDO::FETCH_ASSOC);
// echo '<pre/>'; print_r($bookingData2);
                                    // Merge the results from both queries
                                    $allBookingData = array_merge($bookingData, $bookingData1, $bookingData2);
// echo '<pre/>'; print_r($allBookingData);
                                    foreach ($allBookingData as $bookingDatas) {
                                        $dateTime = new DateTime($bookingDatas['dep_date']);

                                        $formattedDate = $dateTime->format('d F Y, H:i');


                                        ?>
                                        <tr>
                                            <td class="id"> <?php echo $bookingDatas['id']; ?></td>
                                            <td class="title">
                                                <?php echo $bookingDatas['dep_location'] . " -> " . $bookingDatas['arrival_location'] . " " . $formattedDate ?>
                                            </td>
                                            <!-- <td class="type">Stanard</td> -->
                                            <!-- <td class="coat">Rs. 35 213</td> -->
                                            <td class="status"><?php echo $bookingDatas['booking_status']; ?></td>
                                            <td class="action">
                                                <button type="button" class="btn btn-typ3 w-100 mb-2"><svg width="12"
                                                        height="12" viewBox="0 0 12 12" fill="none"
                                                        xmlns="http://www.w3.org/2000/svg">
                                                        <path
                                                            d="M6 0C4.81331 0 3.65328 0.351894 2.66658 1.01118C1.67989 1.67047 0.910851 2.60754 0.456725 3.7039C0.0025997 4.80025 -0.11622 6.00665 0.115291 7.17054C0.346802 8.33443 0.918247 9.40352 1.75736 10.2426C2.59648 11.0818 3.66557 11.6532 4.82946 11.8847C5.99335 12.1162 7.19975 11.9974 8.2961 11.5433C9.39246 11.0891 10.3295 10.3201 10.9888 9.33342C11.6481 8.34672 12 7.18669 12 6C12 4.4087 11.3679 2.88258 10.2426 1.75736C9.11742 0.632141 7.5913 0 6 0V0ZM1.2 6C1.20236 4.93462 1.5591 3.90031 2.214 3.06L8.94 9.786C8.23085 10.3355 7.3819 10.6752 6.48945 10.7667C5.597 10.8582 4.69677 10.6977 3.89089 10.3035C3.085 9.90935 2.40572 9.2972 1.9301 8.53653C1.45447 7.77586 1.20155 6.89712 1.2 6ZM9.786 8.94L3.06 2.214C3.9841 1.51165 5.1314 1.16794 6.28945 1.24652C7.4475 1.3251 8.53785 1.82065 9.3586 2.6414C10.1794 3.46215 10.6749 4.5525 10.7535 5.71055C10.8321 6.8686 10.4883 8.0159 9.786 8.94Z"
                                                            fill="white" />
                                                    </svg> Cancel Booking</button>
                                                <!-- <button type="button" class="btn btn-typ4 w-100"><svg width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M6 0C4.81331 0 3.65328 0.351894 2.66658 1.01118C1.67989 1.67047 0.910851 2.60754 0.456725 3.7039C0.0025997 4.80025 -0.11622 6.00665 0.115291 7.17054C0.346802 8.33443 0.918247 9.40352 1.75736 10.2426C2.59648 11.0818 3.66557 11.6532 4.82946 11.8847C5.99335 12.1162 7.19975 11.9974 8.2961 11.5433C9.39246 11.0891 10.3295 10.3201 10.9888 9.33342C11.6481 8.34672 12 7.18669 12 6C12 4.4087 11.3679 2.88258 10.2426 1.75736C9.11742 0.632141 7.5913 0 6 0V0ZM1.2 6C1.20236 4.93462 1.5591 3.90031 2.214 3.06L8.94 9.786C8.23085 10.3355 7.3819 10.6752 6.48945 10.7667C5.597 10.8582 4.69677 10.6977 3.89089 10.3035C3.085 9.90935 2.40572 9.2972 1.9301 8.53653C1.45447 7.77586 1.20155 6.89712 1.2 6ZM9.786 8.94L3.06 2.214C3.9841 1.51165 5.1314 1.16794 6.28945 1.24652C7.4475 1.3251 8.53785 1.82065 9.3586 2.6414C10.1794 3.46215 10.6749 4.5525 10.7535 5.71055C10.8321 6.8686 10.4883 8.0159 9.786 8.94Z" fill="white"/>
                                                </svg> Details</button> -->
                                                <a href="flight-booking-details.php?booking_id=<?php echo $bookingDatas['id']; ?>"
                                                    class="btn btn-typ4 w-100">
                                                    <path
                                                        d="M6 0C4.81331 0 3.65328 0.351894 2.66658 1.01118C1.67989 1.67047 0.910851 2.60754 0.456725 3.7039C0.0025997 4.80025 -0.11622 6.00665 0.115291 7.17054C0.346802 8.33443 0.918247 9.40352 1.75736 10.2426C2.59648 11.0818 3.66557 11.6532 4.82946 11.8847C5.99335 12.1162 7.19975 11.9974 8.2961 11.5433C9.39246 11.0891 10.3295 10.3201 10.9888 9.33342C11.6481 8.34672 12 7.18669 12 6C12 4.4087 11.3679 2.88258 10.2426 1.75736C9.11742 0.632141 7.5913 0 6 0V0ZM1.2 6C1.20236 4.93462 1.5591 3.90031 2.214 3.06L8.94 9.786C8.23085 10.3355 7.3819 10.6752 6.48945 10.7667C5.597 10.8582 4.69677 10.6977 3.89089 10.3035C3.085 9.90935 2.40572 9.2972 1.9301 8.53653C1.45447 7.77586 1.20155 6.89712 1.2 6ZM9.786 8.94L3.06 2.214C3.9841 1.51165 5.1314 1.16794 6.28945 1.24652C7.4475 1.3251 8.53785 1.82065 9.3586 2.6414C10.1794 3.46215 10.6749 4.5525 10.7535 5.71055C10.8321 6.8686 10.4883 8.0159 9.786 8.94Z"
                                                        fill="white" />
                                                    </svg> Details
                                                    <!-- </button> -->
                                                </a>
                                            </td>
                                        </tr>
                                        <?php
                                    }
                                    ?>
                                </table>
                            </div>
                            <div class="d box booking-cancelled" style="display:none;">
                                <table>
                                    <?php
                                    $today = date('Y-m-d');

                                    // nimmi code
                                    $cancelid = $conn->prepare("SELECT * FROM cancel_booking WHERE cancel_status = '1' AND user_agent_id = :id");
                                    $cancelid->execute(array('id' => $id));
                                    $cancelidData = $cancelid->fetchAll(PDO::FETCH_ASSOC);

                                    // old code
                                    $stmtbookingid = $conn->prepare('SELECT * FROM temp_booking WHERE user_id = :userid and booking_status = :bookingStatus ');
                                    $stmtbookingid->execute(array('userid' => $id, 'bookingStatus' => "Cancelled"));
                                    $bookingData = $stmtbookingid->fetchAll(PDO::FETCH_ASSOC);

                                    // Merge the results from both queries
                                    $allCancelData = array_merge($bookingData, $cancelidData);

                                    foreach ($allCancelData as $bookingDatas) {
                                        $dateTime = new DateTime($bookingDatas['dep_date']);

                                        $formattedDate = $dateTime->format('d F Y, H:i');
                                        ?>
                                        <tr>
                                            <td class="id"> <?php echo $bookingDatas['id']; ?></td>
                                            <td class="title">
                                                <?php echo $bookingDatas['dep_location'] . " -> " . $bookingDatas['arrival_location'] . " " . $formattedDate ?>
                                            </td>
                                            <!-- <td class="type">Stanard</td> -->
                                            <!-- <td class="coat">Rs. 35 213</td> -->
                                            <td class="status"><?php echo $bookingDatas['booking_status']; ?></td>
                                            <td class="action">
                                                <button type="button" class="btn btn-typ3 w-100 mb-2" disabled><svg width="12"
                                                        height="12" viewBox="0 0 12 12" fill="none"
                                                        xmlns="http://www.w3.org/2000/svg">
                                                        <path
                                                            d="M6 0C4.81331 0 3.65328 0.351894 2.66658 1.01118C1.67989 1.67047 0.910851 2.60754 0.456725 3.7039C0.0025997 4.80025 -0.11622 6.00665 0.115291 7.17054C0.346802 8.33443 0.918247 9.40352 1.75736 10.2426C2.59648 11.0818 3.66557 11.6532 4.82946 11.8847C5.99335 12.1162 7.19975 11.9974 8.2961 11.5433C9.39246 11.0891 10.3295 10.3201 10.9888 9.33342C11.6481 8.34672 12 7.18669 12 6C12 4.4087 11.3679 2.88258 10.2426 1.75736C9.11742 0.632141 7.5913 0 6 0V0ZM1.2 6C1.20236 4.93462 1.5591 3.90031 2.214 3.06L8.94 9.786C8.23085 10.3355 7.3819 10.6752 6.48945 10.7667C5.597 10.8582 4.69677 10.6977 3.89089 10.3035C3.085 9.90935 2.40572 9.2972 1.9301 8.53653C1.45447 7.77586 1.20155 6.89712 1.2 6ZM9.786 8.94L3.06 2.214C3.9841 1.51165 5.1314 1.16794 6.28945 1.24652C7.4475 1.3251 8.53785 1.82065 9.3586 2.6414C10.1794 3.46215 10.6749 4.5525 10.7535 5.71055C10.8321 6.8686 10.4883 8.0159 9.786 8.94Z"
                                                            fill="white" />
                                                    </svg> Cancel Booking</button>
                                                <button type="button" class="btn btn-typ4 w-100" disabled><svg width="12"
                                                        height="12" viewBox="0 0 12 12" fill="none"
                                                        xmlns="http://www.w3.org/2000/svg">
                                                        <path
                                                            d="M6 0C4.81331 0 3.65328 0.351894 2.66658 1.01118C1.67989 1.67047 0.910851 2.60754 0.456725 3.7039C0.0025997 4.80025 -0.11622 6.00665 0.115291 7.17054C0.346802 8.33443 0.918247 9.40352 1.75736 10.2426C2.59648 11.0818 3.66557 11.6532 4.82946 11.8847C5.99335 12.1162 7.19975 11.9974 8.2961 11.5433C9.39246 11.0891 10.3295 10.3201 10.9888 9.33342C11.6481 8.34672 12 7.18669 12 6C12 4.4087 11.3679 2.88258 10.2426 1.75736C9.11742 0.632141 7.5913 0 6 0V0ZM1.2 6C1.20236 4.93462 1.5591 3.90031 2.214 3.06L8.94 9.786C8.23085 10.3355 7.3819 10.6752 6.48945 10.7667C5.597 10.8582 4.69677 10.6977 3.89089 10.3035C3.085 9.90935 2.40572 9.2972 1.9301 8.53653C1.45447 7.77586 1.20155 6.89712 1.2 6ZM9.786 8.94L3.06 2.214C3.9841 1.51165 5.1314 1.16794 6.28945 1.24652C7.4475 1.3251 8.53785 1.82065 9.3586 2.6414C10.1794 3.46215 10.6749 4.5525 10.7535 5.71055C10.8321 6.8686 10.4883 8.0159 9.786 8.94Z"
                                                            fill="white" />
                                                    </svg> Details</button>
                                            </td>
                                        </tr>
                                        <?php
                                    }
                                    ?>
                                </table>
                            </div>
                            <button type="button" class="btn btn-typ4 fs-14 fw-500 more-btn">Load More</button>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Tab Container 2 -->
            <input class="tab-radio" id="main-tab-2" name="main-group" type="radio" />
            <div class="tab-content col-md-9 pl-md-0">
                <div class="row my-4">
                    <div class="col-12">
                        <h2 class="title-typ2 mb-0">Cancellation</h2>
                    </div>
                </div>
                <div class="cancellation-container box-border p-4">
                    <strong class="fs-16 fw-500 light-blue-txt mb-3 d-block">Cancellation</strong>
                    <div class="d-flex flex-md-row flex-column-reverse justify-content-between">
                        <ul id="filterOptionsFlight" class="cancellation-tab-filter d-flex flex-md-row flex-column-reverse">
                            <li><a href="#" class="upcoming">Upcoming</a>
                            </li>
                            <li><a href="#" class="cancelled">Cancelled</a>
                            </li>
                            <li class="active"><a href="#" class="all">All</a>
                            </li>
                        </ul>
                        <ul id="filterOptionsPackage"
                            class="cancellation-tab-filter flex-md-row flex-column-reverse d-none">
                            <li><a href="#" class="upcoming">Upcoming</a>
                            </li>
                            <li><a href="#" class="cancelled">Cancelled</a>
                            </li>
                            <li class="active"><a href="#" class="all">All</a>
                            </li>
                        </ul>
                        <div class="position-relative slct-btn-wrp">
                            <ul class="select-tab-item">
                                <li class="active-btn">
                                    <button type="button" data-target="#filterOptionsFlight"
                                        class="btn filterOptionsFlight">Flight</button>
                                    <button type="button" class="btn downbtn">Down</button>
                                </li>
                                <li>
                                    <button type="button" data-target="#filterOptionsPackage"
                                        class="btn filterOptionsPackage">Package</button>
                                    <button type="button" class="btn downbtn">Down</button>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div id="FlightCancellationList" class="cancellation-tab-filter-content">
                        <div class="item upcoming">
                            <ul class="row align-items-center ml-0 mr-0">
                                <li class="col-md-1 text-md-center mb-md-0 mb-2"><svg width="21" height="17"
                                        viewBox="0 0 21 17" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            d="M9.012 7.59L4.502 0.518L6.433 0L13.384 6.42L18.646 5.01C19.0303 4.9071 19.4397 4.96107 19.7842 5.16004C20.1287 5.35902 20.3801 5.6867 20.483 6.071C20.5859 6.4553 20.5319 6.86474 20.333 7.20924C20.134 7.55375 19.8063 7.8051 19.422 7.908L4.45 11.918L3.674 9.02L3.915 8.955L6.382 11.4L3.756 12.104C3.54067 12.1617 3.31221 12.1459 3.10693 12.0589C2.90165 11.9719 2.73132 11.8189 2.623 11.624L0 6.898L1.449 6.51L3.915 8.955L9.012 7.589V7.59ZM2.534 14.958H18.534V16.958H2.534V14.958Z"
                                            fill="#2391D1"></path>
                                    </svg></li>
                                <li class="col-md-4 mb-md-0 mb-2">
                                    <div class="d-flex align-items-center">
                                        <span class="">COK</span>
                                        <div class="d-flex flex-column align-items-center direction-icon ml-2 mr-2">
                                            <span class="oneway d-flex">
                                                <svg width="29" height="12" viewBox="0 0 29 12" fill="none"
                                                    xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M29 6L19 0.226497V11.7735L29 6ZM0 7L20 7V5L0 5L0 7Z"
                                                        fill="#A19595"></path>
                                                </svg>
                                            </span>
                                            <span class="return d-flex">
                                                <svg width="29" height="12" viewBox="0 0 29 12" fill="none"
                                                    xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M0 6L10 11.7735L10 0.226497L0 6ZM9 7L29 7V5L9 5V7Z"
                                                        fill="#A19595"></path>
                                                </svg>
                                            </span>
                                        </div>
                                        <span class="">DXB</span>
                                    </div>
                                    <div>Travel Start Date: <span>Thu, 15 Dec 2022</span></div>
                                </li>
                                <li class="col-md-4 d-flex flex-column mb-md-0 mb-2">
                                    <span>Booking ID: <strong>FB00088951</strong></span>
                                    <span>Booking Date : <span>Tue, 06 Dec 2022</span></span>
                                </li>
                                <li class="col-md-3">
                                    <span class="status-not-booked">Not Booked</span>
                                </li>
                            </ul>
                        </div>
                        <div class="item cancelled">
                            <ul class="row align-items-center ml-0 mr-0">
                                <li class="col-md-1 text-md-center mb-md-0 mb-2"><svg width="21" height="17"
                                        viewBox="0 0 21 17" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            d="M9.012 7.59L4.502 0.518L6.433 0L13.384 6.42L18.646 5.01C19.0303 4.9071 19.4397 4.96107 19.7842 5.16004C20.1287 5.35902 20.3801 5.6867 20.483 6.071C20.5859 6.4553 20.5319 6.86474 20.333 7.20924C20.134 7.55375 19.8063 7.8051 19.422 7.908L4.45 11.918L3.674 9.02L3.915 8.955L6.382 11.4L3.756 12.104C3.54067 12.1617 3.31221 12.1459 3.10693 12.0589C2.90165 11.9719 2.73132 11.8189 2.623 11.624L0 6.898L1.449 6.51L3.915 8.955L9.012 7.589V7.59ZM2.534 14.958H18.534V16.958H2.534V14.958Z"
                                            fill="#2391D1"></path>
                                    </svg></li>
                                <li class="col-md-4 mb-md-0 mb-2">
                                    <div class="d-flex align-items-center">
                                        <span class="">KCZ</span>
                                        <div class="d-flex flex-column align-items-center direction-icon ml-2 mr-2">
                                            <span class="oneway d-flex">
                                                <svg width="29" height="12" viewBox="0 0 29 12" fill="none"
                                                    xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M29 6L19 0.226497V11.7735L29 6ZM0 7L20 7V5L0 5L0 7Z"
                                                        fill="#A19595"></path>
                                                </svg>
                                            </span>
                                            <span class="return d-flex">
                                                <svg width="29" height="12" viewBox="0 0 29 12" fill="none"
                                                    xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M0 6L10 11.7735L10 0.226497L0 6ZM9 7L29 7V5L9 5V7Z"
                                                        fill="#A19595"></path>
                                                </svg>
                                            </span>
                                        </div>
                                        <span class="">DXB</span>
                                    </div>
                                    <div>Travel Start Date: <span>Sat, 17 Dec 2022</span></div>
                                </li>
                                <li class="col-md-4 d-flex flex-column mb-md-0 mb-2">
                                    <span>Booking ID: <strong>FB00089094</strong></span>
                                    <span>Booking Date : <span>Fri, 09 Dec 2022</span></span>
                                </li>
                                <li class="col-md-3">
                                    <span class="status-not-booked">Not Booked</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div id="PackageCancellationList" class="cancellation-tab-filter-content d-none">
                        <div class="item upcoming">
                            <ul class="row align-items-center ml-0 mr-0">
                                <li class="col-md-1 text-md-center mb-md-0 mb-2">
                                    <img src="images/img22.png" alt="">
                                </li>
                                <li class="col-md-4 mb-md-0 mb-2">
                                    <div class="d-flex align-items-center">
                                        <span class="">Crazy Deal Dubai - 5N Including Dubai Shopping Festival</span>
                                    </div>
                                    <div>Package Start Date: <span>Sat, 24 Dec 2022</span></div>
                                </li>
                                <li class="col-md-4 d-flex flex-column mb-md-0 mb-2">
                                    <span>Booking ID: <strong>PB00086552</strong></span>
                                    <span>Booking Date: <span>Sat, 10 Dec 2022</span></span>
                                </li>
                                <li class="col-md-3">
                                    <span class="status-not-booked">Not Booked</span>
                                </li>
                            </ul>
                        </div>
                        <div class="item cancelled">
                            <ul class="row align-items-center ml-0 mr-0">
                                <li class="col-md-1 text-md-center mb-md-0 mb-2">
                                    <img src="images/img20.png" alt="">
                                </li>
                                <li class="col-md-4 mb-md-0 mb-2">
                                    <div class="d-flex align-items-center">
                                        <span class="">Magical Dubai With Yas Island (Winter)</span>
                                    </div>
                                    <div>Package Start Date: <span>Mon, 12 Dec 2022</span></div>
                                </li>
                                <li class="col-md-4 d-flex flex-column mb-md-0 mb-2">
                                    <span>Booking ID: <strong>PB00086551</strong></span>
                                    <span>Booking Date: <span>Thu, 01 Dec 2022</span></span>
                                </li>
                                <li class="col-md-3">
                                    <span class="status-not-booked">Not Booked</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Tab Container 3 -->
            <input class="tab-radio" id="main-tab-3" name="main-group" type="radio" />
            <div class="tab-content col-md-9 pl-md-0">
                <div class="row my-4">
                    <div class="col-12">
                        <h2 class="title-typ2 mb-0">Profile</h2>
                    </div>
                </div>
                <div class="box-border p-4">
                    <div class="row profileinfo">
                        <div class="col-12">
                            <strong class="fs-16 fw-500 light-blue-txt mb-3 d-block">Personal Information</strong>
                            <button type="button" class="btn profile-edit-button">
                                <svg width="17" height="14" viewBox="0 0 17 14" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M0 10.7375V13.2153H6.60767V11.6543H1.56932V10.7375C1.56932 10.2088 4.15457 9.00295 6.60767 9.00295C7.40059 9.01121 8.18525 9.11858 8.94513 9.31681L10.2006 8.06136C9.04425 7.67316 7.84661 7.45841 6.60767 7.43363C4.40236 7.43363 0 8.53215 0 10.7375ZM6.60767 0C4.7823 0 3.30383 1.47847 3.30383 3.30383C3.30383 5.1292 4.7823 6.60767 6.60767 6.60767C8.43304 6.60767 9.9115 5.1292 9.9115 3.30383C9.9115 1.47847 8.43304 0 6.60767 0ZM6.60767 4.95575C5.69911 4.95575 4.95575 4.22065 4.95575 3.30383C4.95575 2.38702 5.69911 1.65192 6.60767 1.65192C7.51622 1.65192 8.25959 2.39528 8.25959 3.30383C8.25959 4.21239 7.52448 4.95575 6.60767 4.95575ZM16.2714 7.72271L15.4454 8.54867L13.7522 6.89676L14.5782 6.0708C14.6631 5.98756 14.7773 5.94094 14.8962 5.94094C15.0151 5.94094 15.1292 5.98756 15.2142 6.0708L16.2714 7.12802C16.4448 7.30147 16.4448 7.59056 16.2714 7.76401M8.25959 12.3398L13.2649 7.33451L14.9581 8.98643L10.0024 14H8.25959V12.3398Z"
                                        fill="white" />
                                </svg>
                            </button>
                        </div>
                        <div class="col-md-8">
                            <!-- <div class="row mb-2">
                                    <div class="col-md-4 col-5">User Name</div>
                                    <div class="col-1">:</div>
                                    <div class="col">Sreejith Ravi</div>
                                </div> -->
                            <div class="row mb-2">
                                <div class="col-md-4 col-5">First Name</div>
                                <div class="col-1">:</div>
                                <div class="col"><?php echo $user['first_name'] ?></div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-md-4 col-5">Last Name</div>
                                <div class="col-1">:</div>
                                <div class="col"><?php echo $user['last_name'] ?></div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-md-4 col-5">Phone Number</div>
                                <div class="col-1">:</div>
                                <div class="col"><?php echo $user['mobile'] ?></div>
                            </div>
                            <!-- <div class="row mb-2">
                                    <div class="col-md-4 col-5">Date of Birth</div>
                                    <div class="col-1">:</div>
                                    <div class="col">05-08-1989</div>
                                </div> -->
                            <div class="row mb-2">
                                <div class="col-md-4 col-5">Address</div>
                                <div class="col-1">:</div>
                                <div class="col"><?php echo $user['contact_address'] ?></div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-md-4 col-5">Country</div>
                                <div class="col-1">:</div>
                                <div class="col"><?php echo $user['country'] ?></div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-md-4 col-5">State</div>
                                <div class="col-1">:</div>
                                <div class="col"><?php echo $user['state'] ?></div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-md-4 col-5">Town / City</div>
                                <div class="col-1">:</div>
                                <div class="col"><?php echo $user['city'] ?></div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-md-4 col-5">ZIP Code</div>
                                <div class="col-1">:</div>
                                <div class="col"><?php echo $user['zip_code'] ?></div>
                            </div>
                        </div>
                    </div>
                    <div id="alert-container"></div>
                    <div class="row" id="editprofile">

                        <form action="" method="POST" enctype="multipart/form-data" id="image-form">

                            <div class="col-md-12">
                                <strong class="fs-16 fw-500 light-blue-txt mb-3 d-block">Personal Infomation</strong>
                                <div class="row">
                                    <div class="col-6 mb-3">
                                        <label for="">First Name</label>
                                        <input type="text" name="fname" id="fname"
                                            value="<?php echo $user['first_name']; ?>" class="form-control">
                                    </div>
                                    <div class="col-6 mb-3">
                                        <label for="">Last Name</label>
                                        <input type="text" name="lname" id="lname" value="<?php echo $user['last_name']; ?>"
                                            class="form-control">
                                    </div>
                                    <div class="col-6 mb-3">
                                        <label for="">Phone Number</label>
                                        <input type="text" name="mobile" id="mobile" value="<?php echo $user['mobile']; ?>"
                                            class="form-control">
                                    </div>
                                    <!-- <div class="col-12 mb-3">
                                            <label for="">Date of Birth</label>
                                            <input type="text" class="form-control">
                                        </div> -->
                                    <div class="col-6 mb-3">
                                        <label for="">Upload profile Photo</label>
                                        <label class="uploadFile form-control">
                                            <span class="filename"></span>
                                            <input type="file" class="inputfile form-control" name="p-image" id="p-image">
                                        </label>
                                        <?php if ($user) { ?>
                                            <img src="uploads/profile/<?php echo $pimage; ?>" alt="profile img" width="60"
                                                height="60">
                                        <?php } ?>
                                        <span class="d-block">Note : Maximum Image Size 1000 kb</span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <strong class="fs-16 fw-500 light-blue-txt mb-3 d-block">Contact Details</strong>
                                <div class="row">
                                    <div class="col-12 mb-3">
                                        <label for="">Address</label>
                                        <input type="text" name="Address" id="address"
                                            value="<?php echo $user['contact_address']; ?>" class="form-control">
                                    </div>
                                    <div class="col-6 mb-3">
                                        <label for="">Country</label>
                                        <!-- <input type="text" name="country" id="country" class="form-control" value="<?php echo $user['country']; ?>"> -->
                                        <select id="endusercountry" name="endusercountry" class="form-control">
                                            <option value="<?php echo $user['country']; ?>"><?php echo $user['country']; ?>
                                            </option>
                                            <option value="">Loading countries...</option>
                                        </select>
                                    </div>
                                    <div class="col-6 mb-3">
                                        <label for="">State</label>
                                        <!-- <input type="text" name="state" id="state" class="form-control" value="<?php echo $user['state']; ?>"> -->
                                        <select id="enduserstate" name="enduserstate" class="form-control">
                                            <option value="<?php echo $user['state']; ?>"><?php echo $user['state']; ?>
                                            </option>
                                            <option value="">Select State</option>
                                        </select>
                                    </div>
                                    <div class="col-6 mb-3">
                                        <label for="">City</label>
                                        <input type="text" name="endusercity" id="endusercity" class="form-control"
                                            value="<?php echo $user['city']; ?>">
                                    </div>
                                    <div class="col-6 mb-3">
                                        <label for="">Zip Code / Postel Code</label>
                                        <input type="text" name="zipcode" id="zipcode" class="form-control"
                                            value="<?php echo $user['zip_code']; ?>">
                                    </div>
                                    <input type="hidden" name="uid" id="uid" class="form-control"
                                        value="<?php echo $user['id']; ?>">
                                    <input type="hidden" name="uid" id="hcountry" class="form-control"
                                        value="<?php echo $user['country']; ?>">
                                    <input type="hidden" name="uid" id="hstate" class="form-control"
                                        value="<?php echo $user['state']; ?>">

                                </div>
                            </div>
                            <div class="col-12 d-flex">
                                <button type="submit" id="enuserupdate" class="btn btn-typ4">UPDATE PROFILE</button>
                                <button type="button" VALUE="Back" onClick="history.go();"
                                    class="btn btn-typ3 ml-2">CANCEL</button>
                            </div>

                        </form>
                    </div>
                </div>
            </div>
            <!-- Tab Container 4 -->
            <input class="tab-radio" id="main-tab-4" name="main-group" type="radio" />
            <div class="tab-content col-md-9 pl-md-0">
                <div class="row my-4">
                    <div class="col-12">
                        <h2 class="title-typ2 mb-0">Settings</h2>
                    </div>
                </div>
                <form action="" method="POST">
                    <div class="box-border custom-radio p-4">
                        <!-- <strong class="fs-16 fw-500 light-blue-txt mb-3 d-block">Change Password</strong>
                        <span>Change with</span>
                        <input type="radio" id="tab1" name="password-change" checked>
                        <label for="tab1">Password</label>
                        <input type="radio" id="tab2" name="password-change">
                        <label for="tab2">OTP</label> -->
                        <article class="sub-tabs-container mt-3">
                            <div class="row justify-content-between">
                                <div class="col-md-5 mb-3">
                                    <div class="row">
                                        <div class="col-12 mb-3">
                                            <input type="password" name="user-current-password" id="user-current-password"
                                                class="form-control" placeholder="Enter Current Password">
                                            <span id="current-password-error" style="color:red;"></span>
                                        </div>
                                        <div class="col-12 mb-3">
                                            <input type="password" name="user-new-password" id="user-new-password"
                                                class="form-control" placeholder="Enter New Password">
                                            <span id="new-password-error" style="color:red;"></span>
                                        </div>
                                        <div class="col-12 mb-3">
                                            <input type="password" name="user-new-varify-password"
                                                id="user-new-varify-password" class="form-control"
                                                placeholder="Confirm New Password">
                                            <span id="verify-password-error" style="color:red;"></span>
                                        </div>
                                        <div class="col-12">
                                            <button type="submit" id="change-user-password"
                                                class="btn btn-typ3 pl-5 pr-5">SAVE</button>
                                            <div id="login_message"></div>
                                            <!-- <button type="button" class="btn btn-typ3 pl-5 pr-5">SAVE</button> -->
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <div class="row">
                                        <div class="col-12">
                                            <ul class="password-features-list">
                                                <li>Contains between X- XX characters</li>
                                                <li>Contains at least X- mixed case letter</li>
                                                <li>Contains at least X number</li>
                                                <li>Contains at least X special character</li>
                                                <li>Not contain white space</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </article>
                        <!-- <article class="sub-tabs-container password-change mt-3">
                            <div class="row justify-content-between">
                                <div class="col-md-5 mb-3">
                                    <div class="row">
                                        <div class="col-12 mb-3">
                                            <input type="password" class="form-control">
                                        </div>
                                        <div class="col-12 mb-3">
                                            <input type="password" class="form-control" placeholder="Enter New Password">
                                        </div>
                                        <div class="col-12 mb-3">
                                            <input type="password" class="form-control" placeholder="Confirmr New Password">
                                        </div>
                                        <div class="col-12 mb-3">
                                            OTP has been sent to your Mobile Number
                                        </div>
                                        <div class="col-12 d-flex">
                                            <button type="button" class="btn btn-typ3 pl-5 pr-5">SAVE</button>
                                            <button type="button" class="btn light-blue-txt pl-0 ml-3">Resend OTP</button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <div class="row">
                                        <div class="col-12">
                                            <ul class="password-features-list">
                                                <li>Contains between X- XX characters</li>
                                                <li>Contains at least X- mixed case letter</li>
                                                <li>Contains at least X number</li>
                                                <li>Contains at least X special character</li>
                                                <li>Not contain white space</li>
                                            </ul>
                                        </div>
                                    </div>    
                                </div>
                            </div>
                        </article> -->
                    </div>
                </form>
            </div>
            <!-- Modal HTML -->
            <div class="modal fade bd-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel"
                aria-hidden="true">
                <div class="modal-dialog modal-sm">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLabel">Success</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"
                                onclick="handleClose()">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            Password changed successfully.
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-primary" data-dismiss="modal"
                                onclick="handleClose()">Close</button>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Tab Container 5 -->
            <!--check auther already review added-->
            <?php

            include_once 'includes/class.Data.php';

            $reviewObj = new Data();

            $review = $reviewObj->select_review($id);
            // print_r($review);
            ?>
            <input class="tab-radio" id="main-tab-5" name="main-group" type="radio" />
            <div class="tab-content col-md-9 pl-md-0">
                <div class="row my-4">
                    <div class="col-12">
                        <h2 class="title-typ2 mb-0">Reviews</h2>
                    </div>
                </div>
                <div class="box-border p-4">
                    <form method="POST" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-6">
                                <?php $star = $review[0]['rating']; ?>
                                <strong class="fs-16 fw-500 light-blue-txt mb-3 d-block">Rate Our Services</strong>
                                <div class="star-rating">
                                    <input type="radio" id="5-stars" name="rating" value="5" <?php if ($star == 5)
                                        echo 'checked'; ?> />
                                    <label for="5-stars" class="star">&#9733;</label>
                                    <input type="radio" id="4-stars" name="rating" value="4" <?php if ($star == 4)
                                        echo 'checked'; ?> />
                                    <label for="4-stars" class="star">&#9733;</label>
                                    <input type="radio" id="3-stars" name="rating" value="3" <?php if ($star == 3)
                                        echo 'checked'; ?> />
                                    <label for="3-stars" class="star">&#9733;</label>
                                    <input type="radio" id="2-stars" name="rating" value="2" <?php if ($star == 2)
                                        echo 'checked'; ?> />
                                    <label for="2-stars" class="star">&#9733;</label>
                                    <input type="radio" id="1-star" name="rating" value="1" <?php if ($star == 1)
                                        echo 'checked'; ?> />
                                    <label for="1-star" class="star">&#9733;</label>
                                </div>
                            </div>
                            <div class="desc col-6 d-flex flex-column mb-3">
                                <label for="title" class="fs-16 fw-500 light-blue-txt mb-3 d-block">Title</label>
                                <input class="form-control h-auto" name="title" id="title"
                                    value="<?php echo $review[0]['title']; ?>" placeholder="Enter title">
                            </div>
                            <div class="desc col-6 d-flex flex-column mb-3">
                                <label for="description"
                                    class="fs-16 fw-500 light-blue-txt mb-3 d-block">Description</label>
                                <textarea class="form-control h-auto" name="description" id="description" cols="30" rows="6"
                                    placeholder="write your review here.."><?php echo $review[0]['description']; ?></textarea>
                            </div>
                            <div class="desc col-6 d-flex flex-column mb-3">
                                <label for="p-image" class="fs-16 fw-500 light-blue-txt mb-3 d-block">Upload Image</label>
                                <label class="uploadFile form-control">
                                    <span class="filename"></span>
                                    <input type="file" class="form-control" name="review_pic" id="review_pic"
                                        placeholder="upload profile image">
                                </label>
                                <?php if ($review) { ?>
                                    <img src="uploads/reviews/<?php echo $review[0]['image']; ?>" height="100" width="100">
                                    <p><?php echo $review[0]['image']; ?></p>
                                <?php } ?>
                            </div>
                        </div>
                        <div class="error-container"></div>
                        <div class="row">
                            <div class="col-md-12">
                                <button type="submit" id="review_valid" class="btn btn-typ4">Submit</button>
                                <button type="submit" id="review_delete" class="btn btn-typ4"
                                    data-userid="<?php echo $id; ?>">Clear</button>
                            </div>
                        </div>
                </div>
                </form>
            </div>
        </div>
        </div>
        </div>
    </section>
    <!--  Login Modal -->
    <?php
    require_once("includes/login-modal.php");
    ?>
    <!--  forgot Modal -->
    <?php
    require_once("includes/forgot-modal.php");
    ?>
    <div class="modal flight-search-loading" id="FlightSearchLoading" tabindex="-1" role="dialog"
        aria-labelledby="ForgotPasswordModalTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-body">
                    <div class="row justify-content-center">
                        <div class="col-12">
                            <form>
                                <div class="fs-16 fw-300 text-center mb-4">Please wait are searching for the cheapest fare
                                    for flights</div>
                                <div class="form-row flight-direction align-items-center justify-content-center mb-4">
                                    <strong class="col-md-5 text-md-right text-center mb-md-0 mb-2">Kochi</strong>
                                    <div
                                        class="col-md-1 d-flex flex-md-column flex-column-reverse align-items-center direction-icon">
                                        <span class="oneway d-flex">
                                            <svg width="29" height="12" viewBox="0 0 29 12" fill="none"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <path d="M29 6L19 0.226497V11.7735L29 6ZM0 7L20 7V5L0 5L0 7Z"
                                                    fill="#4756CB" />
                                            </svg>
                                        </span>
                                        <span class="return d-flex">
                                            <svg width="29" height="12" viewBox="0 0 29 12" fill="none"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <path d="M0 6L10 11.7735L10 0.226497L0 6ZM9 7L29 7V5L9 5V7Z"
                                                    fill="#4756CB" />
                                            </svg>
                                        </span>
                                    </div>
                                    <strong class="col-md-5 text-md-left text-center mt-md-0 mt-2">Dubai</strong>
                                </div>
                                <div class="progress mb-5">
                                    <div class="progress-bar" role="progressbar" style="width: 75%" aria-valuenow="75"
                                        aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <div class="row justify-content-center mb-5">
                                    <div class="col-lg-8 col-md-10">
                                        <div class="row justify-content-between">
                                            <div class="col-4 justify-content-center">
                                                <div class="mb-4 d-flex justify-content-center">
                                                    <svg width="60" height="39" viewBox="0 0 60 39" fill="none"
                                                        xmlns="http://www.w3.org/2000/svg">
                                                        <path
                                                            d="M47.0925 0.984076C44.6576 2.07045 40.874 4.16828 36.6034 6.60326L16.1495 3.75621C15.0017 3.59067 13.8318 3.67241 12.7181 3.99596L8.27894 5.25466C7.90433 5.25466 7.90433 5.62927 8.27894 5.77912L25.3613 13.2714C19.5922 16.7178 14.46 19.8646 12.2124 21.2881C11.8113 21.54 11.3596 21.7002 10.8894 21.7572C10.4192 21.8142 9.9423 21.7666 9.49268 21.6177L5.01981 20.2017C4.30954 19.9484 3.53311 19.951 2.82458 20.2092L0 21.363L9.44024 29.2299C9.91481 29.628 10.4921 29.8842 11.1057 29.969C11.7193 30.0538 12.3444 29.9638 12.9091 29.7094C17.4195 27.694 29.2947 22.2246 38.1356 17.6169C57.7653 7.20265 59.938 5.4045 59.938 3.75621C59.938 1.17138 52.4458 -1.48837 47.0888 0.984076H47.0925Z"
                                                            fill="#969696" />
                                                        <line y1="38" x2="60" y2="38" stroke="#969696" stroke-width="2" />
                                                    </svg>
                                                </div>
                                                <div class="trvl-dtls text-center fw-300">
                                                    <div class="label">Depart</div>
                                                    <div class="date">
                                                        <strong class="fw-500">11</strong>
                                                        <div>
                                                            Nov, 2022 <br>
                                                            Friday
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-4 justify-content-center">
                                                <div class="mb-4 d-flex justify-content-center">
                                                    <svg width="60" height="39" viewBox="0 0 60 39" fill="none"
                                                        xmlns="http://www.w3.org/2000/svg">
                                                        <path
                                                            d="M13.5678 0.984076C15.9499 2.07045 19.6514 4.16828 23.8292 6.60326L43.8389 3.75621C44.9618 3.59067 46.1063 3.67241 47.1958 3.99596L51.5385 5.25466C51.905 5.25466 51.905 5.62927 51.5385 5.77912L34.8272 13.2714C40.4709 16.7178 45.4917 19.8646 47.6905 21.2881C48.0829 21.54 48.5248 21.7002 48.9848 21.7572C49.4447 21.8142 49.9113 21.7666 50.3512 21.6177L54.7269 20.2017C55.4217 19.9484 56.1813 19.951 56.8745 20.2092L59.6377 21.363L50.4025 29.2299C49.9382 29.628 49.3735 29.8842 48.7732 29.969C48.1729 30.0538 47.5614 29.9638 47.0089 29.7094C42.5965 27.694 30.9792 22.2246 22.3303 17.6169C3.1269 7.20265 1.00133 5.4045 1.00133 3.75621C1.00133 1.17138 8.33087 -1.48837 13.5715 0.984076H13.5678Z"
                                                            fill="#969696" />
                                                        <line y1="38" x2="60" y2="38" stroke="#969696" stroke-width="2" />
                                                    </svg>
                                                </div>
                                                <div class="trvl-dtls text-center fw-300">
                                                    <div class="label">Return</div>
                                                    <div class="date">
                                                        <strong class="fw-500">19</strong>
                                                        <div>
                                                            Nov, 2022 <br>
                                                            Saturday
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-4 justify-content-center">
                                                <div class="mb-4 d-flex justify-content-center">
                                                    <svg width="35" height="38" viewBox="0 0 35 38" fill="none"
                                                        xmlns="http://www.w3.org/2000/svg">
                                                        <path
                                                            d="M24.7 22.8C27.1481 22.8001 29.5016 23.7453 31.2698 25.4385C33.0379 27.1316 34.0843 29.4419 34.1905 31.8877L34.2 32.3V34.2C34.2003 35.1587 33.8382 36.0821 33.1864 36.785C32.5345 37.488 31.641 37.9186 30.685 37.9905L30.4 38H3.8C2.84131 38.0003 1.91792 37.6382 1.21496 36.9864C0.511994 36.3345 0.0814023 35.441 0.00950022 34.485L0 34.2V32.3C0.000141441 29.8519 0.945329 27.4984 2.63845 25.7302C4.33158 23.9621 6.64193 22.9157 9.0877 22.8095L9.5 22.8H24.7ZM17.1 0C19.6196 0 22.0359 1.00089 23.8175 2.78249C25.5991 4.56408 26.6 6.98044 26.6 9.5C26.6 12.0196 25.5991 14.4359 23.8175 16.2175C22.0359 17.9991 19.6196 19 17.1 19C14.5804 19 12.1641 17.9991 10.3825 16.2175C8.60089 14.4359 7.6 12.0196 7.6 9.5C7.6 6.98044 8.60089 4.56408 10.3825 2.78249C12.1641 1.00089 14.5804 0 17.1 0Z"
                                                            fill="#969696" />
                                                    </svg>
                                                </div>
                                                <div class="trvl-dtls text-center fw-300">
                                                    <div class="label">Traveller</div>
                                                    <div class="date">
                                                        <strong class="fw-500">01</strong>
                                                        <div>
                                                            1 Adult
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="fs-16 fw-300 text-center">
                                    This may take upto a minite
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--model popup nimmi review success and fail-->
    <div class="modal fade" id="responseModal" tabindex="-1" aria-labelledby="responseModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="responseModalLabel"></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"
                        onclick="redirectToReviewPage()">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body justify-content-center" id="responseMessage">
                    <!-- Response message will be inserted here -->
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-primary" data-dismiss="modal"
                        onclick="redirectToReviewPage()">Close</button>
                </div>
            </div>
        </div>
    </div>
    <!--end-->
    <?php
    require_once("includes/footer.php");
    ?>
    <script>
        $(".text-below-button").click(function () {
            $(this).parents('.modal').modal('hide');
        });
        $(".forgot-passward > button").click(function () {
            $(this).parents('.modal').modal('hide');
        });

        $('#FlightSearchLoading').modal({
            show: false
        })

        /******************TAB WITHOUT ID*******************************/
        $(document).ready(function () {
            $(".maintab-label").click(function () {
                $(this).addClass("active").siblings().removeClass("active");
            });

            var $btns = $('.subtab-label').click(function () {
                var $el = $('.' + this.id).show();
                $('#parent > div').show();
                $('#parent > div').not($el).hide();
                $btns.removeClass('active');
                $(this).addClass('active');
            });
            // var $btns = $('.subtab-label').click(function() {
            //     if (this.id == 'all') {
            //         $('#parent > div').show();
            //         $(".more-btn").show();
            //     } else {
            //         var $el = $('.' + this.id).show();
            //         $('#parent > div').not($el).hide();
            //         $(".more-btn").hide();
            //     }
            //     $btns.removeClass('active');
            //     $(this).addClass('active');
            // });

            $(".profile-edit-button").click(function () {
                $("#editprofile").css("display", "flex");
                $(this).parents(".profileinfo").hide();
            })

            $("input[type=file]").change(function (e) {
                $(this).parents(".uploadFile").find(".filename").text(e.target.files[0].name);
            });
        })
        /***************************************************************/
        $('[name=password-change]').each(function (i, d) {
            var p = $(this).prop('checked');
            //   console.log(p);
            if (p) {
                $('.password-change').eq(i)
                    .addClass('on');
            }
        });

        $('[name=password-change]').on('change', function () {
            var p = $(this).prop('checked');

            // $(type).index(this) == nth-of-type
            var i = $('[name=password-change]').index(this);

            $('.password-change').removeClass('on');
            $('.password-change').eq(i).addClass('on');
        });

        /**************Scroll To Top*****************/
        $(window).on('scroll', function () {
            if (window.scrollY > window.innerHeight) {
                $('#scrollToTop').addClass('active')
            } else {
                $('#scrollToTop').removeClass('active')
            }
        })

        $('#scrollToTop').on('click', function () {
            $("html, body").animate({
                scrollTop: 0
            }, 500);
        })
        /**********************************************/
        $(document).ready(function () {
            $(".select-tab-item li .btn").click(function () {
                $(this).parent().addClass("active-btn").siblings().removeClass("active-btn");
                $(this).parents(".select-tab-item").removeClass("open");
            });
            $(".downbtn").click(function () {
                // $(this).parents(".select-tab-item").children().addClass("active-btn");
                $(this).parents(".select-tab-item").toggleClass("open");
            });
            $('#filterOptionsFlight li a').click(function () {
                // fetch the class of the clicked item
                var ourClass = $(this).attr('class');

                // reset the active class on all the buttons
                $('#filterOptionsFlight li').removeClass('active');
                // update the active state on our clicked button
                $(this).parent().addClass('active');

                if (ourClass == 'all') {
                    // show all our items
                    $('#FlightCancellationList').children('div.item').show();
                } else {
                    // hide all elements that don't share ourClass
                    $('#FlightCancellationList').children('div:not(.' + ourClass + ')').hide();
                    // show all elements that do share ourClass
                    $('#FlightCancellationList').children('div.' + ourClass).show();
                }
                return false;
            });
            $('#filterOptionsPackage li a').click(function () {
                // fetch the class of the clicked item
                var ourClass = $(this).attr('class');

                // reset the active class on all the buttons
                $('#filterOptionsPackage li').removeClass('active');
                // update the active state on our clicked button
                $(this).parent().addClass('active');

                if (ourClass == 'all') {
                    // show all our items
                    $('#PackageCancellationList').children('div.item').show();
                } else {
                    // hide all elements that don't share ourClass
                    $('#PackageCancellationList').children('div:not(.' + ourClass + ')').hide();
                    // show all elements that do share ourClass
                    $('#PackageCancellationList').children('div.' + ourClass).show();
                }
                return false;
            });

            $(".filterOptionsFlight").click(function () {
                $("#filterOptionsFlight").addClass("d-flex").removeClass("d-none");
                $("#filterOptionsPackage").removeClass("d-flex").addClass("d-none");
                $("#FlightCancellationList").addClass("d-block").removeClass("d-none");
                $("#PackageCancellationList").removeClass("d-block").addClass("d-none");
            })
            $(".filterOptionsPackage").click(function () {
                $("#filterOptionsPackage").addClass("d-flex").removeClass("d-none");
                $("#filterOptionsFlight").removeClass("d-flex").addClass("d-none");
                $("#PackageCancellationList").addClass("d-block").removeClass("d-none");
                $("#FlightCancellationList").removeClass("d-block").addClass("d-none");
            })
        });
        // nimmi - review model popup 15-05-2024
        function redirectToReviewPage() {
            window.location.href = 'user-dashboard.php';
        }
        function handleClose() {
            window.location.href = 'logout.php';
        }
    </script>





    <?php
    // if(isset($_POST['userprofileupdate']))
    // {
    //      echo "<script>alert('test');</script>";

    //     $firstName=$_POST['fname'];
    //     $lastName=$_POST['lname'];
    //     $phone=$_POST['mobile'];
    //     $address=$_POST['address'];
    //     $country=$_POST['country'];
    //     $state=$_POST['state'];
    //     $city=$_POST['city'];
    //     $postal=$_POST['zipcode'];
    //     $image=$_POST['p-image'];
    //     // $image=$_FILES["p-image"]["tmp_name"];



    //         $ppic=$_FILES["p-image"]["name"];
    //         // get the image extension
    //         $extension = substr($ppic,strlen($ppic)-4,strlen($ppic));
    //         // allowed extensions
    //         $allowed_extensions = array(".jpg","jpeg",".png");
    //         // $allowed_extensions = array(".png");
    //         // Validation for allowed extensions .in_array() function searches an array for a specific value.
    //         if($ppic){
    //             if(!in_array($extension,$allowed_extensions))
    //             {
    //                 echo "<script>alert('Invalid format. Only jpg / jpeg/ png /gif format allowed');</script>";
    //             }

    //         }



    //         if($ppic){
    //             $imgnewfile=md5($imgfile).time().$extension;
    //             move_uploaded_file($_FILES["p-image"]["tmp_name"],"uploads/profile/".$imgnewfile);

    //         }else{
    //             $imgnewfile=$row['image'];
    //         }

    //         $stmt = $conn->prepare('UPDATE users SET first_name = :fname, last_name = :lname,mobile = :mobile,image = :image,contact_address = :address,country = :country,state = :state,city = :city,zip_code = :zip_code WHERE id = :id');

    //         // Bind parameters to the statement
    //         $stmt->bindParam(':fname', $firstName);
    //         $stmt->bindParam(':lname', $lastName);
    //         $stmt->bindParam(':mobile', $phone);
    //         $stmt->bindParam(':image', $$imgnewfile);
    //         $stmt->bindParam(':address', $address);
    //         $stmt->bindParam(':country', $country);
    //         $stmt->bindParam(':state', $state);
    //         $stmt->bindParam(':city', $city);
    //         $stmt->bindParam(':zip_code', $postal);
    //         $stmt->bindParam(':id', $id);

    //         // Set the parameters


    //         // Execute the statement
    //         $stmt->execute();
    //         if ($stmt->rowCount() > 0) {
    //             echo "<script>alert('You have successfully updated the data');</script>";
    //           } else {
    //             echo "<script>alert('Something Went Wrong. Please try again');</script>";
    //           }

    //         // $update="update login set first_name='$firstName',last_name='$lastName',image='$imgnewfile',phone='$phone',email='$email',address='$address',country='$country',city='$city',zipcode='$postal' where id='$id'";	

    //         // $query=mysqli_query($conn, $update);
    //         // if ($query) {
    //         // echo "<script>alert('You have successfully updated the data');</script>";
    //         // echo "<script type='text/javascript'> document.location ='home.php'; </script>";
    //         // } else{
    //         // echo "<script>alert('Something Went Wrong. Please try again');</script>";
    //         // }



    // }
}
?>