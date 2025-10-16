<?php
$lang = $interview->transcript_alt_lang;
if (isset($_GET['panel'])) {
    $panel = $_GET['panel'];
}
$transcript_option = 'selected="selected"';
$toggleSwitch = '';
$index_option = '';
if ((isset($panel) && $panel == '1') || ($interview->hasIndex() && (!isset($panel) || $panel != '0'))) {
    $transcript_option = '';
    $index_option = 'selected="selected"';
    $toggleSwitch = 'checked="checked"';
}
$isTranslate = "0";
if (isset($_GET['translate']) && $_GET['translate'] == '1') {
    $targetLanguage = $interview->language;
    $isTranslate = "1";
} else {
    $targetLanguage = $interview->transcript_alt_lang;
}
$tAvailable = "";
if (isset($_GET['t_available']) && $_GET['t_available'] == '1') {
    $tAvailable = "1";
}

$toggleClass = "non-greyed-out";
$toggleAvailable = "";
$toggleAttr = "";
$toggleLanguageSwitch = "";

if ((($interview->index == '' && ($interview->transcript != '' || strpos($interview->transcript, 'No transcript') === false)) || ($interview->index != '' && ($interview->transcript == '' || strpos($interview->transcript, 'No transcript') !== false))) && $isTranslate == "0" || $tAvailable == "1") {
    $toggleClass = "greyed_out";
    $toggleAttr = 'disabled="diabled"';
    $toggleAvailable = "hide";
    $toggleLanguageSwitch = "translate-toggle-preview";
}

if ($interview->hasIndex()) {
    $searchThisLabel = 'Index';
} else {
    $searchThisLabel = 'Transcript';
}
$toggleDisplay = "";
//if (!$interview->transcript)
//    $toggleDisplay = "display:none;";
?>

<input type="hidden" id="is_translate" value="<?php echo $isTranslate; ?>">
<!-- <div id="search-toggle"  class="<?php echo $toggleAvailable; ?>" >
    <span class="toggle-txt-info">Transcript</span>

    <div class="switch" style="<?php echo $toggleDisplay; ?>">
        <div style="display:none;">Toggle Index/Transcript View Switch.</div>
        <input class="switch-input" type="checkbox" title="Toggle Display Switch" id="toggle_switch" name="toggle_switch" <?php echo $toggleSwitch;
echo $toggleAttr; ?>>
        <label for="toggle_switch" class="switch-label">Toggle Index/Transcript View Switch.</label>
    </div>
    <span class="toggle-txt-info">Index</span>
    <label for="search-type" style="display:none;">Search Type</label>
    <select id="search-type" title="Search Type" style="display: none;">
        <option id="search-transcript" value="0" <?php echo $transcript_option ?>>Transcript</option>
        <option id="search-index" value="1" <?php echo $index_option ?>>Index</option>
    </select>
    <button id="print-pdf" title="Print" class="print-btn"><i class="fa fa-print"></i> Print</button>
</div> -->

<span id="alert"></span>
<form id="search-form" onSubmit="return false;" name="search-form" class="preview-search-form">
    <fieldset>
        <div class="search-content">
            <label for="kw" style="display:none;">Search keyword field</label>
            <input class="kw-empty" title="Search keyword field" id="kw" name="kw" size="30" value="Keyword" placeholder="Search this Interview" />
            <button class="search-button" id="submit-btn">Go</button>
            <a href="#" class="searchclear-button" id="clear-btn">Clear search term X</a>
            <div id="accordionHolderSearch">
                <h3>
                    <span class="toggle-span">Index</span>
                    <div id="paginate" class="compact-theme simple-pagination">
                        <!-- <ul>
                            <li class="disabled">
                                <span class="current prev"><img src="/imgs/arrow-square.webp" alt="Previous" /></span>
                            </li>
                            <li>
                                <span id="paginate_info" class="search_paginate_info">Showing 1 - 5 of 8</span>
                            </li>
                            <li>
                                <a href="#page-2" class="page-link next"><img src="/imgs/arrow-square.webp" alt="Next" /></a>
                            </li>
                        </ul> -->
                    </div>
                    <span id="paginate_info" class="search_paginate_info"></span>
                </h3>
                <div>
                    <div id="search-results"></div>
                </div>
<!--                <h3>
                    <span class="toggle-span">Transcript</span>
                    <div id="paginate" class="compact-theme simple-pagination">
                        <ul>
                            <li class="disabled">
                                <span class="current prev"><img src="/imgs/arrow-square.webp" alt="Previous" /></span>
                            </li>
                            <li>
                                <span id="paginate_info">Showing 1 - 5 of 8</span>
                            </li>
                            <li>
                                <a href="#page-2" class="page-link next"><img src="/imgs/arrow-square.webp" alt="Next" /></a>
                            </li>
                        </ul>
                    </div>
                </h3>
                <div>
                    <div id="search-results"></div>
                </div>-->
                
            </div> 
        </div>
    </fieldset>
</form>