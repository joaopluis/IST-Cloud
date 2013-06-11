<?php
session_start();
include('../Net/SFTP.php');
$sftp = new Net_SFTP('sigma.ist.utl.pt');
if (!$sftp->login($_SESSION['name'],$_SESSION['pwd'])) {
    exit('Login Failed');
}
$mode = $_GET['mode'];
if($mode == "ln"){
    $orig = explode("/",base64_decode($_GET['orig']));
    $orig="/afs/ist.utl.pt/users/".substr($orig[0], -2, 1)."/".substr($orig[0], -1, 1)."/".base64_decode($_GET['orig']);
    
    $tgt = explode("/",base64_decode($_GET['tgt']));
    $tgt="/afs/ist.utl.pt/users/".substr($tgt[0], -2, 1)."/".substr($tgt[0], -1, 1)."/".base64_decode($_GET['tgt']);
    $sftp->exec('ln -s "'.$orig.'" "'.$tgt.'"'); // == $sftp->nlist('.')
    header('Location:index.php?');
} elseif ($mode == "mkdir"){
    if($_POST['foldername']){
        $path = $_GET['path']."/".$_POST['foldername'];
        $sftp->exec('mkdir "'.$path.'"');
        header('Location: index.php?path='.$path);
    }
} elseif ($mode == "rename"){
    if($_POST['filename'] && $_POST['newname']){
        $sftp->rename($_GET['path'].'/'.$_POST['filename'],$_GET['path'].'/'.$_POST['newname']);
        header('Location: index.php?path='.$_GET['path']);
    }
} elseif ($mode == "remove"){
    if($_POST['filename'] ){
        $sftp->delete($_GET['path'].'/'.$_POST['filename']);
        header('Location: index.php?path='.$_GET['path']);
    }
} elseif ($mode == "removedir"){
    if($_POST['filename'] ){
        $sftp->delete($_GET['path'].'/'.$_POST['filename'], true);
        header('Location: index.php?path='.$_GET['path']);
    }
} elseif ($mode == "perm"){
    
    $usr = $_POST['istid'];
    $path = $_GET['path'];
    $expl = explode("/",$path);
    $last = array_pop($expl);
    $perms = $_POST['editperm'];
    $sftp->exec('fs sa '.$path.' '.$usr.' '.$perms);
    $ds=@ldap_connect("ldaps://ldap.ist.utl.pt");
    if ($ds) {  
        
        ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, 3);     
        ldap_set_option($ds, LDAP_OPT_RESTART, true);   
        
        $r=@ldap_bind($ds); // bind anonimo; para obter mais dados, e' preciso autenticar
    }
    
    
    if($r){
        
        $sr=ldap_search($ds, "ou=People,dc=ist,dc=utl,dc=pt", "uid=".$_SESSION['name'], array("uid", "displayName"));
        
        $entries = ldap_get_entries($ds, $sr);
        $myname = $entries[0]['displayname'][0];
        $mymail = array_pop($entries[0]['uid'])."@ist.utl.pt";
        $sr=ldap_search($ds, "ou=People,dc=ist,dc=utl,dc=pt", "uid=".$usr, array("uid","displayName"));
        
        $entries = ldap_get_entries($ds, $sr);
        $othername = $entries[0]['displayname'][0];
        $othermail = array_pop($entries[0]['uid'])."@ist.utl.pt";
        $to = $othermail;
        $subject = "IST Cloud - Partilha";
        $message = "<html>
<head>
<title>IST Cloud</title>
</head>
<body>
<img src='http://web.ist.utl.pt/joaopluis/cloud/logopreto.png' alt='IST Cloud' />
<p>Olá ".$othername.".</p>";
        if($perms == "none")
            $message .= "<p>O utilizador ".$myname." removeu as permissões que tinhas na pasta <strong>".$last."</strong>. Caso tenhas criado um atalho, talvez o devas remover (por exemplo, utilizando o comando <pre>rm ".$last."</pre>).";
        if($perms == "read" || $perms == "write"){
            $link = "http://web.ist.utl.pt/joaopluis/cloud/ops.php?mode=ln&orig=".urlencode(base64_encode($_SESSION['name']."/".$path))."&tgt=".urlencode(base64_encode($usr."/".$last));
            $message .= "<p>O utilizador ".$myname." deu-te permissões de ".(($perms == "write")?"leitura e escrita":"leitura")." na pasta  <strong>".$last."</strong>.</p>
<p>Se quiseres criar na tua área de trabalho um atalho para esta pasta, inicia sessão na IST Cloud e acede a <a href='".$link."'>".$link."</a> para criar um atalho.</p>";
        }
        $message .= "<p>Esta mensagem foi enviada automaticamente pela IST Cloud.</p></body>
</html>";
        $from = $mymail;
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=utf-8" . "\r\n";
        $headers .= "From:" . $from;
        mail($to,$subject,$message,$headers);
    }
    header('Location:index.php?path='.$path);
}
?>