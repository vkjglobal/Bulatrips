<?php
session_start();
if (!isset($_SESSION['adminid'])) {
    ?>
    <script>
        window.location = "index.php"    </script>
    <?php
} else {
    include "includes/dbConnect.php";

    $query = "SELECT * FROM contact";
    $rs_result = mysqli_query($conn, $query);

    ?>

    <?php
    include "includes/header.php";
    ?>


    <!-- Product List Start -->
    <div class="container-fluid h-100 pt-4 px-4">
        <div class="border-primary rounded p-4">
            <strong class="fs-16 fw-500 light-blue-txt mb-3 d-block text-left">Contact Information</strong>
            <div class="d-flex align-items-center justify-content-end mb-4">
                <a href="contact-list.php">Show All</a>
            </div>
            <div class="table-responsive">
                <table class="table text-start align-middle table-bordered table-hover mb-0 contact-list-table">
                    <thead>
                        <tr class="">
                            <th scope="col">Sl No.</th>
                            <th scope="col">Customer Name</th>
                            <th scope="col">Email</th>
                            <th scope="col">Subject</th>
                            <th scope="col">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // $ret=mysqli_query($conn,"select * from product where status='active'");
                        $cnt = 1;
                        $row = mysqli_num_rows($rs_result);
                        if ($row > 0) {
                            while ($row = mysqli_fetch_array($rs_result)) {
                                $contact_id = "viewMessage_" . $row['id'];
                                $send_id = "sendMessage_" . $row['id'];
                                $form_id = "form_" . $row['id'];

                                ?>
                                <tr>
                                    <td><?php echo $cnt; ?></td>

                                    <td><?php echo $row['customer_name']; ?></td>
                                    <td><?php echo $row['email']; ?></td>
                                    <td><?php echo $row['subject']; ?></td>
                                    <td>
                                        <button class="btn text-secondary view" data-bs-toggle="modal"
                                            data-bs-target="#<?php echo $contact_id; ?>" id="<?php echo $row['id']; ?>">
                                            <i class="fa fa-eye">&nbsp;</i>
                                        </button>
                                        <button class="btn btn-sm btn-secondary" data-bs-toggle="modal"
                                            data-bs-target="#<?php echo $send_id; ?>">Send Reply</button>
                                    </td>

                                </tr>
                                <!-- ================== -->
                                <div class="modal fade" id="<?php echo $contact_id; ?>" data-bs-backdrop="static"
                                    data-bs-keyboard="false" tabindex="-1" aria-labelledby="viewMessage" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h1 class="modal-title fs-5 text-secondary" id="staticBackdropLabel">
                                                    <?php echo $row['subject']; ?>
                                                </h1>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                    aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <form action="" class="row">
                                                    <div class="col-12 mb-2">
                                                        <strong class="d-block"><?php echo $row['customer_name']; ?></strong>
                                                        <span><?php echo $row['email']; ?></span>
                                                    </div>
                                                    <div class="col-12 mb-2">
                                                        <p>
                                                            <?php echo $row['message']; ?>
                                                        </p>
                                                    </div>
                                                    <div class="col-12 d-flex justify-content-end">
                                                        <button type="button" class="btn btn-secondary"
                                                            data-bs-dismiss="modal">Close</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- ==============view messgae ends ======= -->
                                <!-- Send Message -->
                                <div class="modal fade" id="<?php echo $send_id; ?>" data-bs-backdrop="static"
                                    data-bs-keyboard="false" tabindex="-1" aria-labelledby="sendMessage" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h1 class="modal-title fs-5 text-secondary" id="staticBackdropLabel">Send Reply</h1>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                    aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">

                                                <div class="col-12 mb-2">
                                                    <label for="">Subject</label>
                                                    <input type="text" class="form-control" id="subject">
                                                    <span class="errortext" id="subjectError" style="color:red"></span>
                                                </div>
                                                <div class="col-12 mb-2">
                                                    <label for="">Message</label>
                                                    <textarea name="" id="message" cols="30" rows="6"
                                                        class="form-control"></textarea>
                                                    <span class="errortext" id="messageError" style="color:red"></span>

                                                </div>
                                                <div class="col-12 d-flex justify-content-end">
                                                    <button type="button" class="btn btn-secondary" onclick="myFunction('<?php echo $row['email']; ?>', '<?php echo $send_id; ?>')">Send</button>
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- End Send Message -->
                                <?php
                                $cnt = $cnt + 1;
                            }
                        } else { ?>
                            <tr>
                                <th style="text-align:center; color:red;" colspan="6">No Record Found</th>
                            </tr>
                        <?php } ?>


                    </tbody>
                </table>



            </div>
        </div>
    </div>
    <!-- Product List End -->
    <?php
    include "includes/footer.php";
    ?>
    <script>


        function myFunction(email, modalId) {
            // Prevent form submission
            event.preventDefault();

            // Get the form fields within the specific modal
            var subject = document.querySelector("#" + modalId + " #subject").value;
            var message = document.querySelector("#" + modalId + " #message").value;
            var subjectError = document.querySelector("#" + modalId + " #subjectError");
            var messageError = document.querySelector("#" + modalId + " #messageError");

            // Clear previous errors
            subjectError.textContent = '';
            messageError.textContent = '';

            // Validate fields
            if (subject.trim() === '') {
                subjectError.textContent = "Enter subject";
                return false;
            }
            if (message.trim() === '') {
                messageError.textContent = 'Enter Message';
                return false;
            }

            // Perform AJAX request
            $.ajax({
                url: 'contact_us_mail.php', // Replace with your form processing script
                type: 'POST',
                data: { subject: subject, message: message, email: email, email_flag: "no" },
                success: function (response) {
                    if (parseInt(response) === 1) {
                        console.log("asd");
                        messageError.textContent = 'Message sent Successfully';
                        console.log("123");
                    
                        $.ajax({
                            url: 'contact_us_mail.php',
                            type: 'POST',
                            data: { subject: subject, message: message, email: email, email_flag: "yes" },
                            success: function (response) {}
                        });

                    } else {
                        messageError.textContent = 'Failed to send';
                    }
                                    },
                error: function (xhr, status, error) {
                    console.log(error);
                }
            });
        }
    </script>
    <!-- End Send Message -->



    <?php
}
?>