<?php
class CSNmssql extends CSNdataprovider implements CSNdataproviderInterface {
    private $connectionid;
    private $exectime;
    
    function CSNmssql($loginname, $password, $database, $server, $openconnection = true) {
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
            $rv = @mssql_select_db($databasename, $this->connectionid);
            $this->errormessage = mssql_get_last_message();
        }
        return $rv;
    }
    
    function openconnection() {
        if (!$this->connected) {
            $this->connectionid = @mssql_connect($this->server, $this->loginname, $this->password); 
            $this->connected = ($this->connectionid <> 0);
            $this->errormessage = @mssql_get_last_message();
            if ($this->connected)
                $this->selectdatabase($this->database);
        }
        return $this->connected;
    }
    
    function closeconnection() {
        if ($this->connected)
            @mssql_close($this->connectionid);
    }
    
    function execute($querystring) {
        if (!$this->connected) {
            $this->errormessage = "ERROR: NOT CONNECTED";
            return $this->errormessage;
        }
        
        $time = microtime(true);
        $this->errormessage = "";
        $oldsetting = ini_set ("track_errors", true);
        $resultid = @mssql_query($querystring, $this->connectionid);
        ini_restore("track_errors");
        if (is_bool($resultid)) {
            if (!$resultid) {
                $this->errormessage = "ERROR: " . mssql_get_last_message();
                return $this->errormessage;
            }
            else
                $this->errormessage = "ERROR: " . mssql_get_last_message();
                return $this->errormessage;
        }
        $i = 0;
        $rows = array();
		while ($row = mssql_fetch_object($resultid)) { 
			$rows[$i] = @get_object_vars($row);
			$i++;
        }
         @mssql_free_result($resultid);
         $this->exectime = round(microtime(true) - $time,4);
        return $rows;
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