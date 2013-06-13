<?php
include_once("functions.php");
$file = $_GET['file'];
unset($_GET['file']);
$name = filehash(basename($file), time());
$audio = true;
include("download.php");
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8" />
        <link rel="stylesheet" href="style.css" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
        <script src="http://cdn.jquerytools.org/1.2.7/full/jquery.tools.min.js"></script>
        <script src="http://code.jquery.com/ui/1.10.3/jquery-ui.js"></script>
        <link rel="shortcut icon" href="images/favicon.png" type="image/x-icon" />
        <link rel="apple-touch-icon-precomposed" href="images/touchicon.png">
        <link rel="stylesheet" href="http://code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css" />
        <title>IST Cloud</title>
    </head>
    <body class="audio">
        <div id="audioContainer">
            <audio id="audioPlayer" autoplay>
                <source src="istcloud_files/audio/<?php echo $name; ?>" type="audio/mpeg">
                Your browser does not support the audio element.
            </audio>
            <img src="images/touchicon.png" />
            <div class="topControls">
                <i id="audioPlay" class="icon-play"></i> <i id="audioPause" class="icon-pause"></i><span><?php echo substr(basename($file),0,-4); ?></span>
            </div>
            <div class="bottomControls">
                <i id="audioStop" class="icon-stop"></i> <i id="audioUnmute" class="icon-volume-high"></i> <i id="audioMute" class="icon-volume-mute"></i>
                <div id="timeRange">
                    <div id="slider-progress"></div>
                    <div id="slider-bubble"></div>
                    <div id='slider-handle' class="ui-slider-handle"></div>
                </div>
            </div>
            
        </div>
        <script>
            function pad (str, max) {
                return str.length < max ? pad("0" + str, max) : str;
            }
            function deletefile(){
                $.ajax({
                    type: "POST",
                    url: "ops.php?mode=unlink",
                    async : false,
                    data: { path: "audio/<?php echo $name; ?>" }
                });
            }
            window.onbeforeunload = deletefile;
            window.onunload = deletefile;
            $( document ).ready(function(){
                var sliding = false;
                var audio = document.getElementById('audioPlayer');
                $("#audioPlay").live('click', function(e) {
                    e.preventDefault();
                    audio.play();
                    $(this).hide();
                    $("#audioPause").show();
                });
                $("#audioPause").live('click', function(e) {
                    e.preventDefault();
                    audio.pause();
                    $(this).hide();
                    $("#audioPlay").show();
                });
                $("#audioStop").live('click', function(e) {
                    e.preventDefault();
                    audio.pause();
                    audio.currentTime = 0;
                    $("#audioPause").hide();
                    $("#audioPlay").show();
                    
                });
                $("#audioMute").live('click', function(e) {
                    e.preventDefault();
                    audio.muted = true;
                    $(this).hide();
                    $("#audioUnmute").show();
                });
                
                $("#audioUnmute").live('click', function(e) {
                    e.preventDefault();
                    audio.muted = false;
                    $(this).hide();
                    $("#audioMute").show();
                });
                $('#slider-bubble').hide();
                console.log(audio.duration);
                $('#timeRange').slider({
                    animate: "fast",
                    handle: '#slider-handle',
                    min: 0,
                    start: function(e,ui){
                        $('#slider-bubble').fadeIn('fast');
                        sliding = true;
                    },
                    stop: function(e,ui){
                        $('#slider-bubble').fadeOut('fast');
                        var width = (ui.value/$('#timeRange').slider( "option", "max" ))*100;
                        console.log(width);
                        $("#slider-progress").animate({width: width + '%'}, "fast");
                        audio.currentTime = ui.value;
                        sliding = false;
                    },
                    slide: function(e,ui){
                        // $('#box').css('-moz-opacity', ui.value/100.00).text(ui.value);
                        //mypos = $('#slider-handle').position().left + 2;  //grab position of slider dot + 2
                        var width = (ui.value/$('#timeRange').slider( "option", "max" ))*100;
                        console.log(ui.value);
                        var mins = Math.floor(ui.value/60);
                        var segs = ui.value%60;
                        $('#slider-bubble').css('left', width+'%').text(mins+":"+pad(segs.toString(),2));
                        $("#slider-handle").css('left', width+'%');
                    },
                    change: function(e,ui) {
                        var width = (ui.value/$('#timeRange').slider( "option", "max" ))*100;
                        $("#slider-progress").css('width', width + '%');
                    }
                });
                
                audio.addEventListener('loadedmetadata', function() {
                    $( "#timeRange" ).slider( "option", "max", Math.ceil(audio.duration));
                });
                audio.addEventListener('timeupdate',function (){
                    if(!sliding){
                        $('#timeRange').slider( "value", audio.currentTime );
                    }
                });
            });
        </script>
    </body>
    
</html>