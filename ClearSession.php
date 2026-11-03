<?php

// Start session
if(!session_id()){
    session_start();
}

unset($_SESSION['facebook_access_token']);
unset($_SESSION['player']);
unset($_SESSION['player-id']);
unset($_SESSION['continue-as-a-guest']);

setcookie( 'zoomthulhu-player-id', "", time() - 3600, "/" );

header("Location: https://fraser.epizy.com/php/Index.php");

?>
