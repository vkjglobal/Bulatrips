<?php

// require_once("includes/header.php");
include("includes/header.php");
include('includes/dbConnect.php');
include_once('includes/class.Reviews.php');
$reviews = new Reviews($conn);
$resultReview = $reviews->getReviesDetails();//Fetch review details form rview table.
// echo $resultReview;

$query = "SELECT airport_code,airport_name,country_name FROM airportlocations";
$stmt = $conn->prepare($query);
$stmt->execute();
$airports = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<section class="midbar-wrapper">
    <div id="MidbarCarousel" class="midbar-carousel carousel slide carousel-fade d-none d-md-block" data-ride="carousel">
        <div class="carousel-inner">
            <div class="carousel-item active">
                <img src="images/home-banner1.jpg" class="d-block w-100" alt="...">
                <div class="carousel-caption d-none d-md-block">
                    <h2>Let's go new</h2>
                    <h3>Explore and travel.</h3>
                </div>
            </div>
            <div class="carousel-item">
                <img src="images/home-banner2.jpg" class="d-block w-100" alt="...">
                <div class="carousel-caption d-none d-md-block">
                    <h2>Let's go new</h2>
                    <h3>Explore and travel.</h3>
                </div>
            </div>
            <div class="carousel-item">
                <img src="images/home-banner3.jpg" class="d-block w-100" alt="...">
                <div class="carousel-caption d-none d-md-block">
                    <h2>Let's go new</h2>
                    <h3>Explore and travel.</h3>
                </div>
            </div>
        </div>
    </div>
    <div class="container flight-search-midbar">
        <div class="row p-md-0 p-3">
            <!-- <form class="flight-search col-12" id="flight-search" method="POST" action="search.php"> -->
            <form class="flight-search col-12" id="flight-search" method="post" action="search.php">

                <span class="lbl">
                    <svg width="30" height="30" viewBox="0 0 30 30" fill="none" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                        <rect width="30" height="30" fill="url(#pattern0)" />
                        <defs>
                            <pattern id="pattern0" patternContentUnits="objectBoundingBox" width="1" height="1">
                                <use xlink:href="#image0_69_24" transform="scale(0.00195312)" />
                            </pattern>
                            <image id="image0_69_24" width="512" height="512" xlink:href="data:image/png;base64,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" />
                        </defs>
                    </svg>
                    FLIGHTS
                </span>
                <div class="d-flex flex-md-row flex-column">
                    <div class="d-flex align-items-center justify-content-center mb-md-0 mb-3">
                        <input type="radio" value="Return" id="return" name="tab" checked="checked">
                        <label for="return">Round-trip</label>
                        <input type="radio" id="one-way" value="OneWay" name="tab">
                        <label for="one-way">One-way</label>
                        <input type="radio" id="multi-city" value="Circle" name="tab">
                        <label for="multi-city">Multi-city</label>
                    </div>
                    <div class="d-flex align-items-center">
                        <div class="select-class-wrp">
                            <select name="cabin-preference" class="select-class" id="cabin-preference">
                                <option value="Y">Economy</option>
                                <option value="S">Premium</option>
                                <option value="C">Business</option>
                                <option value="F">First</option>
                            </select>
                        </div>
                        <span class="person-select" onclick="return fetchAndAlert()">
                            <label for="" class="select-lbl">Traveller <span id="totalCount" class="count">1</span><span class="downarrow"></span></label>
                            <div class='select-dropbox'>
                                <span class="selectbox d-flex justify-content-between">
                                    <label class="fs-13 fw-600" for="">Adults
                                        <span class="fs-11">12 years and above</span>
                                    </label>
                                    <span class="selec-wrp d-inline-flex align-items-center">
                                        <!-- <input type='number' name="adult" min=1 value=1> -->
                                        <input type="number" id="adult_count" name="adult" min="1" value=1>
                                        <span class='minus'>-</span>
                                        <span class='add'>+</span>
                                    </span>
                                </span>
                                <span class="selectbox d-flex justify-content-between">
                                    <label class="fs-13 fw-600" for="">Children
                                        <span class="fs-11">2 - 11 years</span>
                                    </label>
                                    <span class="selec-wrp d-inline-flex align-items-center">
                                        <input type='number' id="child-count" name="child" min=0 value=0>
                                        <span class='minus'>-</span>
                                        <span class='add'>+</span>
                                    </span>
                                </span>
                                <span class="selectbox d-flex justify-content-between">
                                    <label class="fs-13 fw-600" for="">Infants
                                        <span class="fs-11">Under 2 years</span>
                                    </label>
                                    <span class="selec-wrp d-inline-flex align-items-center">
                                        <input type='number' id="infant-count" name="infant" min=0 value=0>
                                        <span class='minus'>-</span>
                                        <span class='add'>+</span>
                                    </span>
                                </span>
                            </div>
                        </span>
                    </div>
                </div>

                <div class="srch-fld">
                    <div class="search-box on row">
                        <div class="form-fields col-md-3">
                            <input type="text" id="airport-input" name="airport" class="form-control" placeholder="Departing From">
                            <!-- <input type="text" id="airportInput" name="airport" class="form-control" autocomplete="off"> -->
                        </div>
                        <div class="form-fields col-md-3">
                            <!-- <input type="text" class="form-control" placeholder="Going To"> -->
                            <input type="text" id="arrivalairport-input" name="arrivalairport" class="form-control" placeholder="Going To">

                        </div>
                        <div class="form-fields col-md-2 calndr-icon">
                            <input type="text" class="form-control" id="from" name="from">
                            <span class="icon">
                                <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path id="Vector" d="M3.25 0C2.38805 0 1.5614 0.34241 0.951903 0.951903C0.34241 1.5614 0 2.38805 0 3.25V14.75C0 15.612 0.34241 16.4386 0.951903 17.0481C1.5614 17.6576 2.38805 18 3.25 18H14.75C15.612 18 16.4386 17.6576 17.0481 17.0481C17.6576 16.4386 18 15.612 18 14.75V3.25C18 2.38805 17.6576 1.5614 17.0481 0.951903C16.4386 0.34241 15.612 0 14.75 0H3.25ZM1.5 5.5H16.5V14.75C16.5 15.2141 16.3156 15.6592 15.9874 15.9874C15.6592 16.3156 15.2141 16.5 14.75 16.5H3.25C2.78587 16.5 2.34075 16.3156 2.01256 15.9874C1.68437 15.6592 1.5 15.2141 1.5 14.75V5.5ZM13.25 11.5C12.9185 11.5 12.6005 11.6317 12.3661 11.8661C12.1317 12.1005 12 12.4185 12 12.75C12 13.0815 12.1317 13.3995 12.3661 13.6339C12.6005 13.8683 12.9185 14 13.25 14C13.5815 14 13.8995 13.8683 14.1339 13.6339C14.3683 13.3995 14.5 13.0815 14.5 12.75C14.5 12.4185 14.3683 12.1005 14.1339 11.8661C13.8995 11.6317 13.5815 11.5 13.25 11.5V11.5ZM9 11.5C8.66848 11.5 8.35054 11.6317 8.11612 11.8661C7.8817 12.1005 7.75 12.4185 7.75 12.75C7.75 13.0815 7.8817 13.3995 8.11612 13.6339C8.35054 13.8683 8.66848 14 9 14C9.33152 14 9.64946 13.8683 9.88388 13.6339C10.1183 13.3995 10.25 13.0815 10.25 12.75C10.25 12.4185 10.1183 12.1005 9.88388 11.8661C9.64946 11.6317 9.33152 11.5 9 11.5V11.5ZM13.25 7.5C12.9185 7.5 12.6005 7.6317 12.3661 7.86612C12.1317 8.10054 12 8.41848 12 8.75C12 9.08152 12.1317 9.39946 12.3661 9.63388C12.6005 9.8683 12.9185 10 13.25 10C13.5815 10 13.8995 9.8683 14.1339 9.63388C14.3683 9.39946 14.5 9.08152 14.5 8.75C14.5 8.41848 14.3683 8.10054 14.1339 7.86612C13.8995 7.6317 13.5815 7.5 13.25 7.5ZM9 7.5C8.66848 7.5 8.35054 7.6317 8.11612 7.86612C7.8817 8.10054 7.75 8.41848 7.75 8.75C7.75 9.08152 7.8817 9.39946 8.11612 9.63388C8.35054 9.8683 8.66848 10 9 10C9.33152 10 9.64946 9.8683 9.88388 9.63388C10.1183 9.39946 10.25 9.08152 10.25 8.75C10.25 8.41848 10.1183 8.10054 9.88388 7.86612C9.64946 7.6317 9.33152 7.5 9 7.5V7.5ZM4.75 7.5C4.41848 7.5 4.10054 7.6317 3.86612 7.86612C3.6317 8.10054 3.5 8.41848 3.5 8.75C3.5 9.08152 3.6317 9.39946 3.86612 9.63388C4.10054 9.8683 4.41848 10 4.75 10C5.08152 10 5.39946 9.8683 5.63388 9.63388C5.8683 9.39946 6 9.08152 6 8.75C6 8.41848 5.8683 8.10054 5.63388 7.86612C5.39946 7.6317 5.08152 7.5 4.75 7.5ZM3.25 1.5H14.75C15.716 1.5 16.5 2.284 16.5 3.25V4H1.5V3.25C1.5 2.284 2.284 1.5 3.25 1.5Z" fill="#6D759C" />
                                </svg>
                            </span>
                        </div>
                        <div class="form-fields col-md-2 calndr-icon">
                            <input type="text" class="form-control" id="to" name="to">
                            <span class="icon">
                                <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path id="Vector" d="M3.25 0C2.38805 0 1.5614 0.34241 0.951903 0.951903C0.34241 1.5614 0 2.38805 0 3.25V14.75C0 15.612 0.34241 16.4386 0.951903 17.0481C1.5614 17.6576 2.38805 18 3.25 18H14.75C15.612 18 16.4386 17.6576 17.0481 17.0481C17.6576 16.4386 18 15.612 18 14.75V3.25C18 2.38805 17.6576 1.5614 17.0481 0.951903C16.4386 0.34241 15.612 0 14.75 0H3.25ZM1.5 5.5H16.5V14.75C16.5 15.2141 16.3156 15.6592 15.9874 15.9874C15.6592 16.3156 15.2141 16.5 14.75 16.5H3.25C2.78587 16.5 2.34075 16.3156 2.01256 15.9874C1.68437 15.6592 1.5 15.2141 1.5 14.75V5.5ZM13.25 11.5C12.9185 11.5 12.6005 11.6317 12.3661 11.8661C12.1317 12.1005 12 12.4185 12 12.75C12 13.0815 12.1317 13.3995 12.3661 13.6339C12.6005 13.8683 12.9185 14 13.25 14C13.5815 14 13.8995 13.8683 14.1339 13.6339C14.3683 13.3995 14.5 13.0815 14.5 12.75C14.5 12.4185 14.3683 12.1005 14.1339 11.8661C13.8995 11.6317 13.5815 11.5 13.25 11.5V11.5ZM9 11.5C8.66848 11.5 8.35054 11.6317 8.11612 11.8661C7.8817 12.1005 7.75 12.4185 7.75 12.75C7.75 13.0815 7.8817 13.3995 8.11612 13.6339C8.35054 13.8683 8.66848 14 9 14C9.33152 14 9.64946 13.8683 9.88388 13.6339C10.1183 13.3995 10.25 13.0815 10.25 12.75C10.25 12.4185 10.1183 12.1005 9.88388 11.8661C9.64946 11.6317 9.33152 11.5 9 11.5V11.5ZM13.25 7.5C12.9185 7.5 12.6005 7.6317 12.3661 7.86612C12.1317 8.10054 12 8.41848 12 8.75C12 9.08152 12.1317 9.39946 12.3661 9.63388C12.6005 9.8683 12.9185 10 13.25 10C13.5815 10 13.8995 9.8683 14.1339 9.63388C14.3683 9.39946 14.5 9.08152 14.5 8.75C14.5 8.41848 14.3683 8.10054 14.1339 7.86612C13.8995 7.6317 13.5815 7.5 13.25 7.5ZM9 7.5C8.66848 7.5 8.35054 7.6317 8.11612 7.86612C7.8817 8.10054 7.75 8.41848 7.75 8.75C7.75 9.08152 7.8817 9.39946 8.11612 9.63388C8.35054 9.8683 8.66848 10 9 10C9.33152 10 9.64946 9.8683 9.88388 9.63388C10.1183 9.39946 10.25 9.08152 10.25 8.75C10.25 8.41848 10.1183 8.10054 9.88388 7.86612C9.64946 7.6317 9.33152 7.5 9 7.5V7.5ZM4.75 7.5C4.41848 7.5 4.10054 7.6317 3.86612 7.86612C3.6317 8.10054 3.5 8.41848 3.5 8.75C3.5 9.08152 3.6317 9.39946 3.86612 9.63388C4.10054 9.8683 4.41848 10 4.75 10C5.08152 10 5.39946 9.8683 5.63388 9.63388C5.8683 9.39946 6 9.08152 6 8.75C6 8.41848 5.8683 8.10054 5.63388 7.86612C5.39946 7.6317 5.08152 7.5 4.75 7.5ZM3.25 1.5H14.75C15.716 1.5 16.5 2.284 16.5 3.25V4H1.5V3.25C1.5 2.284 2.284 1.5 3.25 1.5Z" fill="#6D759C" />
                                </svg>
                            </span>
                        </div>
                        <span id="errormessage"></span>
                        <div class="form-fields col-md-2">
                            <!-- <button class="btn btn-typ1 w-100 form-control">Search</button> -->
                            <input type="submit" name="go" class="btn btn-typ1 w-100 form-control" value="Search">

                        </div>

                    </div>
                    <div class="search-box row multi-city-search">
                        <div class="col-md-10">
                            <div class="row">
                                <div class="form-fields col-md-4">
                                    <!-- <input type="text" class="form-control" placeholder="Departing From"> -->
                                    <input type="text" id="departure_from_1" name="departure_from_1" class="form-control" placeholder="Departing From">

                                </div>
                                <div class="form-fields col-md-4">
                                    <input type="text" id="arrival_to_1" name="arrival_to_1" class="form-control" placeholder="Going To">

                                </div>
                                <div class="form-fields col-md-2 calndr-icon">
                                    <input type="date" class="form-control date-multy-city" id="departure_date_1" name="departure_date_1">
                                    <span class="icon">
                                        <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path id="Vector" d="M3.25 0C2.38805 0 1.5614 0.34241 0.951903 0.951903C0.34241 1.5614 0 2.38805 0 3.25V14.75C0 15.612 0.34241 16.4386 0.951903 17.0481C1.5614 17.6576 2.38805 18 3.25 18H14.75C15.612 18 16.4386 17.6576 17.0481 17.0481C17.6576 16.4386 18 15.612 18 14.75V3.25C18 2.38805 17.6576 1.5614 17.0481 0.951903C16.4386 0.34241 15.612 0 14.75 0H3.25ZM1.5 5.5H16.5V14.75C16.5 15.2141 16.3156 15.6592 15.9874 15.9874C15.6592 16.3156 15.2141 16.5 14.75 16.5H3.25C2.78587 16.5 2.34075 16.3156 2.01256 15.9874C1.68437 15.6592 1.5 15.2141 1.5 14.75V5.5ZM13.25 11.5C12.9185 11.5 12.6005 11.6317 12.3661 11.8661C12.1317 12.1005 12 12.4185 12 12.75C12 13.0815 12.1317 13.3995 12.3661 13.6339C12.6005 13.8683 12.9185 14 13.25 14C13.5815 14 13.8995 13.8683 14.1339 13.6339C14.3683 13.3995 14.5 13.0815 14.5 12.75C14.5 12.4185 14.3683 12.1005 14.1339 11.8661C13.8995 11.6317 13.5815 11.5 13.25 11.5V11.5ZM9 11.5C8.66848 11.5 8.35054 11.6317 8.11612 11.8661C7.8817 12.1005 7.75 12.4185 7.75 12.75C7.75 13.0815 7.8817 13.3995 8.11612 13.6339C8.35054 13.8683 8.66848 14 9 14C9.33152 14 9.64946 13.8683 9.88388 13.6339C10.1183 13.3995 10.25 13.0815 10.25 12.75C10.25 12.4185 10.1183 12.1005 9.88388 11.8661C9.64946 11.6317 9.33152 11.5 9 11.5V11.5ZM13.25 7.5C12.9185 7.5 12.6005 7.6317 12.3661 7.86612C12.1317 8.10054 12 8.41848 12 8.75C12 9.08152 12.1317 9.39946 12.3661 9.63388C12.6005 9.8683 12.9185 10 13.25 10C13.5815 10 13.8995 9.8683 14.1339 9.63388C14.3683 9.39946 14.5 9.08152 14.5 8.75C14.5 8.41848 14.3683 8.10054 14.1339 7.86612C13.8995 7.6317 13.5815 7.5 13.25 7.5ZM9 7.5C8.66848 7.5 8.35054 7.6317 8.11612 7.86612C7.8817 8.10054 7.75 8.41848 7.75 8.75C7.75 9.08152 7.8817 9.39946 8.11612 9.63388C8.35054 9.8683 8.66848 10 9 10C9.33152 10 9.64946 9.8683 9.88388 9.63388C10.1183 9.39946 10.25 9.08152 10.25 8.75C10.25 8.41848 10.1183 8.10054 9.88388 7.86612C9.64946 7.6317 9.33152 7.5 9 7.5V7.5ZM4.75 7.5C4.41848 7.5 4.10054 7.6317 3.86612 7.86612C3.6317 8.10054 3.5 8.41848 3.5 8.75C3.5 9.08152 3.6317 9.39946 3.86612 9.63388C4.10054 9.8683 4.41848 10 4.75 10C5.08152 10 5.39946 9.8683 5.63388 9.63388C5.8683 9.39946 6 9.08152 6 8.75C6 8.41848 5.8683 8.10054 5.63388 7.86612C5.39946 7.6317 5.08152 7.5 4.75 7.5ZM3.25 1.5H14.75C15.716 1.5 16.5 2.284 16.5 3.25V4H1.5V3.25C1.5 2.284 2.284 1.5 3.25 1.5Z" fill="#6D759C" />
                                        </svg>
                                    </span>
                                </div>
                            </div>
                            <div class="row mt-md-2">
                                <div class="form-fields col-md-4">
                                    <!-- <input type="text" id="departure_from_2" name="departure_from_2" class="form-control" placeholder="Departing From"> -->
                                    <input type="text" id="departure_from_2" name="departure_from_2" class="form-control" placeholder="Departing From">
                                </div>
                                <div class="form-fields col-md-4">
                                    <input type="text" id="arrival_to_2" name="arrival_to_2" class="form-control" placeholder="Going To">

                                </div>
                                <div class="form-fields col-md-2 calndr-icon">
                                    <input type="date" class="form-control date-multy-city" id="departure_date_2" name="departure_date_2">
                                    <span class="icon">
                                        <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path id="Vector" d="M3.25 0C2.38805 0 1.5614 0.34241 0.951903 0.951903C0.34241 1.5614 0 2.38805 0 3.25V14.75C0 15.612 0.34241 16.4386 0.951903 17.0481C1.5614 17.6576 2.38805 18 3.25 18H14.75C15.612 18 16.4386 17.6576 17.0481 17.0481C17.6576 16.4386 18 15.612 18 14.75V3.25C18 2.38805 17.6576 1.5614 17.0481 0.951903C16.4386 0.34241 15.612 0 14.75 0H3.25ZM1.5 5.5H16.5V14.75C16.5 15.2141 16.3156 15.6592 15.9874 15.9874C15.6592 16.3156 15.2141 16.5 14.75 16.5H3.25C2.78587 16.5 2.34075 16.3156 2.01256 15.9874C1.68437 15.6592 1.5 15.2141 1.5 14.75V5.5ZM13.25 11.5C12.9185 11.5 12.6005 11.6317 12.3661 11.8661C12.1317 12.1005 12 12.4185 12 12.75C12 13.0815 12.1317 13.3995 12.3661 13.6339C12.6005 13.8683 12.9185 14 13.25 14C13.5815 14 13.8995 13.8683 14.1339 13.6339C14.3683 13.3995 14.5 13.0815 14.5 12.75C14.5 12.4185 14.3683 12.1005 14.1339 11.8661C13.8995 11.6317 13.5815 11.5 13.25 11.5V11.5ZM9 11.5C8.66848 11.5 8.35054 11.6317 8.11612 11.8661C7.8817 12.1005 7.75 12.4185 7.75 12.75C7.75 13.0815 7.8817 13.3995 8.11612 13.6339C8.35054 13.8683 8.66848 14 9 14C9.33152 14 9.64946 13.8683 9.88388 13.6339C10.1183 13.3995 10.25 13.0815 10.25 12.75C10.25 12.4185 10.1183 12.1005 9.88388 11.8661C9.64946 11.6317 9.33152 11.5 9 11.5V11.5ZM13.25 7.5C12.9185 7.5 12.6005 7.6317 12.3661 7.86612C12.1317 8.10054 12 8.41848 12 8.75C12 9.08152 12.1317 9.39946 12.3661 9.63388C12.6005 9.8683 12.9185 10 13.25 10C13.5815 10 13.8995 9.8683 14.1339 9.63388C14.3683 9.39946 14.5 9.08152 14.5 8.75C14.5 8.41848 14.3683 8.10054 14.1339 7.86612C13.8995 7.6317 13.5815 7.5 13.25 7.5ZM9 7.5C8.66848 7.5 8.35054 7.6317 8.11612 7.86612C7.8817 8.10054 7.75 8.41848 7.75 8.75C7.75 9.08152 7.8817 9.39946 8.11612 9.63388C8.35054 9.8683 8.66848 10 9 10C9.33152 10 9.64946 9.8683 9.88388 9.63388C10.1183 9.39946 10.25 9.08152 10.25 8.75C10.25 8.41848 10.1183 8.10054 9.88388 7.86612C9.64946 7.6317 9.33152 7.5 9 7.5V7.5ZM4.75 7.5C4.41848 7.5 4.10054 7.6317 3.86612 7.86612C3.6317 8.10054 3.5 8.41848 3.5 8.75C3.5 9.08152 3.6317 9.39946 3.86612 9.63388C4.10054 9.8683 4.41848 10 4.75 10C5.08152 10 5.39946 9.8683 5.63388 9.63388C5.8683 9.39946 6 9.08152 6 8.75C6 8.41848 5.8683 8.10054 5.63388 7.86612C5.39946 7.6317 5.08152 7.5 4.75 7.5ZM3.25 1.5H14.75C15.716 1.5 16.5 2.284 16.5 3.25V4H1.5V3.25C1.5 2.284 2.284 1.5 3.25 1.5Z" fill="#6D759C" />
                                        </svg>
                                    </span>
                                </div>


                            </div>
                            <div id="additional_trips">
                            </div>
                            <div class="form-fields">
                                <button type="button" id="add_trip_button" class="btn add-trip fw-500 dark-blue-txt">Add Trip +</button>
                            </div>
                        </div>

                        <div class="col-md-2">
                            <div class="form-fields">
                                <!-- <button class="btn btn-typ1 w-100 form-control">Search</button> -->
                                <input type="submit" value="Search" class="btn btn-typ1 w-100 form-control">
                            </div>
                        </div>
                    </div>
                </div>
            </form>

        </div>
    </div>
</section>
<section class="holiday-section">
    <div class="container">
        <div class="row">
            <div class="col-12 hd-wraper">
                <strong>Choose Your</strong>
                <h4>Perfect Holiday</h4>
                <p>Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aene an commodo ligula eget dolor. Aenean massa. Cum sociis the</p>
            </div>
            <div class="col-12">
                <div class="row">
                    <div class="col-lg-3 col-md-4 bx-mb">
                        <div class="btn-wrp">
                            <div class="package-cat-slider owl-carousel owl-theme">
                                <a href="#" class="cat-btn item">
                                    <img src="images/img8.png" alt="">
                                    <span>Dubai</span>
                                </a>
                                <a href="#" class="cat-btn item">
                                    <img src="images/malaysia.jpg" alt="">
                                    <span>Malaysia</span>
                                </a>
                                <a href="#" class="cat-btn item">
                                    <img src="images/Singapore.jpg" alt="">
                                    <span>Singapore</span>
                                </a>
                            </div>
                            <!-- <a href="#" class="cat-btn">
                                    <img src="images/img8.png" alt="">
                                    <span>Dubai</span>
                                </a> -->
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-4 bx-mb">
                        <div class="btn-wrp">
                            <a href="#" class="package-btn">
                                <img src="images/img8.png" alt="">
                                <span class="package-info">
                                    <strong class="packageName fs-16 fw-600">Museum of the <br> Future</strong>
                                    <span class="priceInfo">
                                        <span class="fs-13 fw-400">From</span>
                                        <strong class="fs-13 fw-700"><span>₹</span> 3222.22</strong>
                                    </span>
                                </span>
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-4 bx-mb">
                        <div class="btn-wrp">
                            <a href="#" class="package-btn">
                                <img src="images/img3.png" alt="">
                                <span class="package-info">
                                    <strong class="packageName fs-16 fw-600">Museum of the <br> Future</strong>
                                    <span class="priceInfo">
                                        <span class="fs-13 fw-400">From</span>
                                        <strong class="fs-13 fw-700"><span>₹</span> 3222.22</strong>
                                    </span>
                                </span>
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-4 bx-mb">
                        <div class="btn-wrp">
                            <a href="#" class="package-btn">
                                <img src="images/img4.png" alt="">
                                <span class="package-info">
                                    <strong class="packageName fs-16 fw-600">Museum of the <br> Future</strong>
                                    <span class="priceInfo">
                                        <span class="fs-13 fw-400">From</span>
                                        <strong class="fs-13 fw-700"><span>₹</span> 3222.22</strong>
                                    </span>
                                </span>
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-4 bx-mb">
                        <div class="btn-wrp">
                            <a href="#" class="package-btn">
                                <img src="images/img5.png" alt="">
                                <span class="package-info">
                                    <strong class="packageName fs-16 fw-600">Museum of the <br> Future</strong>
                                    <span class="priceInfo">
                                        <span class="fs-13 fw-400">From</span>
                                        <strong class="fs-13 fw-700"><span>₹</span> 3222.22</strong>
                                    </span>
                                </span>
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-4 bx-mb">
                        <div class="btn-wrp">
                            <a href="#" class="package-btn">
                                <img src="images/img6.png" alt="">
                                <span class="package-info">
                                    <strong class="packageName fs-16 fw-600">Museum of the <br> Future</strong>
                                    <span class="priceInfo">
                                        <span class="fs-13 fw-400">From</span>
                                        <strong class="fs-13 fw-700"><span>₹</span> 3222.22</strong>
                                    </span>
                                </span>
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-4 bx-mb">
                        <div class="btn-wrp">
                            <a href="#" class="package-btn">
                                <img src="images/img7.png" alt="">
                                <span class="package-info">
                                    <strong class="packageName fs-16 fw-600">Museum of the <br> Future</strong>
                                    <span class="priceInfo">
                                        <span class="fs-13 fw-400">From</span>
                                        <strong class="fs-13 fw-700"><span>₹</span> 3222.22</strong>
                                    </span>
                                </span>
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-4 bx-mb">
                        <div class="btn-wrp">
                            <div class="package-cat-slider owl-carousel owl-theme">
                                <a href="#" class="cat-btn item">
                                    <img src="images/img1.png" alt="">
                                    <span>Thailand</span>
                                </a>
                                <a href="#" class="cat-btn item">
                                    <img src="images/MachuPicchu-Peru.jpg" alt="">
                                    <span>Peru</span>
                                </a>
                                <a href="#" class="cat-btn item">
                                    <img src="images/Paris.jpg" alt="">
                                    <span>Paris</span>
                                </a>
                            </div>
                            <!-- <a href="#" class="cat-btn">
                                    <img src="images/img1.png" alt="">
                                    <span>Thailand</span>
                                </a> -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<section class="video-banner">
    <div class="container">
        <div class="row">
            <div class="col-12 hd-wraper white-txt">
                <strong>Go & Discover</strong>
                <h4>Breathtaking Cities</h4>
                <p>Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aene an commodo ligula eget dolor. Aenean massa. Cum sociis the</p>
            </div>
            <div class="col-12 video-wrapper">
                <div class="video-container" id="video-container">
                    <video id="video" preload="metadata" poster="//cdn.jsdelivr.net/npm/big-buck-bunny-1080p@0.0.6/poster.jpg">
                        <source src="images/video-home.mp4" type="video/mp4">
                    </video>

                    <div class="play-button-wrapper">
                        <div title="Play video" class="play-gif" id="circle-play-b">
                            <!-- SVG Play Button -->
                            <svg width="23" height="25" viewBox="0 0 23 25" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M21.097 14.4351L3.42452 24.6892C1.92473 25.5585 0 24.5059 0 22.7533V2.2451C0 0.495342 1.92195 -0.560066 3.42452 0.312034L21.097 10.5662C21.4382 10.7609 21.7218 11.0424 21.9191 11.3822C22.1163 11.7219 22.2202 12.1078 22.2202 12.5006C22.2202 12.8934 22.1163 13.2793 21.9191 13.619C21.7218 13.9588 21.4382 14.2403 21.097 14.4351Z" fill="black" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- <section class="choose-flight">
    <div class="container">
        <div class="row">
            <div class="col-12 hd-wraper">
                <strong>Choose Your</strong>
                <h4>Popular Flight Near You</h4>
                <p>Find deals on domestic and international flights</p>
            </div>
        </div>
    </div>
    <div class="popular-flights">
        <ul class="nav nav-pills mb-4 justify-content-center" id="pills-tab" role="tablist">
            <li class="nav-item">
                <a class="nav-link btn border-radius-100 active" id="pills-International-tab" data-toggle="pill" href="#pills-International" role="tab" aria-controls="pills-International" aria-selected="true">International</a>
            </li>
            <li class="nav-item">
                <a class="nav-link btn border-radius-100" id="pills-Domestic-tab" data-toggle="pill" href="#pills-Domestic" role="tab" aria-controls="pills-Domestic" aria-selected="false">Domestic</a>
            </li>
        </ul>
        <div class="tab-content" id="pills-tabContent">
            <div class="tab-pane fade show active" id="pills-International" role="tabpanel" aria-labelledby="pills-International-tab">
                <div class="owl-carousel international-flights owl-theme owl-loaded">
                    <div class="owl-stage-outer">
                        <div class="owl-stage">
                            <a class="owl-item" href="#">
                                <img src="images/img9.jpg" alt="">
                                <span class="flight-info">
                                    <strong>Cochin to London, UK</strong>
                                    <span>Sept 16 - Sept 23 · Round trip</span>
                                </span>
                            </a>
                            <a class="owl-item" href="#">
                                <img src="images/img10.jpg" alt="">
                                <span class="flight-info">
                                    <strong>Cochin to London, UK</strong>
                                    <span>Sept 16 - Sept 23 · Round trip</span>
                                </span>
                            </a>
                            <a class="owl-item" href="#">
                                <img src="images/img11.jpg" alt="">
                                <span class="flight-info">
                                    <strong>Cochin to London, UK</strong>
                                    <span>Sept 16 - Sept 23 · Round trip</span>
                                </span>
                            </a>
                            <a class="owl-item" href="#">
                                <img src="images/img12.jpg" alt="">
                                <span class="flight-info">
                                    <strong>Cochin to London, UK</strong>
                                    <span>Sept 16 - Sept 23 · Round trip</span>
                                </span>
                            </a>
                            <a class="owl-item" href="#">
                                <img src="images/img13.jpg" alt="">
                                <span class="flight-info">
                                    <strong>Cochin to London, UK</strong>
                                    <span>Sept 16 - Sept 23 · Round trip</span>
                                </span>
                            </a>
                            <a class="owl-item" href="#">
                                <img src="images/img14.jpg" alt="">
                                <span class="flight-info">
                                    <strong>Cochin to London, UK</strong>
                                    <span>Sept 16 - Sept 23 · Round trip</span>
                                </span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="tab-pane fade" id="pills-Domestic" role="tabpanel" aria-labelledby="pills-Domestic-tab">
                <div class="owl-carousel domestic-flights owl-theme owl-loaded">
                    <div class="owl-stage-outer">
                        <div class="owl-stage">
                            <a class="owl-item" href="#">
                                <img src="images/img12.jpg" alt="">
                                <span class="flight-info">
                                    <strong>Cochin to Edinburgh, UK</strong>
                                    <span>Sept 16 - Sept 23 · Round trip</span>
                                </span>
                            </a>
                            <a class="owl-item" href="#">
                                <img src="images/img9.jpg" alt="">
                                <span class="flight-info">
                                    <strong>Cochin to London, UK</strong>
                                    <span>Sept 16 - Sept 23 · Round trip</span>
                                </span>
                            </a>
                            <a class="owl-item" href="#">
                                <img src="images/img14.jpg" alt="">
                                <span class="flight-info">
                                    <strong>Cochin to Edinburgh, UK</strong>
                                    <span>Sept 16 - Sept 23 · Round trip</span>
                                </span>
                            </a>
                            <a class="owl-item" href="#">
                                <img src="images/img11.jpg" alt="">
                                <span class="flight-info">
                                    <strong>Cochin to London, UK</strong>
                                    <span>Sept 16 - Sept 23 · Round trip</span>
                                </span>
                            </a>

                            <a class="owl-item" href="#">
                                <img src="images/img13.jpg" alt="">
                                <span class="flight-info">
                                    <strong>Cochin to London, UK</strong>
                                    <span>Sept 16 - Sept 23 · Round trip</span>
                                </span>
                            </a>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section> -->
<!-- <section>
    <div class="container">
        <div class="row">
            <div class="col-12 hd-wraper">
                <strong>Choose Your</strong>
                <h4>Best Flight Booking Offers</h4>
                <p>When you book with us, you know you're booking with the best in the business</p>
            </div>
            <div class="col-12">
                <ul class="offer-info-wrp">
                    <li>
                        <img src="images/cashback-icon.svg" alt="">
                        <div class="offer-info">
                            <strong>Get 8% Cashback</strong>
                            <span>on Flights with Travelsite</span>
                        </div>
                    </li>
                    <li>
                        <img src="images/discount-icon.svg" alt="">
                        <div class="offer-info">
                            <strong>Flat 12% Off on</strong>
                            <span>Flights via Mobiwik</span>
                        </div>
                    </li>
                    <li>
                        <img src="images/travel-icon.svg" alt="">
                        <div class="offer-info">
                            <strong>International</strong>
                            <span>Travel Guidlines</span>
                        </div>
                    </li>
                    <li>
                        <img src="images/voucher-icon.svg" alt="">
                        <div class="offer-info">
                            <strong>Book a flight</strong>
                            <span>Get valuable vouchers</span>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</section> -->
<section class="travel-reviews">
    <div class="container">
        <div class="row">
            <div class="col-12 hd-wraper white-txt">
                <strong>Read The Top</strong>
                <h4>Travel Reviews</h4>
                <p>Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aene an commodo ligula eget dolor. Aenean massa. Cum sociis the</p>
            </div>
        </div>
    </div>
    <div class="review-slider-wrapper">
        <div class="owl-carousel travel-reviews-carousel owl-theme owl-loaded">
            <div class="owl-stage-outer">
                <div class="owl-stage">
                    <?php foreach($resultReview as $resultReviews){?>
                        <div class="owl-item">
                            <div class="review-item row">
                                <div class="col-md-3">
                                    <!-- <img src="images/lady-img1.png" alt=""> -->
                                    <?php if($resultReviews['image'] == ''){ ?>
                                        <img src="uploads/reviews/logo.png" alt="">
                                    <?php }else{ ?>
                                        <img src="uploads/reviews/<?php echo $resultReviews['image']; ?>" alt="">
                                    <?php } ?>
                               </div>
                                <div class="review-info col-md-9">
                                    <h5><?php echo $resultReviews['title']?></h5>
                                    <ul>
                                    <?php 
                                        $rating = $resultReviews['rating'];
                                        for ($i = 1; $i <= 5; $i++) {
                                            if ($i <= $rating) {
                                                echo '<li><span class="star-rate"></span></li>'; // Display a filled star if $i is less than or equal to $rating
                                            } else {
                                                echo '<li><span class="star-rate-white"></span></li>'; // Display an empty star if $i is greater than $rating
                                            }
                                        }
                                    ?>
                                        <!-- <li><span class="star-rate"></span></li>
                                        <li><span class="star-rate"></span></li> -->
                                   </ul>
                                    <div class="txt-cntnt">
                                        <?php echo $resultReviews['description']; ?>
                                    </div>
                                    <strong><?php echo $resultReviews['author']?></strong>
                                </div>
                            </div>
                        </div>
                    <?php } ?>    
                 </div>
            </div>
        </div>
    </div>
</section>
<!-- Button trigger modal -->


<!--  Login Modal -->
<?php
require_once("includes/login-modal.php");
?>
<!--  forgot Modal -->
<?php
require_once("includes/forgot-modal.php");

?>



<?php
require_once("includes/footer.php");
include('loading-popup.php');
?>

<script>
    $(window).scroll(function() {
        var sc = $(window).scrollTop()
        var topH = $("header").outerHeight();
        if (sc > topH) {
            $("header").addClass("fixed")
        } else {
            $("header").removeClass("fixed")
        }
    });

    $('.midbar-carousel').carousel({
        interval: 2000,
        pause: false,
    })
    /************Datepicker******************/
    $(function() {
        var dateFormat = "mm/dd/yy",
            from = $("#from")
            .datepicker({
                //defaultDate: "+1w",
                changeMonth: true,
                minDate: 0,
            })
            .on("change", function() {
                to.datepicker("option", "minDate", getDate(this));
            }),
            to = $("#to").datepicker({
                //defaultDate: "+1w",
                changeMonth: true
            })
            .on("change", function() {
                from.datepicker("option", "maxDate", getDate(this));
            });

        function getDate(element) {
            var date;
            try {
                date = $.datepicker.parseDate(dateFormat, element.value);
            } catch (error) {
                date = null;
            }

            return date;
        }
    });


    $(function() {
        $(".date-multy-city").datepicker({
            dateFormat: "D, M d",
            minDate: 0
        });
    });

    // $(function () {
    //     $("#from").datepicker({
    //         onSelect: function (selectedDate) {
    //             var orginalDate = new Date(selectedDate);
    //             var monthsAddedDate = new Date(new Date(orginalDate).setMonth(orginalDate.getMonth() + 3));
    //             $("#to").datepicker("option", 'minDate', selectedDate);
    //             $("#to").datepicker("option", 'maxDate', monthsAddedDate);
    //         }
    //     });

    //     $("#to").datepicker({
    //         onSelect: function (selectedDate) {
    //             var orginalDate = new Date(selectedDate);
    //             var monthsAddedDate = new Date(new Date(orginalDate).setMonth(orginalDate.getMonth() - 3));
    //             $("#from").datepicker("option", 'minDate', monthsAddedDate);
    //             $("#from").datepicker("option", 'maxDate', selectedDate);
    //         }
    //     })
    // });
    /************Custom Play Button**********/
    const video = document.getElementById("video");
    const circlePlayButton = document.getElementById("circle-play-b");

    function togglePlay() {
        if (video.paused || video.ended) {
            video.play();
        } else {
            video.pause();
        }
    }

    circlePlayButton.addEventListener("click", togglePlay);
    video.addEventListener("playing", function() {
        circlePlayButton.style.opacity = 0;
    });
    video.addEventListener("pause", function() {
        circlePlayButton.style.opacity = 1;
    });
    /***************************************/
    $(document).ready(function() {
        $('.package-cat-slider').owlCarousel({
            loop: true,
            autoplay: true,
            margin: 0,
            nav: false,
            dots: false,
            smartSpeed: 500,
            animateOut: 'fadeOut',
            items: 1
        })
        $('.international-flights').owlCarousel({
            loop: true,
            autoplay: true,
            margin: 5,
            nav: false,
            dots: false,
            smartSpeed: 1000,
            responsive: {
                0: {
                    items: 1
                },
                600: {
                    items: 2
                },
                800: {
                    items: 4
                },
                1200: {
                    items: 6
                }
            }
        })
        $('.domestic-flights').owlCarousel({
            loop: true,
            autoplay: true,
            margin: 5,
            nav: false,
            dots: false,
            smartSpeed: 1000,
            responsive: {
                0: {
                    items: 1
                },
                600: {
                    items: 2
                },
                800: {
                    items: 4
                },
                1200: {
                    items: 6
                }
            }
        })
        $('.travel-reviews-carousel').owlCarousel({
            loop: true,
            autoplay: true,
            margin: 13,
            nav: false,
            dots: true,
            smartSpeed: 1000,
            responsive: {
                0: {
                    items: 1
                },
                800: {
                    items: 2
                },
                1200: {
                    items: 3
                }
            }
        })

        $('.select-class').select2();

        // $('[name=tab]').each(function(i,d){
        //     var p = $(this).prop('checked');
        //     //   console.log(p);
        //     if(p){
        //         $('.search-box').eq(i)
        //         .addClass('on');
        //     }    
        // });  

        // $('[name=tab]').on('change', function(){
        //     var p = $(this).prop('checked');

        //     // $(type).index(this) == nth-of-type
        //     var i = $('[name=tab]').index(this);

        //     $('.search-box').removeClass('on');
        //     $('.search-box').eq(i).addClass('on');
        // });

        $('.flight-search input').click(function() {
            if ($('#return').is(':checked')) {
                $("#to").show().next(".icon").show()
            } else(
                $("#to").hide().next(".icon").hide()
            )
            if ($('#multi-city').is(':checked')) {
                $(".search-box.multi-city-search").css("display", "flex").siblings().hide()
            } else(
                $(".search-box.multi-city-search").hide().siblings().show()
            )
        })


        // $('#multi-city').click(function() {
        //     $(".search-box.multi-city-search").show()
        //     $(".multi-city-search").siblings(".search-box").hide();
        // });


        $(".select-lbl").click(function() {
            $(this).parent(".person-select").toggleClass("open");
            $(".select-dropbox").toggle();
        })


        $('.add').on('click', function() {
            this.parentNode.querySelector('input[type=number]').stepUp();
        })
        $('.minus').on('click', function() {
            this.parentNode.querySelector('input[type=number]').stepDown();
        })


    });

    $(".text-below-button").click(function() {
        $(this).parents('.modal').modal('hide');
    });
    $(".forgot-passward > button").click(function() {
        $(this).parents('.modal').modal('hide');
    });

    $('#FlightSearchLoading').modal({
        show: false
    })

    /**************Scroll To Top*****************/
    $(window).on('scroll', function() {
        if (window.scrollY > window.innerHeight) {
            $('#scrollToTop').addClass('active')
        } else {
            $('#scrollToTop').removeClass('active')
        }
    })

    $('#scrollToTop').on('click', function() {
        $("html, body").animate({
            scrollTop: 0
        }, 500);
    })
    /**********************************************/
</script>

<script>
    
    // Fetching values and displaying them using alert
    function fetchAndAlert() {
        var adultCount = parseInt(document.getElementById("adult_count").value, 10);
        var childCount = parseInt(document.getElementById("child-count").value, 10);
        var infantCount = parseInt(document.getElementById("infant-count").value, 10);
        var totalCount = adultCount + childCount + infantCount;

        document.getElementById("totalCount").innerText = totalCount;
        // alert("Adults: " + adultCount + "\nChildren: " + childCount + "\nInfants: " + infantCount + "\nTotal: " + totalCount);
        
   }
</script>




</body>

</html>