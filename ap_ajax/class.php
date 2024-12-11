<?php 
    class JunClass
    {
        public $pCourse;
        public $pYear;
        public $pCourseYear;
        
        function __construct($pCourse, $pYear, $pCourseYear)
        {
            $this->Course = $pCourse;
            $this->Year = $pYear;
            $this->CourseYear = $pCourseYear;
            $this->aCourseYear = [['CourseYear' => 'Kinder 1', 'Year' => '1','College' => 'K','Course' => 'Kinder'],
                                  ['CourseYear' => 'Kinder 2', 'Year' => '2','College' => 'K','Course' => 'Kinder'],
                                  ['CourseYear' => 'Grade 1', 'Year' => '1','College' => 'Y','Course' => 'ELEM'],
                                  ['CourseYear' => 'Grade 2', 'Year' => '2','College' => 'Y','Course' => 'ELEM'],
                                  ['CourseYear' => 'Grade 3', 'Year' => '3','College' => 'Y','Course' => 'ELEM'],
                                  ['CourseYear' => 'Grade 4', 'Year' => '4','College' => 'Y','Course' => 'ELEM'],
                                  ['CourseYear' => 'Grade 5', 'Year' => '5','College' => 'Y','Course' => 'ELEM'],
                                  ['CourseYear' => 'Grade 6', 'Year' => '6','College' => 'Y','Course' => 'ELEM'],
                                  ['CourseYear' => 'Grade 7', 'Year' => '1','College' => 'H','Course' => 'HS'],
                                  ['CourseYear' => 'Grade 8', 'Year' => '2','College' => 'H','Course' => 'HS'],
                                  ['CourseYear' => 'Grade 9', 'Year' => '3','College' => 'H','Course' => 'HS'],
                                  ['CourseYear' => 'Grade 10', 'Year' => '4','College' => 'H','Course' => 'HS']];
            
        }

        function getYear()
        {
            for ($i=0;$i<12;$i++) { 
                if ($this->CourseYear == $this->aCourseYear[$i]['CourseYear']){
                    return $this->aCourseYear[$i]['Year'];
                }
            }
        }

        function getCollege()
        {
            for ($i=0;$i<12;$i++) { 
                if ($this->CourseYear == $this->aCourseYear[$i]['CourseYear']){
                    return $this->aCourseYear[$i]['College'];
                }
            }
        }

        function getCourse()
        {
            for ($i=0;$i<12;$i++) { 
                if ($this->CourseYear == $this->aCourseYear[$i]['CourseYear']){
                    return $this->aCourseYear[$i]['Course'];
                }
            }
        }

        function getCourseYear()
        {
            for ($i=0;$i<12;$i++) { 
                if ($this->Year == $this->aCourseYear[$i]['Year'] and $this->Year == $this->aCourseYear[$i]['College']){
                    return $this->aCourseYear[$i]['CourseYear'];
                }
            }
        }

        function ClearSchedules()
        {
            $html="";
            for ($i=0;$i<6;$i++) {
            $html .=   "<tr><td style='text-align: center'></td>".
                            "<td><br></td>".
                            "<td> </td>".
                            "<td> </td>".
                            "<td> </td>".
                            "<td> </td>".
                            "<td> </td>";
            }
            return $html."<hr>";
        }    

        function ClearTableOfFees()
        {
            $html="";
            $html .= "<tr><td>Tuition</td><td style='text-align: right;  padding-top:0; padding-right: 20px;'>".number_format(0,2)."</td></tr>";
            return $html;
        }

        function ClearBreakdownOfFees()
        {
            $html="";
            $html .= "<tr><td>Downpayment</td><td style='text-align: right; padding-right: 20px;'>".number_format(0,2)."</td></tr>";
            return $html;
        }

    }
?>


