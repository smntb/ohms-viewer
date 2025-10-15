jQuery(function ($) {
    var loaded = false;

    $('#audio-panel .video').attr('id', 'avalon-video');

    $('.translate-link').click(function (e) {
        var urlIndexPiece = '';
        var re;
        e.preventDefault();
        var toggleAvailability = "";
        if ($(this).attr('data-toggleAvailable') == 'hide') {
            toggleAvailability = "&t_available=1";
        }
        if ($('#search-type').val() == 'Index') {
            var activeIndexPanel = $('#accordionHolder').accordion('option', 'active');
            if (activeIndexPanel !== false) {
                urlIndexPiece = '&index=' + activeIndexPanel;
            }
        }
        parent.widget('get_offset');
        var pos = parent.offsetTime;
        if ($(this).attr('data-linkto') == $(this).attr('data-default')) {
            re = /&translate=(.*)/g;
            location.href = location.href.replace(re, '') + '&time=' + Math.floor(pos) + toggleAvailability + '&panel=' + $('#search-type').val() + urlIndexPiece;
        } else {
            re = /&time=(.*)/g;
            location.href = location.href.replace(re, '') + '&translate=1&time=' + Math.floor(pos) + toggleAvailability + '&panel=' + $('#search-type').val() + urlIndexPiece;
        }
    });


    $('body').on('click', 'a.jumpLink', function (e) {
        e.preventDefault();
        var target = $(e.target);
        curPlayPoint = 0;
        curPlayPoint = target.data('timestamp');
        widget('set_offset', {'offset': curPlayPoint * 60})
        widget('play');
    });
    $('body').on('click', 'a.indexJumpLink', function (e) {
        e.preventDefault();
        var target = $(e.target);
        try {
            endAt = $(this).parent().parent().next().next().find('.indexJumpLink').data('timestamp');
            exhibitIndex = $(this).parents('div').prev();
        } catch (e) {
            endAt = null;
        }
        curPlayPoint = 0;
        curPlayPoint = target.data('timestamp');
        widget('set_offset', {'offset': curPlayPoint})
        widget('play');
        $('body').animate({scrollTop: 0}, 800);
    });

});
