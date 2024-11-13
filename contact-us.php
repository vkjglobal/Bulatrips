<?php
session_start();
require_once("includes/header.php");
?>
   
    <section class="pt-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <h1 class="title-typ4 w-100 fw-400 mb-5 text-center"><strong class="fw-500">Contact Us</strong></h1>
                    <div class="row">
                        <div class="col-12">
                            <div class="register-form">
                                <form method="post" action="" class="reg-wrp" id="contactus-submit">
                                    <div class="form-row on reg-box">
                                        <div class="form-group mb-4 col-md-6">
                                            <!-- <input type="text" class="form-control" id="" placeholder=" Name" required> -->
                                            <input type="text" class="form-control" name="contact-name" id="contact-name" placeholder="Name" >

                                            <!-- <span class="position-absolute text-danger fs-12">Please fill out this filed</span> -->
                                        </div>
                                        <!-- <div class="form-group mb-4 col-md-6">
                                        </div> -->
                                        <div class="form-group mb-4 col-md-6">
                                            <input type="email" class="form-control" name="contact-email" id="contact-email" placeholder="Email" >
                                        </div>
                                        <div class="form-group mb-4 col-md-6">
                                            <input type="text" class="form-control" name="contact-subject" id="contact-subject" placeholder="Subject" >
                                        </div>
                                        
                                        <div class="form-group mb-4 col-12">
                                            <textarea name="contact-message" name="contact-message" id="contact-message" cols="30" rows="4" class="form-control h-100" placeholder="Message" ></textarea></textarea>
                                        </div>
                                        <!-- <div class="form-group chkbx col-12 mt-3">
                                            <input type="checkbox" id="logintab-agent" checked>
                                            <label for="logintab-agent" class="fz-13 fw-400">
                                                <span class="chk-txt fs-13 fw-400">I Agree to <a href="">terms & conditions</a></span>
                                            </label>
                                        </div> -->
                                        <!-- <div class="g-recaptcha" data-sitekey="6LfjocInAAAAANQKNxDixe7ZiO3R4Y6s-OS5Y0Fd"></div> -->
                                        <div class="col-md-4 mx-auto mt-4">

                                            <button type="submit" name="contactus-submit" id="contactus-submit-button" class="btn border-radius-5 btn-typ1 fs-15 fw-400 w-100">Send</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Modal -->

    
  
    <?php
        require_once("includes/footer.php");
    ?>
    <script>
        $(document).ready(function(){
            /******************TAB**************/
            $('[name=tab]').each(function(i,d){
                var p = $(this).prop('checked');
                //   console.log(p);
                if(p){
                    $('.reg-box').eq(i)
                    .addClass('on');
                }    
            });  

            $('[name=tab]').on('change', function(){
                var p = $(this).prop('checked');
                
                // $(type).index(this) == nth-of-type
                var i = $('[name=tab]').index(this);
                
                $('.reg-box').removeClass('on');
                $('.reg-box').eq(i).addClass('on');
            });
            /************************************/
            /*************Image Upload with Preview***************/
            // $("input[type=file]").change(function (e) {
            //     $(this).parents(".uploadFile").find(".filename").text(e.target.files[0].name);
            // });
            /*****************************************************/
        });
        $(".text-below-button").click(function(){
            $(this).parents('.modal').modal('hide');
        });
        $(".forgot-passward > button").click(function(){
            $(this).parents('.modal').modal('hide');
        });
        
        $('#FlightSearchLoading').modal({
            show:false
        })
        /**************Scroll To Top*****************/
        $(window).on('scroll',function() {
            if (window.scrollY > window.innerHeight) {
                $('#scrollToTop').addClass('active')
            } else {
                $('#scrollToTop').removeClass('active')
            }
        })

        $('#scrollToTop').on('click',function() {
            $("html, body").animate({ scrollTop: 0 }, 500);
        })
        /**********************************************/
        function readURL(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();

                reader.onload = function (e) {
                    $('#pro-pic')
                        .attr('src', e.target.result);
                };

                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
