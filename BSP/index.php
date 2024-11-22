<?php
$nar_msgType = "AR";
$nar_merTxnTime = date('YmdHis');

$nar_merId = "80012010";
$nar_mcccode = "4772";
$nar_merBankCode = "8001011";
// Default Curreny should be in USD or FJD? Currently BSP provide default CURRENCY in FJD? I think we should ask them to make the USD default currency. and ask them the USD currency code


$nar_orderNo = "ORD_" . $nar_merTxnTime;
$nar_txnCurrency = "242"; // Currency code (static as per the table)
$nar_txnAmount = "30.00"; // Example transaction amount, this can be dynamic as well

$nar_remitterEmail = "nafeesdroidor@gmail.com"; // Example email, dynamic value can be added
$nar_remitterMobile = "+92324031636"; // Example mobile, dynamic value can be added
$nar_cardType = "EX"; // Example card type (static as per the table)
$nar_paymentDesc = "TestPayment"; // Static payment description
$nar_version = "1.0"; // Static version as per the table
$nar_returnUrl = "https://staging.bulatrips.com/BSPPaymentResponse.php"; // Static return URL
$nar_Secure = "IPGSECURE"; // Can be dynamic as per requirements, but static here for now

// Checksum Calculation: Combine dynamic data for checksum generation
$checksum_data = $nar_cardType."|".$nar_merBankCode."|".$nar_merId."|".$nar_merTxnTime."|".$nar_msgType."|".$nar_orderNo."|".$nar_paymentDesc."|".$nar_remitterEmail."|".$nar_remitterMobile."|".$nar_txnAmount."|".$nar_txnCurrency."|".$nar_version."|".$nar_returnUrl;

$nar_checkSum = hash('sha256', $checksum_data); // Use correct checksum logic based on your requirements
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Form</title>
</head>
<body>
    <form name="myform" action="https://uat2.yalamanchili.in/MPI_v1/mercpg" method="post">
        <input type="text" id="nar_msgType" name="nar_msgType" value="<?php echo $nar_msgType; ?>"/>
        <input type="text" id="nar_merTxnTime" name="nar_merTxnTime" value="<?php echo $nar_merTxnTime; ?>"/>
        <input type="text" id="nar_merBankCode" name="nar_merBankCode" value="<?php echo $nar_merBankCode; ?>"/>
        <input type="text" id="nar_orderNo" name="nar_orderNo" value="<?php echo $nar_orderNo; ?>"/>
        <input type="text" id="nar_merId" name="nar_merId" value="<?php echo $nar_merId; ?>"/>
        <input type="text" id="nar_txnCurrency" name="nar_txnCurrency" value="<?php echo $nar_txnCurrency; ?>"/>
        <input type="text" id="nar_txnAmount" name="nar_txnAmount" value="<?php echo $nar_txnAmount; ?>"/>
        <input type="text" id="nar_remitterEmail" name="nar_remitterEmail" value="<?php echo $nar_remitterEmail; ?>"/>
        <input type="text" id="nar_remitterMobile" name="nar_remitterMobile" value="<?php echo $nar_remitterMobile; ?>"/>
        <input type="text" id="nar_cardType" name="nar_cardType" value="<?php echo $nar_cardType; ?>"/>
        <input type="text" id="nar_checkSum" name="nar_checkSum" value="<?php echo $nar_checkSum; ?>"/>
        <input type="text" id="nar_paymentDesc" name="nar_paymentDesc" value="<?php echo $nar_paymentDesc; ?>"/>
        <input type="text" id="nar_version" name="nar_version" value="<?php echo $nar_version; ?>"/>
        <input type="text" id="nar_mcccode" name="nar_mcccode" value="<?php echo $nar_mcccode; ?>"/>
        <input type="text" id="nar_returnUrl" name="nar_returnUrl" value="<?php echo $nar_returnUrl; ?>"/>
        <input type="text" id="nar_Secure" name="nar_Secure" value="<?php echo $nar_Secure; ?>"/>
        <input type="submit" value="Pay via NARADA® Secure">
    </form>
</body>
</html>
