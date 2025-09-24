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
            <span class="icon list active">List</span>
            <span class="icon grid">Grid</span>
        </div>

        <select class="browser-type">
            <option value="all">Type</option>
            <option value="person">Person</option>
            <option value="place">Place</option>
            <option value="date">Date</option>
            <option value="org">Org</option>
            <option value="event">Event</option>
        </select>
        <select class="browser-sort">
            <option value="all">Sort</option>
            <option value="one">ID ↑</option>
            <option value="one">ID ↓</option>
            <option value="one">Type ↑</option>
            <option value="one">Type ↓</option>
            <option value="one">Entity Name ↑</option>
            <option value="one">Entity Name ↓</option>

        </select>
        <div class="browser-search">
            <input type="text" class="browser-search" placeholder="Search">
            <button class="by-voice">Voice</button>
        </div>
    </div>
    <div class="list-section">
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

        <table class="browser-table">
            <thead>
                <tr>
                    <th>#</th>
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
    <div class="grid-section">
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

</script>