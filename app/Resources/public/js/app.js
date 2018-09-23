var $ = require('jquery');
require('bootstrap-sass');
require('selectize');
require('timeago');

(function($) {
    function initSelectize() {
        $('select').selectize();
        $('.selectizable').selectize();
    }

    function initTooltips() {
        $('[data-toggle="tooltip"]').tooltip();
    }

    function initTimeago() {
        $.timeago.settings.allowFuture = true;
        $("time.timeago, abbr.timeago").timeago();
    }

    $(function() {
        //app.page = app.page || 'any';

        // initSelectize();
        initTooltips();
        initTimeago();
    });
})(jQuery);
