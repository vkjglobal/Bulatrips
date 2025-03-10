
$(document).ready(function() {  
  
  $('#admin-login').submit(function(event) {
    event.preventDefault();
    // Validate form data
      $(".errortext").remove();
    var email = $('#email').val();
    var password = $('#password').val();
    // alert(email,password);
    emailReg = /^[^\s@]+@[^\s@]+\.(?!con$)[^\s@]+$/;
    valid = true;
    
    if($('#email').val() == ''|| !emailReg.test($('#email').val())) {
        
        $('#email').after('<sapan class="errortext" style="color:red">Enter valid Email Id.</span>')
        valid = false;
    }
    if(password == ''){
      $('#password').after('<sapan class="errortext" style="color:red">Enter your Password.</span>')
      valid = false;
    }
  
    if( !valid ){       
      return false;
      }
    
  
    
    // Set up form data for submission
    $('#login_message').text("");
    var formData = new FormData(this);
    // Submit form via AJAX
    $.ajax({
        url:'model/adminlogin',
      type: 'post',
      data: formData,
      processData: false,
        contentType: false,        
      success: function(response) {
         // alert(response);
          if ($.trim(response) == 'error2') {
             //       alert("invalid email or password");                            
              $('#email').val('');
              $('#password').val('');
              $('#password').after('<span id="errorlogin" class="errortext" style="color:red">Invalid Email or Password</span>');                                
              return false;
           
          }
          else if ($.trim(response) == 'error3') {
              //       alert("invalid password");              
              $('#email').val('');
              $('#password').val('');              
              $('#password').after('<span id="errorlogin1" class="errortext" style="color:red">Invalid Password</span>');              
              return false;

          } 
          else {
              //alert('kk');
          window.location.href='home.php';
        }
              
      },
      error: function() {
          alert('Error submitting form'); return false;
      }
    });
  });
   //====================Forgot password page===========

    $('#forgot_pw').submit(function (event) {
        event.preventDefault();
        // Validate form data
        $(".errortext").remove();
        var email1 = $('#email').val();        
        emailReg1 = /^[^\s@]+@[^\s@]+\.(?!con$)[^\s@]+$/;
        valid = true;

        if ($('#email1').val() == '' || !emailReg1.test($('#email1').val())) {

            $('#email1').after('<sapan class="errortext" style="color:red">Enter valid Email Id.</span>')
            valid = false;
        }
        if (!valid) {
            return false;
        }
        else {
            // Set up form data for submission
            //$('#login_message').text("");
            var forgotpw = new FormData(this);
            // Submit form via AJAX
            $.ajax({
                url: 'model/admin_forgot_pw',
                type: 'post',
                data: forgotpw,
                processData: false,
                contentType: false,
                success: function (response) {
                   // alert(response); exit;
                    if (($.trim(response) == 'error12') || ($.trim(response) == 'error13')) {
                        // alert("iiiii"); exit;
                        //     if email not in valid format or email not found in DB                             
                        $('#email1').val('');                        
                        $('#email1').after('<span id="errorlogin" class="errortext" style="color:red">Invalid Email</span>');
                        return false;

                    }
                    else if ($.trim(response) == 'error14') {
                       // alert("failed to sent email"); exit;             
                        $('#email1').val('');                       
                        $('#email1').after('<span id="errorlogin1" class="errortext" style="color:red">Failed to send Email</span>');
                        return false;

                    }
                    else if ($.trim(response) == 'error15') {
                        $('#email1').val('');
                        $('#email1').after('<span id="errorlogin1" class="errortext" style="color:red">Please check your email for instructions to reset your password.</span>');
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
            //window.location.href = 'forgot-password.php';
        }

     });
   //=================Reset password form===============
    $('#reset_pw').submit(function (event) {
        event.preventDefault();
        // Validate form data
        $(".errortext").remove();
        var new_pw = $('#new_pw').val();
        var confirm_password = $('#confirm_password').val();
        var token_pw = $('#token_pw').val();
         regex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]+$/;
        valid = true;
        if (new_pw == '') {
            $('#new_pw').after('<sapan class="errortext" style="color:red">Enter your New Password.</span>')
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
            // Set up form data for submission
            //$('#login_message').text("");
            var resetpw = new FormData(this);
            // Submit form via AJAX
            $.ajax({
                url: 'model/admin_reset_pw.php',
                type: 'post',
                data: resetpw,
                processData: false,
                contentType: false,
                success: function (response) {
                   // alert(response); exit;       
                    if (new_pw.length < 8) {
                        $('#confirm_password').val('');
                        $('#new_pw').val('');
                        $('#new_pw').after('<sapan class="errortext" style="color:red">Password is too short.Minimum 8 characters required</span>')
                        return false;// Password is too short
                    }
                    else if (!regex.test(new_pw)) {
                        $('#confirm_password').val('');
                        $('#new_pw').val('');
                        $('#new_pw').after('<sapan class="errortext" style="color:red">Password  requires at least one lowercase letter, one uppercase letter, one digit, and one special character </span>')
                         return false; // Password does not meet complexity requirements
                    
                    }
                    else if ($.trim(response) == 'error21') {                                    
                         $('#confirm_password').val('');
                         $('#new_pw').val('');
                         $('#confirm_password').after('<span id="errorlogin1" class="errortext" style="color:red">Please fill both the username and password fields!</span>');
                        return false;

                    }
                    else if ($.trim(response) == 'error22') {
                         $('#confirm_password').val('');
                         $('#new_pw').val('');
                         $('#confirm_password').after('<span id="errorlogin1" class="errortext" style="color:red">Token is Invalid/Expired!Try Again to get Link to Mail</span>');
                         return false

                    }
                     else if ($.trim(response) == 'error24') {
                         $('#confirm_password').val('');
                         $('#new_pw').val('');
                         $('#confirm_password').after('<span id="errorlogin1" class="errortext" style="color:red">New password and Confirm password are not Match</span>');
                         return false

                    }
                     else if ($.trim(response) == 'error23') {
                         $('#confirm_password').val('');
                         $('#new_pw').val('');
                         $('#confirm_password').after('<span id="errorlogin1" class="errortext" style="color:red">Your password has been successfully reset. You can now log in with your new password</span>');
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
            //window.location.href = 'forgot-password.php';
        }

    });
   //================================

});
    