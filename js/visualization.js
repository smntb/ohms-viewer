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

        activateWordCloud();



    };
    const activateWordCloud = function () {
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
                    gridSize: 2,
                    sizeRange: [20, 20],
                    rotationRange: [-90, 90],
                    shape: 'square',
                    drawOutOfBound: true,
                    textStyle: {
                        normal: {
                            color: function (params) {
                                return labelColors[params.data.labelType] || '#333';
                            }
                        },
                        emphasis: {
                            shadowBlur: 10,
                            shadowColor: '#333'
                        }
                    },
                    data: entityData.map(a => ({
                            name: a.text, // word shown
                            value: 1, // value ignored since size fixed
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

// Initialize ECharts instance



//        chart.setOption(option);
//        window.onresize = chart.resize;
//        chart.on('click', function (params) {
//    // params.name => word
//    // params.value => weight
//    // params.data.ref / params.data.labelType => your custom fields
//    const word = params.name;
//    const ref  = params.data.ref;
//    const label = params.data.labelType;
//
//    console.log('Clicked:', { word, ref, label });
//
//    // Example actions:
//    // 1) Scroll to a table row with the same ref
////    const row = document.getElementById(`row-ref-${ref}`);
////    if (row) row.scrollIntoView({ behavior: 'smooth', block: 'center' });
//
//    // 2) Or trigger your own function / modal / filter
//    // openAnnotationDetail({ word, ref, label });
//  });

    };
    const browserTab = function () {
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

            