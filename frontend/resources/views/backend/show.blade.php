@extends("layouts.app")

@section("css")
@endsection

@section("content")
    <section class="main">
        <div class="container">
            <div class="row justify-content-center">

                <div class="col-12 col-md-12 col-lg-12">
                    <!-- Append Temple Dropdown List -->
                    <div class="select-wrapper ms-auto">
                        <select class="form-select select2 video_dropdown_list" aria-label="Default select example">
                            @foreach ($showAllTempleNameRecords as $key => $value)
                            <option value="{{ $value->id }}" {{ ($records->rtmp->id == $value->id) ? 'selected' : '' }}>
                                {{ $value->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-12">
                    <div class="video-wrapper">
                        <div class="cover">
                            <video id="video" controls class='video' width='600' muted autoplay>
                                <!-- <source
                                    src="https://riverisland.scene7.com/is/content/RiverIsland/c20171205_Original_Penguin_AW17_Video"
                                    type="video/mp4" />
                                <source
                                    src="https://riverisland.scene7.com/is/content/RiverIsland/c20171205_Original_Penguin_AW17_Video_OGG" /> -->
                                    <source src="{{ asset('assets') }}/img/sample_national_flag.mp4" type="video/mp4" />
                                <!-- <img src="fall-back image" alt=""> -->
                            </video>
                            <div class="overlayText">
                                <!-- Append Record -->
                                <label class="m-1 blinking-text"></label>
                            </div>
                        </div>
                        <a class="video-play-btn" href="#"></a>
                    </div>
                    <!-- Append Temple Live Stream -->
                    <div class="add_live_stream_recorded">
                        @if($_SERVER['SERVER_ADDR'] == '127.0.0.1' || $_SERVER['SERVER_NAME'] == 'localhost')
                            <input type="hidden" name="live_stream_recorded_url" id="live_stream_recorded_url" value="{{ asset('') }}{{ $records->recording_url }}">
                        @else
                            <input type="hidden" name="live_stream_recorded_url" id="live_stream_recorded_url" value="{{ env('CLOUDFRONT_URL') }}/{{ $records->recording_url }}">
                        @endif
                    </div>
                </div>

                <div class="col-12">
                    <!-- Append Temple Live Stream -->
                    <div class="add_live_stream_recorded_video">
                        <ul class="video-list">
                            @foreach ($records_all as $key => $value)
                            <li class="video-list-item delete_record_{{ $value->id }}">
                                <div class="player-icon">
                                    <a href="#"
                                        onClick="play_recording('{{ $value->id }}', `{{ route('video.show', base64_encode($value->id)) }}`)">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                                            class="bi bi-play-fill" viewBox="0 0 16 16">
                                            <path
                                                d="m11.596 8.697-6.363 3.692c-.54.313-1.233-.066-1.233-.697V4.308c0-.63.692-1.01 1.233-.696l6.363 3.692a.802.802 0 0 1 0 1.393z" />
                                        </svg>
                                    </a>
                                </div>
                                <div class="cl-content">
                                    <h4><a href="#"
                                            onClick="play_recording('{{ $value->id }}', `{{ route('video.show', base64_encode($value->id)) }}`)">
                                            video {{ $key+1 }}
                                        </a></h4>
                                    <p><b>Channel: </b> {{ $records->rtmp->rtmp_url }}/{{ $records->rtmp->stream_key }}</p>
                                </div>

                                <div class="cl-action">

                                    <label
                                        class="text-primary hide_class show_class_{{ $value->id }} {{ ($value->id == $id) ? '' : 'd-none' }}"><strong>Active</strong></label>

                                    <a href="#"
                                        onClick="delete_recording('{{ $value->id }}', `{{ route('video.delete', $value->id) }}`)"
                                        class="delete-icon show_class hide_class_{{ $value->id }} {{ ($value->id == $id) ? 'd-none' : '' }}"
                                        data-toggle="tooltip" data-placement="top" title="Delete">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                                            class="bi bi-trash-fill" viewBox="0 0 16 16">
                                            <path
                                                d="M2.5 1a1 1 0 0 0-1 1v1a1 1 0 0 0 1 1H3v9a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V4h.5a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H10a1 1 0 0 0-1-1H7a1 1 0 0 0-1 1H2.5zm3 4a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-1 0v-7a.5.5 0 0 1 .5-.5zM8 5a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-1 0v-7A.5.5 0 0 1 8 5zm3 .5v7a.5.5 0 0 1-1 0v-7a.5.5 0 0 1 1 0z" />
                                        </svg>
                                    </a>
                                    <a href="#"
                                        onClick="share('{{ $value->id }}', `{{ route('record.share', $value->id) }}`)"
                                        class="copy-icon share share_{{ $value->id }}" data-toggle="tooltip" data-placement="top" title="Copy & Share">
                                        <img width="40%" src="{{ asset('assets') }}/img/copy.gif" alt="">
                                    </a>
                                </div>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                </div>

            </div>
        </div>
    </section>
@endsection

@section("scripts")
    <script>
        $(document).ready(function() {

            record_stream();
        });

        $(".video_dropdown_list").on("change", function() {

            var templeID = $(this).val();
            showVideosRecords(templeID);
        });

        function play_recording(id, url) {
            playVideosRecords(id);
            changeurl(url);
        }

        function delete_recording(id, url) {

            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: url,
                        type: "DELETE",
                        datatype: 'json',
                        cache: false,
                        contentType: false,
                        processData: false,
                        success: function(response) {
                            console.log(response);
                            if (response.status == false) {
                                $(".error_message").html(response.message);
                                toster("Error", response.message, "error");
                            }
                            if (response.status == true) {
                                $(".delete_record_" + id).hide();
                                toster("Success", response.message, "success");
                                Swal.fire(
                                    'Deleted!',
                                    'Your file has been deleted.',
                                    'success'
                                );
                                if (response.result == 0) {
                                    setTimeout(() => {
                                        location.reload(true);
                                    }, 3000);
                                }
                            }
                        },
                        error: function(error) {
                            console.log(error);
                        }
                    });
                }
            });
        }
    </script>
@endsection