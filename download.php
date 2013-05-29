<?php
session_start();
ini_set('display_errors', '0');

$file = $_GET['file'];

include('../Net/SFTP.php');

$sftp = new Net_SFTP('sigma.ist.utl.pt');
if (!$sftp->login($_SESSION['name'], $_SESSION['pwd'])) {
    exit('Login Failed');
}

$sftp->get($file, 'istcloud_files/'.basename($file));
header("Cache-Control: public");
header("Content-Description: File Transfer");
header("Content-Disposition: attachment; filename=".basename($file));
header("Content-Type: application/zip");
header("Content-Transfer-Encoding: binary");

// read the file from disk
readfile('istcloud_files/'.basename($file));
unlink('istcloud_files/'.basename($file));

?>