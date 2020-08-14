(function ($) {

    var saveFib = function (e) {
        $.post({
            url: fib.ajaxUrl,
            dataType: 'json',
            success: function (data) {
                // TODO
            },
            data: {
                action: 'save_fib',
                reversed: e.data.isReversed,
                fibonacci: $(e.target).text(),
                id: fib.postId
            }
        });
    };

    $('.fibseq').click({isReversed: false}, saveFib);

    $('.fibseq-rev').click({isReversed: true}, saveFib);

})(jQuery);