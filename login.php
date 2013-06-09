<body class="loginPage">
    <div id="loginStrip">
        <div id="joaopluis"><a href="http://web.ist.utl.pt/joaopluis"><img src="logojoaoluis.png" /></a></div>
    </div>
    <div id="loginContainer">
        
        <form action="?path=<?php echo $_GET['path']; ?>" method="post">
            <img src="logologin.png" id="loginlogo" />
            <input type="text" name="username" placeholder="IST ID" class="campo" />
            <input type="password" name="password" placeholder="Password" class="campo" />
            <input type="submit" value="Entrar" class="botao" />
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
            <p>Ao carregares em ENTRAR, estás a aceitar que a IST Cloud use as tuas credenciais para ligação SSH ao servidor Sigma, NUNCA as guardando.</p>
        </div>
        <div class="footerstuff">
            <i class="icon-github"></i>O código da IST Cloud está disponível em <a href="github.com/joaopluis/IST-Cloud/">https://github.com/joaopluis/IST-Cloud/</a>.
        </div>
    </div>
</body>