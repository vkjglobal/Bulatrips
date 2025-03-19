<?php
    session_start();
    require_once("includes/header.php");
?>

<!-- BREADCRUMB STARTS HERE -->
<section style="margin-top:20px;margin-bottom: 10px;">
            <div class="container">
                <div class="row">
                    <div class="col-12">
                        <ul class="breadcrumbs">
                            <li><a href="index" style="text-decoration: underline !important;">Home</a></li>
                            <li> About Us </li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>
        <!-- BREADCRUMB STARTS HERE -->

    <section class="pt-5">

        

        <div class="container">
            <div class="row">
                <div class="col-12 mb-4">
                    <div class="row">
                        <div class="col-md-6 p-0">
                            <div class="container">
                                <div class="mb-4">
                                    <h1 class="fw-bold">About Bulatrips.com</h1>
                                    <p class="text-muted">Your smarter way to book flights online</p>
                                </div>

                                <div class="row">
                                    <div class="col-lg-12 mx-auto">
                                        <p class="lead text-muted">At Bulatrips.com, we're revolutionizing flight search with intelligent technology that finds you the perfect flights at the best prices.  Through our partnership with leading travel technology providers, we serve as your trusted intermediary platform, connecting you directly with global service providers. Bulatrips processes real-time availability and pricing data, enabling seamless booking experiences while ensuring secure transactions through established payment systems.  We analyse real-time fares across hundreds of airlines worldwide, delivering smart route combinations and alternative options even during peak seasons.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 p-0">
                            <img src="images/A350_fly-over-scaled-e1722987701816.jpg" alt="">
                        </div>
                    </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="mt-3 mb-3">
                                    <h2 class="fw-bold">What Sets Us Apart</h2>
                                    <p class="text-muted">Our cutting-edge search engine goes beyond standard flight comparison.  By processing countless route combinations, we help you find the most efficient and cost-effective options you won't find elsewhere.  Whether you're planning a business trip or vacation, or looking for alternative routes to your destination, our intelligent system helps you discover the best possibilities.
                                    </p>
                                    <div class="row text-center">
                                        <div class="col-md-3">
                                            <div  class="p-3" style="border: 1px solid #f57c00; border-radius: 8px;">
                                                <h4>Real-Time Fares</h4>
                                                <p>We provide live fares from hundreds of airlines worldwide.</p>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div  class="p-3" style="border: 1px solid #f57c00; border-radius: 8px;">
                                                <h4>Intelligent Routes</h4>
                                                <p>Discover the most efficient and cost-effective travel options.</p>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div  class="p-3" style="border: 1px solid #f57c00; border-radius: 8px;">
                                                <h4>Simple & Transparent</h4>
                                                <p>Enjoy an easy booking process with no hidden fees.</p>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div  class="p-3" style="border: 1px solid #f57c00; border-radius: 8px;">
                                                <h4>Seamless Booking</h4>
                                                <p>Enjoy instant confirmation and automated support</p>
                                            </div>
                                        </div>
                                        
                                    </div>

                                </div>

                                <div class="col-md-12 p-0 mt-5">
                                    <div class="mb-4">
                                        <h1 class="fw-bold">The Bulatrips Difference</h1>
                                    </div>

                                    <div class="row">
                                        <div class="col-lg-12 mx-auto">
                                            <p class="lead">We believe finding the perfect flight shouldn't be complicated.  Bulatrips combines powerful technology with simplicity, ensuring you book with confidence every time.  We're committed to continuous innovation, making travel planning effortless for everyone.  Our streamlined platform offers easy self-service booking management and transparent pricing, putting you in control of your travel arrangements from start to finish.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mt-5">
                                    <h2 class="fw-bold">Why Choose Bulatrips?</h2>
                                    <ul class="list-group list-group-flush w-100 mx-auto">
                                        <li class="list-group-item"><i class="fa fa-check" style="color: #f57c00;"></i> confirmation & automated support</li>
                                        <li class="list-group-item"><i class="fa fa-check" style="color: #f57c00;"></i> Transparent pricing with no hidden fees</li>
                                        <li class="list-group-item"><i class="fa fa-check" style="color: #f57c00;"></i> Seamless self-service booking management</li>
                                        <li class="list-group-item"><i class="fa fa-check" style="color: #f57c00;"></i> Secure transactions with trusted payment systems</li>
                                    </ul>
                                </div>

                                <div class="mt-5">
                                    <h2 class="fw-bold">Take Off With Us</h2>
                                    <p>Ready to experience a smarter way to book online?  Start your journey with <strong>Bulatrips.com</strong> today!</p>
                                    <!-- <a href="#" class="btn btn-primary btn-lg">Book Now</a> -->
                                </div>
                            </div>
                        </div>


                    </div>
                </div>
            </div>
        </div>
    </section>
    <?php
        require_once("includes/login-modal.php");
        require_once("includes/forgot-modal.php");
        require_once("includes/footer.php");
    ?>
</body>
</html>