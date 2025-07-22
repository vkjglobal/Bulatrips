<style>
.bodycontant {
    display: flex;
    justify-content: center;
    align-items: center;
    height: 70vh;
    background: url('images/home-banner1.jpg') center center/cover no-repeat; /* Use your background image here */
    color: white;
    text-align: center;
    position: relative;
    overflow: hidden;
}
.content {
    position: relative;
    z-index: 2;
    max-width: 600px;
    padding: 20px;
    padding: 20px;
    background: #121e7e;
    border-radius: 10px;
}
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

<div class="container-jumbotron">
    <div class="bodycontant">
        <div class="content">
            <h1>Booking Not Found</h1>
            <p><?php
                $Errmessage = "We're unable to retrieve your booking details at the moment.";
                echo "<p style='font-size:18px; line-height: 1.5;'>".$Errmessage."</p>";
                
                // Show booking reference if available
                if (isset($bookingData['mf_reference'])) {
                    echo "<p style='font-size:16px; margin: 15px 0;'><strong>Booking Reference:</strong> " . htmlspecialchars($bookingData['mf_reference']) . "</p>";
                }
                
                // Show last known status from database
                if (isset($bookingData['booking_status'])) {
                    echo "<p style='font-size:14px; opacity: 0.9;'><strong>Last Known Status:</strong> " . ucfirst($bookingData['booking_status']) . "</p>";
                }
                
                if (isset($responseData['Message'])) {
                    echo "<p style='font-size:14px; opacity: 0.8;'>Technical Details: " . htmlspecialchars($responseData['Message']) . "</p>";
                }
            ?>
            <p style='font-size:16px; margin-top: 20px;'>
                This could happen if:
                <br>• Your booking status has changed
                <br>• There's a temporary connection issue
                <br>• The booking reference is no longer valid
            </p>
            </p>
            <div class="d-flex flex-column align-items-center gap-2">
                <a href="user-dashboard" class="btn btn-typ7 mb-2 btn-primary">
                    <i class="fas fa-tachometer-alt mr-2"></i>GO TO DASHBOARD
                </a>
                <a href="index" class="btn btn-outline-light">
                    <i class="fas fa-search mr-2"></i>SEARCH FLIGHTS
                </a>
                <button onclick="window.location.reload()" class="btn btn-outline-light mt-2">
                    <i class="fas fa-redo mr-2"></i>TRY AGAIN
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Add SweetAlert option for contact support
document.addEventListener('DOMContentLoaded', function() {
    // Add a help button
    const helpBtn = document.createElement('button');
    helpBtn.innerHTML = '<i class="fas fa-question-circle mr-2"></i>NEED HELP?';
    helpBtn.className = 'btn btn-warning mt-2';
    helpBtn.onclick = function() {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Need Assistance?',
                html: `
                    <p>If you continue to experience issues, please:</p>
                    <ul style="text-align: left; margin: 20px 0;">
                        <li>Check your internet connection</li>
                        <li>Try refreshing the page</li>
                        <li>Contact our support team</li>
                    </ul>
                    <p><strong>Support:</strong> support@bulatrips.com</p>
                `,
                icon: 'info',
                confirmButtonText: 'Got it',
                confirmButtonColor: '#3085d6'
            });
        } else {
            alert('Support: support@bulatrips.com');
        }
    };
    document.querySelector('.d-flex').appendChild(helpBtn);
});
</script>