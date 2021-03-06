<body class="loginPage">
    <div id="loginStrip">
        <div id="joaopluis"><a href="http://web.ist.utl.pt/joaopluis"><img src="images/logojoaoluis.png" /></a></div>
    </div>
    <div id="loginContainer">
        
        <form action="?path=<?php echo $_GET['path']; ?>" method="post" <?php if($man) echo "class='man'"; ?>>
            <img src="images/logologin.png" id="loginlogo" />
            <?php if($man){ ?>
                <h1>Em manutenção.</h1>
                <p>Tenta novamente mais tarde.</p>
            <?php } else { ?>
            <input type="text" name="username" placeholder="IST ID" class="campo" />
            <input type="password" name="password" placeholder="Password" class="campo" />
            <input type="submit" value="Entrar" class="botao" />
            <?php } ?>
        </form>
        <?php
        if(array_key_exists("logout",$_GET)){
            echo '<div id="loginMsg" class="info logout">Sessão terminada com sucesso.</div>';
        } elseif($warning){
            echo '<div id="loginMsg" class="warning">'.$warning.'</div>';
        } else {
            echo '<div id="loginMsg"></div>';
        }
        ?>
        <div class="footerstuff big">
            <i class="icon-notification"></i>
            <h3>A IST Cloud não é um serviço oficial do IST/DSI.</h3>
            <p class="nomobile">Ao carregares em ENTRAR, estás a aceitar que a IST Cloud use as tuas credenciais para ligação SSH ao servidor Sigma, NUNCA as guardando.</p>
        </div>
        <div class="footerstuff nomobile">
            <i class="icon-github"></i>O código da IST Cloud está disponível em <a href="https://github.com/joaopluis/IST-Cloud/">github.com/joaopluis/IST-Cloud/</a>.
        </div>
    </div>
</body>