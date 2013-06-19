<?php
session_start();
ini_set('display_errors', '0');
include('../Net/SFTP.php');
$sftp = new Net_SFTP('sigma.ist.utl.pt');
if(array_key_exists("logout",$_GET)){
    session_unset();
    session_destroy();
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8" />
        <link rel="stylesheet" href="style.css" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
        <script src="http://cdn.jquerytools.org/1.2.7/full/jquery.tools.min.js"></script>
        <link rel="shortcut icon" href="images/favicon.png" type="image/x-icon" />
        <link rel="apple-touch-icon-precomposed" href="images/touchicon.png">
        <title>IST Cloud</title>
    </head>
    
    <?php
    include 'functions.php';
    if($_POST['password']){ 
        if($sftp->login("ist173292", $_POST['password'])){
        //LOG IN
        $_SESSION['name'] = "ist173292";
        $_SESSION['pwd'] = $_POST['password'];
        $sftp = new Net_SFTP('sigma.ist.utl.pt');
        } else {
            $warning = "Dados incorretos.";
        }
    }
    if($sftp->login($_SESSION['name'], $_SESSION['pwd'])){ 
        include 'main.php';
    } else { 
        include 'login.php';
    } ?>
</html>