function VisualizationJS() {
    var entityData;
    var mapData;
    var chart1;
    var chart2;
    this.initialize = function (entityRows, mapPoints) {
        setupDropdownTabs("#custom-tabs-left");
        setupDropdownTabs("#custom-tabs-right");
        if (mapPoints.length > 0) {

            mapData = mapPoints;
            let [map1, marker1] = loadMap('map_area_1');
            let [map2, marker2] = loadMap('map_area_2');
            $('#map-tab-1-head').click(function () {
                refreshMap(map1, marker1);
            });
            $('#map-tab-2-head').click(function () {
                refreshMap(map2, marker2);
            });
        }
        
        if (entityRows.length > 0) {
            browserTab();
            entityData = entityRows;
            wordCloudTab();
        }
        
        


    };
    const refreshMap = function (map, markers) {
        if (!('CSS' in window && CSS.supports && CSS.supports('aspect-ratio', '1/1'))) {
            const w = document.getElementById('map').offsetWidth;
            document.getElementById('map').style.height = Math.max(320, Math.round(w * 9 / 16)) + 'px';
        }
        map.invalidateSize();
        if (markers.length) {
            const group = L.featureGroup(markers);
            map.fitBounds(group.getBounds().pad(0.2));
        }
    }
    const esc = function (s) {
        return String(s).replace(/[&<>"']/g, c => ({'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'}[c]));
    }
    const loadMap = function (mapId) {
        const map = L.map(mapId).setView([20, 0], 2);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

// Plot markers
        const markers = [];
        mapData.forEach(row => {
            const lat = parseFloat(row.lat), lng = parseFloat(row.lng);
            if (!Number.isFinite(lat) || !Number.isFinite(lng))
                return;

            const text = String(row.text || '');
            const first_ref = row.first_ref;
            const count = Number(row.count || 0);

            const popupHtml = `<strong class="map_highlight" data-ref="${first_ref}">${esc(text)}${count ? ' (' + count + ')' : ''}</strong>`;
            const m = L.marker([lat, lng]).addTo(map).bindPopup(popupHtml);
            markers.push(m);
        });

// Fit to markers
        if (markers.length) {
            const group = L.featureGroup(markers);
            map.fitBounds(group.getBounds().pad(0.2));
        }
        return [map, markers];
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
    const applyGridFilter = function () {
        let id = $(this).data('id');
        const $c = $('.grid-container' + id);
        const $it = $c.find('.grid-item' + id);
        const $labels = $('#type_filter' + id), $q = $('#browser_search' + id), $sort = $('#sortBy');
        const sel = ($labels.val() || []).map(s => s.toLowerCase());
        const hasSel = sel.length > 0;
        const q = ($q.val() || '').toLowerCase();
        const order = $sort.val();

        // filter (uses data-label and data-text)
        $it.each(function () {
            const $el = $(this);
            const lbl = String($el.data('label') || '').toLowerCase();
            const txt = String($el.data('text') || '').toLowerCase();
            $el.toggle((!hasSel || sel.includes(lbl)) && (!q || txt.includes(q)));
        });

        // sort (uses data-count, data-text, data-label)
        const arr = $it.get().sort((a, b) => {
            const $a = $(a), $b = $(b);
            const ac = +$a.data('count') || 0, bc = +$b.data('count') || 0;
            const at = String($a.data('text') || '').toLowerCase();
            const bt = String($b.data('text') || '').toLowerCase();
            const al = String($a.data('label') || '').toLowerCase();
            const bl = String($b.data('label') || '').toLowerCase();
            switch (order) {
                case 'count-desc':
                    return (bc - ac) || at.localeCompare(bt);
                case 'count-asc':
                    return (ac - bc) || at.localeCompare(bt);
                case 'name-asc':
                    return at.localeCompare(bt) || al.localeCompare(bl);
                case 'name-desc':
                    return bt.localeCompare(at) || al.localeCompare(bl);
                case 'type-asc':
                    return al.localeCompare(bl) || at.localeCompare(bt);
                case 'type-desc':
                    return bl.localeCompare(al) || at.localeCompare(bt);
                default:
                    return 0;
            }
        });
        $(arr).appendTo($c);
    }
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

$(document).on("click", ".anno-row, .timeline_event, .grid-item, .map_highlight", function (e) {
    e.preventDefault(); // optional, prevents default action
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


//        $('.anno-row, .timeline_event, .grid-item, .map_highlight').click(function () {
//            
//        });
        $('#browser_search1, #browser_search2').on('keyup', applyFilters);
        $('#type_filter1, #type_filter2').on('change', applyFilters);
        $('#browser_search1, #browser_search2').on('keyup', applyGridFilter);
        $('#type_filter1, #type_filter2').on('change', applyGridFilter);

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

            