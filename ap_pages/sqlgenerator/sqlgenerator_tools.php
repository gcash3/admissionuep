<?php
class SPGenTools {
    static function gettables($userid, $driver, $connection=null) {
        global $APP_DBCONNECTION;
		if ($connection == null)
			$connection = $APP_DBCONNECTION;
        $tables = array();
        $sql = '';
        $field = 'Tables_in_' . APP_DB_DATABASE;
        if ($driver == 'mysqli') 
            $sql = "SHOW TABLES";
        else {
            $sql = "SELECT Name FROM SYS.TABLES 
			        WHERE Name NOT LIKE '[_]%' AND Type = 'U' 
					ORDER BY Name";
            $field = 'Name';
        }
        if ($sql) {
            $results = $connection->execute($sql, false);
            if (!Tools::emptydataset($results))
                foreach ($results as $record)
                    $tables[$record[$field]] = $record[$field];
            return $tables;
        }
    }
        
    static function getttablecolumns($userid, $tablename, $driver, $withtype = true, $connection=null) {
        global $APP_DBCONNECTION;
		if ($connection == null)
			$connection = $APP_DBCONNECTION;		
        $columns = array();
        if ($driver == 'mysqli') 
            $sql = "DESCRIBE $tablename";
        else
            $sql = "SELECT Field = COLUMN_NAME,
                           Type  = DATA_TYPE + CASE WHEN CHARACTER_MAXIMUM_LENGTH IS NOT NULL THEN '(' + CAST(CHARACTER_MAXIMUM_LENGTH AS VARCHAR(4)) + ')' ELSE '' END
                    FROM INFORMATION_SCHEMA.COLUMNS
                    WHERE TABLE_NAME = '$tablename'
                    ORDER BY ORDINAL_POSITION";
        if ($sql) {
            $results = $connection->execute($sql, false);
            if (!Tools::emptydataset($results)) {
                $maxlen = 0;
                foreach ($results as $record) {
                    $maxlen = max($maxlen, strlen($record['Field']));
                }
                foreach ($results as $record) {
                    $type = $record['Type'];
                    if ((substr($type,0,4) != 'char') && (substr($type,0,7) != 'varchar') && (strpos($type,'(') !== false)) {
                        $type = substr($type,0, stripos($type,'('));
                    }
                    $columns[$record['Field']] = $record['Field'] . str_repeat(' ', $maxlen - strlen($record['Field'])) . ($withtype ? ' ' . strtoupper($type) : '');
                }
            }
        }
        return $columns;
    }
        
}
?>