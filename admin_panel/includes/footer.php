
            <!-- Footer Start -->

            <div class="container-fluid pt-4 px-0 footer-section">
                <div class="bg-primary p-4">
                    <div class="row">
                        <div class="col-12 text-center text-lg-end small credit">
                            Designed By <a href="https://htmlcodex.com">HTML Codex</a>Reubro International
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer End -->
        </div>
        <!-- Content End -->


        <!-- Back to Top -->
        <!-- <a href="#" class="btn btn-lg btn-primary btn-lg-square back-to-top"><i class="bi bi-arrow-up"></i></a> -->
    </div>

    <!-- JavaScript Libraries -->
    <script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/5/tinymce.min.js" referrerpolicy="origin"></script>

    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="lib/chart/chart.min.js"></script>
    <script src="lib/easing/easing.min.js"></script>
    <script src="lib/waypoints/waypoints.min.js"></script>
    <script src="lib/owlcarousel/owl.carousel.min.js"></script>
    <script src="lib/tempusdominus/js/moment.min.js"></script>
    <script src="lib/tempusdominus/js/moment-timezone.min.js"></script>
    <script src="lib/tempusdominus/js/tempusdominus-bootstrap-4.min.js"></script>
   <!--  <script src="http://localhost/mcst/ckeditor/config.js"></script> -->
    <script src="lib/perfectscrollbar/perfect-scrollbar.min.js"></script>
    <!-- Template Javascript -->
    <script src="js/main.js"></script>
    <script src="js/general.js"></script>
    <script>
//  tinymce.init({
//   selector: 'textarea#editor',  //Change this value according to your HTML
//   auto_focus: 'element1',
//   width: "700",
//   height: "200"
//  }); 
 
    var ps = new PerfectScrollbar('.sidebar');

  CKEDITOR.replace('editor', {
    height: 500,
    allowedContent: true,
    // extraAllowedContent: '*[*]',
    versionCheck:false
  });
  
  CKEDITOR.replace('histogrameditor', {
    height: 200
  });
 
 </script>

</body>

</html>