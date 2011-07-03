<?php

require 'facebook.php';
require 'fbmain.php';
require_once 'lastfmapi/lastfmapi.php';
define('YOUR_APP_ID', 'YOURAPPID');
define('YOUR_APP_SECRET', 'YOURAPPSECRETHERE');
if (isset($_POST['tt'])){
	if(isset($_GET['url']) && isset($_GET['img'])){
		$trackurl = $_GET['url'];
		$trackimg = $_GET['img'];
		$artist = $_POST['artist'];
		$track = $_POST['title'];
		
		$attachment =  array(
			'message' => $_POST['tt'],
			'name' => "Title of the url",
			'link' => "URL-for-your-app-on-facebook",
			'caption' => $artist,
			'description' => $track,
			'picture'=>$trackimg,
			'actions' => json_encode(array('name' => $track,'link' => $trackurl))
		);
		// set the target url
		try {
			$statusUpdate = $facebook->api('/me/feed', 'post', $attachment);
			echo $trackurl." - <img src='".$trackimg."' alt='image album' />";	
		} catch (FacebookApiException $e) {
			d($e);
		}
	}
	
	if (isset($statusUpdate)) { ?>
		<br />
		<b style="color: red">Status Updated Successfully! Status id: <?php echo $statusUpdate['id']?></b>
	<?php 
	} ?>
	</div>
<body></body>
<?php 
}elseif(isset($_GET['token'])){
	$token = $_GET['token'];
	$vars = array(
	'apiKey' => 'last-fm-api-key',
	'secret' => 'last-fm-api-key-secret',
	'token' => $token
	);
	$lastfm = true;
}else{
	header('Location: http://www.last.fm/api/auth/?api_key=last-fm-api-key');
	$lastfm = false;
}


if($lastfm){
	setcookie("lastFMtoken", $vars['token'], time()+30*3600);
	$auth = new lastfmApiAuth('getsession', $vars);
	$apiClass = new lastfmApi();
	$userPackage = $apiClass->getPackage($auth, 'user');
	$trackPackage = $apiClass->getPackage($auth, 'track');
	$userVars = array(
	'user' => $auth->username
	);

	$trackVars = array(
	'user' => $auth->username
	);
	
	if ( $users = $userPackage->getRecentTracks($userVars) ) {
		// Method call is a success. Process the array returned here
		$lasttrack = $users[0]['artist']['name']." - ".$users[0]['name'];
		$trackVars = array(
			'artist' => $users[0]['artist']['name'],
			'track' => $users[0]['name']);
	}
	else {
		// Error: show which error and go no further.
		echo '<b>UserError '.$userPackage->error['code'].' - </b><i>'.$userPackage->error['desc'].'</i>';
	}

	if ( $trackinfo = $trackPackage->getInfo($trackVars) ) {
		// Method call is a success. Process the array returned here
		$trackurl = $trackinfo['url'];
		$trackimage = $trackinfo['album']['image']['small'];
	}
	else {
		// Error: show which error and go no further.
		echo '<b>Error '.$trackPackage->error['code'].' - </b><i>'.$trackPackage->error['desc'].'</i>';
	}
	

	$posturl = "callback.php?img=". $trackimage ."&amp;url=". $trackurl;
	?>
	<!DOCTYPE html>
	<html xmlns="http://www.w3.org/1999/xhtml" xmlns:fb="http://www.facebook.com/2008/fbml">
    	<head>
        	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
			<title>Visions and Views - Last.fm and Facebook</title>
			<link rel="stylesheet" href="css/style.css" type="text/css" />
		</head>
	<body>
    <div id="fb-root"></div>
        <script type="text/javascript">
            window.fbAsyncInit = function() {
                FB.init({appId: '<?=YOUR_APP_ID?>', status: true, cookie: true, xfbml: true});
 
                /* All the events registered */
                FB.Event.subscribe('auth.login', function(response) {
                    // do something with response
                    login();
                });
                FB.Event.subscribe('auth.logout', function(response) {
                    // do something with response
                    logout();
                });
            };
            (function() {
                var e = document.createElement('script');
                e.type = 'text/javascript';
                e.src = document.location.protocol +
                    '//connect.facebook.net/en_US/all.js';
                e.async = true;
                document.getElementById('fb-root').appendChild(e);
            }());
 
            function login(){
                document.location.href = "YOUR_LOGIN_URL";
            }
            function logout(){
                document.location.href = "YOUR_LOGIN_URL";
            }
		</script>
			<div id="header">
				<h1>Announce current song in Facebook with Last.fm</h1>
				
				<h2>Welcome <?php echo $fbme['name']?></h2>
			</div>
			<div id="box">
				<form name="fb_status" action="<?php echo $posturl ?>;" method="post">
				<label for="artist">Artist: </label>
				<input type="text" value="<?php echo $trackVars['artist']?>" name="artist" />
				<label for="title">Title: </label>
				<input type="text" value="<?php echo $trackVars['track'] ?>" name="title" />
				<label for="tt" class="status">Status: </label>
				<textarea id="tt" name="tt" cols="50" rows="5">Facebookstatus</textarea></label> 
				<input type="submit" value="Update My Status" />
			</div>
		</body>
	</html>
	<?php }?>