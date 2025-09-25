function VisualizationJS() {
    var entityData;
    var chart1;
    var chart2;
    this.initialize = function (entityRows) {
        setupDropdownTabs("#custom-tabs-left");
        setupDropdownTabs("#custom-tabs-right");
        browserTab();
        entityData = entityRows;
        wordCloudTab();

    };
    const resizeWordCloud = function () {
        $('#wordcloud-tab-1-head').click(function () {
            chart1.resize();
        });
        $('#wordcloud-tab-2-head').click(function () {
            chart2.resize();
        });


    };
    const wordCloudTab = function () {
        
        const labelColors = {
            PERSON: '#dea590',
            PLACE: '#9aa6c1',
            DATE: '#e1be90',
            ORG: '#ced1ab',
            EVENT: '#c6a5ac'
        };
        var option = {
            tooltip: {show: true, formatter: p => `${p.name} (${p.value})`},
            series: [{
                    type: 'wordCloud',
                    gridSize: 8,
                    sizeRange: [20, 40],
                    rotationRange: [0, 0],
                    shape: 'square',
                    drawOutOfBound: false,
                    textStyle: {
                        normal: {
                            color: function (params) {
                                return labelColors[params.data.labelType] || '#333';
                            }
                        },
                        emphasis: {
                            shadowBlur: 1,
                            shadowColor: '#333'
                        }
                    },
                    data: entityData.map(a => ({
                            name: a.text, // word shown
                            value: Math.floor(Math.random() * a.count) + a.count, // value ignored since size fixed
                            ref: a.first_ref,
                            labelType: a.label  // custom field used for color
                        }))
                }]
        };
        chart1 = echarts.init(document.getElementById('wordcloud-1'));
        chart2 = echarts.init(document.getElementById('wordcloud-2'));
        chart1.setOption(option);
        chart2.setOption(option);
        window.onresize = chart1.resize
        window.onresize = chart2.resize
        resizeWordCloud();

        chart1.on('click', function (params) {
            const word = params.name;
            const ref = params.data.ref;
            const label = params.data.labelType;
            let container;
            let transcriptTab;
            if ($('.right-side').is(':visible')) {
                transcriptTab = '#transcript-tab-2';
                container = $('.right-side-inner');

            } else {
                container = $('.left-side');
                transcriptTab = '#transcript-tab-1';
            }
            scrollToTranscript(container, transcriptTab, ref)

        });
        chart2.on('click', function (params) {
            const word = params.name;
            const ref = params.data.ref;
            const label = params.data.labelType;
            let container = $('.left-side');
            let transcriptTab = '#transcript-tab-1';
            scrollToTranscript(container, transcriptTab, ref)

        });

    };
    const scrollToTranscript = function (container, transcriptTab, ref) {
        $('a[href="' + transcriptTab + '"]').trigger("click");
        $('html, body').animate({scrollTop: 0}, 100);
        setTimeout(function () {
            let scrollTo = $(transcriptTab + ">.transcript-panel .ref_" + ref);
            container.animate({
                scrollTop: scrollTo.offset().top - container.offset().top + container.scrollTop()
            });
        }, 250);
    };
    const browserTab = function () {
        $('.anno-row').click(function () {
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
            scrollToTranscript(container,transcriptTab,$(this).data('ref'));
        });

        $('.grid-section').hide();
        $('.custom-toggle-icon .icon').on('click', function () {
            $(this).siblings('span').removeClass('active');
            $(this).addClass('active');
            if ($(this).hasClass('list')) {
                $('.grid-section').hide();
                $('.list-section').show();
            } else {
                $('.list-section').hide();
                $('.grid-section').show();
            }
        });
    };
    const setupDropdownTabs = function (containerSelector, dropdownLabel = "Visualization ▼") {
        const $tabs = $(containerSelector).tabs();
        const $dropdownTabs = $(`${containerSelector} .ui-tabs-nav li.dropdown-tab`);
        if ($dropdownTabs.length === 0)
            return;
        const $dropdownContainer = $(`
                            <li class="dropdown-toggle-tab">
                                <div class="dropdown-toggle">${dropdownLabel}</div>
                                <ul class="dropdown-menu" style="display: none;"></ul>
                            </li>
                        `);
        $dropdownTabs.each(function () {
            $(this).appendTo($dropdownContainer.find(".dropdown-menu"));
        });
        $(`${containerSelector} .ui-tabs-nav`).append($dropdownContainer);
        $(`${containerSelector} .dropdown-toggle`).on("click", function (e) {
            e.stopPropagation();
            $(this).siblings(".dropdown-menu").toggle();
        });
        $(document).on("click", function () {
            $(`${containerSelector} .dropdown-menu`).hide();
        });
        $(`${containerSelector} .ui-tabs-nav li a`).on("click", function () {
            $(`${containerSelector} .dropdown-toggle`).removeClass('active');
        });
        $(`${containerSelector} .dropdown-menu a`).on("click", function () {
            const $li = $(this).parent();
            $li.removeClass("ui-tabs-selected ui-state-active");
            $li.parent().prev().addClass('active');
            $li.parent().hide();
        });
    }



}

            