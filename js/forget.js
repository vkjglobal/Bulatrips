$('#forgot-form').submit(function (event) {
    event.preventDefault();
    // Validate form data
    var email = $('#RegisterInputEmail1').val();
    var emailReg = /^[^\s@]+@[^\s@]+\.(?!con$)[^\s@]+$/;
    var valid = true;

    if (email == '' || !emailReg.test(email)) {
        $('#RegisterInputEmail1').after('<span class="text-danger fs-12 position-absolute" style="color:red">Enter valid Email Id.</span>');
        valid = false;
    }

    if (!valid) {
        return false;
    }
    else {
        
        var forgotpw = new FormData(this);
        // alert(forgotpw);
        // Submit form via AJAX
        $("#forgot_sub_btn").attr("disabled", true);
        $("#forgot_sub_btn").html("Submit &nbsp;<i class='fas fa-spinner fa-spin'></i>");

        $.ajax({
            type: 'POST',
            url: 'forget_password',
            data: forgotpw,
            processData: false,
            contentType: false,
            success: function (response) {
                // alert(response); return false;
                if (($.trim(response)=='error12') || ($.trim(response)=='error13')) {
                    $("#forgot_sub_btn").attr("disabled", false);
                    $("#forgot_sub_btn").html("Submit");
                    $('#RegisterInputEmail1').val('');                        
                    $('#RegisterInputEmail1').after('<span id="errorlogin" class="errortext" style="color:red">Invalid Email</span>');
                    return false;

                }
                else if ($.trim(response)=='error14') {
                    $("#forgot_sub_btn").attr("disabled", false);
                    $("#forgot_sub_btn").html("Submit");
                    $('#RegisterInputEmail1').val('');                       
                    $('#RegisterInputEmail1').after('<span id="errorlogin1" class="errortext" style="color:red">Failed to send Email. Please try again.</span>');
                    return false;

                }
                else if ($.trim(response)=='error15') {
                    $("#forgot_sub_btn").html("Submit");
                    $('#RegisterInputEmail1').val('');
                    $('#RegisterInputEmail1').after('<span id="errorlogin1" class="errortext" style="color:Green">Please check your email for instructions to reset your password.</span>');
                    // setTimeout(function () {
                    //     window.location = 'index.php';
                    // }, 3000);
                }

            },
            error: function () {
                alert('Error submitting form'); return false;
            }
        });

    }
});

//////////////////////////////////////////////////////////////////////////////////////////

$('#reset_form').submit(function (event) {
    event.preventDefault();
    // Validate form data
    $(".errortext").remove();
    var new_pwd = $('#password').val();
    var confirm_password = $('#confirm_password').val();
    var token_pw = $('#token_pw').val();
     regex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]+$/;
    valid = true;
    if (new_pwd == '') {
        $('#password').after('<sapan class="errortext" style="color:red">Enter your New Password.</span>')
        valid = false;
    }
    if (confirm_password == '') {
        $('#confirm_password').after('<sapan class="errortext" style="color:red">Enter Confirm Password.</span>')
        valid = false;
    }                
    if (!valid) {
        return false;
    }
    else {
        var resetpw = new FormData(this);
        // alert(resetpw);exit;
        $.ajax({
            // url: 'https://localhost/Travelsite/Travelsite/user_reset_pwd.php',
            url: 'user_reset_pwd',
            type: 'POST',
            data: resetpw,
            processData: false,
            contentType: false,
            success: function (response) {
            //    alert(response);       
                if (new_pwd.length < 8) {
                    $('#confirm_password').val('');
                    $('#password').val('');
                    $('#password').after('<sapan class="errortext" style="color:red">Password is too short.Minimum 8 characters required</span>')
                    return false;// Password is too short
                }
                // else if (!regex.test(new_pwd)) {
                //     $('#confirm_password').val('');
                //     $('#password').val('');
                //     $('#password').after('<sapan class="errortext" style="color:red">Password  requires at least one lowercase letter, one uppercase letter, one digit, and one special character </span>')
                //      return false; // Password does not meet complexity requirements
                // }
                else if ($.trim(response) == 'error21') {                                    
                     $('#confirm_password').val('');
                     $('#password').val('');
                     $('#confirm_password').after('<span id="errorlogin1" class="errortext" style="color:red">Please fill both the password and confirm password fields.</span>');
                    return false;

                }
                else if ($.trim(response) == 'error22') {
                     $('#confirm_password').val('');
                     $('#password').val('');
                     $('#confirm_password').after('<span id="errorlogin1" class="errortext" style="color:red">Token is Invalid/Expired!Try Again to get Link to Mail</span>');
                     return false

                }
                 else if ($.trim(response) == 'error24') {
                     $('#confirm_password').val('');
                     $('#password').val('');
                     $('#confirm_password').after('<span id="errorlogin1" class="errortext" style="color:red">New password and Confirm password are not Match</span>');
                     return false

                }
                 else if ($.trim(response) == 'error23') {
                     $('#confirm_password').val('');
                     $('#password').val('');
                     $('#confirm_password').after('<span id="errorlogin1" class="errortext" style="color:green">Your password has been successfully reset. You can now log in with your new password</span>');
                     // Redirect to another page after 3 seconds
                     setTimeout(function () {
                         window.location = 'index.php'; // Replace 'another-page.html' with your desired page URL
                     }, 3000); // 3000 milliseconds = 3 seconds

                 }

            },
            error: function () {
                alert('Error submitting form'); return false;
            }
        });
    }
});
