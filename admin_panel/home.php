<?php 
/**************************************************************************** 
   Project Name	::> TravelSite
   Module 	::> admin dashboard page
   Programmer	::> Soumya
   Date		::> 29-06-2023
   DESCRIPTION::::>>>>
   This is  code used for admin dashboard page
*****************************************************************************/
/*ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL); */
session_start(); 
if(!isset($_SESSION['adminid'])){
ob_end_clean();
header("Location: index.php");// Redirect to the login page
    exit;
}
    include_once "includes/header.php";   
    include_once "includes/class.reviews.php";
    include_once "includes/class.agents.php";
        $objReviews		= 	new Reviews();
        $objAgents		= 	new Agents();
        $id             =   $_SESSION['adminid'];
        $offset         =   "LIMIT 2"   ;
        $offsetAgent    =   "LIMIT 3"   ;
        $reviews	    =   $objReviews->getDashboardReviews($offset);
        $agents	        =   $objAgents->getDashboardAgents($offsetAgent);
        $TotalAgents	=   count($objAgents->getDashboardAgents());
        $TotalTravellers	=   count($objAgents->getDashboardTravellers());
        $TotalBookings	=   $objAgents->getDashboardTotalBooking();
        $TotalBookingsCount =    $TotalBookings[0]['recordCount'];

     //  print_r($TotalBookings);
       
        ?>

            <!-- Home content Start -->
            <div class="container-fluid h-100 manage-users pt-4 px-4">
                <div class="row g-3 mb-3">
                    <div class="col-md-4 d-flex align-items-stretch">
                        <div class=" manage-user-cards border-primary">
                            <img src="img/icons/cs-icon.svg">

                            <div class="count">
                                <h3><?php echo $TotalAgents; ?></h3>
                                <p>Total Agents</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="manage-user-cards border-primary">
                            <img src="img/icons/ITdm-icon.svg">

                            <div class="count">
                                <h3><?php echo $TotalTravellers; ?></h3>
                                <p>Total Travellers</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="manage-user-cards border-primary">
                            <img src="img/icons/cwb-icon.svg">

                            <div class="count">
                                <h3><?php echo $TotalBookingsCount; ?></h3>
                                <p>Total Bookings</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 d-none">
                        <div class="manage-user-cards border-primary">
                            <img src="img/icons/db1.png">

                            <div class="count">
                                <h3>3134</h3>
                                <p>Total Matches</p>
                            </div>
                        </div>
                    </div>

                </div>
              
                <div class="row g-3 dashboard-bottom">
                    <div class="col-md-4 d-flex align-items-stretch">
                        <div class="new-reg-user border-primary">
                            <h5>NEW AGENTS</h5>
                            <?php foreach($agents as $key =>$val){                                
                                if(is_null($val['image']))
                                {
                                    $val['image']    = "avatar.jpg";
                                }
                                 $agentImage  =    $val['image'];
                                    $AgentImageURL =   "img/avatars/".$agentImage;
                                     if((empty($agentImage)) || (!file_exists($AgentImageURL))) {
                                        // echo "LL";exit;
                                        $dummyAgentImageURL = "img/avatars/avatar.jpg"; // URL of the dummy image
                                        $AgentImageURL = $dummyAgentImageURL;
                                        } 
                                ?>
                            <div class="row g-lg-4 g-2 d-flex align-items-center reg-user mt-4">
                                <div class="col-md-3 reg-user-img">
                                    <img src="<?php echo $AgentImageURL; ?>">
                                </div>
                                <div class="col-md-5 reg-user-name">
                                    <h4><?php echo $val['first_name']." ".$val['last_name']; ?></h4>
                                    <p><span>Credit Balance:</span><strong>$<?php echo $val['credit_balance'];?></strong></p>
                                </div>
                                <div class="col-md-4 reg-user-button">
                                    <button class="btn btn-secondary" onclick="navigateToNextPage(<?php echo $val['id']?>)">VIEW PROFILE</button>
                                </div>
                            </div>            
                            <?php } ?>
                            <div class="row justify-content-center">
                                <button class="col-6 btn btn-typ1 mt-4" onclick="getAllAgents()">View All</button>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-8">
                        <div class="new-pre-user border-primary">
                            <div class="new-pre-user-head">
                                <h5 class="mb-4">REVIEWS</h5>                                
                            </div>
                            <div class="card-body">
                                <div class="chart d-none">
                                    <div class="chartjs-size-monitor">
                                        <div class="chartjs-size-monitor-expand">
                                            <div class=""></div>
                                        </div>
                                        <div class="chartjs-size-monitor-shrink">
                                            <div class=""></div>
                                        </div>
                                    </div>
                                    <canvas id="dashboard-line" class="chartjs-render-monitor"></canvas>
                                </div>
                                <?php foreach($reviews as $k => $val){
                                    $image  =    $val['image'];
                                    $ReviewImageURL =   "img/photos/".$image;
                                     if((empty($image)) || (!file_exists($ReviewImageURL))) {
                                        // echo "LL";exit;
                                        $dummyReviewImageURL = "img/photos/lady-img2.png"; // URL of the dummy image
                                        $ReviewImageURL = $dummyReviewImageURL;
                                        } 
                                 ?>
                                <div class="review-item row g-lg-4 g-2">
                                    <div class="col-md-3">
                                        <img src="<?php echo $ReviewImageURL; ?>" alt="">
                                    </div>
                                    <div class="review-info col-md-9">
                                        <h5><?php echo $val['title']; ?></h5>
                                        <div class="ratings">                                           
                                            <div class="empty-stars"></div>
                                            <div class="full-stars" style="width:80%"></div>
                                        </div>
                                        <div class="txt-cntnt"><?php echo $val['description']; ?></div>
                                        <strong><?php echo $val['author']; ?></strong>
                                    </div>
                                </div>
                                <?php }?>
                                <div class="row justify-content-center">
                                <button class="col-6 btn btn-typ1 mt-4" onclick="getMoreReviews()">More Reviews >></button>
                            </div>
                             </div>
                                </div>
                            </div>
                        </div>

                    </div>                    
                </div>
            </div>
            <script>
            function getMoreReviews() {
                 window.location = "reviews.php";
             }
             function getAllAgents() {
                 window.location = "agents.php";
             }
             function navigateToNextPage($id) {
              // Construct the URL with query parameters
             // alert($id);
              var param1 = $id;
              var url = 'agent-details.php?param1=' + encodeURIComponent(param1);
  
              // Change the window location to the constructed URL
              window.location.href = url;
            }
        </script>
            <!-- Home content End -->



           
            <!-- Footer Start -->
            <?php
                include "includes/footer.php";
            ?>
            <!-- Footer End -->
        </div>
        <!-- Content End -->