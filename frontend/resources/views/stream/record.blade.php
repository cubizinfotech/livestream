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
                    @if($_SERVER['SERVER_ADDR'] == '127.0.0.1' || $_SERVER['SERVER_NAME'] == 'localhost')
                        <input type="hidden" name="recorded_url" id="recorded_url" value="{{ asset('') }}{{ $record->recording_url }}">
                    @else
                        <input type="hidden" name="recorded_url" id="recorded_url" value="{{ env('CLOUDFRONT_URL') }}/{{ $record->recording_url }}">
                    @endif
                </div>
            </div>
        </div>
    </section>
@endsection

@section("scripts")
<script>
    $(document).ready(async function() {
        await record();
    });

    function record() {
        var fileUrl = $("#recorded_url").val();
        var fileExtension = fileUrl.split('.').pop();
        var videoElement = document.getElementById('video');

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
            console.log("Record URL: ", fileUrl);
        }, 1000);
    }
</script>
@endsection