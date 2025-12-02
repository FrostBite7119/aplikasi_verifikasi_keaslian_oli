<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>AdminLTE 3 | Advanced form elements</title>
        <!-- Google Font: Source Sans Pro -->
        <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
        <!-- Font Awesome -->
        <link rel="stylesheet" href="/plugins/fontawesome-free/css/all.min.css">
        <!-- daterange picker -->
        <link rel="stylesheet" href="/plugins/daterangepicker/daterangepicker.css">
        <!-- iCheck for checkboxes and radio inputs -->
        <link rel="stylesheet" href="/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
        <!-- Bootstrap Color Picker -->
        <link rel="stylesheet" href="/plugins/bootstrap-colorpicker/css/bootstrap-colorpicker.min.css">
        <!-- Select2 -->
        <link rel="stylesheet" href="/plugins/select2/css/select2.min.css">
        <link rel="stylesheet" href="/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css">
        <!-- Theme style -->
        <link rel="stylesheet" href="/dist/css/adminlte.min.css">
        <style>
            @keyframes marquee {
                0% { transform: translateX(0); }
                100% { transform: translateX(-100%); }
            }
            .marquee-content {
                animation: marquee 20s linear infinite;
            }
        </style>
    </head>
    <body>        
        <div class="wrapper">
            <!-- Content Wrapper. Contains page content -->
            <div class="content-wrapper" style="margin-left: 0px;">
                <!-- Running Text Banner -->
                <div style="background: #e8a527; padding: 12px 0; overflow: hidden; position: relative;">
                    <div class="marquee-container" style="white-space: nowrap; overflow: hidden;">
                        <div class="marquee-content" style="display: inline-block; padding-left: 100%; animation: marquee 8s linear infinite;">
                            <span style="color: white; font-size: 15px; font-weight: 500; letter-spacing: 0.5px;">
                                Cek keaslian hanya di situs resmi kami qr.yimm.co.id
                            </span>
                        </div>
                    </div>
                </div>
                <!-- Main content -->
                <section class="content mt-3">
                    <div class="container-fluid">
                        <div class="row justify-content-center">
                            <div class="col-10 col-md-4">
                                <div class="card card-default">
                                    <div class="card-body">
                                        <!-- Product Image Section -->
                                        <div class="text-center">
                                            <img src="https://www.ahm.to/assets/image/MPX1.jpg" alt="MPX1 0.8L" style="width: 100%; height: auto;">                                                    
                                        </div>
                                        <h4 class="mt-3 mb-0"><b>MPX1 0.8L</b></h4>
                                        <!-- /.Product Image Section -->
                                    </div>
                                    <!-- /.card-body -->
                                </div>
                                <div class="card card-default">
                                    <div class="card-body">
                                        <!-- QR Valid Title -->
                                        <h5><b>Informasi Verifikasi Produk</b></h5>
                                        
                                        <!-- Info Rows -->
                                        <div class="row">
                                            <div class="col-6">
                                                <label style="color: #666; font-size: 14px;">Total scan dilakukan</label>
                                            </div>
                                            <div class="col-6 text-right">
                                                <span style="font-weight: 600; color: #333; font-size: 16px;">(4/5)</span>
                                            </div>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-6">
                                                <label style="color: #666; font-size: 14px;">Qr Code</label>
                                            </div>
                                            <div class="col-6 text-right">
                                                <span style="font-weight: 600; color: #333; font-size: 14px;">https://ahm.to/nDJIOINZ</span>
                                            </div>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-6">
                                                <label style="color: #666; font-size: 14px;">Serial Number</label>
                                            </div>
                                            <div class="col-6 text-right">
                                                <span style="font-weight: 600; color: #333; font-size: 16px;">357929201</span>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- /.card-body -->
                                </div>
                                <div class="card card-default">
                                    <div class="card-body">
                                        <h5><b>Deskripsi</b></h5>                                        
                                        <p>
                                            Lorem ipsum dolor sit amet consectetur adipisicing elit. Id exercitationem dolorum nobis autem praesentium vel consequatur, vero sint officia, minima, ipsa saepe non porro odit aut. Consequuntur suscipit a accusantium!
                                        </p>
                                        <h5><b>Spesifikasi</b></h5>                                        
                                        <p>
                                            Lorem ipsum dolor sit amet consectetur adipisicing elit. Id exercitationem dolorum nobis autem praesentium vel consequatur, vero sint officia, minima, ipsa saepe non porro odit aut. Consequuntur suscipit a accusantium!
                                        </p>
                                        <div class="row">
                                            <div class="col-12">
                                                <a class="btn btn-danger btn-block">Lapor</a>
                                            </div>
                                        </div>                                        
                                    </div>
                                    <!-- /.card-body -->
                                </div>
                                <!-- /.card -->                                 
                            </div>
                        </div>
                        <!-- /.card -->
                    </div>
                    <!-- /.container-fluid -->
                </section>
                <!-- /.content -->
            </div>
            <!-- /.content-wrapper -->
        </div>
        <!-- ./wrapper -->
        <!-- jQuery -->
        <script src="/plugins/jquery/jquery.min.js"></script>
        <!-- Bootstrap 4 -->
        <script src="/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
        <!-- Select2 -->
        <script src="/plugins/select2/js/select2.full.min.js"></script>
        <!-- AdminLTE App -->
        <script src="/dist/js/adminlte.min.js"></script>
        <!-- Page specific script -->
        <script>
            $(function () {
              //Initialize Select2 Elements
              $('.select2').select2()
            
              //Initialize Select2 Elements
              $('.select2bs4').select2({
                theme: 'bootstrap4'
              })
            })
        </script>
    </body>
</html>