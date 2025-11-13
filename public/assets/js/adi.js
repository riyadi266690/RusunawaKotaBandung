$(document).ready(function() {
    $('.form-adi').on('submit', function() {
        $(this).find('.button-adi').attr('disabled', true) .html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Mengirim...');
    });
});
