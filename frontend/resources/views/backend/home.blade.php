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
                            <option value="" selected>Select Church Stream</option>
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
                                <!-- Append Live OR Offline -->
                                <label class="m-1 blinking-text"></label>
                            </div>
                        </div>
                        <a class="video-play-btn" href="#"></a>
                    </div>
                    <!-- Append Temple Live Stream -->
                    <div class="add_live_stream"></div>
                </div>

                <div class="col-12">
                    <div class="btn-wrapper text-end">
                        <a href="#" class="theme-btn" data-bs-toggle="modal" data-bs-target="#addchurch">Add Church</a>
                    </div>
                    <!-- Append Temple List -->
                    <div class="temple_list"></div>
                </div>
            </div>
        </div>
    </section>
    <!-- Add Church Modal -->
    <div class="modal fade" id="addchurch" tabindex="-1" aria-labelledby="addchurchLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">

                <div class="modal-body">
                    <h4 class="form-title">
                        Create a Church
                    </h4>
                    <form id="TempleForm" method="POST" class="add-form" autocomplete="on">
                        <div class="form-group">
                            <input type="text" name="name" class="form-control" placeholder="Enter Church Name" autofocus>
                        </div>
                        <div class="form-btn text-end">
                            <button type="button" class="theme-btn-alt" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="theme-btn">Save</button>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>
@endsection

@section("scripts")
    <script>
        $(document).ready(async function() {

            await showAllTempleNameRecords();
            await showTempleRecords();
            await getLiveStreamPageLoad();

            $('#addchurch').on('hidden.bs.modal', function() {
                $(this).find('form').trigger('reset');
            });

        });

        $("#TempleForm").submit(function(e) {
            e.preventDefault();

            $.ajax({
                url: "{{ route('rtmps.store') }}",
                type: "POST",
                data: new FormData($(this)[0]),
                datatype: 'json',
                cache: false,
                contentType: false,
                processData: false,
                success: async function(response) {
                    console.log(response);
                    if (response.status == false) {
                        $(".error_message").html(response.message);
                        toster("Error", response.message, "error");
                    }
                    if (response.status == true) {
                        $('#addchurch').modal('hide');
                        toster("Success", response.message, "success");
                        await showTempleRecords();
                        await showAllTempleNameRecords();
                    }
                },
                error: function(error) {
                    console.log(error);
                }
            });
        });

        $(".temple_dropdown_list").on("change", function() {

            var templeID = $(this).val();
            showTempleRecords(templeID);
            // add_remove_class(templeID);
        });

        async function reload_stream(id) {

            var templeID = id;
            showTempleRecords(templeID, 'reload');

            // $(".show_class").removeClass("d-none");
            // $(".hide_class").addClass("d-none");
            // $(".hide_class_" + templeID).addClass("d-none");
            // $(".show_class_" + templeID).removeClass("d-none");
        }

        function delete_stream(id, url) {

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
                                showAllTempleNameRecords();
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