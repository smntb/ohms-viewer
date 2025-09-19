jQuery(function ($) {
    var loaded = false;
    let isAudio = $('#my_player').hasClass('audio');
    const player = videojs('my_player', {
        autoplay: false,
        controls: true,
        preload: 'auto',
        aspectRatio: isAudio ? '1:0' : '16:9',
        controlBar: {
            fullscreenToggle: !isAudio,
            skipButtons: {
                forward: 10,
                backward: 10
            }
        }
    });

    player.on('timeupdate', function () {
        const current = player.currentTime();
        if (exhibitMode) {
            if (current > endAt && endAt != null) {
                player.pause();
                player.currentTime(endAt)
                $(this).jPlayer('pause');
                endAt = null;
                exhibitIndex = null;
                // optional: remove listener so it doesn’t keep firing
                // player.off('timeupdate');
            }
        }

    });
    $('body').on('click', 'a.jumpLink', function (e) {
        e.preventDefault();
        player.currentTime($(e.target).data('timestamp')*60);
        player.play();
    });
    $('body').on('click', 'a.indexJumpLink', function (e) {
        e.preventDefault();
        try {
            endAt = $(this).parent().parent().next().next().find('.indexJumpLink').data('timestamp');
            exhibitIndex = $(this).parents('div').prev();
        } catch (e) {
            endAt = null;
        }
        
        player.currentTime(parseInt($(e.target).data('timestamp')));
        
        player.play();
    });

//    $('.translate-link').click(function (e) {
//        var urlIndexPiece = '';
//        var re;
//        e.preventDefault();
//        var toggleAvailability = "";
//        if ($(this).attr('data-toggleAvailable') == 'hide') {
//            toggleAvailability = "&t_available=1";
//        }
//        if ($('#search-type').val() == 'Index') {
//            var activeIndexPanel = $('#accordionHolder').accordion('option', 'active');
//            if (activeIndexPanel !== false) {
//                urlIndexPiece = '&index=' + activeIndexPanel;
//            }
//        }
//        if ($(this).attr('data-linkto') == $(this).attr('data-default')) {
//            re = /&translate=(.*)/g;
//            location.href = location.href.replace(re, '') + '&time=' + Math.floor(jQuery('#subjectPlayer').data("jPlayer").status.currentTime) + toggleAvailability + '&panel=' + $('#search-type').val() + urlIndexPiece;
//        } else {
//            re = /&time=(.*)/g;
//            location.href = location.href.replace(re, '') + '&translate=1&time=' + Math.floor(jQuery('#subjectPlayer').data("jPlayer").status.currentTime) + toggleAvailability + '&panel=' + $('#search-type').val() + urlIndexPiece;
//        }
//    });
//



});
