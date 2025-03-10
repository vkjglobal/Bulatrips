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
        <form method="post" action="windcaveTest">
            <h4>Payment Details</h4>
            <input type="text" name="CardNumber" minlength="14" maxlength="16" placeholder="Card Number" required value="4111111111111111" /><br><br>
            <input type="text" name="CardHolderName" maxlength="64" placeholder="Card Holder Name" required value="JOHN T DOE"  /><br><br>
            <input type="text" name="ExpiryMonth" minlength="2" maxlength="2" placeholder="MM" required value="01"  /><br><br>
            <input type="text" name="ExpiryYear" minlength="2" maxlength="2" placeholder="YY" required value="26"  /><br><br>
            <input type="text" name="Cvc2" minlength="3" maxlength="4" placeholder="CVC" required value="111"  /><br><br>
            <input type="submit" value="Submit" />
        </form>
    </center>
</body>
</html>
