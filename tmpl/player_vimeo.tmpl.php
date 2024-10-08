<?php

$clipid = $interview->clip_id;
if ($interview->kembed == "" && $interview->media_url != "") {
    $height = ($interview->clip_format == 'audio' ? 95 : 350);
    $video_id = str_replace('https://vimeo.com/', '', str_replace('http://vimeo.com/', '', $interview->media_url));
    $embedcode = '<iframe referrerpolicy="strict-origin" id="vimeo_widget" src="https://player.vimeo.com/video/' . $video_id . '?color=ffffff&badge=0&portrait=false&title=false&byline=false" width="100%" maxwidth="100%" height="' . $height . '" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>';
} elseif ($interview->kembed != "") {
        $embedcode = preg_replace('/\<[\/]{0,1}div[^\>]*\>/i', '', $interview->kembed);
        $embedcode = preg_replace('/(width|height)=["\']\d*["\']\s?/', "", $embedcode);
        $embedcode = str_replace('<iframe ', '<iframe referrerpolicy="strict-origin" title="Video Player" id="vimeo_widget"', $embedcode);
}

if (isset($_GET['time']) && is_numeric($_GET['time'])) {
    $playScript = 'widget.play();';
    $extraScript = 'widget.setCurrentTime(' . ($_GET['time']) . ');';
} else {
    $playScript = '';
    $extraScript = '';
}
$height = ($interview->clip_format == 'audio' ? 95 : 350);

echo <<<VIMEO
<script src="https://player.vimeo.com/api/player.js"></script>
    <div class="video embed-responsive embed-responsive-16by9" style="position:relative;width: auto; height: {$height}px;margin-left: auto; margin-right: auto;">
  <p>&nbsp;</p>
  {$embedcode}
  <script>
var widget = null;
jQuery(document).ready(function () {
  widget = new Vimeo.Player(document.getElementById('vimeo_widget'));
  widget.on('ready', function(event) {
  {$playScript}
  {$extraScript}
});
if (exhibitMode){ 
widget.on('timeupdate', function(data) {
    if (data.seconds > endAt && endAt != null){
        widget.pause();
        endAt = null;
        exhibitIndex.trigger('click');
        endAt = null;
        exhibitIndex = null;
    }
    
  });
}
});


</script>
</div>
VIMEO;
