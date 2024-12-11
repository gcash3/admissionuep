<?php
require_once('UE.class.CSNdataproviderInterface.php');

class Data {
	static function openconnection($connect=true, $login='',$password='',$database='',$servername='') {
		if ($login == '') 
			$login = APP_DB_LOGIN;
		if ($password =='') 
			$password = APP_DB_PASSWORD;
		if ($database == '')
			$database = APP_DB_DATABASE;
		if ($servername == '')
			$servername = APP_DB_SERVERNAME;
		if (APP_DB_DRIVER == 'sqlsrv') 
			return new CSNsqlsrv($login, $password, $database, $servername, $connect);
		else
			return new CSNmssql($login, $password, $database, $servername, $connect);
	}

	static function getcampuses($responsive=false) {
        if ($responsive) {
            $campuses['0'] = 'Manila<span class="hidden-xs"> Campus</span>';
            $campuses['1'] = 'Cal<span class="hidden-xs">oocan Campus</span>';           
        }
        else {
		    $campuses['0'] = 'Manila Campus';
		    $campuses['1'] = 'Caloocan Campus';
        }
		//$campuses['3'] = 'Makati Campus';
		return $campuses;
	}
    
    static function getcampusdescription($campuscode,$responsive=false) {
        $campus = Data::getcampuses($responsive);
        return @$campus[$campuscode];
    }


	static function formatdate($value, $format='m/d/Y') {
		if ($value == null)
			return '';
		if ($value == '')
			return '';
		if($value instanceof DateTime)
			$date = $value->getTimestamp();
		else
			$date = strtotime($value);
		if ($date)
			return date($format, $date);
	}
    
    static function number_format2($n, $d) {
        if ($n)
            return number_format($n, $d);
        else
            return '';
    }    

	static function sanitize($value, $pattern='', $removehtml=true) {
        if ($removehtml) {
            $value = str_ireplace(array('<','>'), '', $value);
            $value = str_ireplace(array("'",'"'), array("`",''), $value);
        }
        
        if (is_array($value)) {
            foreach ($value as $k => $v) {
                if ($pattern == '')
                    $value[$k] = trim($v);
                else {
                    //TODO: construct regex for digits,text,email
                    $value[$k] = preg_replace($pattern, '', trim($value[$k]));
                }
            }
            return $value;
        }
        else {
		    if ($pattern == '')
			    return trim($value);
		    //TODO: construct regex for digits,text,email
		    return preg_replace($pattern, '', trim($value));
        }
	}

	static function getreportformats() {
		$formats['CSV']  = 'Comma Separated Values (*.csv)';
		$formats['HTML'] = 'HTML (*.html)';	
		$formats['XLS']  = 'Microsoft Excel (*.xls)';
		$formats['PDF']  = 'Adobe Acrobat (*.pdf)';
		return $formats;
	}


	static function getsemesterdescription($semester, $responsive=false) {
        if (trim($semester)) {
            if ($responsive) {
                switch (substr($semester,-1)) {
                    case '1': return '1st Sem<span class="hidden-xs">ester S.Y.,</span> ' . '</span><span class="hidden-xs">' . substr($semester,0,2) . '</span>' . substr($semester,2,2) . '<span class="hidden-xs">-' . (substr($semester,0,4) + 1) . '</span>';
                    case '2': return '2nd Sem<span class="hidden-xs">ester S.Y.,</span> ' . '</span><span class="hidden-xs">' . substr($semester,0,2) . '</span>' . substr($semester,2,2) . '<span class="hidden-xs">-' . (substr($semester,0,4) + 1) . '</span>';
                    case '0': return 'Summer ' . substr($semester,0,4);
                    case 'A': return '1st Trimester ' . substr($semester,0,4);
                    case 'B': return '2nd Trimester ' . substr($semester,0,4);
                    case 'C': return '3rd Trimester ' . substr($semester,0,4);
                }
            }
            else {
		        switch (substr($semester,-1)) {
			        case '1': return '1st Semester, S.Y. ' . substr($semester,0,4) . '-' . (substr($semester,0,4) + 1);
			        case '2': return '2nd Semester, S.Y. ' . substr($semester,0,4) . '-' . (substr($semester,0,4) + 1);
			        case '0': return 'Summer ' . substr($semester,0,4);
			        case 'A': return '1st Trimester ' . substr($semester,0,4);
			        case 'B': return '2nd Trimester ' . substr($semester,0,4);
			        case 'C': return '3rd Trimester ' . substr($semester,0,4);
                }
		    }
        }
		return $semester;
	}

	static function getcollegedescription($college) {
		switch (strtoupper($college)) {
			case "ALL": return 'ALL COLLEGES';
			case "A"  : return 'COLLEGE OF ARTS AND SCIENCES';
			case 'B'  : return 'COLLEGE OF BUSINESS ADMINSTRATIONS';
			case 'C'  : return 'COLLEGE OF COMPUTER STUDIES AND SYSTEMS';
			case 'D'  : return 'COLLEGE OF DENTISTRY';
			case 'E'  : return 'COLLEGE OF EDUCATION';
			case 'F'  : return 'COLLEGE OF FINE ARTS, ARCHITECTURE AND DESIGN';
			case 'G'  : return 'GRADUATE SCHOOL';
			case 'H'  : return 'HIGH SCHOOL DEPARTMENT';
			case 'K'  : return 'KINDER DEPARTMENT';
			case 'L'  : return 'COLLEGE OF LAW';
			case 'N'  : return 'COLLEGE OF ENGINEERING';
			case 'Y'  : return 'ELEMENTARY DEPARTMENT';
		}
		return 'COLLEGE CODE: ' . $college;
	}

	static function getdepartments($campuscode='', $withgrouping=0) {
		global $APP_DBCONNECTION;
		if ($APP_DBCONNECTION->connected) {
			$results = $APP_DBCONNECTION->execute("Usp_AP_DT_GetDepartments '$campuscode'");
			
			if (is_array($results) && count($results)) {
				foreach ($results as $record) {
                    if ($withgrouping)
                        $array[$record['DeptCode']] = array($record['Description'] . ' (' . $record['Reference'] . ')', $record['Branch']);
                    else
					    $array[$record['DeptCode']] = $record['Description'] . ' (' . $record['Reference'] . ')';
				}
			}
		}
		return $array;
	}
    
    static function getdepartmentsall($withgrouping=1, $shortdescription=false) {
        global $APP_DBCONNECTION;
        if ($APP_DBCONNECTION->connected) {
            $results = $APP_DBCONNECTION->execute("Usp_AP_DT_GetDepartmentsAll");
            if (is_array($results) && count($results)) {
                foreach ($results as $record) {
                    $description = ($shortdescription) ? $record['ShortDescription'] : $record['Description'] . ' (' . $record['Reference'] . ')';
                    if ($withgrouping)
                        $array[$record['CampusDeptCode']] = array($description, $record['Branch']);
                    else
                        $array[$record['CampusDeptCode']] = $description;
                }
            }
        }
        return $array;
    }
    

	static function getdocumenttypes($all=false) {
		global $APP_DBCONNECTION;
		$all = $all ? 1 : 0;
        $array = array();
		if ($APP_DBCONNECTION->connected) {
			$results = $APP_DBCONNECTION->execute("Usp_AP_DT_GetDocumentTypes '$all'");
			if (is_array($results) && count($results)) {
				foreach ($results as $record) {
					$array[$record['DocumentTypeID']] = $record['Description'];
				}
			}
		}
		return $array;
	}
    
    static function padleft($str, $length=8, $padstr='0') {
        return str_pad($str,$length,$padstr,STR_PAD_LEFT);
    }
    
    static function getincomingdocuments() {
        global $APP_DBCONNECTION, $APP_SESSION;
        $EmployeeCode = $APP_SESSION->getEmployeeCode();
        $sql = "Usp_AP_DT_GetDocumentsIncoming '$EmployeeCode'"; 
        $results = $APP_DBCONNECTION->execute($sql);
        return $results;
    }
    
    static function getpendingclearance() {
        global $APP_DBCONNECTION, $APP_SESSION;
        $EmployeeCode = $APP_SESSION->getEmployeeCode();
        $sql = "Usp_AP_CL_GetApplicationsPending '$EmployeeCode'"; 
        $results = $APP_DBCONNECTION->execute($sql);
        $count = 0;
		if (is_array($results)) {
			foreach ($results as $result) {
				if ($result['Signatories'])
					$count++;
			}
		}
        return $count;
    }
    
    
    static function getclearancepurposetypes() {
        global $APP_DBCONNECTION;
        $array = array();
        if ($APP_DBCONNECTION->connected) {
            $results = $APP_DBCONNECTION->execute("Usp_AP_CL_GetPurposeTypes");
            if (is_array($results) && count($results)) {
                foreach ($results as $record) {
                    $array[$record['PurposeTypeID']] = $record['Description'];
                }
            }
        }
        return $array;
    }    
    
    static function getpayperiodlistbyemployee($employeecode, $periods=48) {
        global $APP_DBCONNECTION;
        $array = array();
        $results = $APP_DBCONNECTION->execute("Payroll..Usp_GetPayPeriodListByEmployee '$employeecode', 1");
        $i=0;
        if (is_array($results) && count($results)) {
            foreach ($results as $record) {
                $array[$record['PayPeriod_Code']] = $record['Description'];
                $i++;
                if ($i==$periods)
                    break;
            }
        }
        return $array;
    }
    

    static function getvltypes($EmployeeCode='') {
        global $APP_DBCONNECTION;
        $array = array();
        if ($APP_DBCONNECTION->connected) {
            if ($EmployeeCode == '')
                $sql = "Usp_AP_VL_GetTypes";
            else
                $sql = "Usp_AP_VL_GetTypesbyEmployee '$EmployeeCode'";
            $results = $APP_DBCONNECTION->execute($sql);
            if (is_array($results) && count($results)) {
                foreach ($results as $record) {
                    $array[$record['LeaveType']] = array(null, $record['Description'], 'data-withdates' => $record['WithDates'], 'data-maxdays' => $record['MaxDays'] );
                }
            }
        }
        return $array;
    }    
    
    static function gethourslist($others='') {
        $array = array();
        $array['8']   = '8 Hours';
        $array['7']   = '7 Hours';
        $array['6']   = '6 Hours';
        $array['5']   = '5 Hours';
        $array['4']   = '4 Hours';
        $array['3']   = '3 Hours';
        $array['2']   = '2 Hours';
        $array['1']   = '1 Hour';
        
        $array['7.50'] = '7 Hrs 30 Mins';
        $array['6.50'] = '6 Hrs 30 Mins';
        $array['5.50'] = '5 Hrs 30 Mins';
        $array['4.50'] = '4 Hrs 30 Mins';
        $array['3.50'] = '3 Hrs 30 Mins';
        $array['2.50'] = '2 Hrs 30 Mins';
        $array['1.50'] = '1 Hrs 30 Mins';
        $array['0.50'] = '0 Hrs 30 Mins';
        
        $array['Others'] = 'Others';
        if ($others && !isset($array[$others])) {
            $hh = floor($others);
            $mm = round(($others - floor($others)) * 60,2);
            $array[$others] = "$hh Hrs $mm Mins";
        }
        return $array;
    }
    
    static function getampmlist() {
        $array = array();
        $array['A'] = 'AM';
        $array['P'] = 'PM';
        $array['W'] = 'AM/PM';
        return $array;
    }
    
    
    static function savelog($Employee_Code, $Action, $PayPeriod_Code='', $Deduction_Code='', $LoanReference_Code='') {
        global $APP_DBCONNECTION;
        global $APP_SESSION;
        
        $IPAddress = DATA::get_actualip();
        //if (APP_PRODUCTION)
        //    $results = $APP_DBCONNECTION->execute(APP_DB_DATABASEPAYROLL . "..Usp_ePayslipSaveLog '$Employee_Code', '$Action', '$IPAddress', '$PayPeriod_Code', '$Deduction_Code', '$LoanReference_Code'");
    }

    static function get_actualip() {
        //Just get the headers if we can or else use the SERVER global
        if ( function_exists( 'apache_request_headers' ) ) {
            $headers = apache_request_headers();
        } else {
            $headers = $_SERVER;
        }
        //Get the forwarded IP if it exists
        if ( array_key_exists( 'X-Forwarded-For', $headers ) && filter_var( $headers['X-Forwarded-For'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 ) ) {
            $the_ip = $headers['X-Forwarded-For'];
        } elseif ( array_key_exists( 'HTTP_X_FORWARDED_FOR', $headers ) && filter_var( $headers['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 )
        ) {
            $the_ip = $headers['HTTP_X_FORWARDED_FOR'];
        } else {
            
            $the_ip = filter_var( $_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 );
        }
        return $the_ip;
    }
    
    static function getmedicines($search, $employeecode, $benefittype="M") {
        global $APP_DBCONNECTION; 
        $sql = APP_DB_DATABASEPMIS . "..Usp_AP_MO_GetMedicines '$search', '$employeecode', '$benefittype'";
        $results = $APP_DBCONNECTION->execute($sql);
        if (!TOOLS::emptydataset($results)) {
            return $results;
        }
        return array();
    }
	
	static function replaceparameters(&$text, $array, $matches=null) {
        if ($matches == null)
            $count = preg_match_all('/\[\@([a-z0-9_ ]+)\]/i', $text, $matches);
        else    
            $count = count($matches);
        for ($i=0; $i<$count; $i++) {
            $key = $matches[1][$i];
            $value = ''; // todo: traverse array to get value
            if (isset($array[$key])) {
                if (!is_array($array[$key]))
                    $value = $array[$key];
            }
            $text = str_replace($matches[0][$i], $value, $text);
        }
    }    

    static function validatenewpassword($username, $currentpassword, $oldpassword, $newpassword, $confirmpassword, $newpasswordrequired=true) {
        if ($currentpassword && ($oldpassword != $currentpassword))
            return 'Invalid current password!';
        elseif ($username && ($newpassword == $username)) 
            return 'Username is not allowed as password!';
        elseif (($newpassword == '') && $newpasswordrequired)
            return 'Please input new password';
        elseif ($newpassword != $confirmpassword)
            return 'Password mismatched!';
        elseif ($newpassword && (strlen($newpassword) < 6))
            return 'New Password  must be at least 6 characters!';
        elseif ($newpassword && (preg_match('/[A-Z]/',$newpassword) == 0))
            return 'New Password must contain at least 1 upper case letter!';
        elseif ($newpassword && (preg_match('/[0-9]/',$newpassword) == 0))
            return 'New Password must contain at least 1 number!';            
        return '';
    }	
         
    static function getoptionsarray($sql, $valuename, $optionname, $returnerror=false) {
        global $APP_DBCONNECTION;
        $array = array();
		if (is_array($sql))
			$results = $sql;
		else
			$results = $APP_DBCONNECTION->execute($sql);
        if (is_array($results)) {
            foreach ($results as $record) {
                $array[$record[$valuename]] = $record[$optionname];
            }
			return $array;
        }
        return $returnerror ? false : $array;
    }    
	
	static function getstudents($searchtext, $foreignonly=0, $returnerror=false) {
		global $APP_SESSION;
		global $APP_DBCONNECTION;
		$UserEmployeeCode = $APP_SESSION->getuserid();
		$sql = "Usp_AP_SearchStudents '$UserEmployeeCode', '$searchtext', 0";
		$results = $APP_DBCONNECTION->execute($sql);
		if (!is_array($results) && !$returnerror)
			$results = array($results);
		return $results;
	}
	
}	

?>
