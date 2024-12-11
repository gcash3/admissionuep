<?php
interface CSNdataproviderInterface {
    public function selectdatabase($databasename);
    public function openconnection();
    public function closeconnection();
    public function execute($querystring);
    function begintransaction();
    function commit();
    function rollback();
}

class CSNdataprovider {
    public $connected;
    public $loginname;
    public $password;
    public $server;
    public $database;
    public $errormessage;
}

?>
