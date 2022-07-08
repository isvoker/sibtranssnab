(function ($, C) {
    document.addEventListener('DOMContentLoaded', function () {
        $('.js__toggle-menu-layer').on('click', function (e) {
            C.ignoreEvent(e);
            $('.js__header-nav-container').toggleClass('hidden');
        });
    });
}(jQuery, Common));