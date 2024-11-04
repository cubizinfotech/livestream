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
                        <select class="form-select select2 temple_dropdown_list" aria-label="Default select example">
                            @foreach ($showAllTempleNameRecords as $key => $value)
                            <option value="{{ $value->stream_key }}" data-id="{{ $value->id }}"
                                {{ ($stream_key == $value->stream_key) ? 'selected' : '' }}>{{ $value->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-12">
                    <ul class="video-list">
                        @if(count($records->rtmp_recording) > 0)
                        @foreach ($records->rtmp_recording as $key => $value)
                        <li class="video-list-item delete_record_{{ $value->id }}">
                            <div class="player-icon">
                                <a href="{{ route('video.show', base64_encode($value->id)) }}" target="">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                                        class="bi bi-play-fill" viewBox="0 0 16 16">
                                        <path
                                            d="m11.596 8.697-6.363 3.692c-.54.313-1.233-.066-1.233-.697V4.308c0-.63.692-1.01 1.233-.696l6.363 3.692a.802.802 0 0 1 0 1.393z" />
                                    </svg>
                                </a>
                            </div>
                            <div class="cl-content">
                                <h4><a href="{{ route('video.show', base64_encode($value->id)) }}" target=""> video
                                        {{ $key+1 }}
                                    </a></h4>
                                <p><b>Channel: </b> {{ $records->rtmp_url }}/{{ $records->stream_key }}</p>
                            </div>
                            <div class="cl-action">
                                <a href="#"
                                    onClick="delete_recording('{{ $value->id }}', `{{ route('video.delete', $value->id) }}`)"
                                    class="delete-icon" data-toggle="tooltip" data-placement="top" title="Delete">
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
                        @else
                        <center>
                            <h4>
                                No Recordings Found!s
                            </h4>
                            <a href="{{ url('/') }}">Homepage</a>
                        </center>
                        @endif
                    </ul>
                </div>
            </div>
        </div>
    </section>
@endsection

@section("scripts")
    <script>
        $(".temple_dropdown_list").on("change", function() {

            var streamKey = $(this).val();
            window.location.href = "{{ url('admin/videos') }}/" + streamKey;

            // var templeID = $('.temple_dropdown_list option:selected').attr('data-id');
            // window.location.href = "{{ url('admin/videos') }}/" + templeID;
        });

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