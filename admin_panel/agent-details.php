<?php 
/**************************************************************************** 
   Project Name	::> TravelSite
   Module 	::> admin dashboard page
   Programmer	::> Soumya
   Date		::> 30-06-2023
   DESCRIPTION::::>>>>
   This is  code used for agent  details page
*****************************************************************************/
include_once "includes/header.php";
include_once "includes/class.agents.php";
$objAgents		= 	new Agents();
if(isset($_GET['param1'])){
    $id             =   trim($_GET['param1']);    
    $agents	        =   $objAgents->getAgentDetails($id);
    $name           =   $agents[0]['first_name']." ".$agents[0]['last_name'];
    $email           =   $agents[0]['email'];
    $phone           =   $agents[0]['mobile'];
    $agent_status    =   $agents[0]['agent_status'];
    $contact_address =   $agents[0]['contact_address'];
    $agency_name     =   $agents[0]['agency_name'];
    $credit_balance   =   $agents[0]['credit_balance'];
///echo $contact_address;
//print_r($agents);
   
    } 

?>
            <!-- Product List Start -->
            <div class="container-fluid h-100 pt-4 px-4">
                <div class="border-primary rounded p-4">
                    <strong class="fs-16 fw-500 light-blue-txt mb-3 d-block text-left">Agent Details</strong>
                    
                    <div class="user-details">
                        <div class="row mb-2">
                            <strong class="col-4">Name:</strong>
                            <strong class="col-8"><?php echo $name;?></strong>
                        </div>
                        <div class="row mb-2">
                            <strong class="col-4">Email:</strong>
                            <span class="col-8"><?php echo $email;?></span>
                        </div>
                        <div class="row mb-2">
                            <strong class="col-4">Phone:</strong>
                            <span class="col-8"><?php echo $phone;?></span>
                        </div>
                        <div class="row mb-2">
                            <strong class="col-4">Status:</strong>
                            <strong class="col-8"><span class="badge bg-success"><?php echo $agent_status;?></span></strong>
                        </div>
                        <div class="row mb-2">
                            <strong class="col-4">Address:</strong>
                            <Address class="col-8"><?php echo $contact_address;?>                            
                            </Address>
                        </div>
                        <div class="row mb-2">
                            <strong class="col-4">Company Details:</strong>
                            <strong class="col-8"><?php echo $agency_name;?></strong>
                        </div>
                        <div class="row mb-2">
                            <strong class="col-4">Credit Balance:</strong>
                            <strong class="col-8"><span><?php echo $credit_balance;?></span></strong>
                        </div>
                        <a href="home.php" class="btn btn-primary d-inline-flex btn-typ3 w-auto">Back</a>
                    </div>
                    
                </div>
            </div>
            <!-- Product List End -->


            <!-- Footer Start -->
            <?php
include "includes/footer.php";
?>
