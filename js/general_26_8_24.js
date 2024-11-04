$(document).ready(function () {
  $('input[name="userType"]').change(function () {

    if ($(this).val() === "agent") {
      $('#agency-field').show();
      $('#agency-address').show();
      $('#agency-country').show();
      $('#agency-state').show();
      $('#agency-city').show();
    } else {
      $('#agency-field').hide();
      $('#agency-address').hide();
      $('#agency-country').hide();
      $('#agency-state').hide();
      $('#agency-city').hide();
    }
  });


  $('#user-signup').submit(function (event) {

    // event.preventDefault();
    // Validate form data
    var fname = $('#userfname').val();
    var lname = $('#userlname').val();
    var phone = $('#userphone').val();
    var dialcode = $('#country_code').val();
    var email = $('#useremail').val();
    var password = $('#password').val();
    var confirmpassword = $('#confirmpassword').val();
    var userType = document.querySelector('input[name="userType"]:checked').value;
    var signeddate = $('#signed-date').val();
    var policy = document.getElementById("logintab-user");

    valid = true;
    if (!valid) {
      event.preventDefault();
    }
    if (fname == '') {

      // $('#userfname').after('<sapan class="text-danger fs-12 position-absolute" style="color:red">Country cannot be blank.</span>')
      document.getElementById("userfname").style.borderColor = "red";
      valid = false;
    }
    if (lname == '') {
      //   $('#userlname').after('<sapan class="text-danger fs-12 position-absolute" style="color:red">Content cannot be blank</span>')
      document.getElementById("userlname").style.borderColor = "red";
      valid = false;
    }
    if (dialcode == '') {
      //   $('#userphone').after('<sapan class="text-danger fs-12 position-absolute" style="color:red">Date cannot be blank</span>')
      document.getElementById("country_code").style.borderColor = "red";


      valid = false;
    }
    if (phone == '') {
      //   $('#userphone').after('<sapan class="text-danger fs-12 position-absolute" style="color:red">Date cannot be blank</span>')
      document.getElementById("userphone").style.borderColor = "red";

      valid = false;
    }
    if (email == '') {
      // $('#useremail').after('<sapan class="text-danger fs-12 position-absolute" style="color:red">Date cannot be blank</span>')
      document.getElementById("useremail").style.borderColor = "red";

      valid = false;
    }
    if (password == '') {
      // $('#useremail').after('<sapan class="text-danger fs-12 position-absolute" style="color:red">Date cannot be blank</span>')
      document.getElementById("password").style.borderColor = "red";

      valid = false;
    }
    if (confirmpassword == '') {
      // $('#useremail').after('<sapan class="text-danger fs-12 position-absolute" style="color:red">Date cannot be blank</span>')
      document.getElementById("confirmpassword").style.borderColor = "red";

      valid = false;
    }
    if (!policy.checked) {
      $('#policyerror').after('<sapan class="text-danger fs-12 position-absolute" >Please accept the privacy policy</span>')
      //  document.getElementById("logintab-user").style.border = "red";

      valid = false;
    }
    if (userType == 'agent') {
      var address = $('#agencyaddress').val();
      var country = $('#agencycountry').val();
      var state = $('#agencystate').val();
      var agencyname = $('#agency').val();
      if (address == '') {
        // $('#agencyaddress').after('<sapan class="text-danger fs-12 position-absolute" style="color:red">Date cannot be blank</span>')
        document.getElementById("agencyaddress").style.borderColor = "red";

        valid = false;
      }
      if (country == '') {
        // $('#agencycountry').after('<sapan class="text-danger fs-12 position-absolute" style="color:red">Date cannot be blank</span>')
        document.getElementById("agencycountry").style.borderColor = "red";

        valid = false;
      }
      if (state == '') {
        // $('#agencystate').after('<sapan class="text-danger fs-12 position-absolute" style="color:red">Date cannot be blank</span>')
        document.getElementById("agencystate").style.borderColor = "red";

        valid = false;
      }
      if (agencyname == '') {
        // $('#agency').after('<sapan class="text-danger fs-12 position-absolute" style="color:red">Date cannot be blank</span>')
        document.getElementById("agency").style.borderColor = "red";

        valid = false;
      }

    }

    if (!valid) {
      return false;
    }
  });


  $('#agencycountry').change(function () {
    var countryCode = $(this).val();
    if (countryCode != '') {
      $.ajax({
        url: 'https://api.countrystatecity.in/v1/countries/' + countryCode + '/states',
        headers: {
          'X-CSCAPI-KEY': 'N3FnUFJkMnJCaFZhQmRWUDlHRGRQR2lLQ2dFU2wzVDhJaDVBNlF2SQ==',
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        method: 'GET',
        success: function (result) {
          var states = result;
          $('#agencystate').empty();
          $('#agencystate').append('<option value="">Select State</option>');
          $.each(states, function (index, state) {
            $('#agencystate').append('<option value="' + state.iso2 + '">' + state.name + '</option>');
          });
        },
        error: function () {
          // alert('Error retrieving states.');
          $('#agencystate').empty();
          $('#agencystate').append('<option value="">Select State</option>');
        }
      });
    } else {
      $('#agencystate').empty();
      $('#agencystate').append('<option value="">Select State</option>');
    }
  });

  $.ajax({
    url: 'https://api.countrystatecity.in/v1/countries',
    headers: {
      'X-CSCAPI-KEY': 'N3FnUFJkMnJCaFZhQmRWUDlHRGRQR2lLQ2dFU2wzVDhJaDVBNlF2SQ==',
      'Content-Type': 'application/json',
      'Accept': 'application/json'
    },
    method: 'GET',
    success: function (result) {
      var countries = result;
      $('#agencycountry').empty();
      $('#agencycountry').append('<option value="">Select Country</option>');
      $.each(countries, function (index, country) {
        $('#agencycountry').append('<option value="' + country.iso2 + '">' + country.name + '</option>');
      });
    },
    error: function () {
      // alert('Error retrieving countries.');
      $('#agencycountry').empty();
      $('#agencycountry').append('<option value="">Select Country</option>');
    }
  });

  //  ---------------------------Login---------------------------

  // const loginForm = document.getElementById("user-login");
  // loginForm.addEventListener('submit', (event) => {
  //   event.preventDefault();

  //   const formData = new FormData(loginForm);

  //   fetch('login-script.php', {
  //     method: 'POST',
  //     body: formData
  //   })
  //   .then(response => response.json())
  //   .then(data => {
  //     if (data.success) {
  //       // loginModal.style.display = "none";
  //       window.location.href = "user-dashboard.php";
  //     } else {
  //       alert("Invalid username or password");
  //     }
  //   })
  //   .catch(error => {
  //     console.error(error);
  //   });
  // });

  $('#user-login').submit(function (event) {
    event.preventDefault();
    // Validate form data
    var email = $('#loginemail').val();
    var password = $('#loginpassword').val();
    emailReg = /^[^\s@]+@[^\s@]+\.(?!con$)[^\s@]+$/;
    valid = true;

    if ($('#loginemail').val() == '' || !emailReg.test($('#loginemail').val())) {

        $('#loginemail').after('<span class="text-danger fs-12 position-absolute" style="color:red">Enter valid Email Id.</span>')
      valid = false;
    }
    if (password == '') {
        $('#loginpassword').after('<span id="pwErr" class="text-danger fs-12 position-absolute" style="color:red">Enter your Password.</span>')
      valid = false;
    }

    if (!valid) {
      return false;
    }



    // Set up form data for submission
    $('#login_message').text("");
    var formData = new FormData(this);
    // Submit form via AJAX
    $.ajax({
      url: 'login-script.php',
      type: 'post',
      data: formData,
      processData: false,
      contentType: false,
      success: function (response) {
        if (response == 'error') {
         // alert("invalid email or password");
            $('#pwErr').text('');

            $('#loginpassword').after('<span id="pwErr" class="text-danger fs-12 position-absolute" style="color:red">invalid email or password</span>')

          $('#email').val('');
          $('#password').val('');
        } else if (response == 'endsuccess') {
          window.location.href = 'user-dashboard.php';
        }
        else if (response == 'agentsuccess') {
          window.location.href = 'agent-dashboard.php';
        } else if (response == 'agenterror') {
         // alert("approval is on progress..");
            $('#pwErr').text('');
            $('#loginpassword').after('<span id="pwErr" class="text-danger fs-12 position-absolute" style="color:red">approval is on progress..</span>')

        }


      },
      error: function () {
        alert('Error submitting form');
      }
    });
  });

  //////////User profile update---------------

  $('#enuserupdate').click(function (event) {
    event.preventDefault();

    var firstname = $("#fname").val();
    var lastname = $("#lname").val();
    var phone = $("#mobile").val();
    var address = $("#address").val();
    var country = $("#endusercountry").val();
    var state = $("#enduserstate").val();
    var city = $("#endusercity").val();
    var zipcode = $("#zipcode").val();
    // var image = $("#p-image").val();
    var id = $("#uid").val();
    var image = $("#p-image")[0].files[0];

    var formData = new FormData();
    formData.append('firstname', firstname);
    formData.append('lastname', lastname);
    formData.append('phone', phone);
    formData.append('address', address);
    formData.append('country', country);
    formData.append('state', state);
    formData.append('city', city);
    formData.append('zipcode', zipcode);
    formData.append('id', id);
    formData.append('image', image);




    // Set up form data for submission
    $('#login_message').text("");
    // var formData = new FormData(this);
    // var dataString = 'firstname='+firstname+'&lastname='+lastname+'&phone='+phone+'&address='+address+'&country='+country+'&state='+state+'&city='+city+'&id='+id+'&zipcode='+zipcode;
    // Submit form via AJAX
    $.ajax({
      url: 'enduser-update.php',
      type: 'post',
      // data: dataString,
      data: formData,
      contentType: false,
      processData: false,
      success: function (response) {
        if (response == 'error') {
          alert("Data not updated");
          $('#email').val('');
          $('#password').val('');
        } else {
          $('#alert-container').html(`
              <div class="alert alert-success alert-dismissible fade show" role="alert">
                Form submitted successfully!
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </div>
              `);
          $('html, body').animate({ scrollTop: 0 }, 'slow');
          setTimeout(function () {
            location.reload();
          }, 3000);
        }



      },
      error: function () {
        alert('Error submitting form');
      }
    });
  });


  /////end user personal information country and state
  //code for load state if exist in db

  var countryCode = $("#hcountry").val();
  if (countryCode != '') {
    $.ajax({
      url: 'https://api.countrystatecity.in/v1/countries/' + countryCode + '/states',
      headers: {
        'X-CSCAPI-KEY': 'N3FnUFJkMnJCaFZhQmRWUDlHRGRQR2lLQ2dFU2wzVDhJaDVBNlF2SQ==',
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      method: 'GET',
      success: function (result) {
        var states = result;
        $('#enduserstate').empty();
        $('#enduserstate').append('<option value="">Select State</option>');
        $.each(states, function (index, state) {
          $('#enduserstate').append('<option value="' + state.iso2 + '">' + state.name + '</option>');
        });
        var currentState = $("#hstate").val();

        $('#enduserstate option').each(function () {
          if ($(this).val() === currentState) {
            $(this).prop('selected', true);
          }
        });
      },
      error: function () {
        // alert('Error retrieving states.');
        $('#enduserstate').empty();
        $('#enduserstate').append('<option value="">Select State</option>');
      }
    });
  }
  //endcode for load state if exist in db

  $('#endusercountry').change(function () {
    var countryCode = $(this).val();
    if (countryCode != '') {
      $.ajax({
        url: 'https://api.countrystatecity.in/v1/countries/' + countryCode + '/states',
        headers: {
          'X-CSCAPI-KEY': 'N3FnUFJkMnJCaFZhQmRWUDlHRGRQR2lLQ2dFU2wzVDhJaDVBNlF2SQ==',
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        method: 'GET',
        success: function (result) {
          var states = result;
          $('#enduserstate').empty();
          $('#enduserstate').append('<option value="">Select State</option>');
          $.each(states, function (index, state) {
            $('#enduserstate').append('<option value="' + state.iso2 + '">' + state.name + '</option>');
          });
        },
        error: function () {
          // alert('Error retrieving states.');
          $('#enduserstate').empty();
          $('#enduserstate').append('<option value="">Select State</option>');
        }
      });
    } else {
      $('#enduserstate').empty();
      $('#enduserstate').append('<option value="">Select State</option>');
    }
  });

  $.ajax({
    url: 'https://api.countrystatecity.in/v1/countries',
    headers: {
      'X-CSCAPI-KEY': 'N3FnUFJkMnJCaFZhQmRWUDlHRGRQR2lLQ2dFU2wzVDhJaDVBNlF2SQ==',
      'Content-Type': 'application/json',
      'Accept': 'application/json'
    },
    method: 'GET',
    success: function (result) {
      var countries = result;
      $('#endusercountry').empty();
      $('#endusercountry').append('<option value="">Select Country</option>');
      $.each(countries, function (index, country) {
        $('#endusercountry').append('<option value="' + country.iso2 + '">' + country.name + '</option>');
      });
      var currentCountry = $("#hcountry").val();

      // Set the selected attribute on the option whose value matches currentCountry
      $('#endusercountry option').each(function () {
        if ($(this).val() === currentCountry) {
          $(this).prop('selected', true);
        }
      });
    },
    error: function () {
      // alert('Error retrieving countries.');
      $('#endusercountry').empty();
      $('#endusercountry').append('<option value="">Select Country</option>');
    }
  });


  //agent profuile update

  // $('#agentupdate').click(function (event) {
  //   event.preventDefault();

  //   var firstname = $("#fname").val();
  //   var lastname = $("#lname").val();
  //   var phone = $("#mobile").val();
  //   var address = $("#address").val();
  //   var country = $("#agentusercountry").val();
  //   var state = $("#agentuserstate").val();
  //   var city = $("#agentusercity").val();
  //   var zipcode = $("#zipcode").val();
  //   // var image = $("#p-image").val();
  //   var id = $("#uid").val();
  //   var image = $("#p-image")[0].files[0];
  //   //agency
  //   var agencyname = $("#agencyname").val();
  //   var agencyaddress = $("#agencyaddress").val();
  //   var agencycountry = $("#agencyupdatecountry").val();
  //   var agencystate = $("#agencyupdatstate").val();
  //   var agencyzip = $("#agencyzip").val();
  //   var agencycity = $("#agencycity").val();
  //   //kyc
  //   var ownername = $("#ownername").val();
  //   var dob = $("#dob").val();
  //   var kycid = $("#kycid").val();
  //   var kycnumber = $("#kycnumber").val();
  //   var tan = $("#tan").val();
  //   var kycimage = $("#ky-image")[0].files[0];;



  //   var formData = new FormData();
  //   formData.append('firstname', firstname);
  //   formData.append('lastname', lastname);
  //   formData.append('phone', phone);
  //   formData.append('address', address);
  //   formData.append('country', country);
  //   formData.append('state', state);
  //   formData.append('city', city);
  //   formData.append('zipcode', zipcode);
  //   formData.append('id', id);
  //   formData.append('image', image);
  //   formData.append('agencyname', agencyname);
  //   formData.append('agencyaddress', agencyaddress);
  //   formData.append('agencycountry', agencycountry);
  //   formData.append('agencystate', agencystate);
  //   formData.append('agencycity', agencycity);
  //   formData.append('agencyzip', agencyzip);

  //   formData.append('ownername', ownername);
  //   formData.append('dob', dob);
  //   formData.append('kycid', kycid);
  //   formData.append('kycnumber', kycnumber);
  //   formData.append('tan', tan);
  //   formData.append('kycimage', kycimage);




  //   // Set up form data for submission
  //   $('#login_message').text("");
  //   // var formData = new FormData(this);
  //   // var dataString = 'firstname='+firstname+'&lastname='+lastname+'&phone='+phone+'&address='+address+'&country='+country+'&state='+state+'&city='+city+'&id='+id+'&zipcode='+zipcode;
  //   // alert(zipcode);
  //   // Submit form via AJAX
  //   $.ajax({
  //     url: 'agent-update.php',
  //     type: 'post',
  //     // data: dataString,
  //     data: formData,
  //     contentType: false,
  //     processData: false,
  //     success: function (response) {
  //       if (response == 'error') {
  //         alert("Data not updated");
  //         $('#email').val('');
  //         $('#password').val('');
  //       } else {
  //         // alert("Update the data");
  //         $('#alert-container').html(`
  //                   <div class="alert alert-success alert-dismissible fade show" role="alert">
  //                       Form submitted successfully!
  //                       <button type="button" class="close" data-dismiss="alert" aria-label="Close">
  //                       <span aria-hidden="true">&times;</span>
  //                       </div>
  //               `);
  //         $('html, body').animate({ scrollTop: 0 }, 'slow');
  //         setTimeout(function () {
  //           location.reload();
  //         }, 3000);

  //       }



  //     },
  //     error: function () {
  //       alert('Error submitting form');
  //     }
  //   });
  // });
  $('#agentupdate').click(function (event) {
    event.preventDefault();

    // Remove existing error messages
    $('.error-message').remove();

    // Validation flag
    var isValid = true;

    // Validation function for checking if a field is empty and display error message
    function validateField(field, errorMessage) {
        if (!field.val() || field.val().trim() === '') {
            isValid = false;
            field.after(`<div class="error-message" style="color:red;">${errorMessage}</div>`);
        }
    }

    // Get field values
    var firstname = $("#fname");
    var lastname = $("#lname");
    var phone = $("#mobile");
    var address = $("#address");
    var country = $("#agentusercountry");
    var state = $("#agentuserstate");
    var city = $("#agentusercity");
    var zipcode = $("#zipcode");
    var id = $("#uid");
    var image = $("#p-image")[0].files[0];
    var agencyname = $("#agencyname");
    var agencyaddress = $("#agencyaddress");
    var agencycountry = $("#agencyupdatecountry");
    var agencystate = $("#agencyupdatstate");
    var agencyzip = $("#agencyzip");
    var agencycity = $("#agencycity");
    var ownername = $("#ownername");
    var dob = $("#dob");
    var kycid = $("#kycid");
    var kycnumber = $("#kycnumber");
    var tan = $("#tan");
    var kycimage = $("#ky-image")[0].files[0];
    
    // Validate fields
    validateField(firstname, "Please enter First Name");
    validateField(lastname, "Please enter Last Name");
    validateField(phone, "Please enter Phone Number");
    validateField(address, "Please enter Address");
    validateField(country, "Please select Country");
    validateField(state, "Please select State");
    validateField(city, "Please enter City");
    validateField(zipcode, "Please enter Zip Code");
    validateField(agencyname, "Please enter Agency Name");
    validateField(agencyaddress, "Please enter Agency Address");
    validateField(agencycountry, "Please select Agency Country");
    validateField(agencystate, "Please select Agency State");
    validateField(agencyzip, "Please enter Agency Zip Code");
    validateField(ownername, "Please enter Owner Name");
    validateField(dob, "Please enter Date of Birth");
    validateField(kycid, "Please enter KYC ID");
    validateField(kycnumber, "Please enter KYC Number");
    validateField(tan, "Please enter TAN");

    // Check if all fields are valid
    if (!isValid) {
        return; // Exit function if any field is invalid
    }

    var formData = new FormData();
    formData.append('firstname', firstname.val());
    formData.append('lastname', lastname.val());
    formData.append('phone', phone.val());
    formData.append('address', address.val());
    formData.append('country', country.val());
    formData.append('state', state.val());
    formData.append('city', city.val());
    formData.append('zipcode', zipcode.val());
    formData.append('id', id.val());
    formData.append('image', image);
    formData.append('agencyname', agencyname.val());
    formData.append('agencyaddress', agencyaddress.val());
    formData.append('agencycountry', agencycountry.val());
    formData.append('agencystate', agencystate.val());
    formData.append('agencycity', agencycity.val());
    formData.append('agencyzip', agencyzip.val());
    formData.append('ownername', ownername.val());
    formData.append('dob', dob.val());
    formData.append('kycid', kycid.val());
    formData.append('kycnumber', kycnumber.val());
    formData.append('tan', tan.val());
    formData.append('kycimage', kycimage);

    // Set up form data for submission
    $('#login_message').text("");

    // Submit form via AJAX
    $.ajax({
        url: 'agent-update.php',
        type: 'post',
        data: formData,
        contentType: false,
        processData: false,
        success: function (response) {
          // alert(response);return false;
            if (response == 'error') {
                alert("Data not updated");
                $('#email').val('');
                $('#password').val('');
            } else {
                $('#alert-container').html(`
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        Form submitted successfully!
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                        </div>
                `);
                $('html, body').animate({ scrollTop: 0 }, 'slow');
                setTimeout(function () {
                    location.reload();
                }, 3000);
            }
        },
        error: function () {
            alert('Error submitting form');
        }
    });
});



  //change password agent code

  $('#change-password').click(function (event) {
    event.preventDefault();

    var currentPassword = $("#current-password").val();
    var newPassword = $("#new-password").val();
    var newVerifyPassword = $("#new-varify-password").val();

    // Clear previous error messages
    $(".error-message").text('');
    var hasError = false;

    // Validate input fields
    if (currentPassword === '') {
      $("#current-password-error").text("Current passwords cannot be blank.");
      hasError = true;
  }
    if (newPassword === '') {
        $("#new-password-error").text("New passwords cannot be blank.");
        hasError = true;
    }else{
      var regex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{6,}$/;
      if(!regex.test(newPassword))
       {
         $("#new-password-error").text("Password should contains at least one lowercase letter and one uppercase letter, one digit, and one special character");
           hasError = true;
       }
    }
    if (newVerifyPassword === '') {
        $("#verify-password-error").text("Please enter new password for verification.");
        hasError = true;
    }
    if (newPassword !== newVerifyPassword) {
        $("#verify-password-error").text("New passwords do not match.");
        hasError = true;
    }

    // Proceed only if there are no validation errors
    if (hasError) {
        return;
    }
  //   if (newPassword !== newVerifyPassword) {
  //     alert("New passwords do not match.");
  //     return;
  // }

    var formData = new FormData();
    formData.append('currentPassword', currentPassword);
    formData.append('newPassword', newPassword);
   
    // Set up form data for submission
    $('#login_message').text("");
    // var formData = new FormData(this);
    // var dataString = 'firstname='+firstname+'&lastname='+lastname+'&phone='+phone+'&address='+address+'&country='+country+'&state='+state+'&city='+city+'&id='+id+'&zipcode='+zipcode;
    // alert(zipcode);
    // Submit form via AJAX
    $.ajax({
      url: 'change-password-script.php',
      type: 'post',
      // data: dataString,
      data: formData,
      contentType: false,
      processData: false,
      success: function (response) {
        if (response == 'error') {
          alert("Current password is incorrect");
          // $('#email').val('');
          // $('#password').val('');
        } 
        if(response == 'success') {
          //  window.location.href = 'logout.php';
          // Show the modal
          $('.bd-example-modal-sm').modal('show');
          
        } else {
          alert("Unexpected response: " + response);
      }
      },
      error: function () {
        alert('Error submitting form');
      }
    });
  });


     $('#change-user-password').click(function(event) {
        event.preventDefault();

        var currentPassword = $("#user-current-password").val();
        var newPassword = $("#user-new-password").val();
        var newVerifyPassword = $("#user-new-varify-password").val();

        // Clear previous error messages
        $(".error-message").text('');
        var hasError = false;

        // Validate input fields
        if (currentPassword === '') {
          $("#current-password-error").text("Current passwords cannot be blank.");
          hasError = true;
        }
        if (newPassword === '') {
            $("#new-password-error").text("New passwords cannot be blank.");
            hasError = true;
        }else{
          var regex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{6,}$/;
          if(!regex.test(newPassword))
          {
            $("#new-password-error").text("Password should contains at least one lowercase letter and one uppercase letter, one digit, and one special character");
              hasError = true;
          }
        }
        if (newVerifyPassword === '') {
            $("#verify-password-error").text("Please enter new password for verification.");
            hasError = true;
        }
        if (newPassword !== newVerifyPassword) {
            $("#verify-password-error").text("New passwords do not match.");
            hasError = true;
        }
        
        // Proceed only if there are no validation errors
        if (hasError) {
            return;
        }

        // Set up form data for submission
        var formData = new FormData();
        formData.append('currentPassword', currentPassword);
        formData.append('newPassword', newPassword);

        // Clear any previous login messages
        $('#login_message').text("");

        // Submit form via AJAX
        $.ajax({
            url: 'change-password-script.php',
            type: 'post',
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
                if (response === 'error') {
                    alert("Current password is incorrect");
                } else if (response === 'success') {
                    // window.location.href = 'logout.php';
                    // Show the modal
                    $('.bd-example-modal-sm').modal('show');
                } else {
                    alert("Unexpected response: " + response);
                }
            },
            error: function() {
                alert('Error submitting form');
            }
        });
    });


  ///// Agent user personal information country and state
  //code for load state if exist in db

  var countryCode = $("#hagentcountry").val();
  if (countryCode != '') {
    $.ajax({
      url: 'https://api.countrystatecity.in/v1/countries/' + countryCode + '/states',
      headers: {
        'X-CSCAPI-KEY': 'N3FnUFJkMnJCaFZhQmRWUDlHRGRQR2lLQ2dFU2wzVDhJaDVBNlF2SQ==',
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      method: 'GET',
      success: function (result) {
        var states = result;
        $('#agentuserstate').empty();
        $('#agentuserstate').append('<option value="">Select State</option>');
        $.each(states, function (index, state) {
          $('#agentuserstate').append('<option value="' + state.iso2 + '">' + state.name + '</option>');
        });
        var currentState = $("#hagentstate").val();

        $('#agentuserstate option').each(function () {
          if ($(this).val() === currentState) {
            $(this).prop('selected', true);
          }
        });
      },
      error: function () {
        // alert('Error retrieving states.');
        $('#agentuserstate').empty();
        $('#agentuserstate').append('<option value="">Select State</option>');
      }
    });
  }
  //endcode for load state if exist in db

  $('#agentusercountry').change(function () {
    var countryCode = $(this).val();
    if (countryCode != '') {
      $.ajax({
        url: 'https://api.countrystatecity.in/v1/countries/' + countryCode + '/states',
        headers: {
          'X-CSCAPI-KEY': 'N3FnUFJkMnJCaFZhQmRWUDlHRGRQR2lLQ2dFU2wzVDhJaDVBNlF2SQ==',
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        method: 'GET',
        success: function (result) {
          var states = result;
          $('#agentuserstate').empty();
          $('#agentuserstate').append('<option value="">Select State</option>');
          $.each(states, function (index, state) {
            $('#agentuserstate').append('<option value="' + state.iso2 + '">' + state.name + '</option>');
          });
        },
        error: function () {
          // alert('Error retrieving states.');
          $('#agentuserstate').empty();
          $('#agentuserstate').append('<option value="">Select State</option>');
        }
      });
    } else {
      $('#agentuserstate').empty();
      $('#agentuserstate').append('<option value="">Select State</option>');
    }
  });

  $.ajax({
    url: 'https://api.countrystatecity.in/v1/countries',
    headers: {
      'X-CSCAPI-KEY': 'N3FnUFJkMnJCaFZhQmRWUDlHRGRQR2lLQ2dFU2wzVDhJaDVBNlF2SQ==',
      'Content-Type': 'application/json',
      'Accept': 'application/json'
    },
    method: 'GET',
    success: function (result) {
      var countries = result;
      $('#agentusercountry').empty();
      $('#agentusercountry').append('<option value="">Select Country</option>');
      $.each(countries, function (index, country) {
        $('#agentusercountry').append('<option value="' + country.iso2 + '">' + country.name + '</option>');
      });
      var currentCountry = $("#hagentcountry").val();

      // Set the selected attribute on the option whose value matches currentCountry
      $('#agentusercountry option').each(function () {
        if ($(this).val() === currentCountry) {
          $(this).prop('selected', true);
        }
      });
    },
    error: function () {
      // alert('Error retrieving countries.');
      $('#agentusercountry').empty();
      $('#agentusercountry').append('<option value="">Select Country</option>');
    }
  });


  ////agency update country state in agent dashboard

  var countryCode = $("#hagencycountry").val();
  if (countryCode != '') {
    $.ajax({
      url: 'https://api.countrystatecity.in/v1/countries/' + countryCode + '/states',
      headers: {
        'X-CSCAPI-KEY': 'N3FnUFJkMnJCaFZhQmRWUDlHRGRQR2lLQ2dFU2wzVDhJaDVBNlF2SQ==',
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      method: 'GET',
      success: function (result) {
        var states = result;
        $('#agencyupdatstate').empty();
        $('#agencyupdatstate').append('<option value="">Select State</option>');
        $.each(states, function (index, state) {
          $('#agencyupdatstate').append('<option value="' + state.iso2 + '">' + state.name + '</option>');
        });
        var currentState = $("#hagencystate").val();

        $('#agencyupdatstate option').each(function () {
          if ($(this).val() === currentState) {
            $(this).prop('selected', true);
          }
        });
      },
      error: function () {
        // alert('Error retrieving states.');
        $('#agencyupdatstate').empty();
        $('#agencyupdatstate').append('<option value="">Select State</option>');
      }
    });
  }
  //endcode for load state if exist in db

  $('#agencyupdatecountry').change(function () {
    var countryCode = $(this).val();
    if (countryCode != '') {
      $.ajax({
        url: 'https://api.countrystatecity.in/v1/countries/' + countryCode + '/states',
        headers: {
          'X-CSCAPI-KEY': 'N3FnUFJkMnJCaFZhQmRWUDlHRGRQR2lLQ2dFU2wzVDhJaDVBNlF2SQ==',
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        method: 'GET',
        success: function (result) {
          var states = result;
          $('#agencyupdatstate').empty();
          $('#agencyupdatstate').append('<option value="">Select State</option>');
          $.each(states, function (index, state) {
            $('#agencyupdatstate').append('<option value="' + state.iso2 + '">' + state.name + '</option>');
          });
        },
        error: function () {
          // alert('Error retrieving states.');
          $('#agencyupdatstate').empty();
          $('#agencyupdatstate').append('<option value="">Select State</option>');
        }
      });
    } else {
      $('#agencyupdatstate').empty();
      $('#agencyupdatstate').append('<option value="">Select State</option>');
    }
  });

  $.ajax({
    url: 'https://api.countrystatecity.in/v1/countries',
    headers: {
      'X-CSCAPI-KEY': 'N3FnUFJkMnJCaFZhQmRWUDlHRGRQR2lLQ2dFU2wzVDhJaDVBNlF2SQ==',
      'Content-Type': 'application/json',
      'Accept': 'application/json'
    },
    method: 'GET',
    success: function (result) {
      var countries = result;
      $('#agencyupdatecountry').empty();
      $('#agencyupdatecountry').append('<option value="">Select Country</option>');
      $.each(countries, function (index, country) {
        $('#agencyupdatecountry').append('<option value="' + country.iso2 + '">' + country.name + '</option>');
      });
      var currentCountry = $("#hagencycountry").val();

      // Set the selected attribute on the option whose value matches currentCountry
      $('#agencyupdatecountry option').each(function () {
        if ($(this).val() === currentCountry) {
          $(this).prop('selected', true);
        }
      });
    },
    error: function () {
      // alert('Error retrieving countries.');
      $('#agencyupdatecountry').empty();
      $('#agencyupdatecountry').append('<option value="">Select Country</option>');
    }
  });

  


 



 


  $(document).ready(function () {
    // Fetch airport data from the database using AJAX
    $.ajax({
      url: 'fetch_airports.php',
      method: 'GET',
      dataType: 'json',
      success: function (data) {

        var airportsByCountry = {}; // Object to store airports grouped by country

        // Group airports by country
        for (var i = 0; i < data.length; i++) {
          var airport = data[i];
          var country = airport.country_name.trim();

          if (!airportsByCountry[country]) {
            airportsByCountry[country] = [];
          }

          var name = airport.airport_name.trim();
          var code = airport.airport_code.trim();
          var country = airport.country_name.trim();
          var city = airport.city_name.trim();

          // Create an object with the airport code as the label and value
          var airportObject = {
            label: code + ' - ' + name + ' - ' + city + ' - ' + country,
            // value: code + ' - ' + name
            value: code
          };

          airportsByCountry[country].push(airportObject);
        }

        // Get the input element
        var inputElement = $('#airport-input');

        // Initialize the input element as an autocomplete
        inputElement.autocomplete({
          source: function (request, response) {
            var term = request.term.toLowerCase();
            var results = [];

            // Search within airports grouped by country
            Object.keys(airportsByCountry).forEach(function (country) {
              var countryAirports = airportsByCountry[country];

              // Filter airports based on the search term
              var filteredAirports = countryAirports.filter(function (airport) {
                return airport.label.toLowerCase().indexOf(term) !== -1;
              });

              results.push.apply(results, filteredAirports);
            });

            response(results);
          },
          minLength: 3
        });
      },
      error: function (xhr, status, error) {
        console.log(error);
      }
    });
  });



  //------------------Destination auto select----------------------


  $(document).ready(function () {
    // Fetch airport data from the database using AJAX
    $.ajax({
      url: 'fetch_airports.php',
      method: 'GET',
      dataType: 'json',
      success: function (data) {
        var airportsByCountry = {}; // Object to store airports grouped by country

        // Group airports by country
        for (var i = 0; i < data.length; i++) {
          var airport = data[i];
          var country = airport.country_name.trim();

          if (!airportsByCountry[country]) {
            airportsByCountry[country] = [];
          }

          var name = airport.airport_name.trim();
          var code = airport.airport_code.trim();
          var country = airport.country_name.trim();
          var city = airport.city_name.trim();

          // Create an object with the airport code as the label and value
          var airportObject = {
            label: code + ' - ' + name + ' - ' + city + ' - ' + country,
            // value: code + ' - ' + name
            value: code
          };

          airportsByCountry[country].push(airportObject);
        }

        // Get the input element
        var inputElement = $('#arrivalairport-input');

        // Initialize the input element as an autocomplete
        inputElement.autocomplete({
          source: function (request, response) {
            var term = request.term.toLowerCase();
            var results = [];

            // Search within airports grouped by country
            Object.keys(airportsByCountry).forEach(function (country) {
              var countryAirports = airportsByCountry[country];

              // Filter airports based on the search term
              var filteredAirports = countryAirports.filter(function (airport) {
                return airport.label.toLowerCase().indexOf(term) !== -1;
              });

              results.push.apply(results, filteredAirports);
            });

            response(results);
          },
          minLength: 3
        });
      },
      error: function (xhr, status, error) {
        console.log(error);
      }
    });
  });



  // -------------departure autocomplete in search page-----------

  $(document).ready(function () {
    // Fetch airport data from the database using AJAX
    $.ajax({
      url: 'fetch_airports.php',
      method: 'GET',
      dataType: 'json',
      success: function (data) {
        var airportsByCountry = {}; // Object to store airports grouped by country

        // Group airports by country
        for (var i = 0; i < data.length; i++) {
          var airport = data[i];
          var country = airport.country_name.trim();

          if (!airportsByCountry[country]) {
            airportsByCountry[country] = [];
          }

          var name = airport.airport_name.trim();
          var code = airport.airport_code.trim();
          var country = airport.country_name.trim();
          var city = airport.city_name.trim();

          // Create an object with the airport code as the label and value
          var airportObject = {
            label: code + ' - ' + name + ' - ' + city + ' - ' + country,
            value: code
          };

          airportsByCountry[country].push(airportObject);
        }

        // Get the input element
        var inputElement = $('#airport-input-search');

        // Initialize the input element as an autocomplete
        inputElement.autocomplete({
          source: function (request, response) {
            var term = request.term.toLowerCase();
            var results = [];

            // Search within airports grouped by country
            Object.keys(airportsByCountry).forEach(function (country) {
              var countryAirports = airportsByCountry[country];

              // Filter airports based on the search term
              var filteredAirports = countryAirports.filter(function (airport) {
                return airport.label.toLowerCase().indexOf(term) !== -1;
              });

              results.push.apply(results, filteredAirports);
            });

            response(results);
          },
          minLength: 3,
          autoFocus: true,
          select: function (event, ui) {
            // Set the selected airport code as the value
            inputElement.val(ui.item.value);
            return false; // Prevent the default behavior
          }
        });

        // Retrieve the departure airport code from the $_POST['airport']
        var departureAirportCode = $('#hiddenorigin').val();
        // Set the initial value of the input field
        inputElement.val(departureAirportCode);
      },
      error: function (xhr, status, error) {
        console.log(error);
      }
    });
  });



  //----------

  // -------------Arrival autocomplete in search page-----------

  $(document).ready(function () {
    // Fetch airport data from the database using AJAX
    $.ajax({
      url: 'fetch_airports.php',
      method: 'GET',
      dataType: 'json',
      success: function (data) {
        var airportsByCountry = {}; // Object to store airports grouped by country

        // Group airports by country
        for (var i = 0; i < data.length; i++) {
          var airport = data[i];
          var country = airport.country_name.trim();

          if (!airportsByCountry[country]) {
            airportsByCountry[country] = [];
          }

          var name = airport.airport_name.trim();
          var code = airport.airport_code.trim();
          var country = airport.country_name.trim();
          var city = airport.city_name.trim();

          // Create an object with the airport code as the label and value
          var airportObject = {
            label: code + ' - ' + name + ' - ' + city + ' - ' + country,
            value: code
          };

          airportsByCountry[country].push(airportObject);
        }

        // Get the input element
        var inputElement = $('#arrivalairport-input-search');

        // Initialize the input element as an autocomplete
        inputElement.autocomplete({
          source: function (request, response) {
            var term = request.term.toLowerCase();
            var results = [];

            // Search within airports grouped by country
            Object.keys(airportsByCountry).forEach(function (country) {
              var countryAirports = airportsByCountry[country];

              // Filter airports based on the search term
              var filteredAirports = countryAirports.filter(function (airport) {
                return airport.label.toLowerCase().indexOf(term) !== -1;
              });

              results.push.apply(results, filteredAirports);
            });

            response(results);
          },
          minLength: 3,
          autoFocus: true,
          select: function (event, ui) {
            // Set the selected airport code as the value
            inputElement.val(ui.item.value);
            return false; // Prevent the default behavior
          }
        });

        // Retrieve the departure airport code from the $_POST['airport']
        var departureAirportCode = $('#hiddendestination').val();
        // Set the initial value of the input field
        inputElement.val(departureAirportCode);
      },
      error: function (xhr, status, error) {
        console.log(error);
      }
    });
  });


  //-------------------------------

  //Reissue deoarture air[port location autoselect
  //-----------------------------start-----------------------------
  $.ajax({
    url: 'fetch_airports.php',
    method: 'GET',
    dataType: 'json',
    success: function (data) {
      var airportsByCountry = {}; // Object to store airports grouped by country

      // Group airports by country
      for (var i = 0; i < data.length; i++) {
        var airport = data[i];
        var country = airport.country_name.trim();

        if (!airportsByCountry[country]) {
          airportsByCountry[country] = [];
        }

        var name = airport.airport_name.trim();
        var code = airport.airport_code.trim();
        var country = airport.country_name.trim();
        var city = airport.city_name.trim();

        // Create an object with the airport code as the label and value
        var airportObject = {
          label: code + ' - ' + name + ' - ' + city + ' - ' + country,
          value: code
        };

        airportsByCountry[country].push(airportObject);
      }

      // Get the input element
      var inputElement = $('#airport-input-reissue');

      // Initialize the input element as an autocomplete
      inputElement.autocomplete({
        source: function (request, response) {
          var term = request.term.toLowerCase();
          var results = [];

          // Search within airports grouped by country
          Object.keys(airportsByCountry).forEach(function (country) {
            var countryAirports = airportsByCountry[country];

            // Filter airports based on the search term
            var filteredAirports = countryAirports.filter(function (airport) {
              return airport.label.toLowerCase().indexOf(term) !== -1;
            });

            results.push.apply(results, filteredAirports);
          });

          response(results);
        },
        minLength: 3,
        autoFocus: true,
        select: function (event, ui) {
          // Set the selected airport code as the value
          inputElement.val(ui.item.value);
          return false; // Prevent the default behavior
        }
      });

      // Retrieve the departure airport code from the $_POST['airport']
      var departureAirportCode = $('#airport-input-reissue-value').val();
      // Set the initial value of the input field
      inputElement.val(departureAirportCode);
    },
    error: function (xhr, status, error) {
      console.log(error);
    }
  });




  //------------------------------------------end-----------------
//Reissue arrival airport location autoselect
  //-----------------------------start-----------------------------
  $.ajax({
    url: 'fetch_airports.php',
    method: 'GET',
    dataType: 'json',
    success: function (data) {
      var airportsByCountry = {}; // Object to store airports grouped by country

      // Group airports by country
      for (var i = 0; i < data.length; i++) {
        var airport = data[i];
        var country = airport.country_name.trim();

        if (!airportsByCountry[country]) {
          airportsByCountry[country] = [];
        }

        var name = airport.airport_name.trim();
        var code = airport.airport_code.trim();
        var country = airport.country_name.trim();
        var city = airport.city_name.trim();

        // Create an object with the airport code as the label and value
        var airportObject = {
          label: code + ' - ' + name + ' - ' + city + ' - ' + country,
          value: code
        };

        airportsByCountry[country].push(airportObject);
      }

      // Get the input element
      var inputElement = $('#arrivalairport-input-reissue');

      // Initialize the input element as an autocomplete
      inputElement.autocomplete({
        source: function (request, response) {
          var term = request.term.toLowerCase();
          var results = [];

          // Search within airports grouped by country
          Object.keys(airportsByCountry).forEach(function (country) {
            var countryAirports = airportsByCountry[country];

            // Filter airports based on the search term
            var filteredAirports = countryAirports.filter(function (airport) {
              return airport.label.toLowerCase().indexOf(term) !== -1;
            });

            results.push.apply(results, filteredAirports);
          });

          response(results);
        },
        minLength: 3,
        autoFocus: true,
        select: function (event, ui) {
          // Set the selected airport code as the value
          inputElement.val(ui.item.value);
          return false; // Prevent the default behavior
        }
      });

      // Retrieve the departure airport code from the $_POST['airport']
      var departureAirportCode = $('#arrivalairport-input-reissue-value').val();
      // Set the initial value of the input field
      inputElement.val(departureAirportCode);
    },
    error: function (xhr, status, error) {
      console.log(error);
    }
  });




  //------------------------------------------end-----------------
  //***************************************************************** */
  //Reissue Return arrival airport location autoselect
  //-----------------------------start-----------------------------
  $.ajax({
    url: 'fetch_airports.php',
    method: 'GET',
    dataType: 'json',
    success: function (data) {
      var airportsByCountry = {}; // Object to store airports grouped by country

      // Group airports by country
      for (var i = 0; i < data.length; i++) {
        var airport = data[i];
        var country = airport.country_name.trim();

        if (!airportsByCountry[country]) {
          airportsByCountry[country] = [];
        }

        var name = airport.airport_name.trim();
        var code = airport.airport_code.trim();
        var country = airport.country_name.trim();
        var city = airport.city_name.trim();

        // Create an object with the airport code as the label and value
        var airportObject = {
          label: code + ' - ' + name + ' - ' + city + ' - ' + country,
          value: code
        };

        airportsByCountry[country].push(airportObject);
      }

      // Get the input element
      var inputElement = $('#arrivalairport-input-reissue_return');

      // Initialize the input element as an autocomplete
      inputElement.autocomplete({
        source: function (request, response) {
          var term = request.term.toLowerCase();
          var results = [];

          // Search within airports grouped by country
          Object.keys(airportsByCountry).forEach(function (country) {
            var countryAirports = airportsByCountry[country];

            // Filter airports based on the search term
            var filteredAirports = countryAirports.filter(function (airport) {
              return airport.label.toLowerCase().indexOf(term) !== -1;
            });

            results.push.apply(results, filteredAirports);
          });

          response(results);
        },
        minLength: 3,
        autoFocus: true,
        select: function (event, ui) {
          // Set the selected airport code as the value
          inputElement.val(ui.item.value);
          return false; // Prevent the default behavior
        }
      });

      // Retrieve the departure airport code from the $_POST['airport']
      var departureAirportCode = $('#arrivalairport-input-reissue-value_return').val();
      // Set the initial value of the input field
        inputElement.val(departureAirportCode);
      //*******************dep return airport auto select****************** */
        // Get the input element
        var inputElement_return = $('#airport-input-reissue_return');

        // Initialize the input element as an autocomplete
        inputElement_return.autocomplete({
            source: function (request, response) {
                var term = request.term.toLowerCase();
                var results = [];

                // Search within airports grouped by country
                Object.keys(airportsByCountry).forEach(function (country) {
                    var countryAirports = airportsByCountry[country];

                    // Filter airports based on the search term
                    var filteredAirports = countryAirports.filter(function (airport) {
                        return airport.label.toLowerCase().indexOf(term) !== -1;
                    });

                    results.push.apply(results, filteredAirports);
                });

                response(results);
            },
            minLength: 3,
            autoFocus: true,
            select: function (event, ui) {
                // Set the selected airport code as the value
                inputElement_return.val(ui.item.value);
                return false; // Prevent the default behavior
            }
        });

        // Retrieve the departure airport code from the $_POST['airport']
        var departureAirportCode = $('#airport-input-reissue-value_return').val();
        // Set the initial value of the input field
        inputElement_return.val(departureAirportCode);
      //************************************ */
    },
    error: function (xhr, status, error) {
      console.log(error);
    }
  });




  //------------------------------------------end-----------------
  //******************************************************************** */
  //Reissue airline autoselect
  //-----------------------------start-----------------------------
  // $.ajax({
  //   url: 'fetch_airline.php',
  //   method: 'GET',
  //   dataType: 'json',
  //   success: function (data) {
  //     var airportsByCountry = {}; // Object to store airports grouped by country

  //     // Group airports by country
  //     for (var i = 0; i < data.length; i++) {
  //       var airline = data[i];
  //       // var country = airport.country_name.trim();

  //       // if (!airportsByCountry[country]) {
  //       //   airportsByCountry[country] = [];
  //       // }

  //       var name = airline.name.trim();
  //       var code = airline.code.trim();
       
  //       // Create an object with the airport code as the label and value
  //       var airlineObject = {
  //         label: code + ' - ' + name,
  //         value: code
  //       };
  //       airline.push(airlineObject);

  //       // airportsByCountry[country].push(airportObject);
  //     }

  //     // Get the input element
  //     var inputElement = $('#airline-reissue');

  //     // Initialize the input element as an autocomplete
  //     inputElement.autocomplete({
  //       source: function (request, response) {
  //         var term = request.term.toLowerCase();
  //         var results = [];

  //         // Search within airports grouped by country
  //         // Object.keys(airportsByCountry).forEach(function (country) {
  //         //   var countryAirports = airportsByCountry[country];

  //           // Filter airports based on the search term
  //           // var filteredAirports = countryAirports.filter(function (airport) {
  //             return airport.label.toLowerCase().indexOf(term) !== -1;
  //           // });

  //           results.push.apply(results, filteredAirports);
  //         });

  //         response(results);
  //       },
  //       minLength: 3,
  //       autoFocus: true,
  //       select: function (event, ui) {
  //         // Set the selected airport code as the value
  //         inputElement.val(ui.item.value);
  //         return false; // Prevent the default behavior
  //       }
  //     });

  //     // Retrieve the departure airport code from the $_POST['airport']
  //     var departureAirportCode = $('#arrivalairport-input-reissue-value').val();
  //     // Set the initial value of the input field
  //     inputElement.val(departureAirportCode);
  //   },
  //   error: function (xhr, status, error) {
  //     console.log(error);
  //   }
  // });


  $.ajax({
        url: 'fetch_airline.php',
        method: 'GET',
        dataType: 'json',
        success: function(data) {
          var airlines = [];
  
          // Parse each row and extract the airport name, code, and city
          for (var i = 0; i < data.length; i++) {
            var airline = data[i];
            var name = airline.name.trim();
            var code = airline.code.trim();
  
            // Create an object with the airport code as the label and value
            var airlineObject = {
              label: code + ' - ' + name,
              value: code + ' - ' + name
            };
  
            // Add the airport object to the airports array
            airlines.push(airlineObject);
          }
  
          // Get the input element
          var inputElement = $('#airline-reissue');
  
          // Initialize the input element as an autocomplete
          inputElement.autocomplete({
            source: airlines,
            minLength: 2
          });
          var airlinecode =$('#airline-reissue-value').val();
        // Set the initial value of the input field
        inputElement.val(airlinecode);
        },
        error: function(xhr, status, error) {
          console.log(error);
        }
      });

  //-------------------------------End ---------------------------------



  $('#flight-search').submit(function (event) {
    event.preventDefault();
    // Validate form data
    var cabin = $('#cabin-preference').val();
    // var adultCount = $('#adult_count').val();
    // var childCount = $('#child-count').val();
    // var infant = $('#infant').val();
    var adultCount = parseInt($('#adult_count').val());
    var childCount = parseInt($('#child-count').val());
    var infantCount = parseInt($('#infant-count').val());
    var source = $('#airport-input').val();
    var departureDate = $('#from').val();
    var returnDate = $('#to').val();
    var destination = $('#arrivalairport-input').val();
    var tripType = document.querySelector('input[name="tab"]:checked');
    var tripTypeValue = tripType.value;
    var totalcount = adultCount + childCount + infantCount;
   

    
    valid = true;
    if (cabin == '') {

      $('#cabin-preference').after('<sapan class="text-danger fs-12 position-absolute" style="color:red">Cabin Preference cannot be blank.</span>')
      valid = false;
    }
    if (adultCount == '') {
      $('#arrivalairport-input').after('<sapan class="text-danger fs-12 position-absolute" style="color:red">count must be greater than 1</span>')
      valid = false;
    }
    if(tripTypeValue === "OneWay" || tripTypeValue === "Return"){
    if (source == '') {

      $('#airport-input').after('<sapan class="text-danger fs-12 position-absolute" style="color:red">Source cannot be blank.</span>')
      valid = false;
    }
    if (destination == '') {

      $('#arrivalairport-input').after('<sapan class="text-danger fs-12 position-absolute" style="color:red">Destintion cannot be blank.</span>')
      valid = false;
    }
    if (departureDate == '') {

      $('#from').after('<sapan class="text-danger fs-12 position-absolute" style="color:red">Departure Date cannot be blank.</span>')
      valid = false;
    }
    if (source === destination) {
      if ($('#airport-input').val()) {
          $('#airport-input').after('<span class="text-danger fs-12 position-absolute error-message" style="color:red">Source and Destination cannot be the same.</span>');
      } else {
        alert('Source and Destination cannot be the same.');
      }
      valid = false;
    }
  }
    if (totalcount > 9) {

      $('#errormessage').after('<sapan class="text-danger fs-12 position-absolute" style="color:red">Number of Valid Passenger count is maximum 9.</span>')
      valid = false;
    }
    if (adultCount < infantCount) {

      $('#errormessage').after('<sapan class="text-danger fs-12 position-absolute" style="color:red">Number of Infant can not exceed number of Adult selected.</span>')
      valid = false;
    }
    if (tripTypeValue === "Return") {
      if (returnDate == '') {

        $('#to').after('<sapan class="text-danger fs-12 position-absolute" style="color:red">Return Date cannot be blank.</span>')
        valid = false;
      }
    }


    if (!valid) {
      return false;
    }

    // var formData = new FormData();
    var formData = $(this).serialize();

    // if (tripTypeValue === "Circle") {
    //   const tripDetails = collectTripDetails();
    //   const tripDetailsJson = JSON.stringify(tripDetails);
    //   const requestData = {
    //     tripDetails: tripDetailsJson,
    //     formData: formData
    //   };
    //   }
    
    $('#dep-loading').text(source);
    $('#arrival-loading').text(destination);
    var totalpass = parseInt(adultCount) + parseInt(childCount);
    $('#pass-count').text(totalpass);
    $('#dep-date').text(departureDate);

    

    // Perform the form submission using AJAX
    if(tripTypeValue === "OneWay" || tripTypeValue === "Return"){
      $('#FlightSearchLoading').show();
      updateProgressBar(0);

      var intervalId;
      intervalId = setInterval(function () {
        // This code will be executed every second
        // Youcan add code here to update the progress bar during the search
      }, 8000);
    $.ajax({

      url: 'search.php',
      type: 'POST',
      data: formData,
      // processData: false,
      // contentType: false,
      xhr: function () {
        var xhr = new window.XMLHttpRequest();

            // Listen for progress events
            xhr.upload.addEventListener("progress", function (evt) {
              if (evt.lengthComputable) {
                  // Calculate the percentage completed
                  var percentage = (evt.loaded / evt.total) * 100;
                  // Update the progress bar
                  updateProgressBar(percentage);
              }
          }, false);


        return xhr;
    },
      success: function (response) {
        // if (response) {
// console.log(response);
        //   window.location.href = 'result.php';
        //   $('#FlightSearchLoading').hide();
        // }
        if (response) {
          // Clear the interval
          clearInterval(intervalId);

          // Set the progress bar to 100% and complete
          updateProgressBar(100, true);

          // Redirect to result.php after a delay
          setTimeout(function () {
              window.location.href = 'result.php';
              $('#FlightSearchLoading').hide();
          }, 1000); // 1000 milliseconds = 1 second
      }

      },
      error: function () {
        // Handle error cases here

        // Hide the loading popup
        $('#loading-popup').hide();
      }
    });
   
  }
    //multicity search ajax call 
    if(tripTypeValue === "Circle"){
      const departureInputs = document.querySelectorAll('input[name^="departure_from_"]');
      const tripCout =  departureInputs.length;
      
      //collect the trip details dynamically
      const tripDetails = collectTripDetails(tripCout);
      const tripDetailsJson = JSON.stringify(tripDetails);
      const sanitizedTripDetailsJson = encodeURIComponent(tripDetailsJson);
      //pass trip details and form data
      const requestData = {
        tripDetails: sanitizedTripDetailsJson,
        formData: formData
      };
      alert(tripDetailsJson);
      $.ajax({


        url: 'search-multicity.php',
        type: 'POST',
        data: requestData,
        dataType: 'json',
        success: function (response) {
          if (response) {
  
            // window.location.href = 'multicity-result.php';
            // $('#FlightSearchLoading').hide();
          }
  
        },
        error: function () {
          // Handle error cases here
  
          // Hide the loading popup
          $('#loading-popup').hide();
        }
      });
    }





  });

  // function updateProgressBar(percentage) {
  //   alert("testpopup");
  //   $('#progress-bar').css('width', percentage + '%');
  //   $('#progress-bar').attr('aria-valuenow', percentage);
  // }
  function updateProgressBar(percentage, complete) {
    $('#progress-bar').css('width', percentage + '%');
    $('#progress-bar').attr('aria-valuenow', percentage);

    if (complete) {
        $('#progress-bar').addClass('progress-bar-striped');
        $('#progress-bar').removeClass('active');
        $('#progress-bar').removeClass('progress-bar-animated');
        $('#progress-bar').css('width', '100%');
    }
}
 

  //Revalidation API

  $('#validate-flight').click(function (event) {

    event.preventDefault();
    var fsc = $('#fscode').val();
    var formData = new FormData();
    formData.append('fsc', fsc);
    $.ajax({
      url: 'revalidate.php',
      type: 'POST',
      data: formData,
      contentType: false,
      processData: false,
      success: function (response) {
        window.location.href = 'my-booking-step1.php';
        // $('#FlightSearchLoading').hide();


      },
      error: function () {

        // $('#loading-popup').hide();
      }
    });
  });


  //Fare Rule Api call

  // $('#fareRuleApi').click(function (event) {
    // event.preventDefault();
    // var buttonFare = document.getElementById("fareRuleApi");
    // var value = buttonFare.getAttribute("data-value");
    // var count = buttonFare.getAttribute("data-count-value");



    // var formData = new FormData();
    // formData.append('value', value);
    // formData.append('count', count);




    // $('#login_message').text("");

    // $.ajax({
    //   url: 'farerule.php',
    //   type: 'post',
    //   data: formData,
    //   contentType: false,
    //   processData: false,
    //   dataType: 'json',
    //   success: function (response) {
    //     if (response) {

    //       console.log(response.fareRules);
    //       var fareRules = response.fareRules[0]['RuleDetails'];

    //       fareRules.forEach(function (rule, index) {
    //         var category = rule.Category;
    //         var rules = rule.Rules;

    //         // Check if the category is "Penalty" or "Return"
    //         // if (category === "PENALTIES" || category === "TICKET ENDORSEMENTS") {
    //         if (category === "PENALTIES") {
    //           // Create a heading for the category
    //           // var categoryHeading = category;
    //           // $('#fareresult').append(categoryHeading);

    //           // Create a paragraph for the rules
    //           var rulesParagraph = rules;
    //           $('#fareresult').append(rulesParagraph);
    //         }
    //       });
    //     } else {

    //     }



    //   },
    //   error: function () {
    //     alert('Error submitting form');
    //   }
    // });
  // });






  //   document.addEventListener("DOMContentLoaded", function() {
  //     // Retrieve the button element
  //     var button = document.getElementById("fareRuleApi");

  //     // Add a click event listener to the button
  //     button.addEventListener("click", function(event) {
  //       event.preventDefault();
  //         // Retrieve the data-value attribute from the button
  //         var value = button.getAttribute("data-value");

  //         // Do something with the value (e.g., display an alert)
  //         alert("Value clicked: " + value);
  //     });
  // });



  $("#continueRevalidationButton").click(function () {
    $("#loginbookflight").slideDown(1000);
  });

  $("#travellerContinueButton").click(function () {
    $("#travellerDetails").slideDown(1000);
    var button = document.getElementById("travellerContinueButton");
    var adultCounter = button.getAttribute("data-adult");
    let childCounter = button.getAttribute("data-child");
    let infantCounter = button.getAttribute("data-infant");
    var extraSrviceData = JSON.parse(document.getElementById("extraSrviceData").value);
    var userId = button.getAttribute("data-uid");

    if (userId) {
        var parts = userId.split("&&");
        var id = parts[0];
        var total = parts[1].split("=")[1];
        
        checkUserBalance(id, total, continueExecution);
    } else {
        continueExecution();
    }

    function checkUserBalance(id, total) {
        var formData = new FormData();
        formData.append('uid', id);
        formData.append('total', total);
        $.ajax({
            url: 'check_balance.php',
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function (response) {
                if (response === 'success') {
                  continueExecution();
                } else {
                    $('#balanceissueModal').modal('show');
                }
            },
            error: function () {
                window.location.href = 'my-booking-step1.php';
            }
        });
    }
    // .....................................................................................
    function continueExecution() {
     // const extraServiceInbound = [];
     if (extraSrviceData != null) {
      //  const extraServiceInbound = extraSrviceData.filter(service => service.Behavior === 'PER_PAX_INBOUND');
      // const extraServiceInbound = extraSrviceData.filter(service => service.Behavior === 'PER_PAX_INBOUND');
        if ( extraSrviceData.filter(service => service.Behavior === 'PER_PAX_INBOUND').length > 0) {
          var extraServiceInbound = extraSrviceData.filter(service => service.Behavior === 'PER_PAX_INBOUND');
        } 
        else {
          var extraServiceInbound=[];
        }
        console.log(extraServiceInbound); 
    
   }
  //  else{
  //   const extraServiceInbound=[];
  //  }
  
 
//   if (extraServiceInbound.length > 0) {
//     extraServiceInbound=extraServiceInbound;
// } else {
//   extraServiceInbound="";
// }
// alert(extraServiceInbound);
  addAdult();
  addChild();
  addInfant();
  function generateExtraServiceOptions(extraServiceData) {
    let optionsHtml = '<option value="">Select..</option>';
    extraServiceData.forEach((extraService) => {
      if (extraService['Type'] === 'BAGGAGE' && extraService['Behavior'] === 'PER_PAX_OUTBOUND') {
        const description = extraService['Description'];
        const serviceID = extraService['ServiceId'];
        const amount = extraService['ServiceCost']['Amount'];
        // optionsHtml += `<option value="${serviceID}">${description}</option>`;
        optionsHtml += `<option value="${serviceID}/${description}/${amount}">${description} (${amount})</option>`;
      }
    });
    return optionsHtml;
  }
  function generateExtraServiceOptionsChild(extraServiceData) {
    let optionsHtml = '<option value="">Select..</option>';
    extraServiceData.forEach((extraService) => {
      if (extraService['Type'] === 'BAGGAGE' && extraService['Behavior'] === 'PER_PAX_OUTBOUND') {
        const description = extraService['Description'];
        const serviceID = extraService['ServiceId'];
        const amount = extraService['ServiceCost']['Amount'];
        // optionsHtml += `<option value="${serviceID}">${description}</option>`;
        optionsHtml += `<option value="${serviceID}/${description}/${amount}">${description} (${amount})</option>`;
      }
    });
    return optionsHtml;
  }
  function generateExtraMealServiceOptions(extraServiceData) {
    let optionsHtml = '<option value="">Select..</option>';
    extraServiceData.forEach((extraService) => {
      if (extraService['Type'] === 'Meal' && extraService['Behavior'] === 'PER_PAX_OUTBOUND') {
        const description = extraService['Description'];
        const serviceID = extraService['ServiceId'];
        const amount = extraService['ServiceCost']['Amount'];

        // optionsHtml += `<option value="${serviceID}">${description}</option>`;
        optionsHtml += `<option value="${serviceID}/${description}/${amount}">${description} (${amount})</option>`;

      }
    });
    return optionsHtml;
  }
  function generateExtraMealServiceOptionsChild(extraServiceData) {
    let optionsHtml = '<option value="">Select..</option>';
    extraServiceData.forEach((extraService) => {
      if (extraService['Type'] === 'Meal' && extraService['Behavior'] === 'PER_PAX_OUTBOUND') {
        const description = extraService['Description'];
        const serviceID = extraService['ServiceId'];
        const amount = extraService['ServiceCost']['Amount'];

        // optionsHtml += `<option value="${serviceID}">${description}</option>`;
        optionsHtml += `<option value="${serviceID}/${description}/${amount}">${description} (${amount})</option>`;

      }
    });
    return optionsHtml;
  }

  function generateExtraServiceOptionsReturn(extraServiceData) {
   
    let optionsHtml = '<option value="">Select..</option>';
    extraServiceData.forEach((extraService) => {
      if (extraService['Type'] === 'BAGGAGE' && extraService['Behavior'] === 'PER_PAX_INBOUND') {
        const description = extraService['Description'];
        const serviceID = extraService['ServiceId'];
        const amount = extraService['ServiceCost']['Amount'];
        // optionsHtml += `<option value="${serviceID}">${description}</option>`;
        optionsHtml += `<option value="${serviceID}/${description}/${amount}">${description} (${amount})</option>`;
      }
    });
    return optionsHtml;
  }

  function generateExtraMealServiceOptionsReturn(extraServiceData) {
    let optionsHtml = '<option value="">Select..</option>';
    extraServiceData.forEach((extraService) => {
      if (extraService['Type'] === 'Meal' && extraService['Behavior'] === 'PER_PAX_INBOUND') {
        const description = extraService['Description'];
        const serviceID = extraService['ServiceId'];
        const amount = extraService['ServiceCost']['Amount'];

        // optionsHtml += `<option value="${serviceID}">${description}</option>`;
        optionsHtml += `<option value="${serviceID}/${description}/${amount}">${description} (${amount})</option>`;

      }
    });
    return optionsHtml;
  }

  function generateExtraMealServiceOptionsChildReturn(extraServiceData) {
    let optionsHtml = '<option value="">Select..</option>';
    extraServiceData.forEach((extraService) => {
      if (extraService['Type'] === 'Meal' && extraService['Behavior'] === 'PER_PAX_INBOUND') {
        const description = extraService['Description'];
        const serviceID = extraService['ServiceId'];
        const amount = extraService['ServiceCost']['Amount'];

        // optionsHtml += `<option value="${serviceID}">${description}</option>`;
        optionsHtml += `<option value="${serviceID}/${description}/${amount}">${description} (${amount})</option>`;

      }
    });
    return optionsHtml;
  }
  function generateExtraServiceOptionsChildReturn(extraServiceData) {
    let optionsHtml = '<option value="">Select..</option>';
    extraServiceData.forEach((extraService) => {
      if (extraService['Type'] === 'BAGGAGE' && extraService['Behavior'] === 'PER_PAX_INBOUND') {
        const description = extraService['Description'];
        const serviceID = extraService['ServiceId'];
        const amount = extraService['ServiceCost']['Amount'];
        // optionsHtml += `<option value="${serviceID}">${description}</option>`;
        optionsHtml += `<option value="${serviceID}/${description}/${amount}">${description} (${amount})</option>`;
      }
    });
    return optionsHtml;
  }


  function addAdult() {
    const adultContainer = document.getElementById('adultcontainer');
    const endpoint = 'https://api.countrystatecity.in/v1/countries';

    fetch(endpoint, {
      headers: {
        'X-CSCAPI-KEY': 'N3FnUFJkMnJCaFZhQmRWUDlHRGRQR2lLQ2dFU2wzVDhJaDVBNlF2SQ==',
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
    })
      .then(response => response.json())
      .then(data => {
        // const countryData = data.map(country => country.name,countryid => country.iso2);
        const countryData = data.map(country => ({
          name: country.name,
          iso2: country.iso2
        }));
        $faretype = "return";
        for (let i = 1; i <= adultCounter; i++) {
          const div = document.createElement("div");
          div.classList.add("form-row", "pb-lg-3", "pb-2", "bdr-b", "mb-3", "align-items-center");
          div.innerHTML = `
                  


                    <div class="col mb-lg-0 mb-2">
                        <label for="" class="m-0 fw-500">Adult ${i}</label>
                    </div>
                    <div class="col-lg-2 col-md-4 mb-4">
                      <label for="title${i}">Title:</label>
                        <select name="sirLable${i}" id="" class="form-control select-title">
                          
                            <option value="Mr">MR</option>
                            <option value="Mrs">MRS</option>
                            <option value="MISS">MISS</option>
                        </select>
                        <span id="sirLableError${i}" class="text-danger fs-12 position-absolute validation-error"></span>

                    </div>
                    <div class="col-lg-2 col-md-4 mb-4">
                    <label for="firstName${i}">First Name:</label>
                        <input type="text" name="firstName${i}" class="form-control" placeholder="Adult ${i} First name">
                        <span id="firstNameError${i}" class="text-danger fs-12 position-absolute validation-error"></span>
                    </div>
                    <div class="col-lg-2 col-md-4 mb-4">
                    <label for="lastName${i}">Last Name:</label>
                        <input type="text" name="lastName${i}" class="form-control" placeholder="Adult ${i} Last Name">
                        <span id="lastNameError${i}" class="text-danger fs-12 position-absolute validation-error"></span>
                    </div>
                    <div class="col-lg-2 col-md-4 mb-4">
                    <label for="gender${i}">Gender:</label>
                      <select name="gender${i}" class="form-control">
                        <option value="">Select Gender</option>
                        <option value="M">Male</option>
                        <option value="F">Female</option>
                        <option value="U">Other</option>
                      </select>
                      <span id="genderError${i}" class="text-danger fs-12 position-absolute validation-error"></span>
                    </div>
                    <div class="col-lg-2 col-md-4 calndr-icon mb-4">
                    <label for="adultDOB${i}">DOB:</label>
                        <input type="date" name="adultDOB${i}" class="form-control" placeholder="Adult ${i} Date of Birth" onfocus="(this.type='date')" >
                        <span id="adultDOBError${i}" class="text-danger fs-12 position-absolute validation-error"></span>
                    </div>
                    <div class="col-lg-2 col-md-4 mb-4">
                    <label for="passportNo${i}">Passport Number:</label>
                        <input type="text" class="form-control" name="passportNo${i}" placeholder="Adult ${i} Passport No.">
                        <span id="passportNoError${i}" class="text-danger fs-12 position-absolute validation-error"></span>

                    </div>
                    <div class="col-lg-2 col-md-4 calndr-icon mb-4">
                      <label for="pasprtExp${i}">Passport Expiry:</label>
                        <input type="date" name="pasprtExp${i}" class="form-control" placeholder="Adult ${i} Expiry Date" onfocus="(this.type='date')" >
                        <span id="pasprtExpError${i}" class="text-danger fs-12 position-absolute validation-error"></span>

                    </div>
                    <div class="col-md-4 mb-4">
                    <label for="issuingCountry${i}">Passport Issuing Country:</label>
                      <select name="issuingCountry${i}" class="form-control">
                        ${countryData.map(country => `<option value="${country.iso2}">${country.name}</option>`).join('')}
                      </select>
                      <span id="countryError${i}" class="text-danger fs-12 position-absolute validation-error"></span>
                    </div>

                    <div class="col-md-4 mb-4">
                      <label for="nationality${i}">Nationality:</label>
                      <select name="nationality${i}" class="form-control">
                        ${countryData.map(country => `<option value="${country.iso2}">${country.name}</option>`).join('')}
                      </select>
                      <span id="nationalityError${i}" class="text-danger fs-12 position-absolute validation-error"></span>
                    </div>

                    <div class="col-lg-6 col-md-4 calndr-icon mb-4" ${extraSrviceData && extraSrviceData.length > 0 ? '' : 'style="display: none;"'}>
                      <label for="baggageService${i}">Baggage Service:</label>
                      <select name="baggageService${i}" id="baggageService${i}" class="form-control">
                      ${extraSrviceData && extraSrviceData.length > 0 ? generateExtraServiceOptions(extraSrviceData) : ''}
                      </select>
                    </div>
                  

                    <div class="col-lg-6 col-md-4 calndr-icon mb-4" ${extraSrviceData && extraSrviceData.length > 0 ? '' : 'style="display: none;"'}>
                      <label for="mealService${i}">Meal Service:</label>
                      <select name="mealService${i}" id="mealService${i}" class="form-control">
                        ${extraSrviceData && extraSrviceData.length > 0 ? generateExtraMealServiceOptions(extraSrviceData) : ''}
                      </select>
                    </div>
                  
                   
                    <div class="col-lg-6 col-md-4 calndr-icon mb-4" ${extraSrviceData && extraServiceInbound.length > 0 ? '' : 'style="display: none;"'}>
                      <label for="baggageServiceReturn${i}">Return Baggage Service:</label>
                      <select name="baggageServiceReturn${i}" id="baggageServiceReturn${i}" class="form-control">
                      ${extraSrviceData && extraSrviceData.length > 0 ? generateExtraServiceOptionsReturn(extraSrviceData) : ''}
                      </select>
                    </div>

                    <div class="col-lg-6 col-md-4 calndr-icon mb-4" ${extraSrviceData && extraServiceInbound.length > 0 ? '' : 'style="display: none;"'}>
                      <label for="mealServiceReturn${i}">Return Meal Service :</label>
                      <select name="mealServiceReturn${i}" id="mealServiceReturn${i}" class="form-control">
                        ${extraSrviceData && extraSrviceData.length > 0 ? generateExtraMealServiceOptionsReturn(extraSrviceData) : ''}
                      </select>
                    </div>
                  

                `;

          adultContainer.appendChild(div);
        }
      });
    // adultCounter++;
  }


  //child details
  function addChild() {
    const adultContainer = document.getElementById('childcontainer');

    const endpoint = 'https://api.countrystatecity.in/v1/countries';

    fetch(endpoint, {
      headers: {
        'X-CSCAPI-KEY': 'N3FnUFJkMnJCaFZhQmRWUDlHRGRQR2lLQ2dFU2wzVDhJaDVBNlF2SQ==',
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
    })
      .then(response => response.json())
      .then(data => {
        // const countryData = data.map(country => country.name,countryid => country.iso2);
        const countryData = data.map(country => ({
          name: country.name,
          iso2: country.iso2
        }));

        for (let i = 1; i <= childCounter; i++) {
          const div = document.createElement("div");
          div.classList.add("form-row", "pb-lg-3", "pb-2", "bdr-b", "mb-3", "align-items-center");
          div.innerHTML = `
                


                  <div class="col mb-lg-0 mb-2">
                      <label for="" class="m-0 fw-500">Child ${i}</label>
                  </div>
                  <div class="col mb-lg-0 mb-4">
                  <label for="sirLableChild${i}"> Title:</label>
                      <select name="sirLableChild${i}" id="" class="form-control select-title">
                          <option value="MISS">MISS </option>
                          <option value="MSTR">MSTR</option>
                          
                      </select>
                      <span id="sirLableChildError${i}" class="text-danger fs-12 position-absolute validation-error"></span>

                  </div>
                  <div class="col-lg-2 col-md-4 mb-4">
                    <label for="firstNameChild${i}"> First Name:</label>
                      <input type="text" name="firstNameChild${i}" class="form-control" placeholder="Child ${i} First name">
                      <span id="firstNameChildError${i}" class="text-danger fs-12 position-absolute validation-error"></span>
                  </div>
                  <div class="col-lg-2 col-md-4 mb-4">
                  <label for="lastNameChild${i}"> Last Name:</label>
                      <input type="text" name="lastNameChild${i}" class="form-control" placeholder="Child ${i} Last Name">
                      <span id="lastNameChildError${i}" class="text-danger fs-12 position-absolute validation-error"></span>
                  </div>
                  <div class="col-lg-2 col-md-4 mb-4">
                  <label for="genderChild${i}"> Gender:</label>
                  <select name="genderChild${i}" class="form-control">
                    <option value="">Select Gender</option>
                    <option value="M">Male</option>
                    <option value="F">Female</option>
                    <option value="U">Other</option>
                  </select>
                  <span id="genderChildError${i}" class="text-danger fs-12 position-absolute validation-error"></span>
                </div>
                  <div class="col-lg-2 col-md-4 calndr-icon mb-4">
                  <label for="childDOB${i}"> DOB:</label>
                      <input type="date" name="childDOB${i}" class="form-control" placeholder="Child ${i} Date of Birth" onfocus="(this.type='date')"  >

                      <span id="childDOBError${i}" class="text-danger fs-12 position-absolute validation-error"></span>
                  </div>
                  <div class="col-lg-2 col-md-4 mb-4">
                  <label for="passportNoChild${i}">Passport Number:</label>
                      <input type="text" class="form-control" name="passportNoChild${i}" placeholder="Child ${i} Passport No.">
                      <span id="passportNoChildError${i}" class="text-danger fs-12 position-absolute validation-error"></span>

                  </div>
                  <div class="col-lg-2 col-md-4 calndr-icon mb-4">
                  <label for="pasprtExpChild${i}">Passport Expiry:</label>
                      <input type="date" name="pasprtExpChild${i}" class="form-control" placeholder="Child ${i} Expiry Date" onfocus="(this.type='date')" >
                      <span id="pasprtExpChildError${i}" class="text-danger fs-12 position-absolute validation-error"></span>

                  </div>
                  <div class="col-md-4 mb-4">
                  <label for="issuingcountryChild${i}">Passport Issuing Country:</label>
                    <select name="issuingcountryChild${i}" class="form-control">
                      ${countryData.map(country => `<option value="${country.iso2}">${country.name}</option>`).join('')}
                    </select>
                    <span id="issuingcountryChild${i}" class="text-danger fs-12 position-absolute validation-error"></span>
                  </div>

                  <div class="col-md-4 mb-4">
                    <label for="nationalityChild${i}">Nationality:</label>
                    <select name="nationalityChild${i}" class="form-control">
                      ${countryData.map(country => `<option value="${country.iso2}">${country.name}</option>`).join('')}
                    </select>
                    <span id="nationalityChildError${i}" class="text-danger fs-12 position-absolute validation-error"></span>
                  </div>

                  <div class="col-lg-6 col-md-4 calndr-icon mb-4" ${extraSrviceData && extraSrviceData.length > 0 ? '' : 'style="display: none;"'}>
                    <label for="baggageServiceChild${i}">Baggage Service:</label>
                    <select name="baggageServiceChild${i}" id="baggageServiceChild${i}" class="form-control">
                    ${extraSrviceData && extraSrviceData.length > 0 ? generateExtraServiceOptionsChild(extraSrviceData) : ''}
                    </select>
                  </div>
                

                  <div class="col-lg-6 col-md-4 calndr-icon mb-4" ${extraSrviceData && extraSrviceData.length > 0 ? '' : 'style="display: none;"'}>
                    <label for="mealServiceChild${i}">Meal Service:</label>
                    <select name="mealServiceChild${i}" id="mealServiceChild${i}" class="form-control">
                      ${extraSrviceData && extraSrviceData.length > 0 ? generateExtraMealServiceOptionsChild(extraSrviceData) : ''}
                    </select>
                  </div>

                  <div class="col-lg-6 col-md-4 calndr-icon mb-4" ${extraSrviceData && extraServiceInbound.length > 0 ? '' : 'style="display: none;"'}>
                      <label for="baggageServiceChildReturn${i}">Return Baggage Service:</label>
                      <select name="baggageServiceChildReturn${i}" id="baggageServiceChildReturn${i}" class="form-control">
                      ${extraSrviceData && extraSrviceData.length > 0 ? generateExtraServiceOptionsChildReturn(extraSrviceData) : ''}
                      </select>
                    </div>

                    <div class="col-lg-6 col-md-4 calndr-icon mb-4" ${extraSrviceData && extraServiceInbound.length > 0 ? '' : 'style="display: none;"'}>
                      <label for="mealServiceChildReturn${i}">Return Meal Service :</label>
                      <select name="mealServiceChildReturn${i}" id="mealServiceChildReturn${i}" class="form-control">
                        ${extraSrviceData && extraSrviceData.length > 0 ? generateExtraMealServiceOptionsChildReturn(extraSrviceData) : ''}
                      </select>
                    </div>






              `;

          adultContainer.appendChild(div);
        }
      });
    // adultCounter++;
  }

  //Add Infant data

  function addInfant() {
    const adultContainer = document.getElementById('infantcontainer');

    const endpoint = 'https://api.countrystatecity.in/v1/countries';

    fetch(endpoint, {
      headers: {
        'X-CSCAPI-KEY': 'N3FnUFJkMnJCaFZhQmRWUDlHRGRQR2lLQ2dFU2wzVDhJaDVBNlF2SQ==',
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
    })
      .then(response => response.json())
      // .then(data => {
      //         const countryData = data.map(country => country.name);
      .then(data => {
        // const countryData = data.map(country => country.name,countryid => country.iso2);
        const countryData = data.map(country => ({
          name: country.name,
          iso2: country.iso2
        }));

        for (let i = 1; i <= infantCounter; i++) {
          const div = document.createElement("div");
          div.classList.add("form-row", "pb-lg-3", "pb-2", "bdr-b", "mb-3", "align-items-center");
          div.innerHTML = `
                


                  <div class="col mb-lg-0 mb-4">
                      <label for="" class="m-0 fw-500">Infant ${i}</label>
                  </div>
                  <div class="col mb-lg-0 mb-4">
                  <label for="sirLableInfant${i}"> Title:</label>
                      <select name="sirLableInfant${i}" id="" class="form-control select-title">
                        <option value="MISS">MISS </option>
                        <option value="MSTR">MSTR</option>
                      </select>
                      <span id="sirLableInfantError${i}" class="text-danger fs-12 position-absolute validation-error"></span>

                  </div>
                  <div class="col-lg-2 col-md-4 mb-4">
                  <label for="firstNameInfant${i}"> First Name:</label>
                      <input type="text" name="firstNameInfant${i}" class="form-control" placeholder="Infant ${i} First name">
                      <span id="firstNameInfantError${i}" class="text-danger fs-13 position-absolute validation-error"></span>
                  </div>
                  <div class="col-lg-2 col-md-4 mb-4">
                  <label for="lastNameInfant${i}"> Last Name:</label>
                      <input type="text" name="lastNameInfant${i}" class="form-control" placeholder="Infant ${i} Last Name">
                      <span id="lastNameInfantError${i}" class="text-danger fs-12 position-absolute validation-error"></span>
                  </div>
                  <div class="col-lg-2 col-md-4 mb-4">
                  <label for="genderInfant${i}">  Gender:</label>
                  <select name="genderInfant${i}" class="form-control">
                    <option value="">Select Gender</option>
                    <option value="M">Male</option>
                    <option value="F">Female</option>
                    <option value="U">Other</option>
                  </select>
                  <span id="genderError${i}" class="text-danger fs-12 position-absolute validation-error"></span>
                </div>
                  <div class="col-lg-2 col-md-4 calndr-icon mb-4">
                  <label for="infantDOB${i}">  DOB:</label>
                      <input type="date" name="infantDOB${i}" class="form-control" placeholder="Infant ${i} Date of Birth" onfocus="(this.type='date')" >
                      <span id="infantDOBError${i}" class="text-danger fs-12 position-absolute validation-error"></span>
                  </div>
                  <div class="col-lg-2 col-md-4 mb-4">
                  <label for="passportNoInfant${i}">  Passport Number:</label>
                      <input type="text" class="form-control" name="passportNoInfant${i}" placeholder="Infant ${i} Passport No.">
                      <span id="passportNoInfantError${i}" class="text-danger fs-12 position-absolute validation-error"></span>

                  </div>
                  <div class="col-lg-2 col-md-4 calndr-icon mb-4">
                  <label for="pasprtExpInfant${i}">  Passport Expiry:</label>
                      <input type="date" name="pasprtExpInfant${i}" class="form-control" placeholder="Infant ${i} Expiry Date" onfocus="(this.type='date')" >
                      <span id="pasprtExpInfantError${i}" class="text-danger fs-13 position-absolute validation-error"></span>

                  </div>
                  <div class="col-md-4 mb-4">
                  <label for="issuingcountryInfant${i}">  Passport Issuing Country:</label>
                    <select name="issuingcountryInfant${i}" class="form-control">
                      ${countryData.map(country => `<option value="${country.iso2}">${country.name}</option>`).join('')}
                    </select>
                    <span id="countryError${i}" class="text-danger fs-12 position-absolute validation-error"></span>
                  </div>
                  <div class="col-md-4 mb-4">
                    <label for="nationalityInfant${i}">Nationality:</label>
                    <select name="nationalityinfant${i}" class="form-control">
                      ${countryData.map(country => `<option value="${country.iso2}">${country.name}</option>`).join('')}
                    </select>
                    <span id="nationalityChildError${i}" class="text-danger fs-12 position-absolute validation-error"></span>
                  </div>






              `;

          adultContainer.appendChild(div);
        }
      });
    // adultCounter++;
  }

  // var button = document.getElementById("travellerContinueButton");
  // var fareSourceValue = button.getAttribute("data-value");
  // var formData = new FormData();
  // formData.append('fareSourceValue', fareSourceValue);
  // $.ajax({
  //   url: 'seatmap.php',
  //   type: 'post',
  //   data: formData,
  //   contentType: false,
  //   processData: false,
  //   dataType: 'json',
  //   success: function (response) {



  //   },
  //   error: function () {
  //     alert('Error submitting form');
  //   }
  // });
}
  });


  $('.select-title').select2();
  $('.select-location').select2();




  //-----------------------Booking Details Submitted to In House DB -----------------

  $('#booking-submit').submit(function (event) {
    event.preventDefault();
    // Validate form data
    // let adultCounter = 2;
    // let childCounter = 0;
    // let infantCounter = 0;

    var adultCountInput = document.querySelector('input[name="adultCount"]');
    var adultCounter = adultCountInput.value;
    var childCountInput = document.querySelector('input[name="childCount"]');
    var childCounter = childCountInput.value;
    var infantCountInput = document.querySelector('input[name="infantCount"]');
    var infantCounter = infantCountInput.value;

    const validationErrors = []; // Array to store validation errors

    // Loop through the dynamically generated fields
    for (let i = 1; i <= adultCounter; i++) {
      const firstNameInput = document.querySelector(`input[name=firstName${i}]`);
      const lastNameInput = document.querySelector(`input[name=lastName${i}]`);
      const passportNoInput = document.querySelector(`input[name=passportNo${i}]`);
      const adultDOBInput = document.querySelector(`input[name=adultDOB${i}]`);
      const pasprtExpInput = document.querySelector(`input[name=pasprtExp${i}]`);
      // const sirLableInput = document.querySelector(`select[name=sirLable${i+1}]`);
      const sirLableSelect = document.querySelector(`select[name=sirLable${i}]`);
      const sirLableValue = sirLableSelect.value;

      const genderSelect = document.querySelector(`select[name=gender${i}]`);
      const genderValue = genderSelect.value;

      if (firstNameInput.value.trim() === "") {
        displayError(firstNameInput, `First name ${i} is required`);
        validationErrors.push(`First name ${i} is required`);
      } else if (!/^[a-zA-Z\s]+$/.test(firstNameInput.value.trim())) {
        displayError(firstNameInput, `First name ${i} contains invalid characters`);
        validationErrors.push(`First name ${i} contains invalid characters`);
      } else {
        clearError(firstNameInput);
      }

      if (lastNameInput.value.trim() === "") {
        displayError(lastNameInput, `Last name ${i} is required`);
        validationErrors.push(`Last name ${i} is required`);
      } else if (!/^[a-zA-Z\s]+$/.test(lastNameInput.value.trim())) {
        displayError(lastNameInput, `First name ${i} contains invalid characters`);
        validationErrors.push(`First name ${i} contains invalid characters`);
      } else if (lastNameInput.value.trim().length < 1) {
        displayError(lastNameInput, `Last name ${i} should have more than one character`);
        validationErrors.push(`Last name ${i} should have more than one character`);
      } else {
        clearError(lastNameInput);
      }

      if (passportNoInput.value.trim() === "") {
        displayError(passportNoInput, `Passpoet number ${i} is required`);
        validationErrors.push(`Passpoet number ${i} is required`);
      } else {
        clearError(passportNoInput);
      }


      if (sirLableValue === "") {
        displayError(sirLableSelect, `Select a value for Sir/Madam option`);
        validationErrors.push(`Sir/Madam option for Adult ${i} is required`);
      } else {
        clearError(sirLableSelect);
      }

      if (genderValue === "") {
        displayError(genderSelect, `Select gender option`);
        validationErrors.push(`select gender Adult ${i} is required`);
      } else {
        clearError(genderSelect);
      }


      const dobDate = new Date(adultDOBInput.value.trim());
      const currentDate = new Date();

      // Calculate the age difference in years
      const ageDifferenceInMilliseconds = currentDate - dobDate;
      const ageDifferenceInYears = ageDifferenceInMilliseconds / (1000 * 60 * 60 * 24 * 365);


      if (adultDOBInput.value.trim() === "") {
        displayError(adultDOBInput, `Select DOB`);
        validationErrors.push(`DOB option for Adult ${i} is required`);
      } else if (ageDifferenceInYears < 18) {
        displayError(adultDOBInput, `Age should be at least 18 years`);
        validationErrors.push(`Age for Adult ${i} should be at least 18 years`);
      }
      else {
        clearError(adultDOBInput);
      }

      const passportExpiryDate = new Date(pasprtExpInput.value.trim());
      if (pasprtExpInput.value.trim() === "") {
        displayError(pasprtExpInput, `Select Passport Expiry`);
        validationErrors.push(`Passport Expiry for Adult ${i} is required`);
      } else if (passportExpiryDate <= currentDate) {
        displayError(pasprtExpInput, `Invalid Exp Date`);
        validationErrors.push(`Invalid EXP date for Adult ${i} `);
      }
      else {
        clearError(pasprtExpInput);
      }



    }

    for (let i = 1; i <= childCounter; i++) {
      const firstNameChildInput = document.querySelector(`input[name=firstNameChild${i}]`);
      const lastNameChildInput = document.querySelector(`input[name=lastNameChild${i}]`);
      const passportNoChildInput = document.querySelector(`input[name=passportNoChild${i}]`);
      const childDOBInput = document.querySelector(`input[name=childDOB${i}]`);
      const pasprtExpChildInput = document.querySelector(`input[name=pasprtExpChild${i}]`);
      const sirLableSelect = document.querySelector(`select[name=sirLableChild${i}]`);
      const sirLableValue = sirLableSelect.value;
      const genderSelect = document.querySelector(`select[name=genderChild${i}]`);
      const genderValue = genderSelect.value;

      if (firstNameChildInput.value.trim() === "") {
        displayError(firstNameChildInput, `First name ${i} is required`);
        validationErrors.push(`First name ${i} is required`);
      } else if (!/^[a-zA-Z\s]+$/.test(firstNameChildInput.value.trim())) {
        displayError(firstNameChildInput, `First name ${i} contains invalid characters`);
        validationErrors.push(`First name ${i} contains invalid characters`);
      } else {
        clearError(firstNameChildInput);
      }



      if (lastNameChildInput.value.trim() === "") {
        displayError(lastNameChildInput, `Last name ${i} is required`);
        validationErrors.push(`Last name ${i} is required`);
      } else if (!/^[a-zA-Z\s]+$/.test(lastNameChildInput.value.trim())) {
        displayError(lastNameChildInput, `Last name ${i} contains invalid characters`);
        validationErrors.push(`Last name ${i} contains invalid characters`);
      } else if (lastNameChildInput.value.trim().length < 1) {
        displayError(lastNameChildInput, `Last name ${i} should have more than one character`);
        validationErrors.push(`Last name ${i} should have more than one character`);
      }
      else {
        clearError(lastNameChildInput);
      }

      if (passportNoChildInput.value.trim() === "") {
        displayError(passportNoChildInput, `Passpoet number ${i} is required`);
        validationErrors.push(`Passpoet number ${i} is required`);
      } else {
        clearError(passportNoChildInput);
      }

      if (sirLableValue === "") {
        displayError(sirLableSelect, `Select a value for Sir/Madam option`);
        validationErrors.push(`Sir/Madam option for Adult ${i} is required`);
      } else {
        clearError(sirLableSelect);
      }

      if (genderValue === "") {
        displayError(genderSelect, `Select gender option`);
        validationErrors.push(`select gender Adult ${i} is required`);
      } else {
        clearError(genderSelect);
      }


      const dobDate = new Date(childDOBInput.value.trim());
      const currentDate = new Date();

      // Calculate the age difference in years
      const ageDifferenceInMilliseconds = currentDate - dobDate;
      const ageDifferenceInYears = ageDifferenceInMilliseconds / (1000 * 60 * 60 * 24 * 365);

      if (childDOBInput.value.trim() === "") {
        displayError(childDOBInput, `Select DOB`);
        validationErrors.push(`DOB option for Child ${i} is required`);
      } else if (ageDifferenceInYears < 0 || ageDifferenceInYears > 12) {
        displayError(childDOBInput, `Age should be less than or exactly 12 years`);
        validationErrors.push(`Age for Child ${i} should be less than or exactly 12 years`);
      } else {
        clearError(childDOBInput);
      }


      const passportExpiryDate = new Date(pasprtExpChildInput.value.trim());
      if (pasprtExpChildInput.value.trim() === "") {
        displayError(pasprtExpChildInput, `Select Passport Expiry`);
        validationErrors.push(`Passport Expiry for Adult ${i} is required`);
      } else if (passportExpiryDate <= currentDate) {
        displayError(pasprtExpChildInput, `Invalid Exp Date`);
        validationErrors.push(`Invalid EXP date for Adult ${i} `);
      }
      else {
        clearError(pasprtExpChildInput);
      }




    }


    for (let i = 1; i <= infantCounter; i++) {
      const firstNameInfantInput = document.querySelector(`input[name=firstNameInfant${i}]`);
      const lastNameInfantInput = document.querySelector(`input[name=lastNameInfant${i}]`);
      const passportNoInfantInput = document.querySelector(`input[name=passportNoInfant${i}]`);
      const infantDOBInput = document.querySelector(`input[name=infantDOB${i}]`);
      const pasprtExpInfantInput = document.querySelector(`input[name=pasprtExpInfant${i}]`);
      const sirLableSelect = document.querySelector(`select[name=sirLableInfant${i}]`);
      const sirLableValue = sirLableSelect.value;

      const genderSelect = document.querySelector(`select[name=genderInfant${i}]`);
      const genderValue = genderSelect.value;

      if (firstNameInfantInput.value.trim() === "") {
        displayError(firstNameInfantInput, `First name ${i} is required`);
        validationErrors.push(`First name ${i} is required`);
      }
      else if (!/^[a-zA-Z\s]+$/.test(firstNameInfantInput.value.trim())) {
        displayError(firstNameInput, `First name ${i} contains invalid characters`);
        validationErrors.push(`First name ${i} contains invalid characters`);
      } else {
        clearError(firstNameInfantInput);
      }

      if (lastNameInfantInput.value.trim() === "") {
        displayError(lastNameInfantInput, `Last name ${i} is required`);
        validationErrors.push(`Last name ${i} is required`);
      } else if (!/^[a-zA-Z\s]+$/.test(lastNameInfantInput.value.trim())) {
        displayError(lastNameInfantInput, `Last name ${i} contains invalid characters`);
        validationErrors.push(`Last name ${i} contains invalid characters`);
      } else if (lastNameInfantInput.value.trim().length < 1) {
        displayError(lastNameInfantInput, `Last name ${i} should have more than one character`);
        validationErrors.push(`Last name ${i} should have more than one character`);
      }
      else {
        clearError(lastNameInfantInput);
      }

      if (passportNoInfantInput.value.trim() === "") {
        displayError(passportNoInfantInput, `Passpoet number ${i} is required`);
        validationErrors.push(`Passpoet number ${i} is required`);
      } else {
        clearError(passportNoInfantInput);
      }

      if (sirLableValue === "") {
        displayError(sirLableSelect, `Select a value for Sir/Madam option`);
        validationErrors.push(`Sir/Madam option for Adult ${i} is required`);
      } else {
        clearError(sirLableSelect);
      }

      if (genderValue === "") {
        displayError(genderSelect, `Select gender option`);
        validationErrors.push(`select gender Adult ${i} is required`);
      } else {
        clearError(genderSelect);
      }
      const dobDate = new Date(infantDOBInput.value.trim());
      const currentDate = new Date();

      // Calculate the age difference in years
      const ageDifferenceInMilliseconds = currentDate - dobDate;
      const ageDifferenceInYears = ageDifferenceInMilliseconds / (1000 * 60 * 60 * 24 * 365);

      if (infantDOBInput.value.trim() === "") {
        displayError(infantDOBInput, `Select DOB`);
        validationErrors.push(`DOB option for Infant ${i} is required`);
      } else if (ageDifferenceInYears < 0 || ageDifferenceInYears >= 2) {
        displayError(infantDOBInput, `Age should be less than or exactly 2 years`);
        validationErrors.push(`Age for Infant ${i} should be less than or exactly 2 years`);
      } else {
        clearError(infantDOBInput);
      }



      const passportExpiryDate = new Date(pasprtExpInfantInput.value.trim());
      if (pasprtExpInfantInput.value.trim() === "") {
        displayError(pasprtExpInfantInput, `Select Passport Expiry`);
        validationErrors.push(`Passport Expiry for Adult ${i} is required`);
      } else if (passportExpiryDate <= currentDate) {
        displayError(pasprtExpInfantInput, `Invalid Exp Date`);
        validationErrors.push(`Invalid EXP date for Adult ${i} `);
      }
      else {
        clearError(pasprtExpInfantInput);
      }


    }

    var contactfirstname = $('#contactfirstname').val();
    var contactlastname = $('#contactlastname').val();
    var contactcountry = $('#contactcountry').val();
    var contactnumber = $('#contactnumber').val();
    var contactemail = $('#contactemail').val();
    var contactpostcode = $('#contactpostcode').val();
    emailReg = /^[^\s@]+@[^\s@]+\.(?!con$)[^\s@]+$/;
    valid = true;
    if (!valid) {
      event.preventDefault();
    }
    if (contactfirstname == '') {

      $('#contactfirstname').after('<sapan class="text-danger fs-12 position-absolute" >First Name cannot be blank.</span>')
      valid = false;
    }
    if (contactlastname == '') {

      $('#contactlastname').after('<sapan class="text-danger fs-12 position-absolute" >Last Name cannot be blank.</span>')
      valid = false;
    }
    if (contactcountry == '') {

      $('#contactcountry').after('<sapan class="text-danger fs-12 position-absolute" >Phone Code cannot be blank.</span>')
      valid = false;
    }
    if (contactnumber == '') {

      $('#contactnumber').after('<sapan class="text-danger fs-12 position-absolute" >Contact Number cannot be blank.</span>')
      valid = false;
    }
    if (contactemail == '' || !emailReg.test($('#contactemail').val())) {

      $('#contactemail').after('<sapan class="text-danger fs-12 position-absolute" >Enter valid Email Id</span>')
      valid = false;
    }
    if (contactpostcode == '') {

      $('#contactpostcode').after('<sapan class="text-danger fs-12 position-absolute" >Postcode cannot be blank.</span>')
      valid = false;
    }


    // Set up form data for submission
    var formData = new FormData(this);
    if (validationErrors.length === 0 && valid == true) {
      $.ajax({
        type: 'POST',
        url: 'fetchData.php',
        data: formData,
        contentType: false,
        processData: false,
        success: function(response) {
          console.log('Response:', response);
          if (response.success) { // Checking for success response
              window.location.href = 'my-booking-step34.php';
          } else {
              alert('Error in fetch values');
          }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.error('Error submitting form: ', textStatus, errorThrown);
            console.error('Response text: ', jqXHR.responseText);
        }
      });
      // ----------------------end------------------------
      
      // $.ajax({
      //   url: 'booking-script.php',
      //   type: 'post',
      //   data: formData,
      //   processData: false,
      //   contentType: false,
      //   dataType: 'json',
      //   success: function (response) {
      //     // console.log(response);
      //     // alert(response);
      //     if (response.errors.length > 0) {
      //       alert(response.errors);
      //     }
      //     if (response.faretype == "webfare") {
      //       if (response.orderstatus) {
      //         if (response.orderstatus == "order success") {
      //           const redirectURL = 'my-booking-step3.php';
      //           window.location.href = redirectURL;

      //         } else {
      //           alert(response.orderstatus['Message']);
      //         }
      //       }

      //       // const redirectURL = 'my-booking-step3.php';
      //       // window.location.href = redirectURL;
      //     } else {
      //       if (response.orderstatus && response.ticketstatus) {
      //         if (response.orderstatus == "order success" && response.ticketstatus == "ticket sucess") {
      //           const redirectURL = 'my-booking-step3.php';
      //           window.location.href = redirectURL;

      //         } else if (response.orderstatus != "order success") {
      //           // alert(response.orderstatus['Message']);
      //           $('#errorMessage').text(response.orderstatus['Message']);
      //           $('#errorModal').modal('show');
      //           $(".close").click(function(){
      //             $(this).parents('.modal').modal('hide');
      //           });
      //         } else if (response.ticketstatus != "ticket sucess") {
      //           // alert(response.ticketstatus['Message']);
      //           $('#errorMessage').text(response.orderstatus['Message']);
      //           $('#errorModal').modal('show');
      //           $(".close").click(function(){
      //             $(this).parents('.modal').modal('hide');
      //           });
      //         }
      //       }

      //     }



      //   },
      //   error: function () {
      //     // alert('Error submitting form');
      //     console.log();
      //   }
      // });
    } else {
      console.log(validationErrors);
    }
  });


  //------------------------------------END ----------------------------------


  // -------------payment submit--------------------
  $('#payment-booking').submit(function (event) {
    event.preventDefault();

    var formData = new FormData(this);
    $.ajax({
      url: 'payment-script.php',
      type: 'post',
      data: formData,
      processData: false,
      contentType: false,
      dataType: 'json',
      success: function (response) {
        window.location.href = 'confirmation.php?bookingid=' + encodeURIComponent(response.bookingid);
        // alert("Booking successfully Completed");



      },
      error: function () {
        // alert('Error submitting form');
        console.log();
      }
    });
  });
  //------------end------------------------------


  function displayError(input, errorMessage) {
    const errorSpan = input.nextElementSibling;
    errorSpan.textContent = errorMessage;
  }

  function clearError(input) {
    const errorSpan = input.nextElementSibling;
    errorSpan.textContent = "";
  }



  //////////////////Load Login form at the time of book flight------------------

  $('#booking-user-login').submit(function (event) {

    event.preventDefault();
    // Validate form data

    var email = $('#loginemail').val();
    var password = $('#loginpassword').val();
    emailReg = /^[^\s@]+@[^\s@]+\.(?!con$)[^\s@]+$/;
    valid = true;

    if ($('#loginemail').val() == '' || !emailReg.test($('#loginemail').val())) {

      $('#loginemail').after('<sapan class="text-danger fs-12 position-absolute" >Enter valid Email Id.</span>')
      valid = false;
    }
    if (password == '') {
      $('#loginpassword').after('<sapan class="text-danger fs-12 position-absolute" >Enter your Password.</span>')
      valid = false;
    }

    if (!valid) {
      return false;
    }



    // Set up form data for submission
    var formData = new FormData(this);
    // Submit form via AJAX
    $.ajax({
      url: 'login-script.php',
      type: 'post',
      data: formData,
      processData: false,
      contentType: false,
      success: function (response) {
        if (response == 'error') {
          alert("invalid email or password");
          $('#email').val('');
          $('#password').val('');
        } else if (response == 'endsuccess') {
          // document.getElementById('logindiv').style.display = 'block';
          // document.getElementById('login-form').style.display = 'none';
          location.reload(true);
        }
        else if (response == 'agentsuccess') {
          // document.getElementById('logindiv').style.display = 'block';
          // document.getElementById('login-form').style.display = 'none';
          location.reload(true);
        } else if (response == 'agenterror') {
          alert("approval is on progress..");
        }


      },
      error: function () {
        alert('Error submitting form');
      }
    });


  });



  //----------------cancel booking---------------

  $('#cancel-booking').submit(function (event) {
    event.preventDefault();
    var formData = new FormData(this);
    $.ajax({
      url: 'cancel-script.php',
      type: 'post',
      data: formData,
      processData: false,
      contentType: false,
      dataType: 'json',
      success: function (response) {
        // window.location.href = 'confirmation.php?bookingid='+ encodeURIComponent(response.bookingid);
        alert("cancel booking");



      },
      error: function () {
        // alert('Error submitting form');
        console.log();
      }
    });
  });


  //-------------------Multicity add trip form---------------





  let tripNum = 2; // Initialize tripNum with 2 since we already have two trips (1 and 2) in the form
  let totalTrips = 2; // Counter for the total number of trips

  // document.getElementById('add_trip_button').addEventListener('click', function() {
  //     addNewTripField();
  // });

  function addNewTripField() {
      const maxTrips = 6;
      if (totalTrips < maxTrips) {
          tripNum++;
          totalTrips++;

          const newTripDiv = document.createElement('div');
          newTripDiv.classList.add('row', 'mt-md-2');
          newTripDiv.id = `trip_${tripNum}`;

          newTripDiv.innerHTML = `
              <div class="form-fields col-md-4">
                  <input type="text" class="form-control" name="departure_from_${tripNum}" placeholder="Departing From" >
              </div>
              <div class="form-fields col-md-4">
                  <input type="text" class="form-control" name="arrival_to_${tripNum}" placeholder="Going To" >
              </div>
              <div class="form-fields col-md-2 calndr-icon">
                  <input type="date" class="form-control date-multy-city" name="departure_date_${tripNum}" >
                  <span class="icon">
                  <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                      <path id="Vector" d="M3.25 0C2.38805 0 1.5614 0.34241 0.951903 0.951903C0.34241 1.5614 0 2.38805 0 3.25V14.75C0 15.612 0.34241 16.4386 0.951903 17.0481C1.5614 17.6576 2.38805 18 3.25 18H14.75C15.612 18 16.4386 17.6576 17.0481 17.0481C17.6576 16.4386 18 15.612 18 14.75V3.25C18 2.38805 17.6576 1.5614 17.0481 0.951903C16.4386 0.34241 15.612 0 14.75 0H3.25ZM1.5 5.5H16.5V14.75C16.5 15.2141 16.3156 15.6592 15.9874 15.9874C15.6592 16.3156 15.2141 16.5 14.75 16.5H3.25C2.78587 16.5 2.34075 16.3156 2.01256 15.9874C1.68437 15.6592 1.5 15.2141 1.5 14.75V5.5ZM13.25 11.5C12.9185 11.5 12.6005 11.6317 12.3661 11.8661C12.1317 12.1005 12 12.4185 12 12.75C12 13.0815 12.1317 13.3995 12.3661 13.6339C12.6005 13.8683 12.9185 14 13.25 14C13.5815 14 13.8995 13.8683 14.1339 13.6339C14.3683 13.3995 14.5 13.0815 14.5 12.75C14.5 12.4185 14.3683 12.1005 14.1339 11.8661C13.8995 11.6317 13.5815 11.5 13.25 11.5V11.5ZM9 11.5C8.66848 11.5 8.35054 11.6317 8.11612 11.8661C7.8817 12.1005 7.75 12.4185 7.75 12.75C7.75 13.0815 7.8817 13.3995 8.11612 13.6339C8.35054 13.8683 8.66848 14 9 14C9.33152 14 9.64946 13.8683 9.88388 13.6339C10.1183 13.3995 10.25 13.0815 10.25 12.75C10.25 12.4185 10.1183 12.1005 9.88388 11.8661C9.64946 11.6317 9.33152 11.5 9 11.5V11.5ZM13.25 7.5C12.9185 7.5 12.6005 7.6317 12.3661 7.86612C12.1317 8.10054 12 8.41848 12 8.75C12 9.08152 12.1317 9.39946 12.3661 9.63388C12.6005 9.8683 12.9185 10 13.25 10C13.5815 10 13.8995 9.8683 14.1339 9.63388C14.3683 9.39946 14.5 9.08152 14.5 8.75C14.5 8.41848 14.3683 8.10054 14.1339 7.86612C13.8995 7.6317 13.5815 7.5 13.25 7.5ZM9 7.5C8.66848 7.5 8.35054 7.6317 8.11612 7.86612C7.8817 8.10054 7.75 8.41848 7.75 8.75C7.75 9.08152 7.8817 9.39946 8.11612 9.63388C8.35054 9.8683 8.66848 10 9 10C9.33152 10 9.64946 9.8683 9.88388 9.63388C10.1183 9.39946 10.25 9.08152 10.25 8.75C10.25 8.41848 10.1183 8.10054 9.88388 7.86612C9.64946 7.6317 9.33152 7.5 9 7.5V7.5ZM4.75 7.5C4.41848 7.5 4.10054 7.6317 3.86612 7.86612C3.6317 8.10054 3.5 8.41848 3.5 8.75C3.5 9.08152 3.6317 9.39946 3.86612 9.63388C4.10054 9.8683 4.41848 10 4.75 10C5.08152 10 5.39946 9.8683 5.63388 9.63388C5.8683 9.39946 6 9.08152 6 8.75C6 8.41848 5.8683 8.10054 5.63388 7.86612C5.39946 7.6317 5.08152 7.5 4.75 7.5ZM3.25 1.5H14.75C15.716 1.5 16.5 2.284 16.5 3.25V4H1.5V3.25C1.5 2.284 2.284 1.5 3.25 1.5Z" fill="#6D759C" />
                  </svg>
              </span>
              </div>
              <div class="form-fields col-md-2">
                  <button type="button" data-tripnum="${tripNum}" class="close-button">Close</button>
              </div>
          `;

          document.getElementById('additional_trips').appendChild(newTripDiv);
          document.getElementById('add_trip_button').disabled = totalTrips === maxTrips;
          addCloseButtonEventListener(tripNum);
      }
  }

  function addCloseButtonEventListener(tripNum) {
      const closeButton = document.querySelector(`[data-tripnum="${tripNum}"]`);
      closeButton.addEventListener('click', function() {
          const tripNumToRemove = this.getAttribute('data-tripnum');
          removeTrip(tripNumToRemove);
      });
  }

  function removeTrip(tripNumToRemove) {
      const tripToRemove = document.getElementById('trip_' + tripNumToRemove);
      tripToRemove.remove();

      // Update the totalTrips counter and enable the "Add Trip" button after removing a trip
      totalTrips--;
      document.getElementById('add_trip_button').disabled = totalTrips === maxTrips - 1;

      // Remove the trip details from the form submission data
      const departureInput = document.querySelector(`input[name="departure_from_${tripNumToRemove}"]`);
      const arrivalInput = document.querySelector(`input[name="arrival_to_${tripNumToRemove}"]`);
      const departureDateInput = document.querySelector(`input[name="departure_date_${tripNumToRemove}"]`);

      if (departureInput) departureInput.remove();
      if (arrivalInput) arrivalInput.remove();
      if (departureDateInput) departureDateInput.remove();
  }
    


  function collectTripDetails($tripCout) {
    const tripDetails = [];
   
    for (let i = 1; i <= $tripCout; i++) {
      const departureInput = document.querySelector(`input[name="departure_from_${i}"]`);
      const arrivalInput = document.querySelector(`input[name="arrival_to_${i}"]`);
      const departureDateInput = document.querySelector(`input[name="departure_date_${i}"]`);

      const departureFromValue = departureInput.value;
      const arrivalToValue = arrivalInput.value;
      const departureDateValue = departureDateInput.value;
      // if (departureInput && arrivalInput && departureDateInput) {
      // tripDetails.push({
      //   departureFrom: departureInput.value,
      //   arrivalTo: arrivalInput.value,
      //   departureDate: departureDateInput.value
      // });
      const tripDetailObj = {
        [`departureFrom${i}`]: departureFromValue,
        [`arrivalTo${i}`]: arrivalToValue,
        [`departureDate${i}`]: departureDateValue,
      };
      // }
      tripDetails.push(tripDetailObj);
    }
   
    console.log(tripDetails)
    return tripDetails;
  }


 
  //news letter script

  $('#newsletter-subscribe').submit(function (event) {
    event.preventDefault();
    // Validate form data
    var email = $('#newsletter-email').val();
    var name = $('#newsletter-name').val();
    emailReg = /^[^\s@]+@[^\s@]+\.(?!con$)[^\s@]+$/;
    valid = true;

    if (email == '' || !emailReg.test(email)) {

      $('#newsletter-email').after('<sapan class="text-danger fs-12 position-absolute" >Enter valid Email Id.</span>')
      valid = false;
    }
    if (name == '') {
      $('#newsletter-name').after('<sapan class="text-danger fs-12 position-absolute" >Enter your Name.</span>')
      valid = false;
    }

    if (!valid) {
      return false;
    }


    // Set up form data for submission
    $('#login_message').text("");
    var formData = new FormData(this);
    // Submit form via AJAX
    $.ajax({
      url: 'newsletter-script.php',
      type: 'post',
      data: formData,
      processData: false,
      contentType: false,
      success: function (response) {
        // alert(response);
        $('#errorMessage').text(response);
        $('#errorModal').modal('show');
        $(".close").click(function(){
          $(this).parents('.modal').modal('hide');
        });
       


      },
      error: function () {
        alert('Error submitting form');
      }
    });
  });


  //==============================
  //----------------------------Contact Us Submit-------------------
  

$('#contactus-submit').submit(function (event) {
  event.preventDefault();
  // Validate form data
  var email = $('#contact-email').val();
  var name = $('#contact-name').val();
  var subject = $('#contact-subject').val();
  var message = $('#contact-message').val();
  emailReg = /^[^\s@]+@[^\s@]+\.(?!con$)[^\s@]+$/;
  valid = true;

  if (email == '' || !emailReg.test(email)) {

    $('#contact-email').after('<sapan class="position-absolute text-danger fs-12" >Enter valid Email Id.</span>')
    valid = false;
  }
  if (name == '') {
    $('#contact-name').after('<sapan class="position-absolute text-danger fs-12" >Enter your Password.</span>')
    valid = false;
  }
  if (subject == '') {
    $('#contact-subject').after('<sapan class="position-absolute text-danger fs-12" >Enter Subject.</span>')
    valid = false;
  }
  if (message == '') {
    $('#contact-message').after('<sapan class="position-absolute text-danger fs-12" >Enter Message.</span>')
    valid = false;
  }

  if (!valid) {
    return false;
  }



  // Set up form data for submission
  $('#login_message').text("");
  var formData = new FormData(this);
  // Submit form via AJAX
  $.ajax({
    url: 'contactus-script.php',
    type: 'post',
    data: formData,
    processData: false,
    contentType: false,
    success: function (response) {
      // alert(response);
      $('#errorMessage').text(response);
      $('#errorModal').modal('show');
      $(".close").click(function(){
        $(this).parents('.modal').modal('hide');
        location.reload();
      });
    },
    error: function () {
      alert('Error submitting form');
    }
  });
});








});