<?php
date_default_timezone_set($config['timezone']);
$audioFormats = array('.mp3', '.wav', '.ogg', '.flac', '.m4a');
$filepath = $interview->media_url;
$mediaFormat = (strtolower($interview->clipsource) == "aviary") ? $interview->aviaryMediaFormat : substr($filepath, -4, 4);
$rights = (string) $interview->rights;
$usage = (string) $interview->usage;
$acknowledgment = (string) $interview->funding;
$contactemail = '';
$contactlink = '';
$copyrightholder = '';
$protocol = 'https';
if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] != 'on') {
    $protocol = 'http';
}
$host = $_SERVER['HTTP_HOST'];
$uri = $_SERVER['REQUEST_URI'];
$baseurl = "$protocol://$host$uri";
$site_url = "$protocol://$host";
$extraCss = null;
$exhibitMode = 0;
$printMode = 0;
if (isset($config['exhibit_mode']) && $config['exhibit_mode'] <> '') {
    $exhibitMode = $config['exhibit_mode'];
} else {
    $exhibitMode = 0;
}
if (isset($config['print_mode']) && $config['print_mode'] <> '') {
    $printMode = $config['print_mode'];
} else {
    $printMode = 0;
}

if (isset($config[$interview->repository])) {
    $repoConfig = $config[$interview->repository];
} else {
    // Fallback: Find the first nested array
    foreach ($config as $key => $value) {
        if (is_array($value)) {
            $repoConfig = $value;
            break;
        }
    }
}


if (isset($repoConfig)) {
    $contactemail = $repoConfig['contactemail'];
    $contactlink = $repoConfig['contactlink'];
    $copyrightholder = $repoConfig['copyrightholder'];
    if (isset($repoConfig['open_graph_image']) && $repoConfig['open_graph_image'] <> '') {
        $openGraphImage = $repoConfig['open_graph_image'];
    }
    if (isset($repoConfig['open_graph_description']) && $repoConfig['open_graph_description'] <> '') {
        $openGraphDescription = $repoConfig['open_graph_description'];
    }
    if (isset($repoConfig['css']) && strlen($repoConfig['css']) > 0) {
        $extraCss = $repoConfig['css'];
    }
}
$seriesLink = (string) $interview->series_link;
$collectionLink = (string) $interview->collection_link;
$lang = (string) $interview->translate;

$userNotes = trim($interview->user_notes);
$heightAdjustmentClass = "";
if (!empty($userNotes)):
    $heightAdjustmentClass = "adjust_height";
endif;
?>

<!DOCTYPE html>
<html lang="en" class="loading">
    <head>
        <meta http-equiv="Content-Type" content="text/html;charset=UTF-8"/>
        <title><?php echo $interview->title; ?></title>
        <link rel="stylesheet" href="css/viewer.css?v1.4.1" type="text/css"/>
        <?php if (isset($extraCss)) { ?>
            <link rel="stylesheet" href="css/<?php echo $extraCss ?>?v1.0" type="text/css"/>
        <?php }
        ?>
        <link rel="stylesheet" href="css/jquery-ui.toggleSwitch.css" type="text/css"/>
        <link rel="stylesheet" href="css/jquery-ui-1.8.16.custom.css" type="text/css"/>
        <link rel="stylesheet" href="css/font-awesome.css">
        <link rel="stylesheet" href="css/simplePagination.css">
        <script src="//ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
        <script src="js/jquery-ui.toggleSwitch.js"></script>
        <script src="js/toggleSwitch.js?v1.16"></script>
        <script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.8.18/jquery-ui.min.js"></script>
        <script src="js/viewer.js"></script>
        <script type="text/javascript" src="js/tipped/tipped.js"></script>
        <link rel="stylesheet" href="css/tipped/tipped.css" type="text/css"/>
        <script src="js/jquery.simplePagination.js"></script>
        <meta property="og:title" content="<?php echo $interview->title; ?>"/>
        <meta property="og:url" content="<?php echo $baseurl ?>">
        <?php if (isset($openGraphImage)) { ?>
            <meta property="og:image" content="<?php echo "$site_url/$openGraphImage" ?>">
        <?php }
        ?>
        <?php if (isset($openGraphDescription)) { ?>
            <meta property="og:description" content="<?php echo "$openGraphDescription" ?>">
        <?php }
        ?>
        <?php if (isset($repoConfig['ga_tracking_id'])) { ?>
            <!-- Google tag (gtag.js) -->
            <script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo $repoConfig['ga_tracking_id']; ?>"></script>
            <script>
                window.dataLayer = window.dataLayer || [];
                function gtag() {
                    dataLayer.push(arguments);
                }
                gtag('js', new Date());

                gtag('config', '<?php echo $repoConfig['ga_tracking_id']; ?>');
            </script>
        <?php } ?>
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    </head>
    <body>

        <script>
            var exhibitMode = <?php echo $exhibitMode; ?>;
            var endAt = null;
            var exhibitIndex = null;
            var jumpToTime = null;
            if (location.href.search('#segment') > -1) {
                var jumpToTime = parseInt(location.href.replace(/(.*)#segment/i, ""));
                if (isNaN(jumpToTime)) {
                    jumpToTime = 0;
                }
            }
        </script>


    <!-- <div id="main" class="<?php echo $heightAdjustmentClass; ?>">
        
        <div id="main-panels">
            <div id="content-panel">
                <div id="holder-panel"></div>
        <?php
        $indexDisplay = 'display:none';
        $transcriptDisplay = 'display:block';
        if ((isset($panel) && $panel == '1') || ($interview->hasIndex() && (!isset($panel) || $panel != '0'))) {
            $indexDisplay = 'display:block';
            $transcriptDisplay = 'display:none';
        }
        ?>
                <div id="index-panel" class="index-panel" style="<?php //echo $indexDisplay;      ?>">
        <?php //echo $interview->index; ?>
                </div>
                <div id="transcript-panel" class="transcript-panel" style="<?php //echo $transcriptDisplay;      ?>">
        <?php //echo $interview->transcript; ?>
                </div>

            </div>

        </div>
    </div> -->




        <div class="main-box-holder">
            <div class="main-box">
                <div class="left-side">
                    <?php if (in_array($mediaFormat, $audioFormats)) { ?> 
                        <div id="header">
                        <?php } else {
                            ?>
                            <div id="headervid">  
                                <div class="top-details">
                                    <?php
                                }
                                if ($printMode) {
                                    ?> 
                                    <a href="#" class="printCustom" ></a>
                                <?php } if (isset($repoConfig)): ?>
                                    <img id="headerimg"
                                         src="<?php echo $repoConfig['footerimg']; ?>"
                                         alt="<?php echo $repoConfig['footerimgalt']; ?>"/>
                                     <?php endif;
                                     ?>
                                <h1 class="truncate"><?php echo $interview->title; ?></h1>

                                <div id="secondaryMetaData">
                                    <div>
                                        <div class="detail-metadata truncate-collection">
                                            <?php
                                            echo $interview->collection;
                                            if (trim($interview->collection) && trim($interview->series)) {
                                                echo " | ";
                                            }
                                            echo $interview->series;
                                            ?>

                                        </div>
                                        <div class="detail-metadata truncate"><?php echo $interview->repository; ?></div>
                                    </div>
                                </div>
                            </div>
                            <div id="audio-panel">
                                <?php include_once 'tmpl/player_' . $interview->playername . '.tmpl.php'; ?>
                            </div>

                            <div class="bottom-details">
                                <div id="searchbox-panel"><?php include_once 'tmpl/search.tmpl.php'; ?></div>
                                <div id="custom-tabs-left">
                                    <ul>
                                        <li><a href="#about-tab-1">About</a></li>
                                        <li><a href="#index-tab-1">Index <span class="count">4</span></a></li>
                                        <li><a href="#transcript-tab-1">Transcript <span class="count">8</span></a></li>

                                        <!-- These will be moved into dropdown via JS -->
                                        <li class="dropdown-tab"><a href="#wordcloud-tab-1">Word Cloud</a></li>
                                        <li class="dropdown-tab"><a href="#map-tab-1">Map</a></li>
                                        <li class="dropdown-tab"><a href="#timeline-tab-1">Timeline</a></li>
                                        <li class="dropdown-tab"><a href="#browser-tab-1">Browser</a></li>
                                    </ul>

                                    <div id="about-tab-1">
                                        <div class="about-panel">
                                            <strong>Interview Summary</strong>

                                            <p><?php echo $interview->description; ?></p>


                                            <strong>Interview Accession</strong>
                                            <p>
                                                <?php
                                                if (trim($interview->interviewer) && trim($interview->accession)) {
                                                    
                                                }
                                                ?>
                                                <?php echo $interview->accession; ?>
                                            </p>

                                            <strong>Interviewer Name</strong>

                                            <p>
                                                <?php echo $interview->repository; ?>
                                            </p>

                                            <strong>Interviewee Name</strong>

                                            <p><?php
                                                if (trim($interview->interviewer)) {
                                                    echo "{$interview->interviewer}";
                                                }
                                                ?></p>
                                        </div>
                                    </div>
                                    <div id="index-tab-1">
                                        <div id="index-panel" class="index-panel">
                                            <?php echo $interview->index; ?>
                                        </div>
                                    </div>
                                    <div id="transcript-tab-1">
                                        <div id="transcript-panel" class="transcript-panel">
                                            <div class="data-layers">
                                                <div class="custom-checkbox">
                                                    <input type="checkbox" id="toggle-layers" class="toggle-layers" name="toggle-layers">
                                                    <label for="toggle-layers" class="toggle-layers-label">View Data Layers</label>
                                                </div>
                                                <ul class="data-layers-list">
                                                    <li><span class="bdg-person"><i class="fa fa-eye"></i> Person</span></li>
                                                    <li><span class="bdg-place"><i class="fa fa-eye"></i> Place</span></li>
                                                    <li><span class="bdg-date"><i class="fa fa-eye"></i> Date</span></li>
                                                    <li><span class="bdg-org"><i class="fa fa-eye"></i> Org</span></li>
                                                    <li><span class="bdg-event"><i class="fa fa-eye"></i> Event</span></li>
                                                </ul>
                                            </div>
                                            <p>Lorem <span class="bdg-event bdg-text" id="popoverBtn">ipsum</span> dolor sit amet <span class="bdg-person bdg-text">consectetur</span> adipisicing elit. In, totam assumenda <span class="bdg-place bdg-text">consequatur</span> iusto aut vero enim incidunt aspernatur, <span class="bdg-date bdg-text">ipsa</span> perspiciatis velit <span class="bdg-org bdg-text">explicabo</span> esse nemo autem consequuntur! Repellat minima sint omnis.</p>
                                            <?php echo $interview->transcript; ?>
                                        </div>
                                    </div>
                                    <div id="wordcloud-tab-1">
                                        <!-- <div id='wordcloud'></div> -->
                                        WordCloud
                                    </div>
                                    <div id="map-tab-1">
                                        <iframe width="100%" height="400" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="https://maps.google.com/maps?width=100%25&amp;height=600&amp;hl=en&amp;q=1%20Grafton%20Street,%20Dublin,%20Ireland+(My%20Business%20Name)&amp;t=&amp;z=14&amp;ie=UTF8&amp;iwloc=B&amp;output=embed"><a href="https://www.mapsdirections.info/fr/calculer-la-population-sur-une-carte">Estimer la population sur la carte</a></iframe>
                                    </div>
                                    <div id="timeline-tab-1">
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
                                    <div id="browser-tab-1">
                                        <div class="browser-filter">
                                            <div class="custom-toggle-icon">
                                                <span class="icon list active">List</span>
                                                <span class="icon grid">Grid</span>
                                            </div>

                                            <select class="browser-type">
                                                <option value="all">Type</option>
                                                <option value="one">One</option>
                                                <option value="two">Two</option>
                                                <option value="three">Three</option>
                                            </select>
                                            <select class="browser-sort">
                                                <option value="all">Sort</option>
                                                <option value="one">One</option>
                                            </select>
                                            <div class="browser-search">
                                                <input type="text" class="browser-search" placeholder="Search">
                                                <button class="by-voice">Voice</button>
                                            </div>
                                        </div>
                                        <div class="list-section">
                                            <table class="browser-table">
                                                <tr>
                                                    <th>#</th>
                                                    <th>Type</th>
                                                    <th>Text/Entity Name</th>
                                                </tr>
                                                <tr>
                                                    <td>1</td>
                                                    <td>PERSON</td>
                                                    <td>consectetur</td>
                                                </tr>
                                                <tr>
                                                    <td>2</td>
                                                    <td>PLACE</td>
                                                    <td>pelletesque ornare</td>
                                                </tr>
                                                <tr>
                                                    <td>7</td>
                                                    <td>PERSON</td>
                                                    <td>Douglas A. Boyd</td>
                                                </tr>
                                                <tr>
                                                    <td>1</td>
                                                    <td>ORG</td>
                                                    <td>congue et ex</td>
                                                </tr>
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
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="right-side">
                        <button class="toggle-sides"><img src="/imgs/toggle-btn-icon.png" /></button>
                        <div class="right-side-inner">
                            <div class="toolbar-right">
                                <?php if ($interview->translate == '1'): ?>
                                    <div id="translate-toggle" class="<?php echo $toggleLanguageSwitch; ?>">

                                        <a href="#" class="translate-link" id="translate-link" data-lang="<?php echo $interview->language ?>"
                                           data-translate="<?php $interview->transcript_alt_lang; ?>"
                                           data-toggleAvailable="<?php echo $toggleAvailable; ?>"
                                           data-linkto="<?php echo $interview->transcript_alt_lang ?>" data-default="<?php echo $interview->language ?>">
                                            <?php echo $interview->transcript_alt_lang ?></a>
                                        <a href="#" class="translate-link" id="translate-link" data-lang="<?php echo $interview->transcript_alt_lang ?>"
                                           data-translate="<?php $interview->language; ?>"
                                           data-toggleAvailable="<?php echo $toggleAvailable; ?>"
                                           data-linkto="<?php echo $interview->language ?>" data-default="<?php echo $interview->language ?>">
                                            <?php echo $interview->language ?></a>
                                    </div>
                                    <?php
                                endif;
                                ?>
                                <a href="#" class="refreshPage"></a>
                                <?php if ($printMode) {
                                    ?> 
                                    <a href="#" class="printCustom" ></a>
                                <?php } ?>
                            </div>
                            <?php if ($printMode) { ?>
                                <a href="#" class="printCustomMobile" ></a>
                            <?php } if (!empty($userNotes)): ?>
                                <div class="user_notes"><?php echo $interview->user_notes ?>
                                    <img src="imgs/button_close.png" onclick="$('.user_notes').slideToggle();"/>
                                </div>
                            <?php endif; ?>
                            <div id="custom-tabs-right">
                                <ul>
                                    <li><a href="#index-tab-2">Index <span class="count">4</span></a></li>
                                    <li><a href="#transcript-tab-2">Transcript <span class="count">8</span></a></li>

                                    <!-- These will be moved into dropdown via JS -->
                                    <li class="dropdown-tab"><a href="#wordcloud-tab-2">Word Cloud</a></li>
                                    <li class="dropdown-tab"><a href="#map-tab-2">Map</a></li>
                                    <li class="dropdown-tab"><a href="#timeline-tab-2">Timeline</a></li>
                                    <li class="dropdown-tab"><a href="#browser-tab-2">Browser</a></li>
                                </ul>

                                <div id="index-tab-2">
                                    <div id="index-panel" class="index-panel">
                                        <?php echo $interview->index; ?>
                                    </div>
                                </div>
                                <div id="transcript-tab-2">
                                    <div id="transcript-panel" class="transcript-panel">
                                        <div class="data-layers">
                                            <div class="custom-checkbox">
                                                <input type="checkbox" id="toggle-layers" class="toggle-layers" name="toggle-layers">
                                                <label for="toggle-layers" class="toggle-layers-label">View Data Layers</label>
                                            </div>
                                            <ul class="data-layers-list">
                                                <li><span class="bdg-person"><i class="fa fa-eye"></i> Person</span></li>
                                                <li><span class="bdg-place"><i class="fa fa-eye"></i> Place</span></li>
                                                <li><span class="bdg-date"><i class="fa fa-eye"></i> Date</span></li>
                                                <li><span class="bdg-org"><i class="fa fa-eye"></i> Org</span></li>
                                                <li><span class="bdg-event"><i class="fa fa-eye"></i> Event</span></li>
                                            </ul>
                                        </div>
                                        <p>Lorem <span class="bdg-event bdg-text">ipsum</span> dolor sit amet <span class="bdg-person bdg-text">consectetur</span> adipisicing elit. In, totam assumenda <span class="bdg-place bdg-text">consequatur</span> iusto aut vero enim incidunt aspernatur, <span class="bdg-date bdg-text">ipsa</span> perspiciatis velit <span class="bdg-org bdg-text">explicabo</span> esse nemo autem consequuntur! Repellat minima sint omnis.</p>
                                        <?php echo $interview->transcript; ?>
                                    </div>
                                </div>
                                <div id="wordcloud-tab-2">
                                    <div id='wordcloud'></div>
                                </div>
                                <div id="map-tab-2">
                                    <iframe width="100%" height="400" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="https://maps.google.com/maps?width=100%25&amp;height=600&amp;hl=en&amp;q=1%20Grafton%20Street,%20Dublin,%20Ireland+(My%20Business%20Name)&amp;t=&amp;z=14&amp;ie=UTF8&amp;iwloc=B&amp;output=embed"><a href="https://www.mapsdirections.info/fr/calculer-la-population-sur-une-carte">Estimer la population sur la carte</a></iframe>
                                </div>
                                <div id="timeline-tab-2">
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
                                <div id="browser-tab-2">
                                    Browser Info
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div id="footer">
                <div id="footer-metadata">
                    <?php if (!empty($rights)) { ?>
                        <p><span></span></p><strong><a href="#" id="lnkRights">View Rights Statement</a></strong>
                        <div id="rightsStatement"><?php echo $rights; ?></div>
                    <?php } else {
                        ?>
                        <p><span></span></p><strong>View Rights Statement</strong>
                    <?php }
                    ?>
                    <?php if (!empty($usage)) { ?>
                        <p><span></span></p><strong><a href="#" id="lnkUsage">View Usage Statement</a></strong>
                        <div id="usageStatement"><?php echo $usage; ?></div>
                    <?php } else {
                        ?>
                        <p><span></span></p><strong>View Usage Statement</strong>
                    <?php }
                    ?>

                    <?php if (!empty($acknowledgment)) { ?>
                        <p><span></span></p><strong><a href="#" id="lnkFunding">Acknowledgment</a></strong>
                        <div id="fundingStatement"><?php echo $acknowledgment; ?></div>
                    <?php } else {
                        ?>
                        <p><span></span></p><strong>Acknowledgment</strong>
                    <?php }
                    ?>
                    <?php if (!empty($collectionLink)) { ?>
                        <p><span></span></p><strong>Collection Link:
                            <?php if (isset($interview->collection_link) && (string) $interview->collection_link != '') { ?>
                                <a href="<?php echo $interview->collection_link ?>"><?php echo $interview->collection ?></a>
                            <?php } else {
                                ?>
                                <?php echo $interview->collection; ?>
                            <?php }
                            ?>
                        </strong>
                    <?php }
                    ?>
                    <?php if (!empty($seriesLink)) { ?>
                        <p><span></span></p>
                        <strong>Series Link:
                            <?php if (isset($interview->series_link) && (string) $interview->series_link != '') { ?>
                                <a href="<?php echo $interview->series_link ?>"><?php echo $interview->series ?></a>
                            <?php } else {
                                ?>
                                <?php echo $interview->series; ?>
                            <?php }
                            ?>
                        </strong>
                    <?php }
                    ?>
                    <?php if (!empty($contactemail)) { ?>
                        <p><span></span></p>
                        <strong>Contact Us: <a href="mailto:<?php echo $contactemail ?>"><?php echo $contactemail ?></a> |
                            <a href="<?php echo $contactlink ?>"><?php echo $contactlink ?></a>
                        </strong>
                    <?php }
                    ?>
                </div>
                <div id="footer-copyright">
                    <small id="copyright"><span>&copy; <?php echo Date("Y") ?></span><?php echo $copyrightholder ?></small>
                </div>
                <div id="footer-logo">
                    <img alt="Powered by OHMS logo" src="imgs/ohms_logo.png" border="0"/>
                </div>
                <br clear="both"/>
            </div>

            <div id="customPopover" class="popover">
                <div class="popover-body">
                    <div><strong>Person:</strong> Lorem Ispum Corp.</div>
                    <div>Wiki Description text running onto 2 rows with elipses cutting it offelipses cutting it off</div>
                    <div><a href="#">Wikipedia</a></div>
                    <div id="paginate" class="simple-pagination">
                        <ul>
                            <li class="disabled">
                                <span class="current prev"><img src="/imgs/arrow-square.webp" alt="Previous"></span>
                            </li>
                            <li>
                                <span id="paginate_info">Showing 1 - 5 of 8</span>
                            </li>
                            <li>
                                <a href="#page-2" class="page-link next"><img src="/imgs/arrow-square.webp" alt="Next"></a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>




            <script src="js/video.min.js"></script>
            <link rel="stylesheet" href="css/video-js.css" type="text/css" media="screen"/>
            <script src="js/jquery.easing.1.4.js"></script>
            <script src="js/jquery.scrollTo-min.js"></script>
            <script src="js/viewer_<?php echo $interview->viewerjs; ?>.js?v=0.12"></script>
            <link rel="stylesheet" href="js/fancybox_2_1_5/source/jquery.fancybox.css?v=2.1.5" type="text/css" media="screen"/>

            <script src="js/fancybox_2_1_5/source/jquery.fancybox.pack.js?v=2.1.5"></script>
            <link rel="stylesheet"
                  href="js/fancybox_2_1_5/source/helpers/jquery.fancybox-buttons.css?v=1.0.5" type="text/css" media="screen"/>
            <script src="js/fancybox_2_1_5/source/helpers/jquery.fancybox-buttons.js?v=1.0.5"></script>
            <script src="js/fancybox_2_1_5/source/helpers/jquery.fancybox-media.js?v=1.0.6"></script>
            <link rel="stylesheet"
                  href="js/fancybox_2_1_5/source/helpers/jquery.fancybox-thumbs.css?v=1.0.7" type="text/css" media="screen"/>
            <script src="js/fancybox_2_1_5/source/helpers/jquery.fancybox-thumbs.js?v=1.0.7"></script>
            <script src="js/popper.js"></script>
            <script src="js/tooltip.js"></script>
            <script src="js/custom.js"></script>
            <script src="https://s3-us-west-2.amazonaws.com/s.cdpn.io/95368/echarts-en.min-421rc1.js"></script>
            <script src="https://s3-us-west-2.amazonaws.com/s.cdpn.io/95368/echarts-wordcloud2.min.js"></script>
            <script>
                                        var allToolTipInstances = {};
                                        $(document).ready(function () {
                                            setTimeout(() => {
                                                $('html').removeClass('loading')
                                            }, 500);
                                        let url = new URL(window.location.href);
                                                let external = '';
                                                if (url.searchParams.has('external')) {
                                        external = '&external=true'
                                        }
                                        $(".printCustom").click(function(){
                                        window.location.href = "viewer.php?action=pdf&cachefile=" + cachefile + external + "";
                                        });
                                                $(".printCustomMobile").click(function(){
                                        window.open("viewer.php?action=pdf&cachefile=" + cachefile + external + "", '_blank');
                                        });
                                                $(".transcript-line").each(function(){
                                        var jumplinkElm = $(this).find('.jumpLink');
                                                var numberOfIntervalsInLine = jumplinkElm.length;
                                                var isNestedElm = $(this).find('.transcript-line');
                                                if (numberOfIntervalsInLine > 1 && isNestedElm.length < 1){
                                        var marginToAdd = 13;
                                                var totalMargin = 13 * numberOfIntervalsInLine;
                                                jumplinkElm.each(function(index){
                                                var currentMargin = totalMargin - (marginToAdd * (index + 1));
                                                        $(this).css('margin-top', currentMargin);
                                                });
                                        }
                                        });
                                                setTimeout(function(){
                                                var htmlTranscript = $('#transcript-panel').html().trim();
                                                        var htmlIndex = $('#index-panel').html().trim();
                                                        var isTranslate = $('#is_translate').val().trim();
                                                        if ((htmlTranscript == "" || htmlTranscript.includes("No transcript")) && isTranslate == "0"){
                                                $('.alpha-circle').hide();
                                                        $('#toggle_switch').attr("disabled", "disabled");
                                                        $('.slider.round').css("background-color", "#ccc");
                                                } else if (htmlIndex == "" && htmlTranscript != "" && isTranslate == "0"){
                                                $('.alpha-circle').hide();
                                                        $('#toggle_switch').attr("disabled", "disabled");
                                                        $('.slider.round').css("background-color", "#ccc");
                                                } else if (htmlIndex == "" && htmlTranscript == "" && isTranslate == "0"){
                                                $('.alpha-circle').hide();
                                                        $('#toggle_switch').attr("disabled", "disabled");
                                                        $('.slider.round').css("background-color", "#ccc");
                                                } else if ((htmlIndex == "" || htmlTranscript == "" || htmlTranscript.includes("No transcript")) && isTranslate == "1"){
                                                $('.alpha-circle').hide();
                                                }
                                                }, 300);
                                                $('.footnoteTooltip').each(function(index, element){
                                        footnoteID = $(element).data('index');
                                                footnoteAttrId = $(element).attr("id");
                                                footnoteHtml = $('#' + footnoteID).parent().children('span').html();
                                                $(element).attr("data-tooltip", footnoteHtml);
                                                activatePopper(footnoteAttrId);
                                        });
                                                $('.info-circle').each(function(index, element){
                                        activatePopperIndexTranscript(element.id, 'i');
                                        });
                                                footnoteHover("bind");
                                                if (jumpToTime !== null) {
                                        jQuery('div.point').each(function (index) {
                                        if (parseInt(jQuery(this).find('a.indexJumpLink').data('timestamp')) == jumpToTime) {
                                        jumpLink = jQuery(this).find('a.indexJumpLink');
                                                jQuery('#accordionHolder').accordion({active: index});
                                                jQuery('#accordionHolder-alt').accordion({active: index});
                                                var interval = setInterval(function () {
<?php
switch ($interview->playername) {
    case 'youtube':
        ?>
                                                        if (player !== undefined &&
                                                                player.getCurrentTime !== undefined && player.getCurrentTime() == jumpToTime) {
        <?php
        break;
    case 'brightcove':
        ?>
                                                        if (modVP !== undefined &&
                                                                modVP.getVideoPosition !== undefined &&
                                                                Math.floor(modVP.getVideoPosition(false)) == jumpToTime) {
        <?php
        break;
    case 'kaltura':
        ?>
                                                        if (kdp !== undefined && kdp.evaluate('{video.player.currentTime}') == jumpToTime) {
        <?php
        break;
    default:
        ?>
                                                        if (Math.floor(player.currentTime) == jumpToTime) {
        <?php
        break;
}
?>
                                                clearInterval(interval);
                                                } else {
                                                jumpLink.click();
                                                }
                                                }
                                                ,
                                                        500
                                                        );
                                                        jQuery(this).find('a.indexJumpLink').click();
                                                }
                                                });
                                                }
                                                $(".fancybox").fancybox();
                                                        $(".various").fancybox({
                                                maxWidth: 800,
                                                        maxHeight: 600,
                                                        fitToView: false,
                                                        width: '70%',
                                                        height: '70%',
                                                        autoSize: false,
                                                        closeClick: false,
                                                        openEffect: 'none',
                                                        closeEffect: 'none'
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
                                                        $(".fancybox-button").fancybox({
                                                prevEffect: 'none',
                                                        nextEffect: 'none',
                                                        closeBtn: false,
                                                        helpers: {
                                                        title: {type: 'inside'},
                                                                buttons: {}
                                                        }
                                                });
                                                        jQuery('#lnkRights').click(function () {
                                                jQuery('#rightsStatement').fadeToggle(400);
                                                        return false;
                                                });
                                                        jQuery('#lnkUsage').click(function () {
                                                jQuery('#usageStatement').fadeToggle(400);
                                                        return false;
                                                });
                                                        jQuery('#lnkFunding').click(function () {
                                                jQuery('#fundingStatement').fadeToggle(400);
                                                        return false;
                                                });
                                                });
                                                function footnoteHover(state){
                                                if (state == "bind"){
                                                $(".footnote-ref").bind("hover",
                                                        function() {
                                                        var footnoteHtmlLength = $(this).find('.footnoteTooltip').attr("data-tooltip").length;
                                                                width = footnoteHtmlLength * 50 / 100;
                                                                if (footnoteHtmlLength > 130){
                                                        $('head').append("<style>.tooltip{ width: " + width + "px }</style>");
                                                        } else{
                                                        $('head').append("<style>.tooltip{ width: 130px; }</style>");
                                                        }
                                                        }
                                                );
                                                } else if (state == "unbind"){
                                                $(".footnote-ref").unbind("hover");
                                                }
                                                }
                                        function activatePopper(element) {
                                        var footnoteHtml = $("#" + element).data("tooltip");
                                                allToolTipInstances[footnoteAttrId] = new Tooltip($("#" + element), {
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
                                                        boundariesElement: $('#transcript-panel'),
                                                        },
                                                },
                                        });
                                        }

                                        function activatePopperIndexTranscript(element, type) {
                                        if (type == 'i'){
                                        var timePoint = $("#" + element).data("time-point");
                                                var id = $("#" + element).data("marker-counter");
                                                var indexTitle = $("#" + element).data("index-title");
                                                var anchorHtml = "<div class='info-toggle' onclick=\"toggleRedirectTranscriptIndex(" + id + ",'transcript-to-index')\" >Segment: <b>" + indexTitle + "</b> " + timePoint + " </div>";
                                                Tipped.create('#' + element, anchorHtml, {
                                                size: 'large',
                                                        radius: true,
                                                        position: 'right'
                                                });
                                        }
                                        }


            </script>
            <script>
                var cachefile = '<?php echo $interview->cachefile; ?>';
                        $(function () {

                        // Custom function to setup dropdown tabs
                        function setupDropdownTabs(containerSelector, dropdownLabel = "Visualization ▼") {
                        const $tabs = $(containerSelector).tabs();
                                const $dropdownTabs = $(`${containerSelector} .ui-tabs-nav li.dropdown-tab`);
                                if ($dropdownTabs.length === 0) return;
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

                        // Apply to both tab containers
                        setupDropdownTabs("#custom-tabs-left");
                                setupDropdownTabs("#custom-tabs-right");
                                function hasTranslateParam() {
                                const urlParams = new URLSearchParams(window.location.search);
                                        return urlParams.get('translate') === '1';
                                }

                        // Set active language tab
                        if (hasTranslateParam()) {
                        $('a[data-lang="<?php echo $interview->language; ?>"]').addClass('active');
                        } else {
                        $('a[data-lang="<?php echo $interview->transcript_alt_lang; ?>"]').addClass('active');
                        }

                        });
                        // Data Layers Toggle Functionality
                        $('.data-layers-list').hide();
                        $('.toggle-layers').change(function() {
                if ($(this).is(':checked')) {
                $('.data-layers-list').show();
                } else {
                $('.data-layers-list').hide();
                }
                });
                        $('.data-layers-list').on('click', '.fa-eye, .fa-eye-slash', function(e) {
                e.stopPropagation();
                        const $icon = $(this);
                        const $span = $icon.closest('span');
                        $span.toggleClass('active-layer');
                        if ($icon.hasClass('fa-eye')) {
                $icon.removeClass('fa-eye').addClass('fa-eye-slash');
                } else {
                $icon.removeClass('fa-eye-slash').addClass('fa-eye');
                }
                });
                        // Initialize ECharts instance
                        var chart = echarts.init(document.getElementById('wordcloud'));
                        const colorPalette = ['#dea590', '#9aa6c1', '#e1be90', '#ced1ab', '#c6a5ac'];
                        var option = {
                        tooltip: {},
                                series: [ {
                                type: 'wordCloud',
                                        gridSize: 2,
                                        sizeRange: [12, 50],
                                        rotationRange: [ - 90, 90],
                                        // The shape of the "cloud" to draw. Can be any polar equation represented as a
                                        // callback function, or a keyword present. Available presents are circle (default),
                                        // cardioid (apple or heart shape curve, the most known polar equation), diamond (
                                        // alias of square), triangle-forward, triangle, (alias of triangle-upright, pentagon, and star.
                                        // Shapes: pentagon, star, random-light, random-dark, circle, cardioid, diamond, triangle-forward, triangle, triangle-upright, apple, heart shape curve
                                        shape: 'circle',
                                        width: 400,
                                        height: 400,
                                        drawOutOfBound: true,
                                        textStyle: {
                                        normal: {
                                        color: function (params) {
                                        const index = params.dataIndex || 0;
                                                return colorPalette[index % colorPalette.length];
                                        }
                                        },
                                                emphasis: {
                                                shadowBlur: 10,
                                                        shadowColor: '#333'
                                                }
                                        },
                                        data: [
                                        {
                                        name: 'Machine Learning',
                                                value: 10000,
                                                textStyle: {
                                                normal: {
                                                color: 'black'
                                                },
                                                        emphasis: {
                                                        color: 'red'
                                                        }
                                                }
                                        },
                                        {
                                        name: 'Deep Learning',
                                                value: 6181
                                        },
                                        {
                                        name: 'Computer Vision',
                                                value: 4386
                                        },
                                        {
                                        name: 'Artificial Intelligence',
                                                value: 4055
                                        },
                                        {
                                        name: 'Neural Network',
                                                value: 3500
                                        },
                                        {
                                        name: 'Algorithm',
                                                value: 3333
                                        },
                                        {
                                        name: 'Model',
                                                value: 2700
                                        },
                                        {
                                        name: 'Supervised',
                                                value: 2500
                                        },
                                        {
                                        name: 'Unsupervised',
                                                value: 2333
                                        },
                                        {
                                        name: 'Natural Language Processing',
                                                value: 1900
                                        },
                                        {
                                        name: 'Chatbot',
                                                value: 1800
                                        },
                                        {
                                        name: 'Virtual Assistant',
                                                value: 1500
                                        },
                                        {
                                        name: 'Speech Recognition',
                                                value: 1400
                                        },
                                        {
                                        name: 'Convolutional Neural Network',
                                                value: 1325
                                        },
                                        {
                                        name: 'Reinforcement Learning',
                                                value: 1300
                                        },
                                        {
                                        name: 'Training Data',
                                                value: 1250
                                        },
                                        {
                                        name: 'Classification',
                                                value: 1233
                                        },
                                        {
                                        name: 'Regression',
                                                value: 1000
                                        },
                                        {
                                        name: 'Decision Tree',
                                                value: 900
                                        },
                                        {
                                        name: 'K-Means',
                                                value: 875
                                        },
                                        {
                                        name: 'N-Gram Analysis',
                                                value: 850
                                        },
                                        {
                                        name: 'Microservices',
                                                value: 833
                                        },
                                        {
                                        name: 'Pattern Recognition',
                                                value: 790
                                        },
                                        {
                                        name: 'APIs',
                                                value: 775
                                        },
                                        {
                                        name: 'Feature Engineering',
                                                value: 700
                                        },
                                        {
                                        name: 'Random Forest',
                                                value: 650
                                        },
                                        {
                                        name: 'Bagging',
                                                value: 600
                                        },
                                        {
                                        name: 'Anomaly Detection',
                                                value: 575
                                        },
                                        {
                                        name: 'Naive Bayes',
                                                value: 500
                                        },
                                        {
                                        name: 'Autoencoder',
                                                value: 400
                                        },
                                        {
                                        name: 'Backpropagation',
                                                value: 300
                                        },
                                        {
                                        name: 'TensorFlow',
                                                value: 290
                                        },
                                        {
                                        name: 'word2vec',
                                                value: 280
                                        },
                                        {
                                        name: 'Object Recognition',
                                                value: 250
                                        },
                                        {
                                        name: 'Python',
                                                value: 235
                                        },
                                        {
                                        name: 'Predictive Analytics',
                                                value: 225
                                        },
                                        {
                                        name: 'Predictive Modeling',
                                                value: 215
                                        },
                                        {
                                        name: 'Optical Character Recognition',
                                                value: 200
                                        },
                                        {
                                        name: 'Overfitting',
                                                value: 190
                                        },
                                        {
                                        name: 'JavaScript',
                                                value: 185
                                        },
                                        {
                                        name: 'Text Analytics',
                                                value: 180
                                        },
                                        {
                                        name: 'Cognitive Computing',
                                                value: 175
                                        },
                                        {
                                        name: 'Augmented Intelligence',
                                                value: 160
                                        },
                                        {
                                        name: 'Statistical Models',
                                                value: 155
                                        },
                                        {
                                        name: 'Clustering',
                                                value: 150
                                        },
                                        {
                                        name: 'Topic Modeling',
                                                value: 145
                                        },
                                        {
                                        name: 'Data Mining',
                                                value: 140
                                        },
                                        {
                                        name: 'Data Science',
                                                value: 138
                                        },
                                        {
                                        name: 'Semi-Supervised Learning',
                                                value: 137
                                        },
                                        {
                                        name: 'Artificial Neural Networks',
                                                value: 125
                                        }
                                        ]
                                } ]
                        };
                        chart.setOption(option);
                        window.onresize = chart.resize;
                        // Custom Popover Functionality
                        const popoverBtn = document.getElementById('popoverBtn');
                        const popover = document.getElementById('customPopover');
                        popoverBtn.addEventListener('click', (e) => {
                        e.stopPropagation(); // prevent immediate close
                                popover.style.display = popover.style.display === 'block' ? 'none' : 'block';
                                const rect = popoverBtn.getBoundingClientRect();
                                popover.style.top = rect.bottom + 12 + window.scrollY + 'px';
                                popover.style.left = rect.left - 15 + window.scrollX + 'px';
                        });
                        document.addEventListener('click', (e) => {
                        if (!popover.contains(e.target) && e.target !== popoverBtn) {
                        popover.style.display = 'none';
                        }
                        });
                        // View Toggle Functionality
                        $('.grid-section').hide();
                        $('.custom-toggle-icon .icon').on('click', function() {
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
            </script>
            <script type="text/javascript">
                        let viewer = new Viewer();
                        viewer.initialize();
            </script>
    </body> 
</html>