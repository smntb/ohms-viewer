
function Viewer() {
    this.initialize = function (cachefile) {

        $('.refreshPage').click(function () {
            $('a[href="#about-tab-1"]').trigger("click");
            $('a[href="#index-tab-2"]').trigger("click");
        });
        $('.fancybox-media').fancybox({
            openEffect: 'none',
            closeEffect: 'none',
            width: '80%',
            height: '80%',
            fitToView: true,
            helpers: {
                media: {}
            }
        });
        let url = new URL(window.location.href);
        let external = '';
        if (url.searchParams.has('external')) {
            external = '&external=true';
        }
        $(".printCustom").click(function () {
            window.location.href = "viewer.php?action=pdf&cachefile=" + cachefile + external + "";
        });
        $(".printCustomMobile").click(function () {
            window.open("viewer.php?action=pdf&cachefile=" + cachefile + external + "", '_blank');
        });

        $('#lnkRights').click(function () {
            $('#rightsStatement').fadeToggle(400);
            return false;
        });
        $('#lnkUsage').click(function () {
            $('#usageStatement').fadeToggle(400);
            return false;
        });
        $('#lnkFunding').click(function () {
            $('#fundingStatement').fadeToggle(400);
            return false;
        });
        switchViews();
        bindOldFootNotes();

        let indexJS = new IndexJS();

        indexJS.initialize();

    };
    this.footerNotes = function (event) {
        bindFootNoteHover(event)
    }
    
    const switchViews = function () {
        $(".toggle-sides").click(function () {
            let activeLeft, activeLeftLink, activeRight, activeRightLink = '';
            if ($("#custom-tabs-left ul li").hasClass("ui-tabs-selected ui-state-active")) {
                activeLeft = $("#custom-tabs-left ul li.ui-tabs-selected.ui-state-active a");
                activeLeftLink = activeLeft.attr("href");

            }
            if ($("#custom-tabs-right ul li").hasClass("ui-tabs-selected ui-state-active")) {
                activeRight = $("#custom-tabs-right ul li.ui-tabs-selected.ui-state-active a");
                activeRightLink = activeRight.attr("href");

            }
            if (activeLeftLink == '#about-tab-1')
                return;
           
            let switchLeftToRight = activeLeftLink.replace(/-1/g, "-2");
            let switchRightToLeft = activeRightLink.replace(/-2/g, "-1");
            $('a[href="' + switchLeftToRight + '"]').trigger("click");
            $('a[href="' + switchRightToLeft + '"]').trigger("click");


        });
    };
    const bindOldFootNotes = function () {
        $('.footnoteTooltip').each(function (index, element) {
            let footnoteID = $(element).data('index');
            let footnoteAttrId = $(element).attr("id");
            let footnoteHtml = $('#' + footnoteID).parent().children('span').html();

            $(element).attr("data-tooltip", footnoteHtml);
            footNotesTooltip('#transcript-tab-1', footnoteAttrId, footnoteHtml);
            footNotesTooltip('#transcript-tab-2', footnoteAttrId, footnoteHtml);
        });
        bindFootNoteHover("bind");
    }
    const bindFootNoteHover = function (state) {
        if (state == "bind") {
            $(".footnote-ref").bind("hover",
                    function () {
                        var footnoteHtmlLength = $(this).find('.footnoteTooltip').attr("data-tooltip").length;
                        width = footnoteHtmlLength * 50 / 100;
                        if (footnoteHtmlLength > 130) {
                            $('head').append("<style>.tooltip{ width: " + width + "px }</style>");
                        } else {
                            $('head').append("<style>.tooltip{ width: 130px; }</style>");
                        }
                    }
            );
        } else if (state == "unbind") {
            $(".footnote-ref").unbind("hover");
        }
    }
    const footNotesTooltip = function (tab, element, footnoteHtml) {

        new Tooltip($(tab + " #" + element), {
            title: footnoteHtml,
            trigger: "hover",
            placement: "bottom",
            html: true,
            eventsEnabled: true,
            modifiers: {
                flip: {
                    behavior: ['left', 'right', 'top']
                },
                preventOverflow: {
                    boundariesElement: $('#transcript-panel')
                }
            }
        });

    }
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
        activateTranscriptPopup();

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
            $(this).attr('value', 'Copied');
            var button = $(this);
            setTimeout(function () {
                button.attr('value', 'Copy');
            }, 1500);
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
    const activateTranscriptPopup = function () {
        $('.info-circle').each(function (index, element) {

            var timePoint = $("." + element.id).data("time-point");
            var id = $("." + element.id).data("marker-counter");
            var indexTitle = $("." + element.id).data("index-title");
            var anchorHtml = "<div class='info-toggle transcript-info-tipped' data-id='" + id + "' >Segment: <b>" + indexTitle + "</b> " + timePoint + " </div>";
            Tipped.create('.' + element.id, anchorHtml, {
                size: 'large',
                radius: true,
                position: 'right'
            });
        });
        //, .transcript-info-tipped
        $(document).on("click", ".transcript-info", function (e) {
            console.log('here');

            let id = $(this).data('id');
            $('.tpd-tooltip').hide();
            let container;
            let indexTab;
            if ($(this).closest('.right-side').length) {
                container = $('.left-side');
                indexTab = '#index-tab-1';

            } else if ($('.right-side').is(':visible')) {
                indexTab = '#index-tab-2';
                container = $('.right-side-inner');

            } else {
                container = $('.left-side');
                indexTab = '#index-tab-1';
            }
            $('a[href="' + indexTab + '"]').trigger("click");
            $('html, body').animate({scrollTop: 0}, 100);
            setTimeout(function () {
                var currentIndex = $(indexTab + ' .accordionHolder').accordion('option', 'active');
                if (currentIndex != id || currentIndex === false) {
                    jQuery(indexTab + ' .accordionHolder').accordion({active: id});
                    jQuery(indexTab + ' .accordionHolder-alt').accordion({active: id});
                }
            }, 250);
        });
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


}
        