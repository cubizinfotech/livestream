<?php
    // echo "<pre>";
    // print_r($records);
    // die;
?>

@if($type == "showTempleRecords")
    <ul class="church-list">
        @foreach ($records as $key => $value)
            <li class="church-list-item delete_record_{{ $value->id }} {{ ($value->status == 2) ? 'border border-warning' : '' }} {{ (isset($value->rtmp_live->id) && $value->rtmp_live->status == 0) ? 'border border-danger' : '' }}" <?php if($value->status == 2) { echo 'data-toggle="tooltip" title="RTMP server creation pending"'; } ?>>
                <div class="cl-content">
                    <h4 class="cursor-pointer"><a href="{{ route('temple.videos', $value->stream_key) }}" target="">{{ $value->name }}</a></h4>
                    <p><b>RTMP URL:</b> {{ $value->rtmp_url }}</p>
                    <p><b>Stream Key:</b> {{ $value->stream_key }}</p>
                </div>
                <div class="cl-action show_class hide_class_{{ $value->id }}">
                    <a href="#" onClick="reload_stream('{{ $value->id }}')" class="reload-icon" data-toggle="tooltip"
                        data-placement="top" title="Reload">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                            class="bi bi-arrow-clockwise" viewBox="0 0 16 16">
                            <path fill-rule="evenodd" d="M8 3a5 5 0 1 0 4.546 2.914.5.5 0 0 1 .908-.417A6 6 0 1 1 8 2v1z" />
                            <path
                                d="M8 4.466V.534a.25.25 0 0 1 .41-.192l2.36 1.966c.12.1.12.284 0 .384L8.41 4.658A.25.25 0 0 1 8 4.466z" />
                        </svg>
                    </a>
                    <a href="#" onClick="delete_stream('{{ $value->id }}', `{{ route('rtmps.destroy', $value->id) }}`)"
                        class="delete-icon" data-toggle="tooltip" data-placement="top" title="Delete">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                            class="bi bi-trash-fill" viewBox="0 0 16 16">
                            <path
                                d="M2.5 1a1 1 0 0 0-1 1v1a1 1 0 0 0 1 1H3v9a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V4h.5a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H10a1 1 0 0 0-1-1H7a1 1 0 0 0-1 1H2.5zm3 4a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-1 0v-7a.5.5 0 0 1 .5-.5zM8 5a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-1 0v-7A.5.5 0 0 1 8 5zm3 .5v7a.5.5 0 0 1-1 0v-7a.5.5 0 0 1 1 0z" />
                        </svg>
                    </a>
                    <a href="#" style="margin-right: -30px;"
                        onClick="share('{{ $value->id }}', `{{ route('live.share', $value->id) }}`)"
                        class="copy-icon share share_{{ $value->id }}" data-toggle="tooltip" data-placement="top" title="Copy & Share">
                        <img width="40%" src="{{ asset('assets') }}/img/copy.gif" alt="">
                    </a>
                    @if(isset($value->rtmp_live->id) && $value->rtmp_live->status == 0)
                        <a href="#"
                            onClick="unblocked('{{ $value->id }}', `{{ route('stream.unblock') }}`)"
                            class="copy-icon share unblocked_{{ $value->id }} text-danger" data-toggle="tooltip" data-placement="top" title="Unblock Stream">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-unblock" viewBox="0 0 16 16">
                                <!-- Circle outline -->
                                <circle cx="8" cy="8" r="7" stroke="currentColor" stroke-width="2" fill="none"></circle>
                                <!-- Diagonal line to indicate "unblocking" -->
                                <line x1="3" y1="3" x2="13" y2="13" stroke="currentColor" stroke-width="2"></line>
                                <!-- Checkmark to indicate "unblock" -->
                                <path d="M6 8l2 2 4-4" stroke="currentColor" stroke-width="2" fill="none"></path>
                            </svg>
                        </a>
                    @endif
                </div>
                <div class="cl-action hide_class show_class_{{ $value->id }} d-none">

                    <label class="text-primary"><strong>Active</strong></label>

                    <a href="#"
                        onClick="share('{{ $value->id }}', `{{ route('live.share', $value->id) }}`)"
                        class="copy-icon share share_{{ $value->id }}" data-toggle="tooltip" data-placement="top" title="Copy & Share">
                        <img width="40%" src="{{ asset('assets') }}/img/copy.gif" alt="">
                    </a>
                </div>
            </li>
        @endforeach
    </ul>
@endif

@if($type == "showVideosRecords")
    <ul class="video-list">
        @foreach ($records as $key => $value)
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
                    <p><b>Channel: </b> {{ $value->rtmp->rtmp_url }}/{{ $value->rtmp->stream_key }}</p>
                </div>
                <div class="cl-action">

                    <label class="text-primary hide_class show_class_{{ $value->id }} d-none"><strong>Active</strong></label>

                    <a href="#" onClick="delete_recording('{{ $value->id }}', `{{ route('video.delete', $value->id) }}`)"
                        class="delete-icon show_class hide_class_{{ $value->id }}" data-toggle="tooltip" data-placement="top"
                        title="Delete">
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
@endif

@if($type == "showAllTempleNameRecords")
    <option value="" selected>Select Church Stream</option>
    @foreach ($records as $key => $value)
        <option value="{{ $value->id }}">{{ $value->name }}</option>
    @endforeach
@endif

@if($type == "getLiveStreamPageLoad")
    @if(!empty($records) && !empty($records->rtmp_live->id) && $records->rtmp_live->status == 1)
        <input type="hidden" name="live_stream_url" id="live_stream_url" value="{{ $records->live_url }}">
        <input type="hidden" name="live_stream_id" id="live_stream_id" value="{{ $records->id }}">
        <input type="hidden" name="live_stream_key" id="live_stream_key" value="{{ $records->stream_key }}">
    @else
        <input type="hidden" name="live_stream_url" id="live_stream_url" value="">
        <input type="hidden" name="live_stream_id" id="live_stream_id" value="">
        <input type="hidden" name="live_stream_key" id="live_stream_key" value="">
    @endif
    <input type="hidden" name="live_rtmp_status" id="live_rtmp_status" value="{{ isset($records->status) ? $records->status : '' }}">
@endif

@if($type == "playVideosRecords")
    @if(!empty($records))
        @if($_SERVER['SERVER_ADDR'] == '127.0.0.1' || $_SERVER['SERVER_NAME'] == 'localhost')
            <input type="hidden" name="live_stream_recorded_url" id="live_stream_recorded_url" value="{{ asset('') }}{{ $records->recording_url }}">
        @else
            <input type="hidden" name="live_stream_recorded_url" id="live_stream_recorded_url" value="{{ env('CLOUDFRONT_URL') }}/{{ $records->recording_url }}">
        @endif
    @else
        <input type="hidden" name="live_stream_recorded_url" id="live_stream_recorded_url" value="">
    @endif
@endif

<script>
    $(function() {
        $('[data-toggle="tooltip"]').tooltip()
    });
</script>