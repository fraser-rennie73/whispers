<?php

// Include fb configuration file
require_once 'Scripts/config.php';

// Start session
if(!session_id()){
    session_start();
}

/*
unset($_SESSION['facebook_access_token']);
unset($_SESSION['player']);
unset($_SESSION['player-id']);
unset($_SESSION['continue-as-a-guest']);

setcookie( 'zoomthulhu-player-id', "", time() - 3600, "/" );
*/

require 'Scripts/dba.php';
require 'Scripts/category.php';
require 'Scripts/article.php';
require 'Scripts/player.php';
require 'Scripts/comment.php';
require 'Scripts/permission.php';
require 'Scripts/buildPage.php';
require 'Scripts/sumariser.php';

//$fbObject = fbInitialise();
//$page = BuildPage( $fbObject );
$page = BuildPage( );

?>

<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="x-ua-compatible" content="IE-edge" />
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width,initial-scale=1.0">
  <meta property="og:title" content="Whispers from the Void">
  <meta property="og:description" content="Check out this content">
  <meta property="og:image" content="https://fraser.epizy.com/php/Images/banner.jpg">
  <meta property="og:url" content="https://fraser.epizy.com/php/Index.php">
	<meta property="og:image:width" content="1000">
	<meta property="og:image:height" content="848">
	<title><?php echo $page->PageTitle; ?></title>
	<link rel="icon" type="image/png" href="Images/favicon.png"/>
	<link href='https://fonts.googleapis.com/css?family=Poiret+One' rel='stylesheet' type='text/css'>
	<link href='https://fonts.googleapis.com/css?family=Limelight' rel='stylesheet' type='text/css'>
	<link href='https://fonts.googleapis.com/css?family=Shadows+Into+Light+Two' rel='stylesheet' type='text/css'>
	<link href='https://fonts.googleapis.com/css?family=Special+Elite' rel='stylesheet' type='text/css'>
	<link href='https://fonts.googleapis.com/css?family=Homemade+Apple' rel='stylesheet' type='text/css'>
	<link href='https://fonts.googleapis.com/css?family=Great+Vibes' rel='stylesheet' type='text/css'>
	<link href='https://fonts.googleapis.com/css?family=Smythe' rel='stylesheet' type='text/css'>
	<link href="https://fonts.googleapis.com/css2?family=UnifrakturCook:wght@700&display=swap" rel="stylesheet">
	<link href="https://fonts.googleapis.com/css?family=Jim+Nightshade" rel="stylesheet" type="text/css">
	<link href="Styles/attachments.css?ver=0536" rel="stylesheet" type="text/css" media="screen"/>
	<?php 
		echo $page->ScriptsHtml(); 
		echo $page->StylesHtml(); 
	?>
</head>
<body class="<?php echo $page->PageClass; ?>" >
	<div id="background"></div>
	<div id="holder">
	<?php echo $page->Content; ?>
	</div>
</body>
</html>