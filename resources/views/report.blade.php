<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>AdminLTE 3 | Log in</title>
        <!-- Google Font: Source Sans Pro -->
        <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
        <!-- Font Awesome -->
        <link rel="stylesheet" href="/plugins/fontawesome-free/css/all.min.css">
        <!-- icheck bootstrap -->
        <link rel="stylesheet" href="/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
        <!-- Select2 -->
        <link rel="stylesheet" href="/plugins/select2/css/select2.min.css">
        <link rel="stylesheet" href="/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css">
        <!-- Theme style -->
        <link rel="stylesheet" href="/dist/css/adminlte.min.css">
    </head>
    <body class="hold-transition login-page">
        <div class="login-box">
            <!-- /.login-logo -->
            <div class="card">
                <div class="card-body">
                    @if($errors->any())
                    <div class="alert alert-danger alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                        <h5><i class="icon fas fa-ban"></i> Alert!</h5>
                        <ul>
                            @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif
                    <form id="report-form" action="/report/store" method="post" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="scan_id" value="{{ $scan ? $scan->scan_id : '' }}">
                        @if(!$scan)
                        <input type="hidden" id="address-input" name="address">
                        <input type="hidden" id="city-input" name="city">
                        <input type="hidden" id="province-input" name="province">
                        <input type="hidden" id="latitude-input" name="latitude">
                        <input type="hidden" id="longitude-input" name="longitude">
                        @endif
                        <div class="form-group">
                            <label for="name-input">Nama</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name-input" name="name" value="{{ old('name') }}" placeholder="Masukan nama" required>
                            @error('name')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="phone-number-input">Nomor Telepon</label>
                            <input type="tel" class="form-control @error('phone_number') is-invalid @enderror" id="phone-number-input" name="phone_number" value="{{ old('phone_number') }}" placeholder="Masukkan nomor telepon" required>
                            @error('phone_number')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                            @enderror
                        </div>
                        @if($needProductColumn)
                        <div class="form-group">
                            <label>Produk</label>
                            <select class="select2 @error('product_id') is-invalid @enderror" data-placeholder="Pilih produk" name="product_id" style="width: 100%;" required>
                                <option value=""></option>
                                @foreach($products as $product)
                                <option value="{{ $product->id }}" {{ old('product_id') == $product->id ? 'selected' : '' }}>{{ $product->name }}</option>
                                @endforeach
                            </select>
                            @error('product_id')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                            @enderror
                        </div>
                        @endif
                        <div class="form-group">
                            <label>Alasan Lapor</label>
                            <select class="select2" multiple="multiple" data-placeholder="Alasan melapor" style="width: 100%;" name="reportReasons[]" required>
                                <option value=""></option>
                                @foreach($reportReasons as $reason)
                                <option value="{{ $reason->id }}">{{ $reason->reason }}</option>
                                @endforeach
                            </select>
                        </div>
                        <!-- textarea -->
                        <div class="form-group">
                            <label>Deskripsi</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" rows="3" name="description" placeholder="Deskripsi ...">{{ old('description') }}</textarea>
                            @error('description')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="product-image-input">Bukti Foto</label>
                            <div class="custom-file">
                                <input id="product-image-input" type="file" class="custom-file-input @error('image') is-invalid @enderror" name="image">
                                <label class="custom-file-label" for="product-image-input">Pilih bukti foto...</label>
                                @error('image')
                                <div class="invalid-feedback">
                                    {{ $message}}
                                </div>
                                @enderror
                            </div>
                        </div>
                        <div class="text-center mb-3">
                            <button id="submit-button" type="submit" class="btn btn-block btn-danger">Lapor</button>
                        </div>
                    </form>
                </div>
                <!-- /.login-card-body -->
            </div>
        </div>
        <!-- /.login-box -->
        <!-- jQuery -->
        <script src="/plugins/jquery/jquery.min.js"></script>
        <!-- Bootstrap 4 -->
        <script src="/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
        <!-- Select2 -->
        <script src="/plugins/select2/js/select2.full.min.js"></script>
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
        <!-- bs-custom-file-input -->
        <script src="/plugins/bs-custom-file-input/bs-custom-file-input.min.js"></script>
        <script>
            $(function () {
                bsCustomFileInput.init();
            });
        </script>
        <script>
            $(function(){
                $('.select2').select2()
            });
        </script>
        <!-- Set button script -->
        <script src="/js/set-button.js"></script>
        <script>
            $(function(){
                $('#report-form').on('submit', function(e){
                    @if(!$scan)
                    e.preventDefault();

                    disableButton(document.getElementById('submit-button'));

                    if(!navigator.geolocation){
                        alert('Browser Anda tidak mendukung Geolocation. Tidak dapat melanjutkan proses.');
                        enableButton(document.getElementById('submit-button'));
                        return;
                    }

                    // Wait for Google Maps to load before requesting location
                    google.maps.importLibrary("geocoding").then(() => {
                        requestLocation();
                    });

                    const form = this;

                    // Function to request user location
                    function requestLocation() {
                        navigator.geolocation.getCurrentPosition(
                            function(position) {
                                const latitude = position.coords.latitude;
                                const longitude = position.coords.longitude;

                                // Success - location granted    
                                const geocoder = new google.maps.Geocoder();
                                geocoder.geocode({location: {lat: latitude, lng: longitude}}, function(results, status) {
                                    if (status === 'OK') {
                                        if (results[0]) {
                                            const address = results[0].formatted_address;
                                            const city = results[0].address_components.find(comp => comp.types.includes('administrative_area_level_2'))?.long_name || 'N/A';
                                            const province = results[0].address_components.find(comp => comp.types.includes('administrative_area_level_1'))?.long_name || 'N/A';

                                            $('#address-input').val(address);
                                            $('#city-input').val(city);
                                            $('#province-input').val(province);
                                            $('#latitude-input').val(latitude);
                                            $('#longitude-input').val(longitude);

                                            // Submit form after location data is populated
                                            form.submit();
                                        } else {
                                            alert('Tidak dapat melanjutkan proses. Silakan coba lagi nanti.');
                                            enableButton(document.getElementById('submit-button'));
                                            return;
                                        }
                                    } else {
                                        alert('Geocoder gagal karena: ' + status + '. Tidak dapat melanjutkan proses.');
                                        enableButton(document.getElementById('submit-button'));
                                        return;
                                    }
                                });
                            },
                            function(error) {
                                // Error - location denied or failed
                                let errorMessage = '';
                                switch(error.code) {
                                    case error.PERMISSION_DENIED:
                                        errorMessage = 'Anda menolak izin akses lokasi. Tidak dapat melanjutkan proses.';
                                        break;
                                    case error.POSITION_UNAVAILABLE:
                                        errorMessage = 'Informasi lokasi tidak tersedia. Pastikan GPS atau layanan lokasi aktif. Tidak dapat melanjutkan proses.';
                                        break;
                                    case error.TIMEOUT:
                                        errorMessage = 'Permintaan lokasi timeout. Tidak dapat melanjutkan proses.';
                                        break;
                                    default:
                                        errorMessage = 'Terjadi kesalahan saat mengambil lokasi. Tidak dapat melanjutkan proses.';
                                }
                                
                                alert(errorMessage);
                                enableButton(document.getElementById('submit-button'));
                                return;
                            },
                            {
                                enableHighAccuracy: true,
                                timeout: 10000,
                                maximumAge: 0
                            }
                        );
                    }
                    @else
                    disableButton(document.getElementById('submit-button'));                    
                    @endif
                });
            })
        </script>
    </body>
</html>