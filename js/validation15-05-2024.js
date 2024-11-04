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
            // alert(response);return false;
            if (response == 'error') {
                alert("Current password is incorrect");
            } 
            if(response == 'success') {
             alert("Password changed successfully.");
            // window.location.href = 'logout.php';
            
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

// review validation

// $('#review_valid').click(function (event) {
//     event.preventDefault();

//     var description = $('#description').val();
//     var errorContainer = $('.error-container'); // Fetching the jQuery object for error container
    
//     // Clear previous errors
//     errorContainer.empty();

//     // Validation
//     var valid = true;
//     var rating = $('.star-rating input[name="rating"]:checked').val(); // Fetch the selected rating value

//     if (!rating) {
//         errorContainer.append('<span class="errortext" style="color:red">Please select a star rating.</span>');
//         valid = false;
//     }

//     if (description === '') {
//         errorContainer.append('<span class="error-text" style="color:red">Description cannot be blank.</span>');
//         valid = false;
//     }

//     var fileInput = $('#p-image')[0].files[0]; // Get the file from the input

//     if (!fileInput) {
//         errorContainer.append('<span class="error-text" style="color:red">Please upload an image.</span>');
//         valid = false;
//     }

//     // If any validation failed, prevent form submission
//     if (!valid) {
//         return false;
//     }

//     // Rest of your code for form submission via AJAX
//     var formData = new FormData();
//     formData.append('rating', rating); // Append the adjusted rating value
//     formData.append('description', description); // Append the adjusted description value
//     formData.append('p-image', fileInput); // Append the image file

//     $.ajax({
//         url: 'review_add.php',
//         type: 'POST',
//         data: formData,
//         contentType: false,
//         processData: false,
//         success: function (response) {
//             alert(response);
//         },
//         error: function () {
//            alert ('error');
//         }
//     });
// });

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
// console.log(formData);
    $.ajax({
        url: 'review_add.php',
        type: 'POST',
        data: formData,
        contentType: false,
        processData: false,
        success: function (response) {
            // alert(response);return false;
            if (response == 'error') {
                alert("Error obtain adding Review");
            }else if(response == 'usuccess'){
               alert('Review Updated successfully!');
               // Redirect after 2 seconds
                setTimeout(function() {
                    window.location.href = 'user-dashboard.php';
                }, 2000);
            }else if(response == 'success') {
                alert('Review Added successfully!');
                // Redirect after 2 seconds
                setTimeout(function() {
                    window.location.href = 'user-dashboard.php';
                }, 2000);
            }else{
                alert(response);
            }
        },
        error: function () {
           alert ('error');
        }
    });
});



