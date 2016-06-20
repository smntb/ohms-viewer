<?php
//Set style values for Youtube player and page based on file format
if ($cacheFile->clip_format == 'audio') {
    $height = "180";
    $width  =  "450";
    $styleheight = "300";
} else {
    $height = "270";
    $width  =  "500";
    $styleheight = "415";
}

        $player_id = $cacheFile->player_id;
        $publisher_id = $cacheFile->account_id;
        $youtubeId = str_replace('http://youtu.be/', '', $cacheFile->media_url);
        $extraScript = '';
        if(isset($_GET['time']) && is_numeric($_GET['time']))
        {
            $extraScript = 'event.target.seekTo(' . (int)$_GET['time'] . ');';
        }
echo <<<YOUTUBE
            <div id="youtubePlayer" style="width: 500px; height: {$height}px;"></div>
            
          <div class="video-spacer"></div>


            <script type="text/javascript">
                var tag = document.createElement('script');
                tag.src = "https://www.youtube.com/iframe_api";
                var firstScriptTag = document.getElementsByTagName('script')[0];
                firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);
                
                var player;
                var setTime = 0;
                function onYouTubeIframeAPIReady() {
                    player = new YT.Player('youtubePlayer', {
                        height: '270',
                        width: '480',
                        videoId: '{$youtubeId}',
                        startAt: setTime,
                        events: {
                            onReady: onPlayerReady
                        }
                    });
                    
                    function onPlayerReady(event)
                    {
                        event.target.playVideo();
                        {$extraScript}
                    }
                }
            </script>
YOUTUBE;
?>
