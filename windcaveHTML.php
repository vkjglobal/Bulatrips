<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Windcave Payment Form</title>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
    <center>
        <form method="post" action="windcaveTest.php">
            <h4>Payment Details</h4>
            <input type="text" name="CardNumber" minlength="14" maxlength="16" placeholder="Card Number" required /><br><br>
            <input type="text" name="CardHolderName" maxlength="64" placeholder="Card Holder Name" required /><br><br>
            <input type="text" name="ExpiryMonth" minlength="2" maxlength="2" placeholder="MM" required /><br><br>
            <input type="text" name="ExpiryYear" minlength="2" maxlength="2" placeholder="YY" required /><br><br>
            <input type="text" name="Cvc2" minlength="3" maxlength="4" placeholder="CVC" required /><br><br>
            <input type="submit" value="Submit" />
        </form>
    </center>
</body>
</html>
