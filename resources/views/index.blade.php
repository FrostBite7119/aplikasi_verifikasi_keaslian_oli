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
        <!-- Bootstrap Color Picker -->
        <link rel="stylesheet" href="/plugins/bootstrap-colorpicker/css/bootstrap-colorpicker.min.css">
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
            <div id="mainContent" class="content-wrapper" style="margin-left: 0px; display: none;">
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
                    <div id="verification-content" class="container-fluid" style="display: none;">
                        <div class="row justify-content-center">
                            <div class="col-10 col-md-4">
                                <div class="card card-default">
                                    <div class="card-body">
                                        <!-- Product Image Section -->
                                        <div class="text-center">
                                            <img id="product-image" src="https://www.ahm.to/assets/image/MPX1.jpg" alt="MPX1 0.8L" style="width: 100%; height: auto;">                                                    
                                        </div>
                                        <h4 class="mt-3 mb-0"><b id="product-name"></b></h4>
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
                                                <span style="font-weight: 600; color: #333; font-size: 16px;">(<span id="total-scans"></span>/<span id="scan-limit"></span>)</span>
                                            </div>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-6">
                                                <label style="color: #666; font-size: 14px;">Qr Code</label>
                                            </div>
                                            <div class="col-6 text-right">
                                                <span id="product-qrcode" style="font-weight: 600; color: #333; font-size: 14px;"></span>
                                            </div>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-6">
                                                <label style="color: #666; font-size: 14px;">Serial Number</label>
                                            </div>
                                            <div class="col-6 text-right">
                                                <span id="product-serial-number" style="font-weight: 600; color: #333; font-size: 16px;"></span>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- /.card-body -->
                                </div>
                                <div class="card card-default">
                                    <div class="card-body">
                                        <h5><b>Deskripsi</b></h5>                                        
                                        <p id="description-box"></p>
                                        <h5><b>Spesifikasi</b></h5>                                        
                                        <p id="specification-box"></p>
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
                    <div id="limit-exceeded-content" class="container-fluid" style="display: none;">
                        <div class="row justify-content-center align-items-center" style="min-height: 90vh;">
                            <div class="col-10 col-md-4">
                                <!-- Warning Icon -->
                                <div class="text-center mb-4">
                                    <i class="fas fa-exclamation-triangle" style="font-size: 120px; color: #e8a527;"></i>
                                </div>
                                
                                <!-- Warning Message -->
                                <div class="text-center mb-4">
                                    <h5 style="color: #5a5a5a; font-weight: 600; margin-bottom: 6px;">
                                        Produk telah melewati batas scan (<span id="limit-total-scans"></span>/<span id="limit-scan-limit"></span>)
                                    </h5>
                                    <p style="color: #999; font-size: 14px; line-height: 1.6; margin-bottom: 10px;">
                                        Produk yang Anda scan sudah melewati batas scan, sehingga tidak dapat diproses lebih lanjut.
                                    </p>
                                    <p style="color: #999; font-size: 14px; line-height: 1.6;">
                                        Silahkan periksa kembali produk yang Anda scan atau hubungi penjual untuk keterangan lebih lanjut.
                                    </p>
                                </div>
                                
                                <!-- Report Button -->
                                <div class="row">
                                    <div class="col-12">
                                        <a class="btn btn-danger btn-block">Lapor</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- /.container-fluid -->
                </section>
                <!-- /.content -->
            </div>
            <div id="notFoundContent" class="content-wrapper" style="margin-left: 0px; display: none;">
                <div class="container-fluid">
                    <div class="row justify-content-center align-items-center" style="min-height: 90vh;">
                        <div class="col-10 col-md-4">
                            <!-- Icon -->
                            <div class="text-center mb-4">
                                <i class="fas fa-times-circle" style="font-size: 120px; color: #dc3545;"></i>
                            </div>
                            
                            <!-- Message -->
                            <div class="text-center mb-4">
                                <h4 style="color: #333; font-weight: 700; margin-bottom: 16px;">
                                    Indikasi Barang Palsu
                                </h4>
                                <p style="color: #666; font-size: 14px; line-height: 1.6; margin-bottom: 10px;">
                                    Produk yang Anda scan terindikasi barang palsu.
                                </p>
                                <p style="color: #666; font-size: 14px; line-height: 1.6;">
                                    Silahkan tanyakan kepada penjual atau melaporkan kepada kami untuk keterangan lebih lanjut.
                                </p>
                            </div>
                            
                            <!-- Report Button -->
                            <div class="row">
                                <div class="col-12">
                                    <a class="btn btn-danger btn-block">Lapor</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /.content-wrapper -->
        </div>
        <!-- ./wrapper -->
        <!-- jQuery -->
        <script src="/plugins/jquery/jquery.min.js"></script>
        <!-- Bootstrap 4 -->
        <script src="/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
        <!-- AdminLTE App -->
        <script src="/dist/js/adminlte.min.js"></script>
        <!-- Google Maps -->
        <script>
            (g=>{var h,a,k,p="The Google Maps JavaScript API",c="google",l="importLibrary",q="__ib__",m=document,b=window;b=b[c]||(b[c]={});var d=b.maps||(b.maps={}),r=new Set,e=new URLSearchParams,u=()=>h||(h=new Promise(async(f,n)=>{await (a=m.createElement("script"));e.set("libraries",[...r]+"");for(k in g)e.set(k.replace(/[A-Z]/g,t=>"_"+t[0].toLowerCase()),g[k]);e.set("callback",c+".maps."+q);a.src=`https://maps.${c}apis.com/maps/api/js?`+e;d[q]=f;a.onerror=()=>h=n(Error(p+" could not load."));a.nonce=m.querySelector("script[nonce]")?.nonce||"";m.head.append(a)}));d[l]?console.warn(p+" only loads once. Ignoring:",g):d[l]=(f,...n)=>r.add(f)&&u().then(()=>d[l](f,...n))})({
                key: "AIzaSyBTU7A1Jmp5qCNLLhcUQD_RC_GadJWecl4",
                v: "weekly",
                // Use the 'v' parameter to indicate the version to use (weekly, beta, alpha, etc.).
                // Add other bootstrap parameters asneeded, using camel case.
            }); 
        </script>
        <!-- Page specific script -->
        <script>
            $(function () {
                @if(isset($error))
                    alert('{{ $error }}');
                    $('body').html('');
                    return;
                @else
                    // Check if geolocation is supported
                    if (!navigator.geolocation) {
                        alert('Geolocation tidak didukung oleh browser Anda. Halaman tidak dapat ditampilkan.');
                        $('body').html('');
                        return;
                    }

                    // Wait for Google Maps to load before requesting location
                    google.maps.importLibrary("geocoding").then(() => {
                        requestLocation();
                    });

                    // Function to request user location
                    function requestLocation() {
                        navigator.geolocation.getCurrentPosition(
                            function(position) {
                                // Success - location granted
                                const latitude = position.coords.latitude;
                                const longitude = position.coords.longitude;

                                const geocoder = new google.maps.Geocoder();
                                geocoder.geocode({location: {lat: latitude, lng: longitude}}, function(results, status) {
                                    if (status === 'OK') {
                                        if (results[0]) {
                                            $.ajax({
                                                url: '/store',
                                                method: 'POST',
                                                data: {
                                                    qrcode: '{{ $qrcode }}',
                                                    scan_location: results[0].formatted_address,
                                                    city: results[0].address_components.find(comp => comp.types.includes('administrative_area_level_2'))?.long_name || 'N/A',
                                                    province: results[0].address_components.find(comp => comp.types.includes('administrative_area_level_1'))?.long_name || 'N/A',
                                                    latitude: latitude,
                                                    longitude: longitude,
                                                    _token: '{{ csrf_token() }}'
                                                },
                                                success: function(response) {
                                                    if(response.status === 'success'){
                                                        $('#product-name').text(response.data.product_name);
                                                        $('#total-scans').text(response.data.total_scans);
                                                        $('#product-qrcode').text(response.data.qrcode);
                                                        $('#product-serial-number').text(response.data.serial_number);
                                                        $('#serial-number').text(response.data.serial_number);
                                                        $('#scan-limit').text(response.data.scan_limit);
                                                        $('#description-box').html(response.data.description);
                                                        $('#specification-box').html(response.data.specification);
                                                        // $('#product-image').attr('src', response.data.product_image);

                                                        // Show content
                                                        $('#mainContent').show();
                                                        $('notFoundContent').html('');
                                                        $('#verification-content').show();
                                                    }else if(response.status === 'limit_exceeded'){
                                                        $('#limit-total-scans').text(response.data.scan_limit);
                                                        $('#limit-scan-limit').text(response.data.scan_limit);

                                                        $('#mainContent').show();
                                                        $('notFoundContent').html('');
                                                        $('#limit-exceeded-content').show();
                                                    }else if(response.status === 'ip_limit_exceeded'){
                                                        $('body').html('');
                                                        alert('Anda telah mencapai batas maksimum scan produk. Permintaan Anda tidak dapat diproses lebih lanjut.');
                                                    }
                                                    else if(response.status === 'not_found'){
                                                        $('#notFoundContent').show();
                                                        $('#mainContent').html('');
                                                    }
                                                },
                                                error: function(xhr, status, error) {
                                                    $('body').html('');
                                                    alert('Tidak dapat menampilkan halaman. Silakan coba lagi nanti.');
                                                }
                                            });                                        
                                        } else {
                                            $('body').html('');
                                            alert('Tidak dapat menampilkan halaman. Silakan coba lagi nanti.');
                                        }
                                    } else {
                                        $('body').html('');
                                        console.log('Geocode gagal karena: ' + status);
                                    }
                                });
                            },
                            function(error) {
                                // Error - location denied or failed
                                let errorMessage = '';
                                switch(error.code) {
                                    case error.PERMISSION_DENIED:
                                        errorMessage = 'Anda menolak izin akses lokasi. Halaman tidak dapat ditampilkan.';
                                        break;
                                    case error.POSITION_UNAVAILABLE:
                                        errorMessage = 'Informasi lokasi tidak tersedia. Pastikan GPS atau layanan lokasi aktif. Halaman tidak dapat ditampilkan.';
                                        break;
                                    case error.TIMEOUT:
                                        errorMessage = 'Permintaan lokasi timeout. Halaman tidak dapat ditampilkan.';
                                        break;
                                    default:
                                        errorMessage = 'Terjadi kesalahan saat mengambil lokasi. Halaman tidak dapat ditampilkan.';
                                }
                                
                                $('body').html('');
                                alert(errorMessage);
                            },
                            {
                                enableHighAccuracy: true,
                                timeout: 10000,
                                maximumAge: 0
                            }
                        );
                    }
                @endif
            })
        </script>
    </body>
</html>