<?php
    require_once "pdo.php";
    session_start();
    if(!isset($_SESSION['user_id']))
    {
        die("Access Denied");
    }
    if ( isset($_POST['Cancel'])) {
            header("Location: index.php");
            return;
    }
    require_once "utils.php";
    if ( isset($_POST['Add'])) {

        // Data validation
        if ( strlen($_POST['first_name']) < 1 || strlen($_POST['last_name']) < 1 || strlen($_POST['headline']) < 1 || strlen($_POST['email']) < 1 || strlen($_POST['summary']) < 1) {
            $_SESSION['error'] = 'All fields are required';
            header("Location: add.php?profile_id=" . $_POST["profile_id"]);
            return;
        }

        if ( strpos($_POST['email'],'@') === false ) {
            $_SESSION['error'] = 'Email address must contain @';
            header("Location: add.php?profile_id=" . $_POST["profile_id"]);
            return;
        }
        $x=validatePos();
        if($x!==true){
            $_SESSION['error'] = $x;
            header("Location: add.php?profile_id=" . $_POST["profile_id"]);
            return;
        }
        $x=validateEdu();
        if($x!==true){
            $_SESSION['error'] = $x;
            header("Location: add.php?profile_id=" . $_POST["profile_id"]);
            return;
        }
        $stmt = $pdo->prepare('INSERT INTO Profile (user_id, first_name, last_name, email, headline, summary) VALUES ( :uid, :fn, :ln, :em, :he, :su)');
        $stmt->execute(array(
            ':uid' => $_SESSION['user_id'],
            ':fn' => $_POST['first_name'],
            ':ln' => $_POST['last_name'],
            ':em' => $_POST['email'],
            ':he' => $_POST['headline'],
            ':su' => $_POST['summary'])
        );
        $profile_id = $pdo->lastInsertId();
        $rank=1;
        for($i=1; $i<=$countpositionsss; $i++) {
            $stmt = $pdo->prepare('INSERT INTO Position (profile_id, rank, year, description) VALUES ( :pid, :rank, :year, :desc)');
            $stmt->execute(array(
            ':pid' => $profile_id,
            ':rank' => $rank,
            ':year' => $_POST['year'.$i],
            ':desc' => $_POST['desc'.$i])
            );
            $rank++;
        }
        $rank=1;
        for($i=1; $i<=$counteducation; $i++) {
            $stmt = $pdo->prepare("SELECT institution_id FROM institution where name = :name");
            $stmt->execute(array(":name" => $_POST['edu_school'.$i]));
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ( $row === false ) {
                $_SESSION['error'] = 'Bad value for institution';
                header( 'Location: add.php' ) ;
                return;
            }
            $stmt = $pdo->prepare('INSERT INTO education (profile_id, institution_id, rank, year) VALUES ( :pid, :inst_id, :rank, :edu_year)');
            $stmt->execute(array(
            ':pid' => $profile_id,
            ':inst_id' => $row['institution_id'],
            ':rank' => $rank,
            ':edu_year' => $_POST['edu_year'.$i])
            );
            $rank++;
        }
        $_SESSION['success'] = 'Record Added';
        header( 'Location: index.php' ) ;
        return;      
    }

?>
<html>
    <head>
        <title>Harsh Gupta Add new Profile</title>
        <?php require_once "head.php"; ?>
    </head>
    <body>

        <div class="container">
            <h1>Adding Profile for <?= $_SESSION['name'] ?></h1>
            <?php
                // Flash pattern
                if ( isset($_SESSION['error']) ) {
                    echo '<p style="color:red">'.$_SESSION['error']."</p>\n";
                    unset($_SESSION['error']);
                }
                if ( isset($_SESSION['success']) ) {
                    echo '<p style="color:green">'.$_SESSION['success']."</p>\n";
                    unset($_SESSION['success']);
                }

            ?>
            <form method="post">
                <p>First Name:
                    <input type="text" name="first_name" size="60"/>
                </p>
                 <p>Last Name:
                    <input type="text" name="last_name" size="60"/>
                </p>
                <p>Email:
                    <input type="text" name="email" size="30"/>
                </p>
                 <p>Headline:<br/>
                    <input type="text" name="headline" size="80"/>
                </p>
                <p>Summary:<br/>
                    <textarea name="summary" rows="8" cols="80"></textarea>
                </p>
                <p>Education :
                    <input type="button" id="addButtonEducation" value="+" />
                    <p id="addEducation"></p>
                </p>
                <p>Position :
                    <input type="button" id="addButtonPosition" value="+" />
                    <p id="addPosition"></p>
                </p>
                <p>
                   <input type="submit" name="Add" value="Add">
                    <input type="submit" name="Cancel" value="Cancel">
                </p>
                
            </form> 
            <script>
                function autocompletion(element){
                   
                    $.getJSON('./school.php?term='+$('#'+element).val(), function(rowz){
                        console.log('JSON Received'); 
                        console.log(rowz);
                        $('#'+element).autocomplete({ source: rowz });
                    });
                }
            </script>
        <script type="text/javascript">
            count=1;
            $('#addButtonPosition').click(function(event){
                event.preventDefault();
                console.log('Clicked');
                $('#addPosition').append('<div id="position'+count+'"><p>Year: <input type="text" name="year'+count+'" value=""><input type="button" value="-" onclick="$(\'#position'+count+'\').remove();return false;"></p><textarea name="desc'+count+'" rows="8" cols="80"></textarea></div><br>') ;
                count++;
                if(count>9){
                    alert('Maximum limit reached.No more positions allowed');
                    $('#addButtonPosition').prop('disabled', true);
                }
            });
            count_edu=1;
            $('#addButtonEducation').click(function(event){
                event.preventDefault();
                console.log('Clicked');
                $('#addEducation').append('<div id="education'+count_edu+'"><p>Year: <input type="text" name="edu_year'+count_edu+'" value=""><input type="button" value="-" onclick="$(\'#education'+count_edu+'\').remove();return false;"></p>Institution : <input name="edu_school'+count_edu+'" id="edu_school'+count_edu+'" size="80" onkeyup="autocompletion(\'edu_school'+count_edu+'\');" /></div><br>') ;
                count_edu++;
                if(count_edu>9){
                    alert('Maximum limit reached.No more positions allowed');
                    $('#addButtonEducation').prop('disabled', true);
                }
            });
            
            
            </script>
    </body>
    
</html>

