<?php
// Start session
if(!session_id()){
    session_start();
}

require 'Scripts/dba.php';
require 'Scripts/category.php';
require 'Scripts/article.php';
require 'Scripts/comment.php';
require 'Scripts/linker2.php';

$page = BuildList();
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
	<title><?php echo $page->PageTitle; ?></title>
	<link rel="icon" type="image/png" href="Images/favicon.png"/>
</head>
<body>
	<div id="holder">
	<?php echo $page; ?>
	</div>
</body>
</html>