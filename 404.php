<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>No Results Found</title>
    <style>
        /* Basic Reset */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        /* Full Page Background */
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background: url('images/home-banner1.jpg') center center/cover no-repeat; /* Use your background image here */
            color: white;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        /* Overlay for Dark Effect */
        .overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            z-index: 1;
        }

        /* Content Centering */
        .content {
            position: relative;
            z-index: 2;
            max-width: 600px;
            padding: 20px;
        }

        /* Headline Styling */
        .content h1 {
            font-size: 48px;
            font-weight: bold;
            margin-bottom: 20px;
        }

        /* Subtext Styling */
        .content p {
            font-size: 18px;
            color: #fff;
            margin-bottom: 30px;
        }

        /* Button Styling */
        .content .btn {
            display: inline-block;
            padding: 15px 30px;
            background-color: #121E7E;
            color: white;
            font-size: 16px;
            font-weight: bold;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
        }

        .content .btn:hover {
            background-color: #121E7E;
        }
    </style>
</head>
<body>



    <!-- Dark Overlay -->
    <div class="overlay"></div>

    <!-- Centered Content -->
    <div class="content">
        <h1>SORRY!</h1>
        <p><?php
            if (isset($_GET['error'])) {
                $errorMessage = urldecode($_GET['error']); // Decode the message for display
                echo "<p>Error: $errorMessage</p>"; // Display the error message
            } else {
                echo "<p>No error message provided.</p>";
            }
            ?>
        </p>
        <a href="index" class="btn">SEARCH AGAIN</a> <!-- Link back to your search page -->
    </div>

</body>
</html>
