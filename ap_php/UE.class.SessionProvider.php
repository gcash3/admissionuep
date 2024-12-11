<?php
class SessionProvider {
    private $_prefix = '';
    
    function __construct($prefix = '_UEADOP_') {
        $this->_prefix = $prefix;
    }
    
    function getPrefix() {
        return $this->_prefix;
    }

    function setUsername($username) {
        $this->setsessionvalue('USERNAME', $username);
    }
    function getUsername() {
        return $this->getsessionvalue('USERNAME');
    }
    
    function setEmployeeCode($employeecode) {
        $this->setsessionvalue('EMPLOYEECODE', trim($employeecode));
    }
    function getEmployeeCode() {
        return $this->getsessionvalue('EMPLOYEECODE');
    }
    
    function setUserID($employeecode) {
		$this->setEmployeeCode($employeecode);
    }
    function getUserID() {
        return $this->getEmployeeCode();
    }
    
    function setEmployeeName($employeename) {
        $this->setsessionvalue('EMPLOYEENAME', $employeename);
    }
    function getEmployeeName() {
        return $this->getsessionvalue('EMPLOYEENAME');
    }

    function setEmployeeFirstname($value) {
        $this->setsessionvalue('EMPLOYEEFIRSTNAME', $value);
    }
    function getEmployeeFirstname() {
        return $this->getsessionvalue('EMPLOYEEFIRSTNAME');
    }    
    
    function setDepartmentReference($departmentreference) {
        $this->setsessionvalue('DEPARTMENTREFERENCE', $departmentreference);
    }
    function getDepartmentReference() {
        return $this->getsessionvalue('DEPARTMENTREFERENCE');
    }
    
    
    function setPassword($password) {
        $this->setsessionvalue('PASSWORD', sha1("lualhatibuildinggroundbreakingday;$password;chitosantosnuarin"));
    }
    function validatePassword($password) {
        return $this->getsessionvalue('PASSWORD') == sha1("lualhatibuildinggroundbreakingday;$password;chitosantosnuarin");
    }
    
    function setCampusCode($campuscode) {
        $this->setsessionvalue('CC', $campuscode);
    }
    function getCampusCode() {
        return $this->getsessionvalue('CC','0');
    }

    function setDualCampus($value) {
        $this->setsessionvalue('DC', $value == true);
    }
    function getDualCampus() {
        return $this->getsessionvalue('DC', false);
    }    
    
    function setLogin($username) {
        $this->setsessionvalue('ISL', sha1("akoaynakalogin;$username;csn;1234567890!@#%^&*()_+"));
    }
    function isLogin() {
        $username = $this->getsessionvalue('USERNAME');
        return $this->getsessionvalue('ISL') == sha1("akoaynakalogin;$username;csn;1234567890!@#%^&*()_+");
    }
    
    public function getsessionvalue($key, $defaultvalue="") {
        $key = $this->_prefix . strtolower($key);
        if (isset($_SESSION[$key]))
            return $_SESSION[$key];
        return $defaultvalue;
    }
    
    public function setsessionvalue($key, $value) {
        $key = $this->_prefix . strtolower($key);
        $_SESSION[$key] = $value;
    }
    
    function getUndefinedSessionValue()
    {
        $sessions = array('EMPLOYEECODE',
                          'PASSWORD');

        foreach ($sessions as $session) {
            $session = $this->_prefix . $session;
            if (!isset($_SESSION[$session]))
                return $session;
        }
        return "";
    }
    
    function session_destroy() {
        foreach ($_SESSION as $key => $value) {
            if (substr($key,0, strlen($this->_prefix)) == $this->_prefix) 
                unset($_SESSION[$key]);
        }
    }

    function session_start($username, $password, $name, $firstname, $departmentreference, $birthdate='') {
        $this->setUsername($username);
        $this->setEmployeeCode($username);
        $this->setLogin($username);
        $this->setPassword($password);
        $this->setEmployeeName($name);
        $this->setEmployeeFirstname($firstname);
        $this->setDepartmentReference($departmentreference);
        $this->setsessionvalue('LOGINSTART', time());
        if  ((($password == '') || 
             ($password == $username) ||
             ($password == Data::formatdate($birthdate)) ||
             ($password == Data::formatdate($birthdate,'mdY')) ||
             ($password == Data::formatdate($birthdate,'dmY')) ||
             ($password == Data::formatdate($birthdate,'Ymd')) ||
             ($password == Data::formatdate($birthdate,'Ydm')))
              && DEBUG_CHECKPASSWORD)
            $this->setMustChangePassword(APP_DEMO == false);
    }

    function setAccessTable($value) {
        $this->setsessionvalue('ACCESSTBL', $value);
    }

    function getAccessTable() {
        return $this->getsessionvalue('ACCESSTBL', array());      
    }

    function getModuleAccess($page='') {
        global $APP_CURRENTPAGE;
        if ($page == '')
            $page = $APP_CURRENTPAGE;
        $accesstable = $this->getsessionvalue('ACCESSTBL', array());
        if (array_key_exists($page, $accesstable))
            return $accesstable[$page];
        else
            return APP_PRODUCTION ? '' : 'CRUDP';
    }
    
    function getAccess($rights, $page='') {
        global $APP_CURRENTPAGE;
        global $APP_CURRENTPAGEDETAILS;
        global $APP_MODULES;
        if ($page=='') {
            $page = $APP_CURRENTPAGE;
            if (trim(@$APP_CURRENTPAGEDETAILS[1]) == false)
                return true;
        }
        else {
            $pagedetails = getmenudetails($page, $APP_MODULES);
            if (is_array($pagedetails) && $pagedetails && (trim(@$pagedetails[1]) == false))
                return true;
        }
        return stripos($this->getModuleAccess($page), $rights) !== false;
    }
    
    function getCanCreate($page='') {
        return $this->getAccess('C', $page) || !APP_PRODUCTION;
    }
	function getCaAdd($page='') {
		return $this->getCanCreate($page);
	}
    
    function getCanRead($page='') {
        return $this->getAccess('R', $page) || !APP_PRODUCTION;;
    }
   	function getCanView($page='') {
		return $this->getCanRead($page);
	}
	
    function getCanUpdate($page='') {
        return $this->getAccess('U', $page) || !APP_PRODUCTION;;
    }
	function getCanEdit($page='') {
		return $this->getCanUpdate($page);
	}
    
    function getCanDelete($page='') {
        return $this->getAccess('D', $page);
    }

    function getCanPrint($page='') {
        return $this->getAccess('P', $page) || !APP_PRODUCTION;;
    }
    
    function getSessionMinutes() {
        $seconds = time() - $this->getsessionvalue('LOGINSTART', time());
        $minutes = floor($seconds / 60);
        return $minutes . ' Mins';
    }
    
    function setApplicationMessage($html, $append=false, $class='success') {
        if ($append)
            $html = $this->getApplicationMessage(false) . $html;
        $this->setsessionvalue('APMSG', $html);   
        $this->setsessionvalue('APMSGCL', $class);   
    }
    
    function getApplicationMessage($deleteafter=true, &$class='') {
        $msg = $this->getsessionvalue('APMSG');
        $class = $this->getsessionvalue('APMSGCL','success');
        if ($deleteafter) {
            $this->setApplicationMessage('');
        }
        return $msg;
    }
    
    function setGoogleAuthenticated($google) {
        $this->setsessionvalue('GOOGLE', $google==true);
    }
    function getGoogleAuthenticated() {
        return $this->getsessionvalue('GOOGLE','0') == true;
    }    
    
    

    function setDemo($demo) {
        $this->setsessionvalue('DEMO', $demo==true);
    }
    function getDemo() {
        return $this->getsessionvalue('DEMO', false);
    }    
    
    function setDashboard($dashboard) {
        $this->setsessionvalue('DASH', $dashboard);
    }
    function getDashboard() {
        return $this->getsessionvalue('DASH', 'dashboard');
    }    

    function setLoginPage($page) {
        $this->setsessionvalue('LOGINPAGE', $page);
    }
    function getLoginPage() {
        return $this->getsessionvalue('LOGINPAGE', 'login');
    }    
    
    function setSinglePage($value) {
        $this->setsessionvalue('SINGLEPAGE', $value==true);
    }
    function getSinglePage() {
        return $this->getsessionvalue('SINGLEPAGE', false);
    }    
    
    function setEmailPreview($value, $append = false) {
        if ($append)
            $value = $this->getEmailPreview(true) . '<hr>' . $value;
        $this->setsessionvalue('EmailPreview', $value);
    }
    
    function getEmailPreview($deleteafter=true) {
        $msg = $this->getsessionvalue('EmailPreview');
        if ($deleteafter) {
            $this->setEmailPreview('');
        }
        return $msg;
    }    
    
    function getMustChangePassword() {
        return $this->getsessionvalue('MCP', false);
    }
    function setMustChangePassword($value) {
        $this->setsessionvalue('MCP', $value==true);
    }
    
    function getEmployeeClass() {
        return $this->getsessionvalue('Class', false);
    }
    function setEmployeeClass($value) {
        $this->setsessionvalue('Class', $value);
    }
	
	function setPageSemester($value) {
		$this->setsessionvalue('PageSemester',$value);
        setcookie('PageSemester',$value,time()+60*60*24*365,APP_BASE);
	}
	function getPageSemester() {
		$semester = $this->getsessionvalue('PageSemester');
        if ($semester == '')
            $semester = @$_COOKIE['PageSemester'];
        return $semester;
	}   
    
    
}  
?>