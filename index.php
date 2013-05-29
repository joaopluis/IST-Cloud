<?php
session_start();
ini_set('display_errors', '0');
include('../Net/SFTP.php');
$sftp = new Net_SFTP('sigma.ist.utl.pt');
if(array_key_exists("logout",$_GET)){
    session_unset();
    session_destroy();
}
$man = false;
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8" />
        <link rel="stylesheet" href="style.css" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
        <script src="http://cdn.jquerytools.org/1.2.7/full/jquery.tools.min.js"></script>
        <title>IST Cloud</title>
    </head>
    <body>
        <?php
if($man){
        ?>
        <div id="loginlogo"><img src="logopreto.png" /></div>
        <h1>Em Manutenção.</h1>
        <?php } else {
    if($_POST['username'] && $sftp->login($_POST['username'], $_POST['password'])){
        $_SESSION['name'] = $_POST['username'];
        $_SESSION['pwd'] = $_POST['password'];
        $sftp = new Net_SFTP('sigma.ist.utl.pt');
    }
    if($sftp->login($_SESSION['name'], $_SESSION['pwd'])){ 
        if($_GET['opt'] == 1) $_SESSION['hidden'] = !$_SESSION['hidden'] ;
        $ds=@ldap_connect("ldaps://ldap.ist.utl.pt");
        if ($ds) {  
            
            ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, 3);     
            ldap_set_option($ds, LDAP_OPT_RESTART, true);   
            
            $r=@ldap_bind($ds); // bind anonimo; para obter mais dados, e' preciso autenticar
        }
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
        $path = '.';
        if($_GET['path'])
            $path = $_GET['path'];
        if(strpos($path,"..") !== false)
            $path = '.';
        $sftp->chdir($path);
        ?> 
        <div id="sidebar">
            <div id="logo"></div>
            <p class="username"><span>Sessão iniciada como:</span><?php echo name($_SESSION['name']); ?></p>
            <?php
        $write = false;
        $perms = explode("\n",$sftp->exec('fs la '.$path));
        if(count($perms) > 3){
            ?>
            <div class="permissions">
                <h3>Permissões</h3>
                <ul>
                    <?php
            for($i = 2; $i < count($perms); $i++){
                $perm = explode(" ",trim($perms[$i]));
                if(substr($perm[0],0,3) == "ist"){
                    echo "<li>";
                    echo flname($perm[0]);
                    echo " <span>";
                    if(strpos($perm[1],"idw") !== false) echo " <i class='icon-pencil'></i>";
                    elseif(strpos($perm[1],"rl") !== false) echo "<i class='icon-eye'></i>";
                    echo "</span>";
                    echo "</li>";
                    if($perm[0] == $_SESSION['name'] && strpos($perm[1],"idw") !== false) $write = true;
                }
            }
                    ?>
                </ul>
                
                <a href="#" class="botao" rel="#perms"><i class="icon-pencil"></i> Editar Permissões</a>
                <!--<p class="legenda"><strong>a</strong> admin &middot; <strong>d</strong> remover &middot; <strong>i</strong> inserir<br /><strong>k</strong> bloquear &middot; <strong>l</strong> listar &middot; <strong>r</strong> leitura &middot; <strong>w</strong> escrita</p>-->
                <p class="legenda"><i class="icon-eye"></i> leitura &middot; <i class="icon-pencil"></i> leitura e escrita</p>
            </div><?php } ?>
            <a href="?logout" class="logout"><span>Sair</span> <i class="icon-signout"></i></a>
            
            <footer>
                <?php preg_match_all("([0-9]+)", $sftp->exec('fs lq'), $matches); $used = $matches[0][2]/1024; $total = $matches[0][1]/1024; $perc = $matches[0][3]; ?>
                <p class="qvals legenda"><?php echo round($used,1)."MB/".$total."MB &middot; ".round($perc,1)."% usado"; ?></p>
                <div id="quota"><div id="qbar" style="width:<?php echo $perc; ?>%"></div></div>
                <div class="joaoluis"><a href="http://web.ist.utl.pt/joaopluis"><span style="font-weight:bold">JOÃO</span><span style="font-weight:300">P</span>LUÍS</a></div></footer>
        </div>
        <div class="overlay" id="perms">
            <h3><i class="icon-pencil"></i> Editar Permissões</h3>
            <p>Escreve o IST ID do utilizador e as permissões que queres que ele tenha.</p>
            <form action="ops.php?mode=perm&path=<?php echo $path; ?>" method="post">
                <div><input type="text" name="istid" placeholder="IST ID" class="campo" />
                    <div class="permbt" data-val="write"><i class="icon-pencil"></i></div>
                    <div class="permbt" data-val="read"><i class="icon-eye"></i></div>
                    <div class="permbt active" data-val="none"><i class="icon-close"></i></div>
                </div>
                <input type="hidden" name="editperm" id="editperm" />
                <div class="bts"><input type="button" value="Cancelar" class="botao close"><input type="submit" value="Alterar" class="botao"></div>
                <div class="clear"></div>
            </form>
        </div>
        <?php if($write){ ?>
        <div id="dragoverlay"><div id="doin"><div>Solta os ficheiros aqui para fazer upload</div></div></div>
        <form action="upload.php?path=<?php echo $path; ?>" enctype="multipart/form-data" method="POST" id="uploadform">
            <fieldset class="uploadavatar">
                <input type="hidden" name="MAX_FILE_SIZE" value="2000000" />
                <input type="file" name="files[]" id="files" multiple />
                <a class="botao upload"><i class="icon-cloud-upload"></i> Upload</a>
            </fieldset>
            <span><i class="icon-cloud-upload"></i> A carregar...</span>
        </form>
        <?php } ?>
        
        <?php
        echo '<div id="bread">';
        $addr = "index.php";
        $actparts = explode('/',$path);
        $actparts[0] = $_SESSION['name'];
        echo '<a href="'.$addr.'"><i class="icon-cloud"></i></a> <span>'.$actparts[0].'</span>';
        if(count($actparts) > 1){
            $addr .= "?path=.";
            foreach($actparts as $i => $part) {
                if($i != 0){
                    $addr .= '/'.$part;
                    echo '<i class="sep icon-caret-right"></i>';
                    echo '<a href="'.$addr.'"><span>'.$part.'</span></a>';
                }
            }
        }
        echo '</div><div class="clear"></div>';
        $files = $sftp->rawlist();
        ksort($files);
        
        echo '<div class="topbar">';
        $expl = explode("/", $path);
        array_pop($expl);
        echo '<a'.($path != "." ? ' href="?path='.implode("/",$expl).'"' : "").' class="botaotb'.($path == "." ? " inactive" : "").' left"><i class="icon-caret-left"></i> Retroceder</a>';
        echo '<a rel="#mkdir" class="botaotb left"><i class="icon-plus"></i> Nova Pasta</a>';
        echo '<a href="?path='.$path.'&opt=1" class="botaotb right"><i class="eye"></i> '.($_SESSION['hidden']?'Esconder':'Mostrar').' ficheiros ocultos</a>';
        echo '<div class="clear"></div></div>';
        ?>
        <div class="overlay onefield" id="mkdir">
            <h3><i class="icon-folder"></i> Criar Pasta</h3>
            <p>Escreve o nome que queres dar à pasta e ela será criada nesta diretoria.</p>
            <form action="ops.php?mode=mkdir&path=<?php echo $path; ?>" method="post">
                <div><input type="text" name="foldername" placeholder="Nome" class="campo" />
                </div>
                <div class="bts">
                    <input type="button" value="Cancelar" class="botao close">
                    <input type="submit" value="Criar" class="botao">
                </div>
                <div class="clear"></div>
            </form>
        </div>
        
        <div class="overlay onefield" id="rename">
            <h3><i class="icon-font"></i> Mudar o nome</h3>
            <p>Escreve o novo nome que queres dar ao ficheiro <span class="flname"></span>.</p>
            <form action="ops.php?mode=rename&path=<?php echo $path; ?>" method="post">
                <div>
                <input type="text" name="newname" placeholder="Novo nome" class="campo" />
                <input type="hidden" name="filename" class="filename" />
                </div>
                <div class="bts">
                    <input type="button" value="Cancelar" class="botao close">
                    <input type="submit" value="Alterar" class="botao">
                </div>
                <div class="clear"></div>
            </form>
        </div>
        
        <div class="overlay" id="remove">
            <h3><i class="icon-remove"></i> Eliminar ficheiro</h3>
            <p>De certeza que queres eliminar o ficheiro <span class="flname"></span>?</p>
            <form action="ops.php?mode=remove&path=<?php echo $path; ?>" method="post">
                <input type="hidden" name="filename" class="filename" />
                <div class="bts">
                    <input type="button" value="Cancelar" class="botao close">
                    <input type="submit" value="Eliminar" class="botao">
                </div>
                <div class="clear"></div>
            </form>
        </div>
        <div class="overlay" id="removefolder">
            <h3><i class="icon-remove"></i> Eliminar pasta</h3>
            <p>De certeza que queres eliminar a pasta <span class="flname"></span>?</p>
            <p>Todos os seus conteúdos serão eliminados.</p>
            <form action="ops.php?mode=removedir&path=<?php echo $path; ?>" method="post">
                <input type="hidden" name="filename" class="filename" />
                <div class="bts">
                    <input type="button" value="Cancelar" class="botao close">
                    <input type="submit" value="Eliminar" class="botao">
                </div>
                <div class="clear"></div>
            </form>
        </div>
        
        
        <?php
        if(count($files) > 2){
            echo '<ul id="filelist">';
            foreach($files as $name => $file){
                if($name != "." && $name != ".." && (substr($name,0,1) != "." || $_SESSION['hidden'])){
                    echo '<li>';
                    
                    echo '<div class="actions">';
                    if($file['type'] == 1) echo '<a href="download.php?file='.$path.'/'.$name.'"><i class="icon-cloud-download"></i></a>';
                    echo '<a data-name="'.$name.'" class="rename"><i class="icon-font"></i></a>';
                    echo '<a data-name="'.$name.'" class="remove'.($file['type'] == 2?"folder":"").'"><i class="icon-remove"></i></a>';
                    echo '</div>';
                    
                    echo '<i class="';
                    if($file['type'] == 1){
                        echo "file";
                        $parts = explode(".",$name);
                        if(count($parts) > 1)
                            echo " icon-file-".array_pop($parts);
                        
                    }
                    elseif($file['type'] == 3) echo 'folder icon-folder share';
                    else echo 'folder icon-folder';
                    echo '"></i> <a href="';
                    if($file['type'] == 1)
                        echo "download.php?file=".$path."/".$name;
                    else echo "?path=".$path."/".$name;
                    echo '">';
                    echo $name;
                    echo "</a>";
                    if($file['type'] == 1) echo " <span>".size($file['size'])."</span>";
                    echo '</li>';
                }
            }
            echo '</ul>';
        }
        else echo "<p class='nofiles'><i class='icon-notification'></i><br />Esta pasta não contém ficheiros.</p>";
        //echo"<pre>";print_r($files);echo"</pre>";
        
        ?>
        <script>
            $("a[rel]").overlay({mask: '#000'});
            $("#files").change(function() {
                $('#uploadform').submit();
                
            });
            $("#rename").overlay({mask: '#000'});
            $('.rename').click(function(){
                $("#rename").overlay().load();
                name = $(this).attr('data-name');
                $('.flname').text(name);
                $('.filename').val(name);
            });
             $("#remove").overlay({mask: '#000'});
            $('.remove').click(function(){
                $("#remove").overlay().load();
                name = $(this).attr('data-name');
                $('.flname').text(name);
                $('.filename').val(name);
            });
              $("#removefolder").overlay({mask: '#000'});
            $('.removefolder').click(function(){
                $("#removefolder").overlay().load();
                name = $(this).attr('data-name');
                $('.flname').text(name);
                $('.filename').val(name);
            });
            $('.permbt').click(function(){
                $('.permbt').removeClass('active');
                $(this).addClass('active');
                $("#editperm").val($(this).attr("data-val"));
                console.log($("#editperm").val());
            });
            // drag over
            function handleDragOver(event) {
                event.stopPropagation();
                event.preventDefault();
                $('#dragoverlay').show();
            }
            
            function handleDragOut(event) {
                $('#dragoverlay').hide();
            }
            
            // drag drop
            function handleDrop(event) {
                event.stopPropagation();
                event.preventDefault();
                $('#dragoverlay').hide();
                $('.uploadavatar').hide();
                $('#uploadform span').show();
                processFiles(event.dataTransfer.files);
            }
            
            function processFiles(droppedFiles) {
                // add your files to the regular upload form
                var uploadFormData = new FormData($("#uploadform")[0]);
                if(droppedFiles.length > 0) { // checks if any files were dropped
                    for(f = 0; f < droppedFiles.length; f++) { // for-loop for each file dropped
                        uploadFormData.append("files[]",droppedFiles[f]);  // adding every file to the form so you could upload multiple files
                    }
                    
                    $.ajax({
                        url : "upload.php?path=<?php echo $path; ?>", // use your target
                        type : "POST",
                        data : uploadFormData,
                        contentType : false,
                        processData : false,
                        success : function(ret) {
                            location.reload(false)
                        }
                    });
                }
                
            }
            var dropArea = document.getElementById('dragoverlay');
            dropArea.addEventListener('drop', handleDrop, false);
            $('body').get(0).addEventListener('dragend', handleDragOut, false);
            $('body').get(0).addEventListener('dragover', handleDragOver, false);
        </script>
        <?php } else { ?>
        <div id="loginlogo"><img src="logopreto.png" /></div>
        <form action="?path=<?php echo $_GET['path']; ?>" method="post">
            <input type="text" name="username" placeholder="IST ID" class="campo" />
            <input type="password" name="password" placeholder="Password" class="campo" />
            <input type="submit" value="Entrar" class="botao" />
            
        </form>
        <?php }} ?>
    </body>
</html>