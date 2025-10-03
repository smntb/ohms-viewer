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
            <link rel="stylesheet" href="css/<?php echo $extraCss ?>?v1.1" type="text/css"/>
        <?php }
        ?>
        <link rel="stylesheet" href="css/jquery-ui.toggleSwitch.css" type="text/css"/>
        <link rel="stylesheet" href="css/jquery-ui-1.8.16.custom.css" type="text/css"/>
        <link rel="stylesheet" href="css/font-awesome.css">
        <link rel="stylesheet" href="css/simplePagination.css">
        <script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>
        <script src="js/jquery-ui.toggleSwitch.js"></script>
        <script src="js/toggleSwitch.js?v1.16"></script>
        <script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.8.18/jquery-ui.min.js"></script>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-ui-multiselect-widget/1.13/jquery.multiselect.css">
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-ui-multiselect-widget/1.13/jquery.multiselect.min.js"></script>
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
                                        <?php if (count($interview->annotations) > 0): ?>
                                            <!-- These will be moved into dropdown via JS -->
                                            <li class="dropdown-tab"><a href="#wordcloud-tab-1" id="wordcloud-tab-1-head">Word Cloud</a></li>
                                            <?php if (count($interview->mapData) > 0): ?>
                                                <li class="dropdown-tab"><a href="#map-tab-1" id="map-tab-1-head">Map</a></li>
                                            <?php endif; ?>
                                            <li class="dropdown-tab"><a href="#timeline-tab-1">Timeline</a></li>
                                            <li class="dropdown-tab"><a href="#browser-tab-1">Browser</a></li>
                                        <?php endif; ?>
                                    </ul>
                                    <div id="about-tab-1">
                                        <div class="about-panel">
                                            <strong>Interview Summary</strong>
                                            <p><?php echo $interview->description; ?></p>
                                            <strong>Interview Accession</strong>
                                            <p><?php echo $interview->accession; ?></p>
                                            <strong>Interviewer Name</strong>
                                            <p><?php echo $interview->interviewer; ?></p>
                                            <strong>Interviewee Name</strong>
                                            <p><?php echo "{$interview->interviewee}"; ?></p>
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
                                                    <input type="checkbox" id="toggle-layers-1" class="toggle-layers" name="toggle-layers">
                                                    <label for="toggle-layers-1" class="toggle-layers-label">View Data Layers</label>
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
                                    <?php
                                    if (count($interview->annotations) > 0):
                                        $tab_tag = '1';
                                        include 'tmpl/visualization.tmpl.php';
                                    endif;
                                    ?>
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
                                    <?php if (count($interview->annotations) > 0): ?>
                                        <!-- These will be moved into dropdown via JS -->
                                        <li class="dropdown-tab"><a href="#wordcloud-tab-2" id="wordcloud-tab-2-head">Word Cloud</a></li>
                                        <?php if (count($interview->mapData) > 0): ?>
                                            <li class="dropdown-tab"><a href="#map-tab-2" id="map-tab-2-head">Map</a></li>
                                        <?php endif; ?>

                                        <li class="dropdown-tab"><a href="#timeline-tab-2">Timeline</a></li>
                                        <li class="dropdown-tab"><a href="#browser-tab-2">Browser</a></li>
                                    <?php endif; ?>
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
                                                <input type="checkbox" id="toggle-layers-2" class="toggle-layers" name="toggle-layers">
                                                <label for="toggle-layers-2" class="toggle-layers-label">View Data Layers</label>
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

                                <?php
                                if (count($interview->annotations) > 0):
                                    $tab_tag = '2';
                                    include 'tmpl/visualization.tmpl.php';
                                endif;
                                ?>
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
            </script>
            <script src="js/visualization.js"></script>

            <script type="text/javascript">
                        let viewer = new Viewer();
                        viewer.initialize();
                        const visualization = new VisualizationJS();
                        visualization.initialize(<?php echo isset($entity_rows) ? json_encode($entity_rows, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) : '[]'; ?>, <?php echo count($interview->mapData) > 0 ? json_encode($interview->mapData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : '[]'; ?>);
            </script>

    </body> 
</html>