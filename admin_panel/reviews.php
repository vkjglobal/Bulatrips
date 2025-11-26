<?php 
 session_start();
if(!isset($_SESSION['adminid'])){
?>
	<script>
    window.location="index.php"    </script>
    <?php
}

 include_once "includes/header.php";
  include_once "includes/class.reviews.php";
$objReviews		= 	new Reviews();
$reviews	    =   $objReviews->getListReviews();
//echo "<pre/>";print_r($reviews);
 ?>            <!-- Product List Start -->
            <div class="container-fluid h-100 pt-4 px-4">
                <div class="border-primary rounded p-4">
                    <strong class="fs-16 fw-500 light-blue-txt mb-3 d-block text-left">Reviews</strong>
                    <div class="d-flex align-items-center justify-content-end mb-4">
                      <!--  <a href="contact-list.php">Show All</a> -->
                    </div>
                    <div>
                    <?php
                   
                    foreach($reviews as $k => $val){ 
                        $title  =   $val['title'];
                        $description  =   $val['description'];
                        $author  =   $val['author'];
                       $image  =   $val['image'];
                       $ratingCount  =   $val['rating'];
                       $status  =   $val['status'];
                       $id  =$val['review_id'];
                    //    $status=$val['status'];
                       //profile image display
                       $profileImageURL =     "img/photos/".$image;
               
            if ((empty($image)) || (!file_exists($profileImageURL))) {
                $dummyImageURL = "uploads/profile/test1.jpg"; // URL of the dummy image
                $profileImageURL = $dummyImageURL;
            }    
                      //  echo "<pre/>";print_r($val);
                        ?>
                        <div class="review-item row g-lg-4 g-2">
                            <!-- <div class="col-md-3">
                                <img src="<?php echo $profileImageURL; ?>" alt="">
                            </div> -->
                            <div class="review-info col-md-9">
                                <div class="mb-2">
                                    <label class="form-label mb-1">Title</label>
                                    <input type="text" class="form-control" name="title_<?php echo $id; ?>" id="title_<?php echo $id; ?>" value="<?php echo htmlspecialchars($title); ?>">
                                </div>
                                <div class="mb-2">
                                    <label class="form-label mb-1">Rating</label>
                                    <select class="form-select" name="rating_<?php echo $id; ?>" id="rating_<?php echo $id; ?>">
                                        <?php for($i = 1; $i <= 5; $i++) { ?>
                                            <option value="<?php echo $i; ?>" <?php if($ratingCount == $i){ echo 'selected'; } ?>><?php echo $i; ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label mb-1">Description</label>
                                    <textarea class="form-control" rows="3" name="description_<?php echo $id; ?>" id="description_<?php echo $id; ?>"><?php echo htmlspecialchars($description); ?></textarea>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label mb-1">Customer / Client Name</label>
                                    <input type="text" class="form-control" name="author_<?php echo $id; ?>" id="author_<?php echo $id; ?>" value="<?php echo htmlspecialchars($author); ?>">
                                </div>
                                <div class="mt-2">
                                    <button type="button" class="btn btn-sm btn-primary me-2" onclick="updateReview(<?php echo $id; ?>)">Update</button>
                                    <button type="button" class="btn btn-sm btn-secondary" onclick="toggleStatus(<?php echo $id.','.$status; ?> )"><?php if($status == 1){ echo 'Hide';}else{echo 'Unhide'; } ?></button>
                                </div>
                            </div>
                        </div>
                       
                            <?php } ?>
                       
                       
                    </div>
                </div>
            </div>
            <!-- Product List End -->
             <!--Start -->
            <div class="modal fade" id="successpop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
                aria-labelledby="addMore" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0 shadow-sm">
                        <div class="modal-body p-4">
                            <div class="alert alert-success mb-0 text-center" id="successMessage">
                                Successfully updated
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--  End -->

            <!-- View Message -->
            <div class="modal fade" id="viewMessage" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
                aria-labelledby="viewMessage" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h1 class="modal-title fs-5 text-secondary" id="staticBackdropLabel">Message</h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form action="" class="row">
                                <div class="col-12 mb-2">
                                    <strong class="d-block">Test</strong>
                                    <span>test@gmail.com</span>
                                </div>
                                <div class="col-12 mb-2">
                                    <p>
                                        Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.
                                    </p>
                                </div>
                                <div class="col-12 d-flex justify-content-end">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End View Message -->
            <!-- Send Message -->
            <div class="modal fade" id="sendMessage" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
                aria-labelledby="sendMessage" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h1 class="modal-title fs-5 text-secondary" id="staticBackdropLabel">Send Reply</h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form action="" class="row">
                                <div class="col-12 mb-2">
                                    <label for="">Subject</label>
                                    <input type="text" class="form-control">
                                </div>
                                <div class="col-12 mb-2">
                                    <label for="">Message</label>
                                    <textarea name="" id="" cols="30" rows="6" class="form-control"></textarea>
                                </div>
                                <div class="col-12 d-flex justify-content-end">
                                    <button type="submit" class="btn btn-secondary">Send</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End Send Message -->
            
            <?php 
            include "includes/footer.php";
  ?>
  
  <script>
  function toggleStatus(rowId,status) {
    //  alert(rowId);return false;
  
  // Perform an AJAX request or any other method to update the database with the new status
  // You can use the rowId to identify the specific row in the database

  // Example AJAX request using jQuery
  $.ajax({
    url: 'process',
    method: 'POST',
    data: { rowId: rowId,status: status },
    success: function(response) {
      if ($.trim(response) == 'success') {
        var msg = (status == 1) ? 'Review successfully hidden.' : 'Review successfully unhidden.';
        $('#successMessage').text(msg);
        $('#successpop').modal('show');
        setTimeout(function() {
          location.reload();
        }, 1500);
        return false;
      }
        else if(($.trim(response) == 'err1') || ($.trim(response) == 'err2')){
            alert("error");
        }
      // Handle the success response
      // For example, you can show a success message or update the UI accordingly
      //showSuccessMessage('Status updated successfully');
    },
    error: function(xhr, status, error) {
      // Handle the error response
      // For example, you can show an error message or handle the error condition
      console.log('Error updating status:', error);
    }
  });
}

function updateReview(reviewId) {
  var title = $('#title_' + reviewId).val();
  var description = $('#description_' + reviewId).val();
  var rating = $('#rating_' + reviewId).val();
  var author = $('#author_' + reviewId).val();

  $.ajax({
    url: 'process',
    method: 'POST',
    data: {
      action: 'update_review',
      reviewId: reviewId,
      title: title,
      description: description,
      rating: rating,
      author: author
    },
    success: function(response) {
      if ($.trim(response) == 'update_success') {
        $('#successMessage').text('Review updated successfully.');
        $('#successpop').modal('show');
        setTimeout(function() {
          location.reload();
        }, 1500);
        return false;
      } else {
        alert('Error while updating review');
      }
    },
    error: function(xhr, status, error) {
      console.log('Error updating review:', error);
    }
  });
}

</script>