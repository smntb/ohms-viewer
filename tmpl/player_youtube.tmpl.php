<?php
function getYouTubeId($url) {
    // Match different YouTube URL formats
    $pattern = '%(?:youtube\.com/(?:[^/]+/.+/|(?:v|embed|shorts)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i';
    if (preg_match($pattern, $url, $matches)) {
        return $matches[1]; // Return the video ID
    }
    return false; // No match found
}
$player_id = $interview->player_id;
$publisher_id = $interview->account_id;
$youtubeId = "";
if ($interview->media_url != "") {
    $youtubeId = getYouTubeId($interview->media_url);
    
} else {
    $kembed = explode(" ", $interview->kembed);
    foreach ($kembed as $k) {

        if (strpos($k, "src=") !== false) {
            $chr_map = array(
                'src' => "",
                '=' => "",
                '"' => "",
                "'" => "",
                'https://www.youtube.com/embed/' => "",
                'http://www.youtube.com/embed/' => ""
            );
            $youtubeId = str_replace(array_keys($chr_map), array_values($chr_map), $k);
            break;
        }
    }
}

$extraScript = '';
if (isset($_GET['time']) && is_numeric($_GET['time'])) {
    $extraScript = 'event.target.seekTo(' . (int)$_GET['time'] . ');';
}

echo <<<YOUTUBE
<div id="youtubePlayer"></div>
<div class="video-spacer"></div>

<script type="text/javascript">
  // Load YT API
  (function(){
    var tag = document.createElement('script');
    tag.src = "https://www.youtube.com/iframe_api";
    var firstScriptTag = document.getElementsByTagName('script')[0];
    firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);
  })();

  // Mobile detection (kept as-is)
  var isMobile = {
    Android: () => /Android/i.test(navigator.userAgent),
    BlackBerry: () => /BlackBerry/i.test(navigator.userAgent),
    iOS: () => /iPhone|iPad|iPod/i.test(navigator.userAgent),
    Opera: () => /Opera Mini/i.test(navigator.userAgent),
    Windows: () => /IEMobile|WPDesktop/i.test(navigator.userAgent),
    any: function(){ return this.Android() || this.BlackBerry() || this.iOS() || this.Opera() || this.Windows(); }
  };

  var player;
  var setTime = 0;
  var videotime = 0;
  var timeupdater = null;

  // Optional globals you use elsewhere
  // var endAt = null;
  // var exhibitMode = false;
  // var exhibitIndex = $('.some-selector');

  function onYouTubeIframeAPIReady() {
    // Compute size (fixes your earlier width override)
    var screenSize = $('body').width();
    var padding = 30;
    var width = 500;
    var height = 280;

    if (screenSize < 530) {
      width = screenSize - padding;
      height = (screenSize - padding) * 0.56; // ~16:9
    }

    player = new YT.Player('youtubePlayer', {
      height: height,
      width: width,
      videoId: '{$youtubeId}',
      startAt: setTime,
      playerVars: {
        playsinline: 1,
        rel: 0,
        origin: location.origin // good practice when using IFrame API
      },
      events: {
        onReady: onPlayerReady,
        onStateChange: onPlayerStateChange
      }
    });

    function onPlayerReady(event) {
      // Add the permissions to the iframe YouTube created
      var iframe = event.target.getIframe();
      iframe.setAttribute('allow', 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture');
      iframe.setAttribute('allowfullscreen', ''); // enables fullscreen button

      // If you want sound, you can unmute after user gesture (click/tap) later.
      if (!isMobile.any()) {
        try { event.target.playVideo(); 
            $extraScript
            } catch(e) {}
      }

      // If you dynamically change size later, you can also do:
      // $(window).on('resize', function(){
      //   var w = $('body').width();
      //   var pad = 30;
      //   var newW = w < 530 ? (w - pad) : 500;
      //   var newH = w < 530 ? ((w - pad) * 0.56) : 280;
      //   player.setSize(newW, newH);
      // });
    }

    function onPlayerStateChange(event) {
      if (event.data === YT.PlayerState.PLAYING) {
        if (typeof endAt !== 'undefined' && endAt != null && typeof exhibitMode !== 'undefined' && exhibitMode) {
          function updateTime() {
            var oldTime = videotime;
            if (player && player.getCurrentTime) {
              videotime = player.getCurrentTime();
            }
            if (videotime !== oldTime) {
              onProgress(videotime);
            }
          }
          if (timeupdater) clearInterval(timeupdater);
          timeupdater = setInterval(updateTime, 500);
        }
      }
    }
  }

  function onProgress(currentTime) {
    if (typeof endAt !== 'undefined' && endAt != null && currentTime > endAt) {
      player.pauseVideo();
      if (timeupdater) clearInterval(timeupdater);
      if (typeof exhibitIndex !== 'undefined' && exhibitIndex && exhibitIndex.trigger) {
        exhibitIndex.trigger('click');
      }
      endAt = null;
      exhibitIndex = null;
    }
  }
</script>
YOUTUBE;
