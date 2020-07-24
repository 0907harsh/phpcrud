<?php
    $countpositionsss=0;
    function validatePos() {
        for($i=1; $i<=9; $i++) {
          if ( ! isset($_POST['year'.$i]) ) continue;
          if ( ! isset($_POST['desc'.$i]) ) continue;
          
          $year = $_POST['year'.$i];
          $desc = $_POST['desc'.$i];
      
          if ( strlen($year) == 0 || strlen($desc) == 0 ) {
            return "All fields are required";
          }
         
          global $countpositionsss;
          $countpositionsss= $countpositionsss+1;
          
          if ( ! is_numeric($year) ) {
            return "Position year must be numeric";
          }
        }
        return true;
      }
    $counteducation=0;
    function validateEdu() {
        for($i=1; $i<=9; $i++) {
          if ( ! isset($_POST['edu_year'.$i]) ) continue;
          if ( ! isset($_POST['edu_school'.$i]) ) continue;
          
          $edu_year = $_POST['edu_year'.$i];
          $edu_school = $_POST['edu_school'.$i];
      
          if ( strlen($edu_year) == 0 || strlen($edu_school) == 0 ) {
            return "All fields are required";
          }
         
          global $counteducation;
          $counteducation= $counteducation+1;
          
          if ( ! is_numeric($edu_year) ) {
            return "Position year must be numeric";
          }
        }
        return true;
      }
