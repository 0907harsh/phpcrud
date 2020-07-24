<?php
    require_once "pdo.php";
    session_start();
    if ( isset($_POST['Cancel'])) {
        header("Location: index.php");
        return;
    }
    require_once "utils.php";
    if ( isset($_POST['Update']) &&  isset($_GET['profile_id']) ) {

        // Data validation
        if ( strlen($_POST['first_name']) < 1 || strlen($_POST['last_name']) < 1 || strlen($_POST['headline']) < 1 || strlen($_POST['email']) < 1 || strlen($_POST['summary']) < 1) {
            $_SESSION['error'] = 'All fields are required';
            header("Location: edit.php?profile_id=" . $_GET["profile_id"]);
            return;
        }

        if ( strpos($_POST['email'],'@') === false ) {
            $_SESSION['error'] = 'Email address must contain @';
            header("Location: edit.php?profile_id=" . $_GET["profile_id"]);
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


        $sql = "UPDATE profile SET first_name = :fn,
                last_name = :ln, email= :em, 
                headline= :he, summary= :su
                WHERE profile_id = ".$_GET['profile_id'];
        $stmt = $pdo->prepare($sql);
        $stmt->execute(array(
            ':fn' => $_POST['first_name'],
            ':ln' => $_POST['last_name'],
            ':em' => $_POST['email'],
            ':he' => $_POST['headline'],
            ':su' => $_POST['summary'])
        );
        
        $stmt = $pdo->prepare('DELETE FROM Position WHERE profile_id=:pid');
        $stmt->execute(array( ':pid' => $_REQUEST['profile_id']));
        $rank = 1;
        for($i=1; $i<=9; $i++) {
                if ( ! isset($_POST['year'.$i]) ) continue;
                if ( ! isset($_POST['desc'.$i]) ) continue;
                $year = $_POST['year'.$i];
                $desc = $_POST['desc'.$i];
                $stmt = $pdo->prepare('INSERT INTO Position
                    (profile_id, rank, year, description)
                    VALUES ( :pid, :rank, :year, :desc)');
                $stmt->execute(array(
                ':pid' => $_REQUEST['profile_id'],
                ':rank' => $rank,
                ':year' => $year,
                ':desc' => $desc)
                );
                $rank++;
        }
        $stmt = $pdo->prepare('DELETE FROM education WHERE profile_id=:pid');
        $stmt->execute(array( ':pid' => $_REQUEST['profile_id']));
        $rank=1;
        for($i=1; $i<=9; $i++) {
            if ( ! isset($_POST['edu_year'.$i]) ) continue;
            if ( ! isset($_POST['edu_school'.$i]) ) continue;
            $stmt = $pdo->prepare("SELECT * FROM institution where name = :name");
            $stmt->execute(array(":name" => $_POST['edu_school'.$i]));
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ( $row === false ) {
                $_SESSION['error'] = 'Bad value for institution';
                header( 'Location: edit.php?profile_id='.$_GET['profile_id'] ) ;
                return;
            }
            $stmt = $pdo->prepare('INSERT INTO education (profile_id, institution_id, rank, year) VALUES ( :pid, :inst_id, :rank, :edu_year)');
            $stmt->execute(array(
            ':pid' => $_GET['profile_id'],
            ':inst_id' => $row['institution_id'],
            ':rank' => $rank,
            ':edu_year' => $_POST['edu_year'.$i])
            );
            $rank++;
        }
        
        $_SESSION['success'] = 'Record updated';
        header( 'Location: index.php' ) ;
        return;
    }

    // Guardian: Make sure that autos_id is present
    if ( ! isset($_SESSION['user_id']) ) {
        $_SESSION['error'] = "Not Logged In";
        header('Location: index.php');
        return;
    }

    $stmt = $pdo->prepare("SELECT * FROM profile where profile_id = :xyz");
    $stmt->execute(array(":xyz" => $_GET['profile_id']));
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ( $row === false ) {
        $_SESSION['error'] = 'Bad value for user_id';
        header( 'Location: index.php' ) ;
        return;
    }


    $stmt = $pdo->prepare("SELECT * FROM position where profile_id = :xyz");
    $stmt->execute(array(":xyz" => $_GET['profile_id']));
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if ( $rows === false ) {
        $_SESSION['error'] = 'Bad value for profile_id';
        header( 'Location: index.php' ) ;
        return;
    }

    $stmt = $pdo->prepare("SELECT * FROM education JOIN institution ON institution.institution_id=education.institution_id where profile_id = :xyz");
    $stmt->execute(array(":xyz" => $_GET['profile_id']));
    $rowsedu = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if ( $rowsedu === false ) {
        $_SESSION['error'] = 'Bad value for profile_id';
        header( 'Location: index.php' ) ;
        return;
    }
    

    $fn = htmlentities($row['first_name']);
    $ln = htmlentities($row['last_name']);
    $em = htmlentities($row['email']);
    $he = htmlentities($row['headline']);
    $su = htmlentities($row['summary']);
?>
<html>
    <head>
        <title>Harsh Gupta Edit Profile</title>
        <?php require_once "head.php"; ?>
    </head>
    <body>
    <div class="container">
        
        <h1>Edit User</h1>
        <?php
            // Flash pattern
            if ( isset($_SESSION['error']) ) {
                echo '<p style="color:red">'.$_SESSION['error']."</p>\n";
                unset($_SESSION['error']);
            }
        ?>
        <form method="post">
            <p>First Name:
            <input type="text" name="first_name" size="60"
            value=<?= $fn ?>
            /></p>
            <p>Last Name:
            <input type="text" name="last_name" size="60"
            value=<?= $ln ?>
            /></p>
            <p>Email:
            <input type="text" name="email" size="30"
            value=<?= $em ?>
            /></p>
            <p>Headline:<br/>
            <input type="text" name="headline" size="80"
            value=<?= $he ?>
            /></p>
            <p>Summary:<br/>
            <textarea name="summary" rows="8" cols="80"><?= $su ?></textarea>
            <br><br>Education :<input type="button" id="addButtonEducation" value="+" />
                <p id="addEducation">
                <?php 
                    $count_edu=1;
                    if($rowsedu!==false){
                        foreach($rowsedu as $i => $value){
                            echo '<div id="education'.$count_edu.'"><p>Year: <input type="text" name="edu_year'.$count_edu.'" value="'.$value['year'].'"><input type="button" value="-" onclick="$(\'#education'.$count_edu.'\').remove();return false;"></p><input name="edu_school'.$count_edu.'" id="edu_school'.$count_edu.'" size="80" onkeyup="autocompletion(\'edu_school'.$count_edu.'\');" value="'.$value['name'].'" /></div><br>';
                            $count_edu++;
                        }
                    }
                ?></p>
            Position : <input type="button" id="addButtonPosition" value="+" />
                <p id="addPosition">
                <?php 
                    $count=1;
                    if($rows!==false){
                        foreach($rows as $i => $value){
                            echo '<div id="position'.$count.'"><p>Year: <input type="text" name="year'.$count.'" value="'.$value['year'].'"><input type="button" value="-" onclick="$(\'#position'.$count.'\').remove();return false;"></p><textarea name="desc'.$count.'" rows="8" cols="80">'.$value['description'].'</textarea></div><br>';
                            $count++;
                        }
                    }
                ?></p>
           
            <p>
            <input type="hidden" name="profile_id"
            value=<?= $_GET['profile_id'] ?>
            />
            
            <input type="submit" name="Update" value="Save">
            <input type="submit" name="Cancel" value="Cancel">
            </p>
        </form>
                    
        </div>
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

