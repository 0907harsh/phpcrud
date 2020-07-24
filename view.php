<?php
    require_once "pdo.php";
    session_start();

    $stmt = $pdo->prepare("SELECT * FROM profile where profile_id = :xyz");
    $stmt->execute(array(":xyz" => $_GET['profile_id']));
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ( $row === false ) {
        $_SESSION['error'] = 'Bad value for autos_id';
        header( 'Location: index.php' ) ;
        return;
    }

    $stmt = $pdo->prepare("SELECT * FROM position where profile_id = :xyz ORDER BY rank");
    $stmt->execute(array(":xyz" => $_GET['profile_id']));
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if ( $rows === false ) {
        $_SESSION['error'] = 'Bad value for profile_id';
        header( 'Location: index.php' ) ;
        return;
    }

    $stmt = $pdo->prepare("SELECT * FROM education JOIN institution ON institution.institution_id=education.institution_id where profile_id = :xyz ORDER BY rank");
    $stmt->execute(array(":xyz" => $_GET['profile_id']));
    $rows_edu = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if ( $rows_edu === false ) {
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
        <title>Harsh Gupta Profile View</title>
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
        <p>First Name:
           <?= $fn ?></p>
        <p>Last Name:
            <?= $ln ?></p>
        <p>Email:
            <?= $em ?></p>
        <p>Headline:<br/>
            <?= $he ?></p>
        <p>Summary:<br>
            <?= $su ?>
        </p>
        <p>Educations:<br>
            <ol>
            <?php 
                if($rows_edu!==false){
                    foreach($rows_edu as $i => $value){
                        echo '<li>'.$value['year'].' : '.$value['name'].'</li>';
                    }
                }
            ?>
            </ol>
        </p>
        <p>Positions:<br>
            <ol>
            <?php 
                if($rows!==false){
                    foreach($rows as $i => $value){
                        echo '<li>'.$value['year'].' : '.$value['description'].'</li>';
                    }
                }
            ?>
            </ol>
        </p>

        <a href="index.php">Done</a>
        


        </div>
    </body>
</html>

