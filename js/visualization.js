function VisualizationJS() {
    var entityData;
    var chart1;
    var chart2;
    this.initialize = function (entityRows) {
        setupDropdownTabs("#custom-tabs-left");
        setupDropdownTabs("#custom-tabs-right");
        if (entityRows.length > 0) {
            browserTab();
            entityData = entityRows;
            wordCloudTab();
        }


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

    const applyFilters = function () {
        let id = $(this).data('id');
        const searchText = ($('#browser_search' + id).val() || '').toLowerCase().trim();

        // MULTISELECT: ensure an array, then lowercase
        let selectedTypes = $('#type_filter' + id).val() || [];
        if (!Array.isArray(selectedTypes))
            selectedTypes = [selectedTypes]; // in case it ever becomes single
        selectedTypes = selectedTypes
                .filter(v => v != null && v !== '')
                .map(v => v.toString().toLowerCase().trim());

        $('#entityTable' + id + ' tbody tr').each(function () {
            const rowText = $(this).text().toLowerCase();
            // Type column (index 1). Supports multiple tokens like "PERSON, ORG" or "PERSON|ORG"
            const rowTypeRaw = $(this).children('td').eq(1).text().toLowerCase();
            const rowTypes = rowTypeRaw
                    .split(/[,\|\/]+/)       // commas, pipes, slashes
                    .map(s => s.trim())
                    .filter(Boolean);

            const matchesSearch = !searchText || rowText.indexOf(searchText) > -1;

            // If no type selected → pass. Else match if any row type is in selectedTypes.
            const matchesType =
                    selectedTypes.length === 0 ||
                    rowTypes.some(t => selectedTypes.includes(t));

            $(this).toggle(matchesSearch && matchesType);
        });
    };
    const browserTab = function () {
        $("#type_filter1, #type_filter2").multiselect({
            header: true,
            noneSelectedText: "Type",
            selectedList: 0,
            selectedText: function (numSelected, total, checkedItems) {
                return numSelected + " selected";
            },

            beforeopen: function () {
                var $select = $(this);
                var selectId = $select.attr('id');

                // Find the correct multiselect menu for this select
                var $dropdown = $('.ui-multiselect-menu').filter(function () {
                    return $(this).find('input[id^="ui-multiselect-' + selectId + '-"]').length > 0;
                }).first();

                if ($dropdown.length) {
                    // Create wrapper if not already there
                    if (!$select.parent().hasClass('multiselect-wrapper')) {
                        $select.wrap('<div class="multiselect-wrapper"></div>');
                    }

                    // Move dropdown into wrapper BEFORE it opens
                    $dropdown.appendTo($select.closest('.multiselect-wrapper'));
                }
            },

            open: function () {
                var $select = $(this);
                var selectId = $select.attr('id');

                var $dropdown = $('.ui-multiselect-menu').filter(function () {
                    return $(this).find('input[id^="ui-multiselect-' + selectId + '-"]').length > 0;
                }).first();

                if ($dropdown.length) {
                    // Optionally re-style
                    $dropdown.css({
                        position: 'absolute',
                        top: $select.outerHeight(),
                        left: 0,
                        zIndex: 1000
                    });
                }
            }
        });



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
            scrollToTranscript(container, transcriptTab, $(this).data('ref'));
        });
        $('#browser_search1, #browser_search2').on('keyup', applyFilters);
        $('#type_filter1, #type_filter2').on('change', applyFilters);

        $('#sortDropdown1, #sortDropdown2 ').on('change', function () {

            const value = $(this).val();
            if (!value)
                return;

            const [col, dir] = value.split('-'); // e.g. "id-asc" → ["id", "asc"]

            let colIndex = 0;
            if (col === 'type')
                colIndex = 1;
            if (col === 'name')
                colIndex = 2;

            const $tbody = $('#entityTable' + $(this).data('id') + ' tbody');
            const $rows = $tbody.find('tr').get();

            $rows.sort(function (rowA, rowB) {
                let aText = $(rowA).children('td').eq(colIndex).text().trim();
                let bText = $(rowB).children('td').eq(colIndex).text().trim();

                if (col === 'id') {
                    aText = parseInt(aText, 10);
                    bText = parseInt(bText, 10);
                }

                if (aText < bText)
                    return dir === 'asc' ? -1 : 1;
                if (aText > bText)
                    return dir === 'asc' ? 1 : -1;
                return 0;
            });

            // Re-attach sorted rows
            $.each($rows, function (_, row) {
                $tbody.append(row);
            });
        });
        $('.grid-section').hide();
        $('.custom-toggle-icon .icon').on('click', function () {
            $(this).siblings('span').removeClass('active');
            $(this).addClass('active');
            let id = $(this).data('id');
            if ($(this).hasClass('list')) {
                $('.grid_' + id).hide();
                $('.list_' + id).show();
            } else {
                $('.list_' + id).hide();
                $('.grid_' + id).show();
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

            