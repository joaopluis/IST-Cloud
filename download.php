<?php
session_start();
ini_set('display_errors', '0');

if(!$file)
    $file = $_GET['file'];

include('../Net/SFTP.php');
include_once('functions.php');

$sftp = new Net_SFTP('sigma.ist.utl.pt');
if (!$sftp->login($_SESSION['name'], $_SESSION['pwd'])) {
    exit('Login Failed');
}

if(isset($_GET['file'])){
    $fullname = 'istcloud_files/'.basename($file);
}
if($name){
    $dir='';
    if($audio)
        $dir = 'audio/';
    $fullname = 'istcloud_files/'.$dir.$name;
}



$sftp->get($file, $fullname);

if(isset($_GET['file'])){
    header("Cache-Control: public");
    header("Content-Description: File Transfer");
    header("Content-Disposition: attachment; filename=".basename($file));
    header("Content-Type: application/zip");
    header("Content-Transfer-Encoding: binary");
    readfile($fullname);
    unlink($fullname);
}


?>