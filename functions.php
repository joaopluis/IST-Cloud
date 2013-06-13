<?php
function name($istid){
    global $ds, $r;
    if($r){
        
        $campos = array("displayName");
        $sr=ldap_search($ds, "ou=People,dc=ist,dc=utl,dc=pt", "uid=$istid", $campos);
        
        $entries = ldap_get_entries($ds, $sr);
        if($entries)
            return $entries[0]["displayname"][0];
    }
    return $istid;
}

function flname($istid){
    $name = name($istid);
    if(substr($name, 0, 3) == "ist")
        return $name;
    $name = explode(" ",$name);
    return $name[0]." ".array_pop($name);
}

function size($bytes){
    $kb = $bytes/1024;
    if($kb < 1024)
        return round($kb,2)."K";
    else return round($kb/1024,2)."M";
}

function filehash($file, $hash){
    $name = $hash.$file;
    return md5($name);
}
?>