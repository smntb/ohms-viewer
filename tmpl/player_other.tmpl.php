<?php
$fileserver = (isset($config['fileserver']) ? $config['fileserver'] : '');

$filepath = $interview->media_url;
if (strpos($filepath, 'http://') !== false || strpos($filepath, 'https://') !== false):
    $linkToMedia = $filepath;
else:
    $linkToMedia = 'http://' . $fileserver . $interview->file_name;
endif;

$validClipFormats = array('audio', 'audiotrans', 'video');
$clipFormat = $interview->clip_format;
if (strtolower($interview->clipsource) == "aviary"):
    $mediaFormat = $clipFormat == 'video' ? 'mp4' : 'mp3';
else:
    $mediaFormat = substr($linkToMedia, -3, 3);
endif;

if ($mediaFormat == 'mpga'):
    $mediaFormat = "mp3";
endif;

if ($clipFormat == 'audio'):
    ?>
    <div class="audio-player">
        <audio id="my_player" controls
               preload="auto"  class="audio video-js">
            <source src="<?php echo $linkToMedia ?>" type="audio/<?php echo $mediaFormat ?>" />
        </audio>
    </div>
<?php else:
    ?>  
    <div class="video-player">
        <video id="my_player" controls
               preload="auto"  class="video-js">
            <source src="<?php echo $linkToMedia ?>" type="video/<?php echo $mediaFormat ?>" />
        </video>
    </div>
<?php endif;
