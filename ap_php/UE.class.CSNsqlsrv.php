<?php
// Microsoft PHP Driver implementation of mssql extension
// Equivalent to CSNmssql class
// 10/18/2017-CSN  Not yet fully tested
class CSNsqlsrv extends CSNdataprovider implements CSNdataproviderInterface {
    private $connectionid;
    
    function __construct($loginname, $password, $database, $server, $openconnection = true) {
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
            $rv = @sqlsrv_query($this->connectionid, "USE $databasename");
            $this->errormessage = $this->geterrormessages();
        }
        return $rv;
    }
    
    function openconnection() {
        if (!$this->connected) {
            $connectioninfo = array( "Database"=> $this->database, "UID" => $this->loginname, "PWD" => $this->password, "TraceOn" => "0", "MultipleActiveResultSets"=>'0');
            $this->connectionid = @sqlsrv_connect($this->server, $connectioninfo); 
            $this->connected = ($this->connectionid == true);
            $this->errormessage = $this->geterrormessages();
            if ($this->connected)
                $this->selectdatabase($this->database);
        }
        return $this->connected;
    }
    
    function closeconnection() {
        if ($this->connected)
            @sqlsrv_close($this->connectionid);
    }
    
    function execute($querystring) {
        if (!$this->connected) {
            $this->errormessage = "ERROR: NOT CONNECTED";
            return $this->errormessage;
        }
        $fh = @fopen('log/sql' . date('Ym') .'.php' ,"a");
        @fwrite($fh, '<?php /* ' . date('Y-m-d h:i:s ') . $querystring . " */ ?> \n");
        @fclose($fh);
        
        $this->errormessage = "";
        $oldsetting = ini_set ("track_errors", true);
        $resultid = @sqlsrv_query($this->connectionid, $querystring);
        ini_restore("track_errors");
        if (is_bool($resultid)) {
            $this->errormessage = "ERROR: " . $this->geterrormessages();
            return $this->errormessage;
        }
        $i = 0;
        $rows = array();
        $found = false;
		while ($row = sqlsrv_fetch_object($resultid)) { 
            $rows[$i] = @get_object_vars($row);
            // convert DateTime object to string
            if (($i==0) || $found) {
                foreach ($rows[$i] as $k => $v)
                    if ($v instanceof DateTime) {
                        $rows[$i][$k] = date('m/d/Y h:m:i', $v->getTimestamp());
                        $found = true;
                    }
            }
			$i++;
		}
 	    @sqlsrv_free_stmt($resultid);
        return $rows;
    }
    
    function begintransaction() {
        return (sqlsrv_begin_transaction($this->connectionid));
    }
    
    function commit() {
        return (sqlsrv_commit($this->connectionid));
    }

    function rollback() {
        return (sqlsrv_rollback($this->connectionid));
    }

    private function geterrormessages($separator = '<br>') {
        $errorstring = '';
        if (($errors = sqlsrv_errors() ) != null) {
            foreach( $errors as $error ) {
                $errorstring .= $error['message'] . $separator;
            }
        }
        return $errorstring;
    }
}
?>