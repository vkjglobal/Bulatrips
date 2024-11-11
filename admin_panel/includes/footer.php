
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
    <!-- <script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/5/tinymce.min.js" referrerpolicy="origin"></script> -->
    <script src="https://cdn.tiny.cloud/1/324g1qw8kre1n3ufyghsptr00quegxl494u9y6w8b6zvdbvk/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
    

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

tinymce.init({
            selector: '#editor',  // Replace with the ID or class of your textarea
            plugins: 'print preview importcss searchreplace autolink directionality visualblocks visualchars fullscreen link codesample table charmap hr pagebreak nonbreaking anchor insertdatetime advlist lists wordcount help charmap quickbars',
            toolbar: 'undo redo | bold italic underline strikethrough | fontselect fontsizeselect formatselect | alignleft aligncenter alignright alignjustify | outdent indent | numlist bullist | forecolor backcolor removeformat | pagebreak | charmap emoticons | fullscreen preview save print | insertfile link anchor codesample',
            menubar: 'file edit view insert format tools table help',
            height: 500,  // Optional: set the editor height
            images_upload_url: 'upload.php',
            file_picker_types: 'image',
            automatic_uploads: true,
            branding: false,  // Optional: remove TinyMCE branding
            quickbars_selection_toolbar: 'bold italic | quicklink h2 h3 blockquote quickimage quicktable',
            contextmenu: 'link image imagetools table',
            content_style: "body { font-family:Helvetica,Arial,sans-serif; font-size:14px }",
        });

 
    var ps = new PerfectScrollbar('.sidebar');


    tinymce.init({
            selector: '#histogrameditor',  // Replace with the ID or class of your textarea
            plugins: 'print preview importcss searchreplace autolink directionality visualblocks visualchars fullscreen link codesample table charmap hr pagebreak nonbreaking anchor insertdatetime advlist lists wordcount help charmap quickbars',
            toolbar: 'undo redo | bold italic underline strikethrough | fontselect fontsizeselect formatselect | alignleft aligncenter alignright alignjustify | outdent indent | numlist bullist | forecolor backcolor removeformat | pagebreak | charmap emoticons | fullscreen preview save print | insertfile link anchor codesample',
            menubar: 'file edit view insert format tools table help',
            height: 500,  // Optional: set the editor height
            images_upload_url: 'upload.php',
            file_picker_types: 'image',
            automatic_uploads: true,
            branding: false,  // Optional: remove TinyMCE branding
            quickbars_selection_toolbar: 'bold italic | quicklink h2 h3 blockquote quickimage quicktable',
            contextmenu: 'link image imagetools table',
            content_style: "body { font-family:Helvetica,Arial,sans-serif; font-size:14px }",
        });
  
  CKEDITOR.replace('histogrameditor', {
    height: 200
  });
 
 </script>

</body>

</html>