@extends("layouts.app")

@section("css")
@endsection

@section("content")
    <section class="main">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-12">
                    <div class="video-wrapper">
                        <div class="cover">
                            <video id="video" controls class='video' width='600' muted autoplay>
                                <source src="{{ asset('assets') }}/img/sample_national_flag.mp4" type="video/mp4" />
                            </video>
                            <div class="overlayText">
                                <label class="m-1 blinking-text"></label>
                            </div>
                        </div>
                        <a class="video-play-btn" href="#"></a>
                    </div>
                </div>
                <input type="hidden" name="stream_url" id="stream_url" value="{{ $live->live_url }}">
            </div>
        </div>
    </section>
@endsection

@section("scripts")
    <script>
        $(document).ready(function() {
            live();
        });

        function live() {
            var url = $("#stream_url").val();
            if (url) {
                console.log("Live URL: ", url);
                var video = document.getElementById("video");
                var videoSrc = url;
                if (Hls.isSupported()) {
                    var hls = new Hls();
                    hls.loadSource(videoSrc);
                    hls.attachMedia(video);
                } 
                else if (video.canPlayType("application/vnd.apple.mpegurl")) {
                    video.src = videoSrc;
                }

                $('#video').trigger('click');
                console.log("LIVE");

                var html = "";
                html += '<b class="text-success"> LIVE </b>';
                html += '<img width="40%" src="{{ asset('assets') }}/img/live-stream.svg" alt="">';

                $(".blinking-text").html(html);
            } 
            else {
                var html = "";
                html += '<b class="text-danger"> OFFLINE </b>';
                html += '<img width="30%" src="{{ asset('assets') }}/img/offline.svg" alt="">';

                $(".blinking-text").html(html);
                console.log("Livestream not active!");
            }
        }
    </script>
@endsection