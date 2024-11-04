<?php
    require_once("includes/header.php");
?>
    <section class="midbar-banner-inner detail-page-banner" style="background-image:url('images/banner10.jpg');">
        <div class="container d-flex align-items-end h-100">
            <h2>Booking</h2>
        </div>
    </section>
    <section>
        <div class="container">
            <div class="row">
                <div class="col-xl-9 col-lg-8 mb-4">
                    <h1 class="dark-blue-txt fw-700 mb-5">Booking Submission</h1>
                    <form class="book-now-form">
                        <div class="form-row border-radius-25 mb-xl-5 mb-4">
                            <div class="form-group col-md-6">
                                <label for="">First Name *</label>
                                <input type="text" class="form-control border-radius-100" id="">
                            </div>
                            <div class="form-group col-md-6">
                                <label for="">Last Name *</label>
                                <input type="text" class="form-control border-radius-100" id="">
                            </div>
                            <div class="form-group col-md-6">
                                <label for="">Email *</label>
                                <input type="email" class="form-control border-radius-100" id="">
                            </div>
                            <div class="form-group col-md-6">
                                <label for="">Phone *</label>
                                <input type="number" class="form-control border-radius-100" id="">
                            </div>
                            <div class="form-group col-md-6">
                                <label for="">Address Line 1</label>
                                <input type="text" class="form-control border-radius-100" id="">
                            </div>
                            <div class="form-group col-md-6">
                                <label for="">Address Line 2</label>
                                <input type="text" class="form-control border-radius-100" id="">
                            </div>
                            <div class="form-group col-md-6">
                                <label for="">City</label>
                                <input type="text" class="form-control border-radius-100" id="">
                            </div>
                            <div class="form-group col-md-6">
                                <label for="">State/Province/Region</label>
                                <input type="text" class="form-control border-radius-100" id="">
                            </div>
                            <div class="form-group col-md-6">
                                <label for="">ZIP code/Postal code</label>
                                <input type="text" class="form-control border-radius-100" id="">
                            </div>
                            <div class="form-group col-md-6">
                                <label for="">Country</label>
                                <input type="text" class="form-control border-radius-100" id="">
                            </div>
                            <div class="form-group col-md-12">
                                <label for="">Special Requirements</label>
                                <textarea class="form-control border-radius-25" name="" id="" cols="30" rows="10"></textarea>
                            </div>
                        </div>
                        <div class="form-row border-radius-25 booking-payment-sec mb-5">
                            <div class="form-group col-md-12">
                                <label for="">Name on card *</label>
                                <input type="text" class="form-control border-radius-100" id="">
                            </div>
                            <div class="form-group col-md-6">
                                <label for="">Card number *</label>
                                <input type="text" class="form-control border-radius-100" id="">
                            </div>
                            <div class="form-group col-md-6 d-flex align-items-end cards-icon-wrp">
                                <div class="form-control">
                                    <img src="images/cards-icon.png" alt="">
                                </div>
                            </div>
                            <div class="form-group col-md-3">
                                <label for="">Expiration date *</label>
                                <input type="text" class="form-control border-radius-100" id="" placeholder="MM">
                            </div>
                            <div class="form-group col-md-3 d-flex align-items-end">
                                <input type="text" class="form-control border-radius-100" id="" placeholder="YEAR">
                            </div>
                            <div class="form-group col-md-3 d-flex align-items-end">
                                <input type="text" class="form-control border-radius-100" id="" placeholder="CVV">
                            </div>
                            <div class="form-group col-md-3 d-flex align-items-end cards-icon-wrp">
                                <div class="form-control d-flex align-items-center">
                                    <img src="images/icon_ccv.png" alt="">
                                    <span class="fw-300">Last 3 Digit</span>
                                </div>
                            </div>
                            <hr class="d-block col-12 mt-5 mb-3" style="border-color: #888888; margin-bottom:0;">
                            <div class="col-12">
                                <strong class="fs-16 fw-700">Or checkout with Paypal</strong>
                                <p class="fw-300">
                                    Lorem ipsum dolor sit amet, vim id accusata sensibus, id ridens quaeque qui. Ne qui vocent ornatus molestie, reque fierent dissentiunt mel ea.
                                </p>
                            </div>
                        </div>
                        <strong class="d-block fs-18 fw-700">Cancellation policy</strong>
                        <div class="form-group chkbx">
                            <input type="checkbox" class="form-check-input" id="exampleCheck1">
                            <label class="form-check-label fw-300" for="exampleCheck1">I accept terms and conditions and general policy.</label>
                        </div>
                        <button type="submit" class="btn btn-typ1 col-md-6 m-auto pl-5 pr-5 border-radius-100 p-3">Submit</button>
                    </form>
                </div>
                <div class="col-xl-3 col-lg-4">
                    <form action="">
                        <div class="d-flex help-btn align-items-center border-radius-25 mb-3">
                            <span class="mr-2">
                                <svg width="40" height="40" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M14.7476 3.06918C15.8257 3.73322 16.2788 5.16286 15.81 6.405L13.607 12.1861C13.1929 13.2798 12.1617 13.9438 11.0758 13.8266L7.74779 13.4673C6.38846 17.6937 6.38846 22.3107 7.74779 26.5372L11.0758 26.1778C12.1617 26.0606 13.1929 26.7246 13.607 27.8184L15.81 33.5916C16.2866 34.8338 15.8335 36.2634 14.7476 36.9274L10.3415 39.6461C9.37274 40.2476 8.15403 40.068 7.36499 39.2164C-2.455 28.5996 -2.455 11.397 7.36499 0.780187C8.15403 -0.071347 9.38055 -0.243216 10.3493 0.350514L14.7554 3.06918H14.7476ZM31.8095 2.6395C36.8093 6.76437 39.9967 13.0142 39.9967 19.9983C39.9967 26.9825 36.8093 33.2323 31.8173 37.3571C31.0205 38.0133 29.8408 37.904 29.1768 37.1071C28.5127 36.3103 28.6299 35.1306 29.4268 34.4666C33.5985 31.0214 36.2469 25.8184 36.2469 19.9983C36.2469 14.1782 33.5985 8.97523 29.4268 5.53003C28.6299 4.8738 28.5127 3.68634 29.1768 2.88949C29.8408 2.09264 31.0205 1.97546 31.8173 2.6395H31.8095ZM26.7784 8.21744C30.2627 10.9674 32.497 15.2172 32.497 19.9983C32.497 24.7794 30.2627 29.0293 26.7862 31.7792C25.9738 32.4198 24.7941 32.2792 24.1535 31.4667C23.5129 30.6542 23.6535 29.4746 24.466 28.834C27.0753 26.7715 28.7471 23.5763 28.7471 19.9983C28.7471 16.4203 27.0753 13.2251 24.466 11.1627C23.6535 10.5221 23.5129 9.34241 24.1535 8.52993C24.7941 7.71746 25.9738 7.57684 26.7862 8.21744H26.7784ZM21.6692 13.7641C23.677 15.1157 24.9972 17.4046 24.9972 19.9983C24.9972 22.592 23.677 24.881 21.677 26.2247C20.8177 26.8028 19.6537 26.5762 19.0755 25.7169C18.4974 24.8575 18.724 23.6935 19.5833 23.1154C20.5911 22.4357 21.2474 21.2951 21.2474 19.9983C21.2474 18.7015 20.5911 17.5609 19.5833 16.8812C18.724 16.3031 18.4974 15.1391 19.0755 14.2797C19.6537 13.4204 20.8177 13.1938 21.677 13.7719L21.6692 13.7641Z" fill="#6A6868"/>
                                </svg>
                            </span>
                            <div class="d-flex flex-column align-items-baseline">
                                <span class="fs-14 fw-400">HELP AND SUPPORT</span>
                                <a href="tel:+55 123 987 00" class="light-blue-txt fw-700">+55 123 987 00</a>
                                <span class="fs-14 fw-400">Monday to Friday 9.00am - 7.30pm</span>
                            </div>
                        </div>
                        <div class="right-blue-box light-blue-bg border-radius-25">
                            <strong class="title-typ1 d-block text-center fw-700 white-txt mb-3">ENQUIRY</strong>
                            <p class="text-center fw-400 white-txt">Abu Dhabi Fully Loaded - Buy 
                                1 Get 1 Free - Winter
                            </p>
                            <div class="row">
                                <div class="col-lg-12 col-md-6">
                                    <div class="white-border mb-4">
                                        <img src="images/img27.png" alt="" class="w-100">
                                    </div>
                                </div>
                                <div class="col-lg-12 col-md-6">
                                    <ul class="summery white-txt fw-500 mb-4">
                                        <li>Tour type Daily Tour</li>
                                        <li>Departure date 18/11/2022</li>
                                        <li>Duration 2-3 hours</li>
                                        <li>Number of Adult1</li>
                                    </ul>
                                    <div class="input-group apply-coupon mb-5">
                                        <input type="text" class="form-control" id="" placeholder="Coupon Code">
                                        <div class="input-group-append">
                                            <button class="btn">Apply</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-row price-summery white-txt">
                                <div class="col-6 fw-500 mb-2">Adult Price</div>
                                <div class="col-6 fw-500 mb-2">Rs.1.650</div>
                                <div class="col-6 fw-500 mb-2">Subtotal</div>
                                <div class="col-6 fw-500 mb-2">Rs.1.650</div>
                                <div class="col-6 fw-500 mb-2">Tax</div>
                                <div class="col-6 fw-500 mb-2">0%</div>
                                <div class="col-6 total-price fw-600">Pay Amount</div>
                                <div class="col-6 total-price fw-600">Rs. 37 156</div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
    <!-- Modal -->
    <!--  Login Modal -->
    <?php
        require_once("includes/login-modal.php");
    ?>
    <!--  forgot Modal -->
     <?php
        require_once("includes/forgot-modal.php");
    ?>
    <div class="modal flight-search-loading" id="FlightSearchLoading" tabindex="-1" role="dialog" aria-labelledby="ForgotPasswordModalTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-body">
                    <div class="row justify-content-center">
                        <div class="col-12">
                            <form>
                                <div class="fs-16 fw-300 text-center mb-4">Please wait are searching for the cheapest fare for flights</div>
                                <div class="form-row flight-direction align-items-center justify-content-center mb-4">
                                    <strong class="col-md-5 text-md-right text-center mb-md-0 mb-2">Kochi</strong>
                                    <div class="col-md-1 d-flex flex-md-column flex-column-reverse align-items-center direction-icon">
                                        <span class="oneway d-flex">
                                            <svg width="29" height="12" viewBox="0 0 29 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M29 6L19 0.226497V11.7735L29 6ZM0 7L20 7V5L0 5L0 7Z" fill="#4756CB"/>
                                            </svg>
                                        </span>
                                        <span class="return d-flex">
                                            <svg width="29" height="12" viewBox="0 0 29 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M0 6L10 11.7735L10 0.226497L0 6ZM9 7L29 7V5L9 5V7Z" fill="#4756CB"/>
                                            </svg>    
                                        </span>
                                    </div>
                                    <strong class="col-md-5 text-md-left text-center mt-md-0 mt-2">Dubai</strong>
                                </div>
                                <div class="progress mb-5">
                                    <div class="progress-bar" role="progressbar" style="width: 75%" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <div class="row justify-content-center mb-5">
                                    <div class="col-lg-8 col-md-10">
                                        <div class="row justify-content-between">
                                            <div class="col-4 justify-content-center">
                                                <div class="mb-4 d-flex justify-content-center">
                                                    <svg width="60" height="39" viewBox="0 0 60 39" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <path d="M47.0925 0.984076C44.6576 2.07045 40.874 4.16828 36.6034 6.60326L16.1495 3.75621C15.0017 3.59067 13.8318 3.67241 12.7181 3.99596L8.27894 5.25466C7.90433 5.25466 7.90433 5.62927 8.27894 5.77912L25.3613 13.2714C19.5922 16.7178 14.46 19.8646 12.2124 21.2881C11.8113 21.54 11.3596 21.7002 10.8894 21.7572C10.4192 21.8142 9.9423 21.7666 9.49268 21.6177L5.01981 20.2017C4.30954 19.9484 3.53311 19.951 2.82458 20.2092L0 21.363L9.44024 29.2299C9.91481 29.628 10.4921 29.8842 11.1057 29.969C11.7193 30.0538 12.3444 29.9638 12.9091 29.7094C17.4195 27.694 29.2947 22.2246 38.1356 17.6169C57.7653 7.20265 59.938 5.4045 59.938 3.75621C59.938 1.17138 52.4458 -1.48837 47.0888 0.984076H47.0925Z" fill="#969696"/>
                                                        <line y1="38" x2="60" y2="38" stroke="#969696" stroke-width="2"/>
                                                    </svg>    
                                                </div>
                                                <div class="trvl-dtls text-center fw-300">
                                                    <div class="label">Depart</div>
                                                    <div class="date">
                                                        <strong class="fw-500">11</strong>
                                                        <div>
                                                            Nov, 2022 <br>
                                                            Friday
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-4 justify-content-center">
                                                <div class="mb-4 d-flex justify-content-center">
                                                    <svg width="60" height="39" viewBox="0 0 60 39" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <path d="M13.5678 0.984076C15.9499 2.07045 19.6514 4.16828 23.8292 6.60326L43.8389 3.75621C44.9618 3.59067 46.1063 3.67241 47.1958 3.99596L51.5385 5.25466C51.905 5.25466 51.905 5.62927 51.5385 5.77912L34.8272 13.2714C40.4709 16.7178 45.4917 19.8646 47.6905 21.2881C48.0829 21.54 48.5248 21.7002 48.9848 21.7572C49.4447 21.8142 49.9113 21.7666 50.3512 21.6177L54.7269 20.2017C55.4217 19.9484 56.1813 19.951 56.8745 20.2092L59.6377 21.363L50.4025 29.2299C49.9382 29.628 49.3735 29.8842 48.7732 29.969C48.1729 30.0538 47.5614 29.9638 47.0089 29.7094C42.5965 27.694 30.9792 22.2246 22.3303 17.6169C3.1269 7.20265 1.00133 5.4045 1.00133 3.75621C1.00133 1.17138 8.33087 -1.48837 13.5715 0.984076H13.5678Z" fill="#969696"/>
                                                        <line y1="38" x2="60" y2="38" stroke="#969696" stroke-width="2"/>
                                                    </svg>        
                                                </div>
                                                <div class="trvl-dtls text-center fw-300">
                                                    <div class="label">Return</div>
                                                    <div class="date">
                                                        <strong class="fw-500">19</strong>
                                                        <div>
                                                            Nov, 2022 <br>
                                                            Saturday
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-4 justify-content-center">
                                                <div class="mb-4 d-flex justify-content-center">
                                                    <svg width="35" height="38" viewBox="0 0 35 38" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <path d="M24.7 22.8C27.1481 22.8001 29.5016 23.7453 31.2698 25.4385C33.0379 27.1316 34.0843 29.4419 34.1905 31.8877L34.2 32.3V34.2C34.2003 35.1587 33.8382 36.0821 33.1864 36.785C32.5345 37.488 31.641 37.9186 30.685 37.9905L30.4 38H3.8C2.84131 38.0003 1.91792 37.6382 1.21496 36.9864C0.511994 36.3345 0.0814023 35.441 0.00950022 34.485L0 34.2V32.3C0.000141441 29.8519 0.945329 27.4984 2.63845 25.7302C4.33158 23.9621 6.64193 22.9157 9.0877 22.8095L9.5 22.8H24.7ZM17.1 0C19.6196 0 22.0359 1.00089 23.8175 2.78249C25.5991 4.56408 26.6 6.98044 26.6 9.5C26.6 12.0196 25.5991 14.4359 23.8175 16.2175C22.0359 17.9991 19.6196 19 17.1 19C14.5804 19 12.1641 17.9991 10.3825 16.2175C8.60089 14.4359 7.6 12.0196 7.6 9.5C7.6 6.98044 8.60089 4.56408 10.3825 2.78249C12.1641 1.00089 14.5804 0 17.1 0Z" fill="#969696"/>
                                                    </svg>        
                                                </div>                                                
                                                <div class="trvl-dtls text-center fw-300">
                                                    <div class="label">Traveller</div>
                                                    <div class="date">
                                                        <strong class="fw-500">01</strong>
                                                        <div>
                                                            1 Adult
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="fs-16 fw-300 text-center">
                                    This may take upto a minite
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
        require_once("includes/footer.php");
    ?>
    <script>
        $(document).ready(function(){
            $('.dep-city').select2();
            $('.price-per-person').select2();
            $('.month-of-travel').select2();
            $('.travel-duration').select2();
            $('.package-type').select2();
            $('.package-themes').select2();
            $('.sort-by').select2();


            $('.related-images').owlCarousel({
                loop:true,
                autoplay:true,
                margin:18,
                nav:false,
                dots:true,
                smartSpeed:1000,
                responsive:{
                    0:{
                        items:1
                    },
                    500:{
                        items:2
                    }
                }
            })
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
    </script>
</body>
</html>