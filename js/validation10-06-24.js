// profile password settings validation
$('#user_password_form').submit(function (event) {
    event.preventDefault();   
    var current_pwd = $("#user-current-password").val();
    var new_pwd = $("#user-new-password").val();
    var confirm_pwd = $("#user-new-varify-password").val();
   
    valid = true;
    
    $(".errortext").remove();
    if(current_pwd == '') {
        $('#user-current-password').after('<span class="errortext" style="color:red">Current password cannot be blank.</span>');	       
        valid = false;
    }
    if(new_pwd == '') {
        $('#user-new-password').after('<span class="errortext" style="color:red">New password cannot be blank.</span>');	       
        valid = false;
    }
    if(confirm_pwd == '') {
        $('#user-new-varify-password').after('<span class="errortext" style="color:red">Please confirm new password.</span>');       
        valid = false;
    }                  
    if (new_pwd !== confirm_pwd) {
        alert("New passwords do not match.");
        valid = false;
    }
    if( !valid ){       
    return valid;
    }	
    var formData = new FormData();
    formData.append('currentPassword', current_pwd);
    formData.append('newPassword', new_pwd);

    $.ajax({
        url: 'change-password-script.php',
        type: 'POST',
        // data: dataString,
        data: formData,
        contentType: false,
        processData: false,
        success: function (response) {
            alert(response);
            if (response == 'error') {
            alert("Current password is incorrect");
            } 
            if(response == 'success') {
            //  alert("Password changed successfully.");
            window.location.href = 'logout.php';
            
            }

        },
        error: function () {
            alert('Error submitting form');
        }
    });
});
// Profile update validation

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

    // validation
    valid = true;
    $(".errortext").remove();
    if(firstname == '') {
        $('#fname').after('<span class="errortext" style="color:red">first name cannot be blank.</span>');	       
        valid = false;
    }
    if(lastname == '') {
        $('#lname').after('<span class="errortext" style="color:red">last name cannot be blank.</span>');	       
        valid = false;
    }
    if(phone == '') {
        $('#mobile').after('<span class="errortext" style="color:red">contact number cannot be blank.</span>');	       
        valid = false;
    }
    if(address == '') {
        $('#address').after('<span class="errortext" style="color:red">address cannot be blank.</span>');	       
        valid = false;
    }
    if(country == '') {
        $('#endusercountry').after('<span class="errortext" style="color:red">country cannot be blank.</span>');	       
        valid = false;
    }
    if(state == '') {
        $('#enduserstate').after('<span class="errortext" style="color:red">state cannot be blank.</span>');	       
        valid = false;
    }
    if(city == '') {
        $('#endusercity').after('<span class="errortext" style="color:red">city cannot be blank.</span>');	       
        valid = false;
    }
    if(zipcode == '') {
        $('#zipcode').after('<span class="errortext" style="color:red">zipcode cannot be blank.</span>');	       
        valid = false;
    }
    if( !valid ){       
        return valid;
    }else{

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

        $.ajax({
            url: 'enduser-update.php',
            type: 'post',
            // data: dataString,
            data: formData,
            contentType: false,
            processData: false,
            success: function (response) {
                // alert(response);
                if (response == 'error') {
                    // alert("Data not updated");
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
    
    }
});

$('#review_valid').click(function (event) {
    event.preventDefault();

    var description = $('#description').val();
    var title = $('#title').val();
    var errorContainer = $('.error-container'); // Fetching the jQuery object for error container
    
    // Clear previous errors
    errorContainer.empty();

    // Validation
    var valid = true;
    var rating = $('.star-rating input[name="rating"]:checked').val(); // Fetch the selected rating value

    if (!rating) {
        errorContainer.append('<span class="errortext" style="color:red">Please select a star rating.</span>');
        valid = false;
    }

    if (description === '') {
        errorContainer.append('<span class="error-text" style="color:red">Description cannot be blank.</span>');
        valid = false;
    }

    if (title === '') {
        errorContainer.append('<span class="error-text" style="color:red">Title cannot be blank.</span>');
        valid = false;
    }

    // var fileInput = $('#review_pic').val(); // Get the file input element
    var fileInput = document.getElementById('review_pic').files[0];
    // alert(fileInput);
    if (!valid) {
        return false;
    }

    // Rest of your code for form submission via AJAX
    var formData = new FormData();
    formData.append('rating', rating); // Append the adjusted rating value
    formData.append('description', description); // Append the adjusted description value
    formData.append('title', title); // Append the adjusted title value
    // formData.append('review_pic', fileInput); // Append the image file
    formData.append('review_pic', fileInput);
// alert(formdata);

    $.ajax({
        url: 'review_add.php',
        type: 'POST',
        data: formData,
        contentType: false,
        processData: false,
        success: function (response) {
            $('#responseMessage').html(response);
            $('#responseModal').modal('show');
            // if (response == 'error') {
            //     alert("Error obtain adding Review");
            // } else {
            //     $('#alert-container').html(`
            //         <div class="alert alert-success alert-dismissible fade show" role="alert">
            //         Review Addeded successfully!
            //         <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            //         <span aria-hidden="true">&times;</span>
            //         </div>
            //         `);
            //     $('html, body').animate({ scrollTop: 0 }, 'slow');
            //     window.location.href = 'user-dashboard.php';
            // }
        },
        error: function () {
            $('#responseMessage').html('An error occurred while adding the review.');
            $('#responseModal').modal('show');
        //   alert ('Error');
        }
    });
});

$('#review_delete').on('click', function() {
    // Retrieve the userID from the data attribute
    var userID = $(this).data('userid');
    console.log('UserID:', userID);  // Log the userID to ensure it's being retrieved correctly

    // Perform the AJAX request
    $.ajax({
        type: 'POST',
        url: 'delete-review.php',
        data: { userID: userID },
        success: function(response) {
            console.log('Server Response:', response);  // Log the response from the server
            alert(response);
            // $('#responseMessage').html(response);
            // $('#responseModal').modal('show');
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error:', error);  // Log any AJAX errors
            // $('#responseMessage').html(error);
            // $('#responseModal').modal('show');
        }
    });
});

$('#requestForm').submit(function (event) {
    event.preventDefault();

    var amount = $('#requestAmount').val();
    var id = $('.uid').val();

    // validation
    var valid = true;
    $(".errortext").remove();
    if (amount === '') {
        $('#requestAmount').after('<span class="errortext" style="color:red">Request amount cannot be blank.</span>');	       
        valid = false;
    }
    if (!valid) {
        return valid;
    }

    var formData = new FormData();
    formData.append('Amount', amount);
    formData.append('userId', id);

    // console.log('Form data:', [...formData.entries()]);

    $.ajax({
        url: 'credit_request.php',
        type: 'POST',
        data: formData,
        contentType: false,
        processData: false,
        success: function (response) {
            console.log('Server response:', response);
            if (response.trim() === 'error') {
                showMessageModal("Error", "Error in request. Please try again later!");
            } else if (response.trim() === 'success') {
                showMessageModal("Success", "Request submitted successfully.");
                // window.location.href = 'agent-dashboard.php'; 
            } else if (response.trim() === 'already_submitted') {
                showMessageModal("Info", "Request already submitted. Please wait for request approval.", false);
            } else {
                showMessageModal("Unexpected Response", "Unexpected response: " + response);
            }
        },
        error: function (jqXHR, textStatus, errorThrown) {
            console.error("Error submitting form:", textStatus, errorThrown);
            showMessageModal("Error", 'Error submitting form: ' + textStatus + ' - ' + errorThrown);
        }
    });
});

function showMessageModal(title, message) {
    $('#messageModalLabel').text(title);
    $('#messageModalBody').text(message);
    $('#messageModal').modal('show');
}

// $('#paymentForm').submit(function(event) {
//     const cardHolderName = $('#custName').val();
//     const cardType = $('#cardType').val();
//     const cardNumber = $('#cardNo').val();
//     const cardExp = $('#cardExp').val();
//     const cvv = $('#cvv').val();
//     const checkTerms = $('#checkTerms').is(':checked');

//     if (!cardHolderName) {
//         alert('Card Holder Name is required');
//         event.preventDefault();
//         return false;
//     }

//     if (!cardType) {
//         alert('Card Type is required');
//         event.preventDefault();
//         return false;
//     }

//     if (!cardNumber || isNaN(cardNumber) || cardNumber.length < 14 || cardNumber.length > 16) {
//         alert('Card Number is invalid');
//         event.preventDefault();
//         return false;
//     }

//     if (!cardExp || isNaN(cardExp) || cardExp.length !== 2) {
//         alert('Card Expiry is invalid');
//         event.preventDefault();
//         return false;
//     }

//     if (!cvv || isNaN(cvv) || cvv.length < 3 || cvv.length > 4) {
//         alert('CVV is invalid');
//         event.preventDefault();
//         return false;
//     }

//     if (!checkTerms) {
//         alert('You must agree to the terms and conditions');
//         event.preventDefault();
//         return false;
//     }

//     return true;
// });

$('#cardNo').on('input', function() {
    let cardNumber = $(this).val().replace(/\s+/g, ''); // Remove all spaces
    if (cardNumber.length < 14 || cardNumber.length > 16) {
        cardNumber = cardNumber.substring(0, 15);
    }
    cardNumber = cardNumber.match(/.{1,4}/g).join(' ');
    $(this).val(cardNumber);
});

// Format Expiry Month field
$('#cardExpmon').on('input', function() {
    let month = $(this).val();
    if (month.length === 1 && month !== '0') {
        month = '0' + month;
    }
    $(this).val(month);
});

// Ensure Expiry Month is between 01 and 12
$('#cardExpmon').on('blur', function() {
    let month = parseInt($(this).val(), 10);
    if (month < 1 || month > 12) {
        $(this).val('');
    }
});

$('#paymentForm').submit(function(event) {
    event.preventDefault();
    let errorMsg = '';
    const cardHolderName = $('#custName').val();
    const cardType = $('#cardType').val();
    const cardNumber = $('#cardNo').val().replace(/\s+/g, ''); // Remove spaces for validation
    const cardExpy = $('#cardExpyear').val();
    const cardExpm = $('#cardExpmon').val();
    const cvv = $('#cvv').val();
    const checkTerms = $('#checkTerms').is(':checked');

    if (!cardHolderName) {
        errorMsg = 'Card Holder Name is required';
    } else if (!cardType) {
        errorMsg = 'Card Type is required';
    } else if (!cardNumber || isNaN(cardNumber) || cardNumber.length < 14 || cardNumber.length > 16) {
        errorMsg = 'Card Number is invalid';
    } else if (!cardExpy || isNaN(cardExpy) || cardExpy.length !== 4) {
        errorMsg = 'Card Expiry Year is invalid';
    } else if (!cardExpm || isNaN(cardExpm) || parseInt(cardExpm) < 1 || parseInt(cardExpm) > 12) {
        errorMsg = 'Card Expiry Month is invalid';
    } else if (!cvv || isNaN(cvv) || cvv.length < 3 || cvv.length > 4) {
        errorMsg = 'CVV is invalid';
    } else if (!checkTerms) {
        errorMsg = 'You must agree to the terms and conditions';
    }

    const formData = {
        Name: cardHolderName,
        cardType: cardType,
        cardNumber: cardNumber,
        cardExpy: cardExpy,
        cardExpm: cardExpm,
        cvv: cvv
    };

    if (errorMsg) {
        $('#errorModalBody').text(errorMsg);
        $('#errorModal').modal('show');
    } else {
        $.ajax({
            url: 'windcave.php',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(formData),
            success: function(response) {
                // console.log(response);
                if (response.status === 'success') {
                    alert("Payment Successful");
                    // console.log("Request ID:", response.requestId);
                    // console.log("Timestamp:", response.timestamp);
                    // call booking api and confirmation page here.
                    // Call the booking API here
                    $.ajax({
                          url: 'booking-script.php',
                          type: 'post',
                          data: {
                                requestId: response.requestId,
                                timestamp: response.timestamp
                            },
                          processData: false,
                          contentType: false,
                          dataType: 'json',
                          success: function (response) {
                            // console.log(response);
                            // alert(response);
                            if (response.errors.length > 0) {
                            //   alert(response.errors);
                              $('#errorMessage').text(response.errors);
                                  $('#errorModal').modal('show');
                                  $(".close").click(function(){
                                    $(this).parents('.modal').modal('hide');
                                  });
                            }
                            if (response.faretype == "webfare") {
                              if (response.orderstatus) {
                                if (response.orderstatus == "order success") {
                                  const redirectURL = 'confirmation.php?bookingid=' + encodeURIComponent(response.bookingid);
                                  window.location.href = redirectURL;
                  
                                } else {
                                //   alert(response.orderstatus['Message']);
                                  $('#errorMessage').text(response.orderstatus['Message']);
                                  $('#errorModal').modal('show');
                                  $(".close").click(function(){
                                    $(this).parents('.modal').modal('hide');
                                  });
                                }
                              }
                  
                              // const redirectURL = 'my-booking-step3.php';
                              // window.location.href = redirectURL;
                            } else {
                              if (response.orderstatus && response.ticketstatus) {
                                if (response.orderstatus == "order success" && response.ticketstatus == "ticket sucess") {
                                  const redirectURL = 'confirmation.php?bookingid=' + encodeURIComponent(response.bookingid);
                                  window.location.href = redirectURL;
                  
                                } else if (response.orderstatus != "order success") {
                                  // alert(response.orderstatus['Message']);
                                  $('#errorMessage').text(response.orderstatus['Message']);
                                  $('#errorModal').modal('show');
                                  $(".close").click(function(){
                                    $(this).parents('.modal').modal('hide');
                                  });
                                } else if (response.ticketstatus != "ticket sucess") {
                                  // alert(response.ticketstatus['Message']);
                                  $('#errorMessage').text(response.orderstatus['Message']);
                                  $('#errorModal').modal('show');
                                  $(".close").click(function(){
                                    $(this).parents('.modal').modal('hide');
                                  });
                                }
                              }
                  
                            }
                  
                  
                  
                          },
                          error: function () {
                            // alert('Error submitting form');
                            console.log();
                          }
                        });
                    // $.ajax({
                    //     url: 'booking_script.php', // Replace with your booking API URL
                    //     method: 'POST',
                    //     data: {
                    //         requestId: response.requestId,
                    //         timestamp: response.timestamp
                    //     },
                    //     success: function(bookingResponse) {
                    //         // Handle the booking API response
                    //         if (bookingResponse.status === 'success') {
                    //             // Redirect to the confirmation page
                    //             window.location.href = "confirmation.php";
                    //         } else {
                    //             // Handle the booking error
                    //             alert("Booking failed: " + bookingResponse.message);
                    //         }
                    //     },
                    //     error: function(jqXHR, textStatus, errorThrown) {
                    //         // Handle AJAX errors
                    //         alert("AJAX error: " + textStatus);
                    //     }
                    // });
                    // window.location.href = "confirmation.php";
                } else {
                    // alert("Error: " + (response.errors ? response.errors.join(", ") : response.message));
                    // console.error("HTTP Code:", response.httpCode);
                    // console.error("Response:", response.response);
                    $('#errorModalBody').text("Error: " + (response.errors ? response.errors.join(", ") : response.message));
                    $('#errorModal').modal('show');
                }
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error:", error);
                console.error("Status:", status);
                console.error("XHR:", xhr);
                try {
                    let jsonResponse = JSON.parse(xhr.responseText);
                    console.error("Response:", jsonResponse);
                } catch (e) {
                    console.error("Failed to parse response as JSON:", xhr.responseText);
                }
            }
        });
    }

    return false;
});

// $('#confirm').submit(function(event) {
//     event.preventDefault();
// alert('helo'); return false;
//     var formData = new FormData(this);
//     $.ajax({
//       url: 'booking-script.php',
//       type: 'post',
//       data: formData,
//       processData: false,
//       contentType: false,
//       dataType: 'json',
//       success: function (response) {
//         // window.location.href = 'confirmation.php?bookingid=' + encodeURIComponent(response.bookingid);
//         // alert("Booking successfully Completed");
//         console.log(response);
//           alert(response);return false;
//           if (response.errors.length > 0) {
//             alert(response.errors);
//           }
//           if (response.faretype == "webfare") {
//             if (response.orderstatus) {
//               if (response.orderstatus == "order success") {
//                 const redirectURL = 'confirmation.php?bookingid=' + encodeURIComponent(response.bookingid);
//                 window.location.href = redirectURL;

//               } else {
//                 alert(response.orderstatus['Message']);
//               }
//             }

//             // const redirectURL = 'my-booking-step3.php';
//             // window.location.href = redirectURL;
//           } else {
//             if (response.orderstatus && response.ticketstatus) {
//               if (response.orderstatus == "order success" && response.ticketstatus == "ticket sucess") {
//                 const redirectURL = 'confirmation.php?bookingid=' + encodeURIComponent(response.bookingid);
//                 window.location.href = redirectURL;

//               } else if (response.orderstatus != "order success") {
//                 // alert(response.orderstatus['Message']);
//                 $('#errorMessage').text(response.orderstatus['Message']);
//                 $('#errorModal').modal('show');
//                 $(".close").click(function(){
//                   $(this).parents('.modal').modal('hide');
//                 });
//               } else if (response.ticketstatus != "ticket sucess") {
//                 // alert(response.ticketstatus['Message']);
//                 $('#errorMessage').text(response.orderstatus['Message']);
//                 $('#errorModal').modal('show');
//                 $(".close").click(function(){
//                   $(this).parents('.modal').modal('hide');
//                 });
//               }
//             }

//           }

//       },
//       error: function () {
//         // alert('Error submitting form');
//         console.log();
//       }
//     });
// });

$('#payNow').click(function(event){
    event.preventDefault();
    
    window.location.href='wind_payment.php';
    
});
$('#confirm').click(function(event) {
    event.preventDefault();

    var formData = $('#bookingForm').serialize();

    $.ajax({
        url: 'booking-script.php',
        type: 'POST',
        data: formData,
        dataType: 'json',
        success: function(response) {
            console.log(response);
            // alert(response);

            if (response.errors && response.errors.length > 0) {
                alert(response.errors.join('\n'));
            } else {
                if (response.faretype == "webfare") {
                    if (response.orderstatus) {
                        if (response.orderstatus == "order success") {
                            const redirectURL = 'confirmation.php?bookingid=' + encodeURIComponent(response.bookingid);
                            window.location.href = redirectURL;
                        } else {
                            alert(response.orderstatus['Message']);
                        }
                    }
                } else {
                    if (response.orderstatus && response.ticketstatus) {
                        if (response.orderstatus == "order success" && response.ticketstatus == "ticket success") {
                            const redirectURL = 'confirmation.php?bookingid=' + encodeURIComponent(response.bookingid);
                            window.location.href = redirectURL;
                        } else if (response.orderstatus != "order success") {
                            $('#errorMessage').text(response.orderstatus['Message']);
                            $('#errorModal').modal('show');
                            $(".close").click(function() {
                                $(this).parents('.modal').modal('hide');
                            });
                        } else if (response.ticketstatus != "ticket success") {
                            $('#errorMessage').text(response.ticketstatus['Message']);
                            $('#errorModal').modal('show');
                            $(".close").click(function() {
                                $(this).parents('.modal').modal('hide');
                            });
                        }
                    }
                }
            }
        },
        error: function(xhr, status, error) {
            console.log('Error submitting form');
            console.log('Status:', status);
            console.log('Error:', error);
            console.log('Response Text:', xhr.responseText);
        }
    });
});
