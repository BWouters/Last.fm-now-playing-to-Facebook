<?php
$api_key = 'YOUR_LASTFM_APP_KEY';
$url = 'YOUR_REDIRECT_URL';
header('Location: https://www.facebook.com/dialog/oauth?client_id='.$api_key.'&redirect_uri='.{$url}. '&scope=publish_stream');
?>

