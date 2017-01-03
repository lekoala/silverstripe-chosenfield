/**
 * ChosenField
 */
(function($) {
    $(function() {
        if ($.entwine) {
            $.entwine('ss', function($) {
                $('.field.chosen select').entwine({
                    onmatch: function() {
                        this._super();
                        var opts = $(this).data('chosen');
                        $(this).chosen(opts);
                    }
                });
            });
        } else {
            $('.field.chosen select').each(function() {
                var opts = $(this).data('chosen');
                $(this).chosen(opts);
            });
        }
    });
})(jQuery);