
<!doctype html>
<html lang="en">

<head>
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <meta name="generator" content="">
    <title>Admin | Live Stream</title>
    <link rel="shortcut icon" type="image/png" sizes='72x72' href="{{ asset('assets') }}/img/favicon.png" width="16" />

    <!-- Font Awesome CSS -->
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <!-- Bootstrap  CSS -->
    <link href="{{ asset('assets') }}/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom styles -->
    <link href="{{ asset('assets') }}/css/style.css" rel="stylesheet">
    <link href="{{ asset('assets') }}/css/custom_css.css" rel="stylesheet">
    <!-- Toster CSS -->
    <link href="{{ asset('assets') }}/css/style.toast.css" rel="stylesheet">
    <!-- SweetAlert CSS -->
    <link href="{{ asset('assets') }}/css/sweetalert2.min.css" rel="stylesheet">
    <!-- Select2 CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/css/select2.min.css" rel="stylesheet" />

    @yield('css')

</head>

<body>

    <nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="{{ url('/') }}">
                {{ config('app.name', 'Laravel') }}
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <!-- Left Side Of Navbar -->
                <ul class="navbar-nav me-auto">

                </ul>

                <!-- Right Side Of Navbar -->
                <ul class="navbar-nav ms-auto">
                    <!-- Authentication Links -->
                    @guest
                        @if (Route::has('login'))
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a>
                            </li>
                        @endif

                        @if (Route::has('register'))
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('register') }}">{{ __('Register') }}</a>
                            </li>
                        @endif
                    @else
                        <li class="nav-item dropdown">
                            <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                {{ Auth::user()->name }}
                            </a>

                            <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                <a class="dropdown-item" href="{{ route('logout') }}"
                                    onclick="event.preventDefault();
                                                    document.getElementById('logout-form').submit();">
                                    {{ __('Logout') }}
                                </a>

                                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                    @csrf
                                </form>

                                <a class="dropdown-item" href="{{ route('admin.home') }}">Admin | Home</a>
                            </div>
                        </li>
                    @endguest
                </ul>
            </div>
        </div>
    </nav>

    @yield('content')

    <!-- Bootstrap Title JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/2.10.2/umd/popper.min.js"></script>
    <!-- Bootstrap  JS -->
    <script src="{{ asset('assets') }}/js/bootstrap.min.js"></script>
    <script src="{{ asset('assets') }}/js/jquery-3.3.1.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js"></script>
    <script src="{{ asset('assets') }}/js/custom_js.js"></script>
    <!-- Toster JS -->
    <script src="{{ asset('assets') }}/js/jquery.toast.js"></script>
    <!-- Select2 JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/js/select2.min.js"></script>
    <!-- SweetAlert JS -->
    <script src="{{ asset('assets') }}/js/sweetalert2.all.min.js"></script>
    <!-- HLS -->
    <script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>
    <!-- flv JS -->
    <script src="{{ asset('assets') }}/js/flv/flv.min.js"></script>

    <script>
        $(".select2").select2({
            placeholder: "Select Church Stream",
            allowClear: true
        });
        
        $('video').on('click', function() {
            // 
        });

        function getLiveStreamPageLoad(templeID = null) {
            $.ajax({
                url: "{{ route('rtmps.index') }}",
                method: "GET",
                data: {
                    type: "getLiveStreamPageLoad",
                    templeID: templeID
                },
                success: function(response) {
                    $(".add_live_stream").html(response);
                    // console.log(response);
                    live_stream();
                },
                error: function(error) {
                    console.log(error);
                }
            });
        }

        function showTempleRecords(templeID = null, type = null) {
            $.ajax({
                url: "{{ route('rtmps.index') }}",
                method: "GET",
                data: {
                    type: "showTempleRecords",
                    templeID: templeID
                },
                success: function(response) {
                    // console.log(response);
                    if (type == null) {
                        $(".temple_list").html(response);
                    }
                    if (templeID) {
                        getLiveStreamPageLoad(templeID);
                    }
                },
                error: function(error) {
                    console.log(error);
                }
            });
        }

        function showVideosRecords(templeID = null) {
            $.ajax({
                url: "{{ route('rtmps.index') }}",
                method: "GET",
                data: {
                    type: "showVideosRecords",
                    templeID: templeID
                },
                success: function(response) {
                    $(".add_live_stream_recorded_video").html(response);
                    // console.log(response);
                },
                error: function(error) {
                    console.log(error);
                }
            });
        }

        function playVideosRecords(templeID = null) {
            $.ajax({
                url: "{{ route('rtmps.index') }}",
                method: "GET",
                data: {
                    type: "playVideosRecords",
                    templeID: templeID
                },
                success: function(response) {
                    $(".add_live_stream_recorded").html(response);
                    // console.log(response);
                    record_stream();


                    $(".show_class").removeClass("d-none");
                    $(".hide_class").addClass("d-none");
                    $(".hide_class_" + templeID).addClass("d-none");
                    $(".show_class_" + templeID).removeClass("d-none");

                },
                error: function(error) {
                    console.log(error);
                }
            });
        }

        function showAllTempleNameRecords() {
            $.ajax({
                url: "{{ route('rtmps.index') }}",
                method: "GET",
                data: {
                    type: "showAllTempleNameRecords"
                },
                success: function(response) {
                    $(".temple_dropdown_list").html(response);
                    // console.log(response);
                },
                error: function(error) {
                    console.log(error);
                }
            });
        }

        function live_stream() {
            var live_url = $("#live_stream_url").val();
            var live_id = $("#live_stream_id").val();
            var live_key = $("#live_stream_key").val();

            if (live_url) {
                console.log(live_url);
                var video = document.getElementById("video");
                var videoSrc = live_url;
                if (Hls.isSupported()) {
                    var hls = new Hls();
                    hls.loadSource(videoSrc);
                    hls.attachMedia(video);
                } else if (video.canPlayType("application/vnd.apple.mpegurl")) {
                    video.src = videoSrc;
                }

                $('#video').trigger('click');
                console.log("LIVE");

                var html = "";
                html += '<b class="text-success"> LIVE </b>';
                html += '<img width="40%" src="{{ asset('assets') }}/img/live-stream.svg" alt="">';

                $(".blinking-text").html(html);
                add_remove_class(live_id);
            } else {
                var html = "";
                html += '<b class="text-danger"> OFFLINE </b>';
                html += '<img width="30%" src="{{ asset('assets') }}/img/offline.svg" alt="">';

                $(".blinking-text").html(html);
                console.log("No one live stream.");
            }
        }

        function record_stream() {

            var fileUrl = $("#live_stream_recorded_url").val();
            var fileExtension = fileUrl.split('.').pop();

            const videoElement = document.getElementById('video');
        
            if(fileExtension == 'flv') {
                if (flvjs.isSupported()) {
                    const flvPlayer = flvjs.createPlayer({
                        type: 'flv',
                        url: fileUrl,
                    });
                    flvPlayer.attachMediaElement(videoElement);
                    flvPlayer.load();
                    flvPlayer.play();
                } else {
                    console.error('FLV not supported in this browser.');
                }

                videoElement.addEventListener('canplay', () => {
                    videoElement.play();
                });
            }
            else {
                videoElement.src = fileUrl;
                videoElement.play();
            }

            var html = "";
            html += '<b class="text-danger">RECORDED</b>'
            html += '<img width="20%" src="{{ asset('assets') }}/img/offline.svg" alt="">'

            $(".blinking-text").html(html);
            $('#video').trigger('click');

            setTimeout(() => {
                videoElement.muted = false;
                console.log("RECORDED VIDEO PLAY ("+fileExtension+")");
            }, 1000);
        }

        function changeurl(url) {
            var new_url = url;
            window.history.pushState("data", "Title", new_url);
            document.title = url;
        }

        function add_remove_class(templeID) {

            if (templeID) {
                $(".show_class").removeClass("d-none");
                $(".hide_class").addClass("d-none");
                $(".hide_class_" + templeID).addClass("d-none");
                $(".show_class_" + templeID).removeClass("d-none");
            }
        }

        function share(id, url) {
            // Copy the text inside the text field
            // navigator.permissions.query({ name: "clipboard-read" });
            try {
                navigator.clipboard.writeText(url).then(function() {
                        toster("Success", "URL copied to clipboard successfully!", "success");
                    })
                    .catch(function(err) {
                        console.error("Failed to copy URL: ", err);
                    });
            } catch (err) {
                alert('Copy Share Link: '+url);
                console.error("Failed to copy URL: ", err);
            }
            
            // $('.share').attr('data-bs-original-title', 'Copy & Share');
            // $('.share_'+id).attr('data-bs-original-title', 'Copied').tooltip('show');
        }
    </script>

    @yield('scripts')

</body>

</html>