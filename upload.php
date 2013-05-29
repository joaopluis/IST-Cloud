<?php
session_start();
ini_set('display_errors', '0');

include('../Net/SFTP.php');
$sftp = new Net_SFTP('sigma.ist.utl.pt');
if($sftp->login($_SESSION['name'], $_SESSION['pwd'])){
    
    
    foreach($_FILES['files']['tmp_name'] as $key => $tmpname){
        $sftp->put($_GET['path']."/".$_FILES['files']['name'][$key], $tmpname, NET_SFTP_LOCAL_FILE);
        
    }
    header('Location:index.php?path='.$_GET['path']);
    
}
?>