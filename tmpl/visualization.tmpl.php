<div id="wordcloud-tab-<?php echo $tab_tag; ?>">
    <div id="wordcloud-<?php echo $tab_tag; ?>"></div> 

</div>
<div id="map-tab-<?php echo $tab_tag; ?>">
    <iframe width="100%" height="400" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="https://maps.google.com/maps?width=100%25&amp;height=600&amp;hl=en&amp;q=1%20Grafton%20Street,%20Dublin,%20Ireland+(My%20Business%20Name)&amp;t=&amp;z=14&amp;ie=UTF8&amp;iwloc=B&amp;output=embed"><a href="https://www.mapsdirections.info/fr/calculer-la-population-sur-une-carte">Estimer la population sur la carte</a></iframe>
</div>
<div id="timeline-tab-<?php echo $tab_tag; ?>">
    <div class="timeline">
        <div class="container left">
            <div class="content">
                <strong>2017</strong>
                <div class="org">ORG: Org Name</div>
                <p>Lorem ipsum dolor sit amet, quo ei simul congue exerci, ad nec admodum perfecto mnesarchum, vim ea mazim fierent detracto. Ea quis iuvaret expetendis his, te elit voluptua dignissim per, habeo iusto primis ea eam.</p>
            </div>
        </div>
        <div class="container right">
            <div class="content">
                <strong>2016</strong>
                <div class="org">ORG: Org Name</div>
                <p>Lorem ipsum dolor sit amet, quo ei simul congue exerci, ad nec admodum perfecto mnesarchum, vim ea mazim fierent detracto. Ea quis iuvaret expetendis his, te elit voluptua dignissim per, habeo iusto primis ea eam.</p>
            </div>
        </div>
        <div class="container left">
            <div class="content">
                <strong>2015</strong>
                <div class="org">ORG: Org Name</div>
                <p>Lorem ipsum dolor sit amet, quo ei simul congue exerci, ad nec admodum perfecto mnesarchum, vim ea mazim fierent detracto. Ea quis iuvaret expetendis his, te elit voluptua dignissim per, habeo iusto primis ea eam.</p>
            </div>
        </div>
        <div class="container right">
            <div class="content">
                <strong>2012</strong>
                <div class="org">ORG: Org Name</div>
                <p>Lorem ipsum dolor sit amet, quo ei simul congue exerci, ad nec admodum perfecto mnesarchum, vim ea mazim fierent detracto. Ea quis iuvaret expetendis his, te elit voluptua dignissim per, habeo iusto primis ea eam.</p>
            </div>
        </div>
        <div class="container left">
            <div class="content">
                <strong>2011</strong>
                <div class="org">ORG: Org Name</div>
                <p>Lorem ipsum dolor sit amet, quo ei simul congue exerci, ad nec admodum perfecto mnesarchum, vim ea mazim fierent detracto. Ea quis iuvaret expetendis his, te elit voluptua dignissim per, habeo iusto primis ea eam.</p>
            </div>
        </div>
        <div class="container right">
            <div class="content">
                <strong>2007</strong>
                <div class="org">ORG: Org Name</div>
                <p>Lorem ipsum dolor sit amet, quo ei simul congue exerci, ad nec admodum perfecto mnesarchum, vim ea mazim fierent detracto. Ea quis iuvaret expetendis his, te elit voluptua dignissim per, habeo iusto primis ea eam.</p>
            </div>
        </div>
    </div>
</div>
<div id="browser-tab-<?php echo $tab_tag; ?>">
    <div class="browser-filter">
        <div class="custom-toggle-icon">
            <span class="icon list active" data-id="<?php echo $tab_tag; ?>">List</span>
            <span class="icon grid" data-id="<?php echo $tab_tag; ?>">Grid</span>
        </div>

        <select id="type_filter<?php echo $tab_tag; ?>" data-id="<?php echo $tab_tag; ?>" class="browser-type">
            <option value="">Type</option>
            <option value="person">Person</option>
            <option value="place">Place</option>
            <option value="date">Date</option>
            <option value="org">Org</option>
            <option value="event">Event</option>
        </select>
        <select id="sortDropdown<?php echo $tab_tag; ?>" data-id="<?php echo $tab_tag; ?>" class="browser-sort">
            <option value="">Sort</option>
            <option value="count-asc">Count ↑</option>
            <option value="count-desc">Count ↓</option>
            <option value="type-asc">Type ↑</option>
            <option value="type-desc">Type ↓</option>
            <option value="name-asc">Entity Name ↑</option>
            <option value="name-desc">Entity Name ↓</option>

        </select>
        <div class="browser-search">
            <input type="text" id="browser_search<?php echo $tab_tag; ?>" class="browser-search" data-id="<?php echo $tab_tag; ?>" placeholder="Search">
            <button class="by-voice">Voice</button>
        </div>
    </div>
    <div class="browser-filter">
        <div class="select-wrapper">
            <label class="select-label">Type</label>
            <select class="browser-type" multiple>
                <option value="all">Type</option>
                <option value="person">Person</option>
                <option value="place">Place</option>
                <option value="date">Date</option>
                <option value="org">Org</option>
                <option value="event">Event</option>
            </select>
        </div>
    </div>

    <!-- Pills will appear here -->
    <div class="pill-container"></div>
    <div class="list-section list_<?php echo $tab_tag; ?>">
        <?php
        $stats = [];

        foreach ($interview->annotations as $a) {
            $label = $a['meta']['label'] ?? '';
            $text = $a['text'] ?? '';
            $ref = isset($a['ref']) ? (int) $a['ref'] : PHP_INT_MAX;

            if ($label === '' || $text === '')
                continue;

            if (!isset($stats[$label][$text])) {
                $stats[$label][$text] = [
                    'count' => 0,
                    'first_ref' => $ref,
                ];
            }

            $stats[$label][$text]['count']++;
            if ($ref < $stats[$label][$text]['first_ref']) {
                $stats[$label][$text]['first_ref'] = $ref;
            }
        }

// (Optional) flatten to sort by frequency desc then label/text asc
        $entity_rows = [];
        foreach ($stats as $label => $texts) {
            foreach ($texts as $text => $meta) {
                $entity_rows[] = [
                    'label' => $label,
                    'text' => $text,
                    'count' => $meta['count'],
                    'first_ref' => $meta['first_ref'],
                ];
            }
        }

        usort($entity_rows, function ($a, $b) {
            // frequency desc
            if ($a['count'] !== $b['count'])
                return $b['count'] <=> $a['count'];
            // then label asc, then text asc
            return [$a['label'], $a['text']] <=> [$b['label'], $b['text']];
        });
        ?>

        <table id="entityTable<?php echo $tab_tag; ?>" class="browser-table">
            <thead>
                <tr>
                    <th>Count</th>
                    <th>Type</th>
                    <th>Text/Entity Name</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($entity_rows as $r): ?>
                    <tr class="anno-row"
                        data-ref="<?= htmlspecialchars((string) $r['first_ref'], ENT_QUOTES, 'UTF-8') ?>">
                        <td style="text-align:right;"><?= (int) $r['count'] ?></td>
                        <td><?= htmlspecialchars($r['label'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($r['text'], ENT_QUOTES, 'UTF-8') ?></td>

                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>


    </div>
    <div class="grid-section grid_<?php echo $tab_tag; ?>">
        <div class="grid-container">
            <div class="grid-item bdg-person">
                <img src="[IMAGE_URL]" alt="Thumbnail">
                <div class="caption">Caption Text</div>
            </div>

            <div class="grid-item bdg-date">
                <img src="[IMAGE_URL]" alt="Thumbnail">
                <div class="caption">Another Caption</div>
            </div>

            <div class="grid-item bdg-org">
                <img src="[IMAGE_URL]" alt="Thumbnail">
                <div class="caption">Yet Another (1)</div>
            </div>

            <div class="grid-item bdg-place">
                <div class="caption no-image">Caption Only</div>
            </div>
        </div>

    </div>
</div>
<script type="text/javascript">
    $(document).ready(function () {
    $('.browser-type').each(function () {
        const $select = $(this);
        const $pillContainer = $select.closest('.browser-filter').next('.pill-container'); // Or customize selector

        $select.select2({
            placeholder: "Type",
            closeOnSelect: false,
            minimumResultsForSearch: Infinity
        });

        function updatePills() {
            $pillContainer.empty();

            const selectedItems = $select.select2('data') || [];

            $.each(selectedItems, function (_, item) {
                const $pill = $(`
                    <span class="pill" data-id="${item.id}">
                        ${item.text}
                        <span class="remove-pill" title="Remove">×</span>
                    </span>
                `);
                $pillContainer.append($pill);
            });
        }

        $select.on('change', updatePills);

        $pillContainer.on('click', '.remove-pill', function () {
            const idToRemove = $(this).parent().data('id');
            const currentValues = $select.val() || [];
            const updatedValues = currentValues.filter(val => val !== idToRemove);
            $select.val(updatedValues).trigger('change');
        });

        updatePills(); // Initial load
    });

    // Optional: close dropdowns on outside click
    $(document).on('click', function (e) {
        $('.select2-container').each(function () {
            const $container = $(this);
            if (
                !$container.is(e.target) &&
                $container.has(e.target).length === 0 &&
                $container.find('.select2-selection--multiple').length
            ) {
                const $select = $container.prev('select');
                if ($select.length && $select.data('select2')?.isOpen()) {
                    $select.select2('close');
                }
            }
        });
    });
});



</script>