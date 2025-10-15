<?php

$clipid = $interview->clip_id;
if ($interview->kembed == "" && $interview->media_url != "") {
    // $height = ($interview->clip_format == 'audio' ? 95 : 350);
    $video_id = str_replace('https://vimeo.com/', '', str_replace('http://vimeo.com/', '', $interview->media_url));
    $embedcode = '<iframe referrerpolicy="strict-origin" id="vimeo_widget" src="https://player.vimeo.com/video/' . $video_id . '?color=ffffff&badge=0&portrait=false&title=false&byline=false" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>';
} elseif ($interview->kembed != "") {
        $embedcode = preg_replace('/\<[\/]{0,1}div[^\>]*\>/i', '', $interview->kembed);
        $embedcode = preg_replace('/(width|height)=["\']\d*["\']\s?/', "", $embedcode);
        $embedcode = str_replace('<iframe ', '<iframe allow="autoplay; fullscreen; picture-in-picture" referrerpolicy="strict-origin" title="Video Player" id="vimeo_widget"', $embedcode);
}

if (isset($_GET['time']) && is_numeric($_GET['time'])) {
    $playScript = 'widget.play();';
    $extraScript = 'widget.setCurrentTime(' . ($_GET['time']) . ');';
} else {
    $playScript = '';
    $extraScript = '';
}
// $height = ($interview->clip_format == 'audio' ? 95 : 310);

echo <<<VIMEO
<style>
.responsive-video {
  position: relative;
  padding-bottom: 56.25%; /* 16:9 ratio */
  height: 0;
  overflow: hidden;
  max-width: 100%;
  background: #000;
}
.responsive-video iframe {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
}
</style>
<script src="https://player.vimeo.com/api/player.js"></script>
    <div class="video responsive-video">
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
