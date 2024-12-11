<?php
$scores = new ScoresUploader();

class ScoresUploader {
    private $columns = array();
    private $data    = array();

    private $applicationnumbercolumn = '';   
    private $namecolumn = '';
    private $emailcolumn = '';
    private $coursecolumn = '';   
    private $scorecolumn = '';
    private $timestampcolumn = ''; 
    
    private $applicationnumbers = array('Application Number');
    private $names = array('Full name','Lastname','Family Name', 'Surname');
    private $emails = array('Email');
    private $courses = array('Course/Program','Strand/Track'); 
    private $scores = array('Score');
    private $timestamps = array('Timestamp');
    private $filename = '';

    private $riasec_shs_array = array();
    private $riasec_college_array = array();    
    private $shs = false;
    private $withriasec = false;
    private $incompleteriasec = false;
    
    function __construct() {
        global $APP_SESSION;
        global $APP_CURRENTPAGE;
        $html = '';
        $body = '';
        if (isset($_POST['UploadGoogleCSV'])) {
            $error = '';
            $file = @$_FILES['GoogleCSV'];
            if (@$file['name'] == '')
                $error = 'No file uploaded!';
            elseif (strtolower(substr($file['name'],-4)) != '.csv')
                $error = 'Invalid file extension. Upload .csv only.';
            elseif (@$file['error']) 
                $error = 'Unable to upload file. Error code: ' . $file['error'];
            else {
                $error = $this->parseGoogleCSV(file_get_contents($file['tmp_name']), $processed);
                if ($processed == 0)
                    $error = 'No records uploaded!';
                else {
                    $this->filename = $file['name'];
                }
            }
            if ($error) {
                echo HTML::alert('Error', $error);
            }
        }
        elseif (isset($_POST['Cancel'])) {
            $this->data = array();
            $this->columns = array();
            unset($_POST['data']);
            unset($_POST['columns']);
        }
        
        
        if (@$_POST['data'] && @$_POST['columns']) {
            $this->data = json_decode(base64_decode($_POST['data']),true);
            $this->columns = json_decode(base64_decode($_POST['columns'])); 
            $this->filename = $_POST['filename'];
            $this->shs = @$_POST['shs'] == 1;
            $this->withriasec = @$_POST['withriasec'] == 1;

            if (!Crypto::ValidateGenericChecksum($_POST['data'], @$_POST['datacs']) || 
                !Crypto::ValidateGenericChecksum($_POST['columns'], @$_POST['columnscs']) ) {
                $APP_SESSION->setApplicationMessage('Invalid data. Please reupload CSV',false,'danger');
                Tools::redirect($APP_CURRENTPAGE);
                return;
            }
                        
        }
        
        $this->applicationnumbercolumn = $this->getColumnValue('ApplicationNumberColumn', $this->applicationnumbers);   
        $this->namecolumn = $this->getColumnValue('NameColumn', $this->names);  
        $this->emailcolumn = $this->getColumnValue('EmailColumn', $this->emails); 
        $this->coursecolumn = $this->getColumnValue('CourseColumn', $this->courses); 
        $this->scorecolumn = $this->getcolumnValue('ScoreColumn', $this->scores);  
        $this->timestampcolumn = $this->getColumnValue('TimestampColumn', $this->timestamps);        
        
        if (isset($_POST['UploadScores'])) {
            $this->uploadScores();
        }
         
        if ($this->columns && $this->data) 
            $this->generateProcessForm();
        else    
            $this->generateUploadForm(); 
    }
        
    function generateUploadForm() {
        $html = '';
        $body = '';
        $title = HTML::icon('search','<span class="search">Browse File</span>');  
        $body .= HTML::hforminputfile('GoogleCSV','Google Sheet CSV','.csv',0,0); 
        $footer= HTML::submitbutton('UploadGoogleCSV','Upload CSV','danger',true);
        $html .= '<form method=post enctype="multipart/form-data">';
        $html .= HTML::box($title, $body, $footer, 'info',true,false,' ');
        $html .= '</form>';       
        echo $html;  
    }
        
        
    function generateProcessForm() {       
        global $APP_SESSION;

        $columns = array();
        if ($this->applicationnumbercolumn)
            $columns[$this->applicationnumbercolumn-1] = 'Application Number';
        if ($this->namecolumn)
            $columns[$this->namecolumn-1] = 'Name';
        if ($this->emailcolumn)
            $columns[$this->emailcolumn-1] = 'Email';
        if ($this->coursecolumn)
            $columns[$this->coursecolumn-1] = 'Course/Strand';
        if ($this->scorecolumn)
            $columns[$this->scorecolumn-1] = 'Scores';
        if ($this->timestampcolumn)
            $columns[$this->timestampcolumn-1] = 'Timestamp';

        $this->initRIASEC();

        if (count($columns) == 0)
            $columns[0] = 'Record Number';
        else {
            if ($this->withriasec) {
                $columns['R']= '<span title="Realistic">R</span>';
                $columns['I']= '<span title="Investigative">I</span>';
                $columns['A']= '<span title="Artistic">A</span>';
                $columns['S']= '<span title="Social">S</span>';
                $columns['E']= '<span title="Enterprising">E</span>';
                $columns['C']= '<span title="Conventional">C</span>';
                $columns['RIASEC']= 'RIASEC';
            }
            $columns['Status']= 'Status';
        }
               
        $body    = '';
        $title   = HTML::Icon('table','Columns Settings');
        $footer  = HTML::submitbutton('Cancel','Cancel','default',false,false,true); 
                 
        $body .= HTML::hformselect('ApplicationNumberColunm', 'Application Number', $this->applicationnumbercolumn, $this->columns, true);   
        $body .= HTML::hformselect('NameColumn', 'Name', $this->namecolumn, $this->columns, true);
        $body .= HTML::hformselect('EmailColumn', 'Email', $this->emailcolumn, $this->columns, true);
        $body .= HTML::hformselect('Course', 'Course/Strand', $this->coursecolumn, $this->columns, true);
        $body .= HTML::hformselect('ScoreColumn', 'Score', $this->scorecolumn, $this->columns, true); 
        $body .= HTML::hformselect('TimestampColumn', 'Timestamp', $this->timestampcolumn, $this->columns, true);  
        $body .= HTML::hforminputtext('filename','Filename',$this->filename,'',true,true,false); 
        if (count($columns) < 6) {
            $body .= HTML::hformdiv('',HTML::alert('Attention!','CSV contains invalid or missing columns!','warning'),0,12);
        }
                
        // columns/data viewstate
        $body .= HTML::hidden('columns', base64_encode(json_encode($this->columns)));
        $body .= HTML::hidden('columnscs', Crypto::GenericChecksum(base64_encode(json_encode($this->columns))));  
        $body .= HTML::hidden('shs',$this->shs?1:0); 
        $body .= HTML::hidden('withriasec',$this->withriasec?1:0); 
        
        // display uploaded data  
        $footer = '';
        if ($this->applicationnumbercolumn) {
            $index = $this->applicationnumbercolumn-1;
            $sql = '';
            $recordnumber = 0;
            foreach ($this->data as $record) {
                $sql .= ($sql ? ",":"") . $record[$index];
                $riasec = $this->getRIASEC($record);
                $this->data[$recordnumber]['RIASEC'] = $riasec;
                $recordnumber++;
            }
            $total = 0;
            if ($sql) {
                $sql = "Usp_OA_GetApplicationNumbers '$sql'";
                $results = $this->executeSQL($sql);
                if (!Tools::emptydataset($results)) {
                    foreach ($results as $recordnumber=>$record) {
                        if (isset($this->data[$recordnumber])) {
                            $rowclass = '';
                            $this->data[$recordnumber]['Status'] = $record['Status'];
                            if ($record['ErrorCode']==0)
                                $total++;
                            if ($record['ErrorCode']==1)
                                $rowclass = 'danger';
                            elseif ($record['ErrorCode']==2)
                                $rowclass = 'success';
                            if ($rowclass)
                                $this->data[$recordnumber]['rowclass'] = $rowclass;   
                            if ($this->withriasec) {
                                $this->parseRIASEC($this->data[$recordnumber]);
                            }
                        }
                    }
                }
            }
            if ($total) {
                if ($APP_SESSION->getCanUpdate())
                    $footer .= HTML::submitbutton("UploadScores","Upload Scores ($total)",'success');  
                else
                    $footer .= '<span class="pull-right text-bold">Insufficient priveledge to upload/update scores!</span>';
            }         
            
        }

        $body .= HTML::hidden('data', base64_encode(json_encode($this->data)));
        $body .= HTML::hidden('datacs',Crypto::GenericChecksum(base64_encode(json_encode($this->data)))); 
       
        $footer .= HTML::submitbutton('Cancel','Cancel','default',false,false,true);        
        echo '<form method=post class="form-horizontal">';
        echo HTML::box($title, $body, $footer, 'success',true,false,' ');  
        
        $table = HTML::datatable('csvdata', $columns, $this->data);
        if (!$this->withriasec) {
            $table .= '<br>'.HTML::alert('Warning!', 'CSV does not contain RIASEC items!');
        }

        if ($this->incompleteriasec) {
            $table .= '<br>'.HTML::alert('Warning!', 'CSV contains incomplete RIASEC item(s)! Please report to ITD.'. "<ul>$this->incompleteriasec</ul>RIASEC assessment will not be uploaded.",'danger',false);
        }

        echo HTML::box(HTML::icon('file','CSV Content'), $table, $footer, 'info', true);
        echo '</form>';   
    }
    
    function parseGoogleCSV($content, &$processed) {
        global $APP_DBCONNECTION;
        $processed = 0;
        $content = trim($content);
        if ($content == "")
            return 'File is empty!';
        $eol = strpos($content,"\r\n") !== false ? "\r\n" : "\n";
        $lines = explode($eol, $content);
        if (count($lines)==1)
            return 'Invalid file content!';
        $columns = str_getcsv($lines[0]);
        array_unshift($columns,'Record Number');
        unset($columns[0]);
        //$words = array('___','?','THE ','FIND ','WHICH ','THIS ','RAIN','CLOUDS','OUR ','THIS','AMONG');
        $words = array('[Score]','[Feedback]');
        foreach ($columns as $key=>$column) {
            if (in_array(strtoupper($column),$words) || (trim($column) == ''))                  
                unset($columns[$key]);
            else {
                foreach ($words as $word) {
                    if (stripos($column, $word) !== false) {
                        unset($columns[$key]);
                        break;
                    }
                }
            }
        }

        
        $this->columns = $columns;
        
        unset($lines[0]);
        $sql = '';
        $ctr = 0;
        $data = '';
        $processed = 0;
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line != '') {
                $this->data[] = str_getcsv($line);
                $processed++;
            }
                
        }
        return '';
    }
    
    function getColumnValue($name, $list) {
        if (isset($_POST[$name]))
            return $_POST[$name];
        foreach ($this->columns as $index => $csvcolumnname) {
            foreach ($list as $columnname) {
                if (stripos($csvcolumnname, $columnname) !== false)
                    return $index;
            }
        }
        return '';
    }   
    
    function uploadScores() {
        global $APP_SESSION;   
        global $APP_CURRENTPAGE;    
        $sql = "";
        $batch=0;
        $array = array($this->applicationnumbercolumn, $this->namecolumn, $this->emailcolumn, $this->coursecolumn, $this->scorecolumn, $this->timestampcolumn);
        
        if ((count(array_unique($array)) < 5) || (in_array(0, $array) !== false ) ) {
            echo HTML::alert('Error!', 'Invalid or duplicate column index selected!');
            return;
        }
        
        $total = 0;
        $ipaddress = Data::get_actualip();
        $errormessage = 'Unable to save scores!';
        $riaseckeys = array('R','I','A','S','E','C');
        foreach ($this->data as $key=>$record) {
            if ($batch >= 20) {
                $sql = "Usp_OA_SaveScoresV2 '" . $APP_SESSION->getEmployeeCode() . "', '$this->filename', '$ipaddress'" . $sql;
                $results = $this->executeSQL($sql, $errormessage);
                if (!is_array($results))
                    break;
                $total += count($results);
                $batch = 0;
                $sql = "";
            }
            $batch++;
            $applicationnumber = $record[$this->applicationnumbercolumn-1];
            $score = $record[$this->scorecolumn-1];
            $name = str_replace($record[$this->namecolumn-1],"'","`");
            $email = $record[$this->emailcolumn-1];
            $course = str_replace($record[$this->coursecolumn-1],"'","`"); 
            $timestamp = $record[$this->timestampcolumn-1];

            $riasec = '';
            foreach ($riaseckeys as $key) {
                $riasec .= str_pad($record[$key],3,'0',STR_PAD_LEFT);
            }
            $sql .= ", $applicationnumber, '$name', '$email', '$course', '$score', '$timestamp', '$riasec'";
        }
        if ($sql) {
            $sql = "Usp_OA_SaveScoresV2 '" . $APP_SESSION->getEmployeeCode() . "', '$this->filename', '$ipaddress'" . $sql;
            $results = $this->executeSQL($sql, $errormessage);
            if (!is_array($results))
                return;
            $total += count($results);      
        }
        if ($total) {
            $s = $total > 0 ? 's' : '';
            $APP_SESSION->setApplicationMessage("($total) record$s uploaded!");
            Tools::redirect($APP_CURRENTPAGE);
        }
        else {
            echo HTML::alert('Attention','No records uploaded!');
        }
    }
    
    function executeSQL($sql, $showerror=false) {
        global $APP_DBCONNECTION;
        global $APP_DEBUGCONTENT;
        $sql = APP_DB_DATABASEADMISSION . "..$sql";
        $APP_DBCONNECTION->begintransaction();
        $results =  $APP_DBCONNECTION->execute($sql);
        if (!is_array($results)) {
            $APP_DBCONNECTION->rollback();
            if ($showerror) {
                echo HTML::alert('Error!', $showerror !== true ? $showerror : $results);
                if (!APP_PRODUCTION)
                    $APP_DEBUGCONTENT = $results . "<br>$sql";
            }
        }
        else
            $APP_DBCONNECTION->commit();
        return $results;
    }

    function initRIASEC() {
        $riasec_shs_array   = array();
        $riasec_shs_array[] = array('Planting and growing crops', 'R');
        $riasec_shs_array[] = array('Solving math problems', 'I');
        $riasec_shs_array[] = array('Being in a play', 'A');
        $riasec_shs_array[] = array('Studying other cultures', 'S');
        $riasec_shs_array[] = array('Talking to people at a party', 'E');
        $riasec_shs_array[] = array('Working with computers', 'C');
        $riasec_shs_array[] = array('Working on cars or lawnmowers', 'R');
        $riasec_shs_array[] = array('Learning about the stars, planets and space', 'I');
        $riasec_shs_array[] = array('Drawing or painting', 'A');
        $riasec_shs_array[] = array('Going to church', 'S');
        $riasec_shs_array[] = array('Working on a sales campaign', 'E');
        $riasec_shs_array[] = array('Using a cash register', 'C');
        $riasec_shs_array[] = array('Constructing with the use of wood ', 'R');
        $riasec_shs_array[] = array('Studying matter and energy', 'I');
        $riasec_shs_array[] = array('Learning a foreign language', 'A');
        $riasec_shs_array[] = array('Working with youth', 'S');
        $riasec_shs_array[] = array('Buying clothes for a store', 'E');
        $riasec_shs_array[] = array('Working from nine to five', 'C');
        $riasec_shs_array[] = array('Setting type for a printing job', 'R');
        $riasec_shs_array[] = array('Using a chemistry set', 'I');
        $riasec_shs_array[] = array('Reading a fiction or plays', 'A');
        $riasec_shs_array[] = array('Helping people with problems', 'S');
        $riasec_shs_array[] = array('Selling life insurance', 'E');
        $riasec_shs_array[] = array('Typing reports', 'C');
        $riasec_shs_array[] = array('Driving a truck', 'R');
        $riasec_shs_array[] = array('Working in a lab', 'I');
        $riasec_shs_array[] = array('Playing a musical instrument', 'A');
        $riasec_shs_array[] = array('Making new friends', 'S');
        $riasec_shs_array[] = array('Leading a group', 'E');
        $riasec_shs_array[] = array('Following a budget', 'C');
        $riasec_shs_array[] = array('Fixing electrical appliances', 'R');
        $riasec_shs_array[] = array('Building rocket models', 'I');
        $riasec_shs_array[] = array('Writing stories or poetry', 'A');
        $riasec_shs_array[] = array('Attending sports events', 'S');
        $riasec_shs_array[] = array('Making your opinions heard', 'E');
        $riasec_shs_array[] = array('Using business machines', 'C');
        $riasec_shs_array[] = array('Building things ', 'R');
        $riasec_shs_array[] = array('Doing puzzles', 'I');
        $riasec_shs_array[] = array('Fashion designing ', 'A');
        $riasec_shs_array[] = array('Belonging to a club', 'S');
        $riasec_shs_array[] = array('Giving talks or speeches', 'E');
        $riasec_shs_array[] = array('Keeping detailed records', 'C');
        $riasec_shs_array[] = array('Studying wild plants and animals and their habitats', 'R');
        $riasec_shs_array[] = array('Using science to get answers', 'I');
        $riasec_shs_array[] = array('Going to concerts or the theatre', 'A');
        $riasec_shs_array[] = array('Working with the elderly', 'S');
        $riasec_shs_array[] = array('Convincing people in their purchases', 'E');
        $riasec_shs_array[] = array('Filing letters and reports', 'C');

        $riasec_college_array[] = array('I like to work on cars', 'R');
        $riasec_college_array[] = array('I like to do puzzles ', 'I');
        $riasec_college_array[] = array('I am good at working independently ', 'A');
        $riasec_college_array[] = array('I like to work in teams ', 'S');
        $riasec_college_array[] = array('I am an ambitious person, I set goals for myself ', 'E');
        $riasec_college_array[] = array('I like to organize things,  (files, desks/offices) ', 'C');
        $riasec_college_array[] = array('I like to build things ', 'R');
        $riasec_college_array[] = array('I like to read about art and music ', 'A');
        $riasec_college_array[] = array('I like to have clear instructions to follow', 'C');
        $riasec_college_array[] = array('I like to try to influence or persuade people', 'E');
        $riasec_college_array[] = array('I like to do experiments', 'I');
        $riasec_college_array[] = array('I like to teach or train people', 'S');
        $riasec_college_array[] = array('I like trying to help people solve their problems', 'S');
        $riasec_college_array[] = array('I like to take care of animals', 'R');
        $riasec_college_array[] = array('I wouldn’t mind working 8 hours per day in an office', 'C');
        $riasec_college_array[] = array('I like selling things', 'E');
        $riasec_college_array[] = array('I enjoy creative writing', 'A');
        $riasec_college_array[] = array('I enjoy science', 'I');
        $riasec_college_array[] = array('I am quick to take on new responsibilities', 'E');
        $riasec_college_array[] = array('I am interested in healing people', 'S');
        $riasec_college_array[] = array('I enjoy trying to figure out how things work', 'I');
        $riasec_college_array[] = array('I like putting things together or assembling things', 'R');
        $riasec_college_array[] = array('I am a creative person', 'A');
        $riasec_college_array[] = array('I pay attention to details', 'C');
        $riasec_college_array[] = array('I like to do filing or typing', 'C');
        $riasec_college_array[] = array('I like to analyze things (problems/ situations)', 'I');
        $riasec_college_array[] = array('I like to play instruments or sing', 'A');
        $riasec_college_array[] = array('I enjoy learning about other cultures', 'S');
        $riasec_college_array[] = array('I would like to start my own business', 'E');
        $riasec_college_array[] = array('I like to cook', 'R');
        $riasec_college_array[] = array('I like acting in plays', 'A');
        $riasec_college_array[] = array('I am a practical person', 'R');
        $riasec_college_array[] = array('I like working with numbers or charts', 'I');
        $riasec_college_array[] = array('I like to get into discussions about issues', 'S');
        $riasec_college_array[] = array('I am good at keeping records of my work', 'C');
        $riasec_college_array[] = array('I like to lead', 'E');
        $riasec_college_array[] = array('I like working outdoors', 'R');
        $riasec_college_array[] = array('I would like to work in an office', 'C');
        $riasec_college_array[] = array('I’m good at math', 'I');
        $riasec_college_array[] = array('I like helping people', 'S');
        $riasec_college_array[] = array('I like to draw', 'A');
        $riasec_college_array[] = array('I like to give speeches', 'E');
        

        $ctr = 0;
        foreach ($riasec_shs_array as $i=>$item) {
            $riasec_shs_array[$i][2] = -1;
            foreach ($this->columns as $columnindex => $columnname) {
                if (stripos($columnname, $item[0]) !== false) {
                    $riasec_shs_array[$i][2] = $columnindex-1;
                    $ctr++;
                    break;
                }
            }
        }
        if ($ctr == count($riasec_shs_array)) {
            $this->shs = true;
            $this->withriasec = true;
        }
        else {
            if ($ctr) {
                $this->incompleteriasec = true;
                foreach ($riasec_shs_array as $item) {
                    if ($item[2] == -1)
                        $this->incompleteriasec .= "<li>" . $item[0] . "</li>";
                }
            }
            $ctr = 0;
            foreach ($riasec_college_array as $i=>$item) {
                $riasec_college_array[$i][2] = -1;
                foreach ($this->columns as $columnindex => $columnname) {
                    if (stripos($columnname, $item[0]) !== false) {
                        $riasec_college_array[$i][2] = $columnindex-1;
                        $ctr++;
                        break;
                    }
                }
            }
            if ($ctr == count($riasec_college_array)) {
                $this->withriasec = true;
                $this->incompleteriasec = false;
            }
            elseif ($ctr) {
                $this->incompleteriasec = '';
                foreach ($riasec_college_array as $item) {
                    if ($item[2] == -1)
                        $this->incompleteriasec .= "<li>" . $item[0] . "</li>";
                }
            }
        }

        $this->riasec_shs_array = $riasec_shs_array;
        $this->riasec_college_array = $riasec_college_array;

    }

    function getRIASEC($record) {
        $riasec = '';
        foreach ($record as $value) {
            foreach ($this->riasec_shs_array as $riasecitem) {
            }
        }
        return $riasec;
    }

    function parseRIASEC(&$record) {
        $items = array();
        if ($this->shs) 
            $items = $this->riasec_shs_array;
        else
            $items = $this->riasec_college_array;
        $riasectally['R'] = 0;
        $riasectally['I'] = 0;
        $riasectally['A'] = 0;
        $riasectally['S'] = 0;
        $riasectally['E'] = 0;
        $riasectally['C'] = 0;
        foreach ($items as $item) {
            $dataindex = isset($item[2]) ? $item[2] : -1;
            if (($dataindex > -1) && @$record[$dataindex]) {
                $key = $item[1];
                $response = strtoupper($record[$dataindex]);
                $riasectally[$key] += ($response == "APPEALING") || ($response == 'I AGREE') || ($response == 'AGREE') ? 1 : 0;
            }
        }
        foreach ($riasectally as $key => $total) {
            $record[$key] = $total;
        }
        arsort($riasectally);
        $i=0;
        $riasec = key($riasectally);
        $high = current($riasectally);
        next($riasectally);
        while (current($riasectally) == $high) {
            $riasec .= key($riasectally);
            next($riasectally);
            if (current($riasectally) == null)
                break;
        }
        $riasec .= ':';
        end($riasectally);
        $low = current($riasectally);
        $riasec .= key($riasectally);
        prev($riasectally);
        if ($high != current($riasectally)) {
            while (current($riasectally) == $low) {
                $riasec .= key($riasectally);
                prev($riasectally);
                if (current($riasectally) == null)
                    break;
            }
        }
        
        $record['RIASEC'] = $riasec;

    }
    
}


?>
