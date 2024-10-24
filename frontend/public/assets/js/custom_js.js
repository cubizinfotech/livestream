
function toster(header, message, type) {
    $.toast({
        /* text: [
            'Fork the repository',
            'Improve/extend the functionality',
            'Create a pull request'
        ], */
        // hideAfter: true,
        // bgColor: '#FF1356',
        heading: header,
        text: message,
        allowToastClose: true,
        icon: type, // success OR error OR warning OR info
        hideAfter: 3000, // in milli seconds
        textAlign: 'left', // left OR right OR center
        showHideTransition: 'slide', // fade OR slide OR plain OR
        position: 'top-right', // top-right OR top-left OR top-center OR mid-center OR bottom-right OR bottom-left OR bottom-center
        loader: true,        // Change it to false to disable loader
        loaderBg: '#9EC600',  // To change the background
        textColor: 'white',
        stack: false
    });
}

$(function () {
    $('[data-toggle="tooltip"]').tooltip()
});

$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

$('.cover').on('click', function() {
    $(this).children().css({
        'z-index': 1,
        'opacity': 1
    });
    $(this).children().trigger('play');
});

$(document).ready(function () {
    // 
});
