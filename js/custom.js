
function Viewer() {
    this.initialize = function () {
        $('.refreshPage').click(function () {
            $('a[href="#about-tab-1"]').trigger("click");
            $('a[href="#index-tab-2"]').trigger("click");
        });
        activateTruncateText();
        let indexJS = new IndexJS();

        indexJS.initialize();
    };
    const activateTruncateText = function () {
        document.querySelectorAll('.truncate').forEach(el => {

            if (el.scrollHeight > el.clientHeight) {
                new Tooltip(el, {
                    title: el.textContent.trim(),
                    placement: 'top',
                    trigger: 'hover',
                    html: false,
                    offset: 10
                });
            }
        });
        document.querySelectorAll('.truncate-collection').forEach(el => {

            if (el.scrollHeight > el.clientHeight) {
                new Tooltip(el, {
                    title: el.textContent.trim(),
                    placement: 'top',
                    trigger: 'hover',
                    html: false,
                    offset: 10
                });
            }
        });
    };
}

function IndexJS() {
    this.initialize = function () {
        bindEvents();

    };

    const bindEvents = function () {
        $('a.indexSegmentLink').on('click', function (e) {
            e.preventDefault();
            $(this).parent().nextAll('.segmentLink').first().slideToggle();
            return false;
        });
        $('.segmentLinkTextBox').on('click', function () {
            $(this).select();
        });
        $('.copyButtonViewer').on('click', function () {
            var text = $(this).prev().val();
            copyToClipboard(text);
        });
        switchIndexToTranscript();
    };
    const  copyToClipboard = function (val) {
        var dummy = document.createElement("textarea");
        document.body.appendChild(dummy);
        dummy.value = val;
        dummy.select();
        document.execCommand("copy");
        document.body.removeChild(dummy);
    }
    const switchIndexToTranscript = function () {
        $('.mapIndexTranscript').click(function () {
            let type = $(this).data('type');
            let id = $(this).data('id');
            let container;
            let transcriptTab;
            if ($(this).closest('.right-side').length) {
                container = $('.left-side');
                transcriptTab = '#transcript-tab-1';

            } else if ($('.right-side').is(':visible')) {
                transcriptTab = '#transcript-tab-2';
                container = $('.right-side-inner');

            } else {
                container = $('.left-side');
                transcriptTab = '#transcript-tab-1';
            }
            $('a[href="' + transcriptTab + '"]').trigger("click");
            $('html, body').animate({scrollTop: 0}, 100);
            setTimeout(function () {
                scrollTo = $(transcriptTab + ">.transcript-panel>.info_trans_" + id);
                container.animate({
                    scrollTop: scrollTo.offset().top - container.offset().top + container.scrollTop()
                });
            }, 250);
        });
    };
    const switchTranscriptToIndex = function () {

    };
    const  toggleRedirectTranscriptIndex = function () {
        let type = $(this).data('type');
        let id = $(this).data('id');
        if (type == 'transcript-to-index') {
            $('#toggle_switch').trigger('click');
            setTimeout(function () {
                $('.tpd-tooltip').hide();
                $('#transcript-panel').hide();
                $('#index-panel').show();
                var currentIndex = $('#accordionHolder').accordion('option', 'active');
                if (currentIndex != id || currentIndex === false) {
                    jQuery('#accordionHolder').accordion({active: id});
                    jQuery('#accordionHolder-alt').accordion({active: id});
                }
            }, 250);
        }
    }
}




            