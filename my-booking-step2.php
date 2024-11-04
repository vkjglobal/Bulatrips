<?php
    require_once("includes/header.php");
?>
    <section class="bg-070F4E steps-indicator">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-9">
                    <div class="steps-bar-title white-txt fw-500 text-md-center">Book your Flight in 4 Simple Steps</div>
                    <div class="process-wrap active-step2">
                        <div class="process-main">
                            <div class="row justify-content-center">
                                <div class="col-md-3 position-relative">
                                    <div class="process-step-cont">
                                        <div class="process-step step-1"></div>
                                        <span class="process-label"><span class="position-relative">Review Booking<button>(Edit)</button></span></span>
                                    </div>
                                </div>
                                <div class="col-md-3 position-relative">
                                    <div class="process-step-cont">
                                        <div class="process-step step-2"></div>
                                        <span class="process-label"><span class="position-relative">Sign In<button>(Edit)</button></span></span>
                                    </div>
                                </div>
                                <div class="col-md-3 position-relative">
                                    <div class="process-step-cont">
                                        <div class="process-step step-3"></div>
                                        <span class="process-label"><span class="position-relative">Traveller Details<button>(Edit)</button></span></span>
                                    </div>
                                </div>
                                <div class="col-md-3 position-relative">
                                    <div class="process-step-cont">
                                        <div class="process-step step-4"></div>
                                        <span class="process-label"><span class="position-relative">Payment</span></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <section>
        <div class="container">
            <div class="form-row">
                <div class="col-12">
                    <h2 class="title-typ2 mb-3 mb-lg-5">My Booking</h2>
                    <div class="booking-step-container booking-step-2">
                        <div class="booking-step mb-4">
                            <ul class="form-row fs-15 mt-md-3 mt-2 pb-md-3 pb-2 bdr-b bdr-none">
                                <li class="col-md-1 text-center mb-md-0 mb-2">
                                    <img src="images/ana-logo.png" alt="">
                                </li>
                                <li class="col-md-3 mb-md-0 mb-2">
                                    <strong class="fw-500 d-block">All Nippon Airlines</strong>
                                    Flight No - <span>NH 1604</span>
                                </li>
                                <li class="col-md-2 mb-md-0 mb-2">
                                    <strong class="fw-500">KCZ</strong> 13 Nov 08:40 
                                </li>
                                <li class="col-md-2 mb-md-0 mb-2">
                                    <strong class="fw-500">DXB</strong> 13 Nov 08:40
                                </li>
                                <li class="col-md-2 mb-md-0 mb-2">
                                    <div>1 stop</div>  
                                    26hr 5m
                                </li>
                                <li class="col-md-2 mb-md-0 mb-2">
                                    <strong class="d-block light-blue-txt">RS.128389</strong> 
                                    1 adult 
                                    Fare Details
                                </li>
                            </ul>
                        </div>
                        <div class="booking-step">
                            <ul class="form-row fs-15 mt-md-3 mt-2">
                                <li class="col-md-1 text-center mb-md-0 mb-2">
                                    <img src="images/emirates-small-logo.png" alt="">
                                </li>
                                <li class="col-md-3 mb-md-0 mb-2">
                                    <strong class="fw-500 d-block">Emirates Airline</strong>
                                    Flight No - <span>EK 316</span>
                                </li>
                                <li class="col-md-2 mb-md-0 mb-2">
                                    <strong class="fw-500">DXB</strong> 13 Nov 08:40 
                                </li>
                                <li class="col-md-2 mb-md-0 mb-2">
                                    <strong class="fw-500">KCZ</strong> 13 Nov 08:40
                                </li>
                                <li class="col-md-2 mb-md-0 mb-2">
                                    <div>1 stop</div>  
                                    24hr 5m
                                </li>
                                <!-- <li class="col-md-2 mb-md-0 mb-2">
                                    <strong class="d-block light-blue-txt">RS.128389</strong> 
                                    1 adult 
                                    Fare Details
                                </li> -->
                            </ul>
                        </div>
                    </div>
                    <strong class="d-block fw-500 pt-md-4 pb-md-4 pt-3 pb-3">Before Booking! Sign in</strong>
                    <div class="mb-4 fw-300">
                        You are loged in with below id. <br>
                        <strong class="fw-500">sree2020@gmail.com</strong> <a href="" class="txt-2391D1 text-decoration">change</a>    
                    </div>
                    <button id="continueButton" class="btn btn-typ3 fs-15 fw-600 pl-4 pr-4">CONTINUE</button>
                    <div class="traveller-details row" id="travellerDetails" style="display: none;">
                        <div class="col-12">
                            <div class="title-typ3 dark-blue-txt fw-500 mb-3 mt-5">Traveller Details</div>
                            <form action="" class="form-row justify-content-center pt-lg-4 pb-lg-4 pt-3 pb-3">
                                <div class="col-lg-10">
                                    <p class="fs-15 fw-400 mb-3">Please enter travellers name exactly as on passport</p>
                                    <div class="form-row pb-lg-3 pb-2 bdr-b mb-3 align-items-center">
                                        <div class="col mb-lg-0 mb-2">
                                            <label for="" class="m-0 fw-500">Adult 1</label>
                                        </div>
                                        <div class="col mb-lg-0 mb-2">
                                            <select name="" id="" class="form-control select-title">
                                                <option value="">Mr</option>
                                                <option value="">Mrs</option>
                                            </select>
                                        </div>
                                        <div class="col-lg-2 col-md-4 mb-lg-0 mb-2">
                                            <input type="text" class="form-control" placeholder="First name">
                                        </div>
                                        <div class="col-lg-2 col-md-4 mb-lg-0 mb-2">
                                            <input type="text" class="form-control" placeholder="Last Name">
                                        </div>
                                        <div class="col-lg-2 col-md-4 calndr-icon mb-lg-0 mb-2">
                                            <input type="text" class="form-control" id="birthDate" placeholder="Date of Birth">
                                            <span class="icon">
                                                <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path id="Vector" d="M3.25 0C2.38805 0 1.5614 0.34241 0.951903 0.951903C0.34241 1.5614 0 2.38805 0 3.25V14.75C0 15.612 0.34241 16.4386 0.951903 17.0481C1.5614 17.6576 2.38805 18 3.25 18H14.75C15.612 18 16.4386 17.6576 17.0481 17.0481C17.6576 16.4386 18 15.612 18 14.75V3.25C18 2.38805 17.6576 1.5614 17.0481 0.951903C16.4386 0.34241 15.612 0 14.75 0H3.25ZM1.5 5.5H16.5V14.75C16.5 15.2141 16.3156 15.6592 15.9874 15.9874C15.6592 16.3156 15.2141 16.5 14.75 16.5H3.25C2.78587 16.5 2.34075 16.3156 2.01256 15.9874C1.68437 15.6592 1.5 15.2141 1.5 14.75V5.5ZM13.25 11.5C12.9185 11.5 12.6005 11.6317 12.3661 11.8661C12.1317 12.1005 12 12.4185 12 12.75C12 13.0815 12.1317 13.3995 12.3661 13.6339C12.6005 13.8683 12.9185 14 13.25 14C13.5815 14 13.8995 13.8683 14.1339 13.6339C14.3683 13.3995 14.5 13.0815 14.5 12.75C14.5 12.4185 14.3683 12.1005 14.1339 11.8661C13.8995 11.6317 13.5815 11.5 13.25 11.5V11.5ZM9 11.5C8.66848 11.5 8.35054 11.6317 8.11612 11.8661C7.8817 12.1005 7.75 12.4185 7.75 12.75C7.75 13.0815 7.8817 13.3995 8.11612 13.6339C8.35054 13.8683 8.66848 14 9 14C9.33152 14 9.64946 13.8683 9.88388 13.6339C10.1183 13.3995 10.25 13.0815 10.25 12.75C10.25 12.4185 10.1183 12.1005 9.88388 11.8661C9.64946 11.6317 9.33152 11.5 9 11.5V11.5ZM13.25 7.5C12.9185 7.5 12.6005 7.6317 12.3661 7.86612C12.1317 8.10054 12 8.41848 12 8.75C12 9.08152 12.1317 9.39946 12.3661 9.63388C12.6005 9.8683 12.9185 10 13.25 10C13.5815 10 13.8995 9.8683 14.1339 9.63388C14.3683 9.39946 14.5 9.08152 14.5 8.75C14.5 8.41848 14.3683 8.10054 14.1339 7.86612C13.8995 7.6317 13.5815 7.5 13.25 7.5ZM9 7.5C8.66848 7.5 8.35054 7.6317 8.11612 7.86612C7.8817 8.10054 7.75 8.41848 7.75 8.75C7.75 9.08152 7.8817 9.39946 8.11612 9.63388C8.35054 9.8683 8.66848 10 9 10C9.33152 10 9.64946 9.8683 9.88388 9.63388C10.1183 9.39946 10.25 9.08152 10.25 8.75C10.25 8.41848 10.1183 8.10054 9.88388 7.86612C9.64946 7.6317 9.33152 7.5 9 7.5V7.5ZM4.75 7.5C4.41848 7.5 4.10054 7.6317 3.86612 7.86612C3.6317 8.10054 3.5 8.41848 3.5 8.75C3.5 9.08152 3.6317 9.39946 3.86612 9.63388C4.10054 9.8683 4.41848 10 4.75 10C5.08152 10 5.39946 9.8683 5.63388 9.63388C5.8683 9.39946 6 9.08152 6 8.75C6 8.41848 5.8683 8.10054 5.63388 7.86612C5.39946 7.6317 5.08152 7.5 4.75 7.5ZM3.25 1.5H14.75C15.716 1.5 16.5 2.284 16.5 3.25V4H1.5V3.25C1.5 2.284 2.284 1.5 3.25 1.5Z" fill="#6D759C"/>
                                                </svg>    
                                            </span>
                                        </div>
                                        <div class="col-lg-2 col-md-4 mb-lg-0 mb-2">
                                            <input type="text" class="form-control" placeholder="Passport No.">
                                        </div>
                                        <div class="col-lg-2 col-md-4 calndr-icon mb-lg-0 mb-2">
                                            <input type="text" class="form-control" id="pasprtExp" placeholder="Expiry Date">
                                            <span class="icon">
                                                <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path id="Vector" d="M3.25 0C2.38805 0 1.5614 0.34241 0.951903 0.951903C0.34241 1.5614 0 2.38805 0 3.25V14.75C0 15.612 0.34241 16.4386 0.951903 17.0481C1.5614 17.6576 2.38805 18 3.25 18H14.75C15.612 18 16.4386 17.6576 17.0481 17.0481C17.6576 16.4386 18 15.612 18 14.75V3.25C18 2.38805 17.6576 1.5614 17.0481 0.951903C16.4386 0.34241 15.612 0 14.75 0H3.25ZM1.5 5.5H16.5V14.75C16.5 15.2141 16.3156 15.6592 15.9874 15.9874C15.6592 16.3156 15.2141 16.5 14.75 16.5H3.25C2.78587 16.5 2.34075 16.3156 2.01256 15.9874C1.68437 15.6592 1.5 15.2141 1.5 14.75V5.5ZM13.25 11.5C12.9185 11.5 12.6005 11.6317 12.3661 11.8661C12.1317 12.1005 12 12.4185 12 12.75C12 13.0815 12.1317 13.3995 12.3661 13.6339C12.6005 13.8683 12.9185 14 13.25 14C13.5815 14 13.8995 13.8683 14.1339 13.6339C14.3683 13.3995 14.5 13.0815 14.5 12.75C14.5 12.4185 14.3683 12.1005 14.1339 11.8661C13.8995 11.6317 13.5815 11.5 13.25 11.5V11.5ZM9 11.5C8.66848 11.5 8.35054 11.6317 8.11612 11.8661C7.8817 12.1005 7.75 12.4185 7.75 12.75C7.75 13.0815 7.8817 13.3995 8.11612 13.6339C8.35054 13.8683 8.66848 14 9 14C9.33152 14 9.64946 13.8683 9.88388 13.6339C10.1183 13.3995 10.25 13.0815 10.25 12.75C10.25 12.4185 10.1183 12.1005 9.88388 11.8661C9.64946 11.6317 9.33152 11.5 9 11.5V11.5ZM13.25 7.5C12.9185 7.5 12.6005 7.6317 12.3661 7.86612C12.1317 8.10054 12 8.41848 12 8.75C12 9.08152 12.1317 9.39946 12.3661 9.63388C12.6005 9.8683 12.9185 10 13.25 10C13.5815 10 13.8995 9.8683 14.1339 9.63388C14.3683 9.39946 14.5 9.08152 14.5 8.75C14.5 8.41848 14.3683 8.10054 14.1339 7.86612C13.8995 7.6317 13.5815 7.5 13.25 7.5ZM9 7.5C8.66848 7.5 8.35054 7.6317 8.11612 7.86612C7.8817 8.10054 7.75 8.41848 7.75 8.75C7.75 9.08152 7.8817 9.39946 8.11612 9.63388C8.35054 9.8683 8.66848 10 9 10C9.33152 10 9.64946 9.8683 9.88388 9.63388C10.1183 9.39946 10.25 9.08152 10.25 8.75C10.25 8.41848 10.1183 8.10054 9.88388 7.86612C9.64946 7.6317 9.33152 7.5 9 7.5V7.5ZM4.75 7.5C4.41848 7.5 4.10054 7.6317 3.86612 7.86612C3.6317 8.10054 3.5 8.41848 3.5 8.75C3.5 9.08152 3.6317 9.39946 3.86612 9.63388C4.10054 9.8683 4.41848 10 4.75 10C5.08152 10 5.39946 9.8683 5.63388 9.63388C5.8683 9.39946 6 9.08152 6 8.75C6 8.41848 5.8683 8.10054 5.63388 7.86612C5.39946 7.6317 5.08152 7.5 4.75 7.5ZM3.25 1.5H14.75C15.716 1.5 16.5 2.284 16.5 3.25V4H1.5V3.25C1.5 2.284 2.284 1.5 3.25 1.5Z" fill="#6D759C"/>
                                                </svg>    
                                            </span>
                                        </div>
                                    </div>
                                    <div class="form-row pb-lg-3 pb-2 align-items-center">
                                        <div class="col mb-md-0 mb-2">
                                            <label for="" class="m-0 fw-500">Contact Details</label>
                                        </div>
                                        <div class="col-md-3 mb-md-0 mb-2">
                                            <input type="text" class="form-control" placeholder="Mobile Number">
                                        </div>
                                        <div class="col-md-4 mb-md-0 mb-2">
                                            <input type="text" class="form-control" placeholder="Email Address">
                                        </div>
                                        <div class="col mb-md-0 mb-2">
                                            <select name="" id="" class="form-control select-location">
                                                <option value="">Location1</option>
                                                <option value="">Location2</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-row chkbx mb-3">
                                        <div class="col d-flex align-items-center">
                                            <span for="" class="m-0 mr-2 fw-500">GST</span>
                                            <input type="checkbox" id="gst" checked="">
                                            <label for="gst" class="fz-13 fw-400">
                                                <span class="chk-txt fs-13 fw-400">Use GSTIN for this booking</span>
                                            </label>
                                        </div>
                                    </div>
                                    <button class="btn btn-typ3 fs-15 fw-600 pl-4 pr-4">CONTINUE</button>
                                </div>
                            </form>
                        </div>
                    </div>
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
        
        $(".text-below-button").click(function(){
            $(this).parents('.modal').modal('hide');
        });
        $(".forgot-passward > button").click(function(){
            $(this).parents('.modal').modal('hide');
        });
        
        $('#FlightSearchLoading').modal({
            show:false
        })

        $( function() {
            $( "#birthDate" ).datepicker({
                dateFormat: "dd-mm-yy",
                maxDate: 0
            });            
        });
        $( function() {
            $( "#pasprtExp" ).datepicker({
                dateFormat: "dd-mm-yy",
                minDate: 0
            });
        });

        $(document).ready(function(){
            $("#continueButton").click(function(){
                $("#travellerDetails").slideDown(1000);
            });
            $('.select-title').select2();
            $('.select-location').select2();
        });
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