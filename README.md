IST Cloud
=========

O IST Cloud é uma interface web para o espaço AFS que o IST disponibiliza.

Funcionalidades
---------------

O IST Cloud permite, em ficheiros:

* Download
* Renomear
* Remover
* Upload

Em pastas:

* Renomear
* Remover
* Alterar permissões
* Criação

Fancy stuff
-----------

Algumas funcionalidades só porque é fancy e fica bem:

* Upload de ficheiros por drag & drop para o browser
* Mostrar/esconder ficheiros ocultos
* Através do diretório LDAP do IST, o IST Cloud sabe reconhecer os IST IDs e traduzi-los para nomes.
* Design responsivo, isto é, o IST Cloud é mobile-ready

Segurança
---------

As credenciais IST são algo importante e que não se deve fornecer a qualquer um.

O IST Cloud, para manter a segurança, **NUNCA** guarda as credenciais do utilizador, sendo estas passadas diretamente ao servidor (tanto que o IST Cloud não depende de nenhuma base de dados).

Além disto, como o IST Cloud conecta por SFTP, não será possível nada que por SFTP não seja possível.

Dependências
------------

O IST Cloud, para o acesso SFTP, depende da biblioteca [phpseclib](http://phpseclib.sourceforge.net/).
