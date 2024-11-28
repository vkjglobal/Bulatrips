<?php
date_default_timezone_set('Pacific/Fiji');
$nar_msgType = "AR";
$nar_merTxnTime = date('YmdHis');

// MID=>80012010
// SID=>8001011

$nar_merId = "800120108001011";
$nar_mcccode = "4722";
$nar_merBankCode = "01";
// Default Curreny should be in USD or FJD? Currently BSP provide default CURRENCY in FJD? I think we should ask them to make the USD default currency. and ask them the USD currency code


$nar_orderNo = "ORD_" . $nar_merTxnTime;
$nar_txnCurrency = "242";
$nar_txnAmount = "1.00";
$nar_remitterEmail = "nafeesdroidor@gmail.com";
$nar_remitterMobile = "92324031636";
$nar_cardType = "EX";
$nar_paymentDesc = "TestPayment";
$nar_version = "1.0";
// $nar_returnUrl = "https://staging.bulatrips.com/BSPPaymentResponse.php";
$nar_returnUrl = "http://localhost/bulatrips/BSPPaymentResponse.php";
$nar_Secure = "IPGSECURE";


$checksum_data = $nar_cardType."|".$nar_merBankCode."|".$nar_merId."|".$nar_merTxnTime."|".$nar_msgType."|".$nar_orderNo."|".$nar_paymentDesc."|".$nar_remitterEmail."|".$nar_remitterMobile."|".$nar_txnAmount."|".$nar_txnCurrency."|".$nar_version."|".$nar_returnUrl;

$data =$checksum_data;
$binary_signature = "";
$fp=fopen("keys/Merchant_pvt.pem","r");
$priv_key=fread($fp,8192);
fclose($fp);
$passphrase="!bulatrips!";
$res = openssl_get_privatekey($priv_key,$passphrase);
openssl_sign($data, $binary_signature, $res, OPENSSL_ALGO_SHA1);
// openssl_free_key($res);
$nar_checkSum = bin2hex($binary_signature);
echo $nar_checkSum;

/*
    $fpq=fopen ("keys/merchant.ipg.yalamanchili.in-key-public.pem","r");
    $pub_key=fread($fpq,8192);
    fclose($fpq);
    $pubs = openssl_get_publickey($pub_key);
    $ok = openssl_verify($data, $bined, $pubs, OPENSSL_ALGO_SHA1);
    echo "check #1: Verification ";
    if ($ok == 1) {
    echo "signature ok (as it should be)\n";
    } elseif ($ok == 0) {
    echo "bad (there's something wrong)\n";
    } else {
    echo "ugly, error checking signature\n";
    }
    die;
*/
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Form</title>
</head>
<body>


    is there any lowest and maximum amount limit?

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