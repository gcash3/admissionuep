<?php
class CSNmysqli extends CSNdataprovider implements CSNdataproviderInterface {
    private $connectionid;
    private $exectime;
    
    function CSNmysqli($loginname, $password, $database, $server, $openconnection = true) {
        $this->loginname = $loginname;
        $this->password  = $password;
        $this->server    = $server;
        $this->database  = $database;
        $this->connected = false;
        if ($openconnection)
            $this->openconnection();
    }
    
   
    function selectdatabase($databasename) {
        $rv = false;
        if ($this->connected) {
            $rv = mysqli_select_db($this->connectionid, $databasename);
        }
        return $rv;
    }
    
    function openconnection() {
        if (!$this->connected) {
            $this->connectionid = @mysqli_connect($this->server, $this->loginname, $this->password); 
            $this->connected = mysqli_connect_errno() == 0;
            if ($this->connected)
                $this->selectdatabase($this->database);
            else
                $this->errormessage = mysqli_connect_error();
        }
        return $this->connected;
    }
    
    function closeconnection() {
        if ($this->connected)
            @mysqli_close($this->connectionid);
    }
    
    function execute($querystring, $storedprocedure = true) {
        if (!$this->connected) {
            $this->errormessage = "ERROR: NOT CONNECTED";
            return $this->errormessage;
        }
        
        $time = microtime(true);
        $this->errormessage = "";
        $oldsetting = ini_set ("track_errors", true);
        if ($storedprocedure) {
            $s = stripos($querystring,' ');
            if ($s > 0) 
                $querystring = substr($querystring,0,$s) . '(' . substr($querystring,$s+1) . ')';
            $querystring = "CALL $querystring";

        }
        if (mysqli_multi_query($this->connectionid, $querystring)) {
            if ($resultid = mysqli_store_result($this->connectionid)) {
                $i = 0;
                $rows = array();
                while ($row = mysqli_fetch_object($resultid)) { 
                    $rows[$i] = @get_object_vars($row);
                    $i++;
                }
                mysqli_free_result($resultid);
                $this->exectime = round(microtime(true) - $time,4);
                while (mysqli_more_results($this->connectionid)) {
                    mysqli_next_result($this->connectionid);
                }
            }
            else {
                $this->errormessage = "ERROR: " . mysqli_error($this->connectionid);
                return $this->errormessage;    
            } 
            return $rows;
        }
        else {
            $this->errormessage = "ERROR: " . mysqli_error($this->connectionid);
            return $this->errormessage;
        }
    }
    
    function begintransaction() {
        return ($this->execute("BEGIN TRANSACTION") == "");
    }
    
    function commit() {
        return ($this->execute("COMMIT") == "");
    }

    function rollback() {
        return ($this->execute("ROLLBACK") == "");
    }

    function execseconds() {
        return $this->exectime;
    }
}
?>