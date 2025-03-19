<?php
session_start();
if (!isset($_SESSION['adminid'])) {
?>
    <script>
        window.location = "index.php"
    </script>
<?php
}
include_once "includes/header.php";
include_once "includes/class.booking.php";
$objBooking = new Booking();


if (isset($_POST['update'])) {
    $objBooking->updateMarkup(2,trim($_POST['markupname']));
    $objBooking->updateIPGSettings(1, trim($_POST['ipg_percentage']));
    $objBooking->updateIPGSettings(6, trim($_POST['ticketing_fee']));
    $objBooking->updateIPGSettings(7, trim($_POST['reissue_fee']));
    $objBooking->updateIPGSettings(8, trim($_POST['reissue_addition']));
    $objBooking->updateIPGSettings(9, trim($_POST['refund_fee']));
    $objBooking->updateIPGSettings(10, trim($_POST['refund_addition']));
}
$listmarkup	=   $objBooking->getmarkupInfo(2);
$markupname = $listmarkup[0]['commission_percentage'];

$ipg_transaction_percentage	= $objBooking->getsettingsInfo(1);
$ticketing_fee	= $objBooking->getsettingsInfo(6);
$reissue_fee	= $objBooking->getsettingsInfo(7);
$reissue_addition	= $objBooking->getsettingsInfo(8);
$refund_fee	= $objBooking->getsettingsInfo(9);
$refund_addition	= $objBooking->getsettingsInfo(10);

?>
<form class="" method="POST" action="" enctype="multipart/form-data">
    <div class="container-fluid pt-4 px-4">
        
        <div class="row border-primary rounded mx-0 p-3">
            <div class="col-12">
                <div class="row" style="display: flex;">
                    <div class="col-12">
                        <strong class="fs-16 fw-500 light-blue-txt d-block" style="font-size: 22px;">Base Markup Percentage</strong>
                        <small>Percentage markup applied to all Mystifly wholesale fares</small>
                        <div class="row mt-2">
                            <div class="col-md-6 mb-3">
                                <input type="text" class="form-control" value="<?php  echo  $markupname; ?>" name="markupname" id="markupname">
                            </div>                                      
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row border-primary rounded mx-0 p-3 mt-3">
            <div class="col-12">
                <div class="row" style="display: flex;">    
                    <div class="col-12">
                        <strong class="fs-16 fw-500 light-blue-txt d-block" style="font-size: 22px;">IPG Transaction Fee Percentage</strong>
                        <small>The Windcave IPG payment gateway charges a percentage fee on each transaction.</small>
                        <div class="row mt-2">
                            <div class="col-md-6 mb-3">
                                <input type="text" class="form-control" value="<?php echo $ipg_transaction_percentage[0]['value']; ?>" name="ipg_percentage" id="ipg_percentage">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row border-primary rounded mx-0 p-3 mt-3">
            <div class="col-12">
                <div class="row" style="display: flex;">    
                    <div class="col-12">
                        <strong class="fs-16 fw-500 light-blue-txt d-block" style="font-size: 22px;">Ticketing Fee (Fixed Amount)</strong>
                        <small>Custom fee per transaction(hidden from customers, added to final price)</small>
                        <div class="row mt-2">
                            <div class="col-md-6 mb-3">
                                <input type="text" class="form-control" value="<?php echo $ticketing_fee[0]['value'];?>" name="ticketing_fee" id="ticketing_fee">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row border-primary rounded mx-0 p-3 mt-3">
            <div class="col-12">
                <div class="row" style="display: flex;">    
                    <div class="col-12">
                        <strong class="fs-16 fw-500 light-blue-txt d-block" style="font-size: 22px;">Service Transaction Fees (Fixed Amount)</strong>
                        <div class="row mt-2">
                            <div class="col-md-6 mb-3">
                                <label>Reissue/Schedule Base Fee:</label>
                                <input type="text" class="form-control" value="<?php echo $reissue_fee[0]['value'];?>" name="reissue_fee" id="reissue_fee">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Refund Base Fee:</label>
                                <input type="text" class="form-control" value="<?php echo $refund_fee[0]['value'];?>" name="refund_fee" id="refund_fee">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Reissue/Schedule Additional markup:</label>
                                <input type="text" class="form-control" value="<?php echo $reissue_addition[0]['value'];?>" name="reissue_addition" id="reissue_addition">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Refund Additional markup:</label>
                                <input type="text" class="form-control" value="<?php echo $refund_addition[0]['value'];?>" name="refund_addition" id="refund_addition">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        
        <div class="col-12 mt-3">
            <div class="row" style="display: flex;">    
                <div class="col-12 d-flex">
                    <input type="submit" name="update" value="Update Settings" id="submit" class="btn btn-primary btn-typ3">
                </div>
            </div>
        </div>
        
    </div>
</form>


<?php
include "includes/footer.php";

?>

<script>
    $(document).ready(function() {

        $("#submit").click(function() {

            valid = true;
            var numberPattern = /^[\d.]+$/;
            $(".errortext").remove();
            
            if($('#markupname').val() == '') {
                $('#markupname').after('<span class="errortext" style="color:red">Markup % cannot be blank.</span>')	       
                valid = false;
            }
            else if(!numberPattern.test($('#markupname').val())){
                $('#markupname').after('<span class="errortext" style="color:red">cannot  include letters or Special Characters</span>')	       
                valid = false;
            }
            if ($('#ipg_percentage').val() == '') {
                $('#ipg_percentage').after('<span class="errortext" style="color:red">Amount cannot be blank.</span>')
                valid = false;
            } else if (!numberPattern.test($('#ipg_percentage').val())) {
                $('#ipg_percentage').after('<span class="errortext" style="color:red">cannot  include letters or Special Characters</span>')
                valid = false;
            }

            if ($('#ticketing_fee').val() == '') {
                $('#ticketing_fee').after('<span class="errortext" style="color:red">Amount cannot be blank.</span>')
                valid = false;
            } else if (!numberPattern.test($('#ticketing_fee').val())) {
                $('#ticketing_fee').after('<span class="errortext" style="color:red">cannot  include letters or Special Characters</span>')
                valid = false;
            }

            if ($('#reissue_fee').val() == '') {
                $('#reissue_fee').after('<span class="errortext" style="color:red">Amount cannot be blank.</span>')
                valid = false;
            } else if (!numberPattern.test($('#reissue_fee').val())) {
                $('#reissue_fee').after('<span class="errortext" style="color:red">cannot  include letters or Special Characters</span>')
                valid = false;
            }

            if ($('#refund_fee').val() == '') {
                $('#refund_fee').after('<span class="errortext" style="color:red">Amount cannot be blank.</span>')
                valid = false;
            } else if (!numberPattern.test($('#refund_fee').val())) {
                $('#refund_fee').after('<span class="errortext" style="color:red">cannot  include letters or Special Characters</span>')
                valid = false;
            }

            if ($('#reissue_addition').val() == '') {
                $('#reissue_addition').after('<span class="errortext" style="color:red">Amount cannot be blank.</span>')
                valid = false;
            } else if (!numberPattern.test($('#reissue_addition').val())) {
                $('#reissue_addition').after('<span class="errortext" style="color:red">cannot  include letters or Special Characters</span>')
                valid = false;
            }
            if (!valid) {
                return valid;
            }

            if ($('#refund_addition').val() == '') {
                $('#refund_addition').after('<span class="errortext" style="color:red">Amount cannot be blank.</span>')
                valid = false;
            } else if (!numberPattern.test($('#refund_addition').val())) {
                $('#refund_addition').after('<span class="errortext" style="color:red">cannot  include letters or Special Characters</span>')
                valid = false;
            }
            if (!valid) {
                return valid;
            }
        }); 
    });
</script>