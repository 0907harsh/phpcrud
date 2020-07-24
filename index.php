<?php
require_once "pdo.php";
session_start();
// If the user requested logout go back to index.php

?>
<html>
    <head>
        <title>Harsh Gupta Resume Registry</title>
        <?php require_once "head.php"; ?>
    </head>
    <body>
    <div class="container">
        <h1>Harsh Gupta's Resume Registry</h1>
        <?php
            if ( isset($_SESSION['error']) ) {
                echo '<p style="color:red">'.$_SESSION['error']."</p>\n";
                unset($_SESSION['error']);
            }
            if ( isset($_SESSION['success']) ) {
                echo '<p style="color:green">'.$_SESSION['success']."</p>\n";
                unset($_SESSION['success']);
            }
            if( !isset($_SESSION['user_id']))   {
                    echo("<p><a href=\"login.php\">Please log in</a></p>");
            }
            $stmt = $pdo->query("SELECT first_name,last_name,headline,profile_id FROM profile JOIN users ON users.user_id=profile.user_id");
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if($rows !== []){
                echo('<table border="1">'."\n");
                echo "<tr><th>";
                echo("Name");
                echo("</th><th>");
                echo("Headline");
                    if(isset($_SESSION['user_id']))   {
                        echo("</th><th>");
                        echo('');
                        echo("</th></tr>\n");
                    } else{
                        echo("</th></tr>\n");
                    }   
                
                    for($i=0;$i<count($rows);$i++) {
                        echo ("<tr><td><a href=\"view.php?profile_id=");
                        echo($rows[$i]['profile_id']."\">");
                        echo($rows[$i]['first_name'].' '.$rows[$i]['last_name']);
                        echo("</a></td><td>");
                        echo(htmlentities($rows[$i]['headline']));
                        if(isset($_SESSION['user_id']))   {
                            echo("</td><td>");
                            echo('<a href="edit.php?profile_id='.$rows[$i]['profile_id'].'">Edit</a> / ');
                            echo('<a href="delete.php?profile_id='.$rows[$i]['profile_id'].'">Delete</a>');
                            echo("</td></tr>\n");
                        }else{
                            echo("</td></tr>");
                        }
                    }
                    echo("</table>");
                }else{
                    echo("<p>No rows Found</p>");
                }
            if(isset($_SESSION['user_id']))   {
                echo("<p><a href=\"add.php\">Add New Entry</a></p>");
                echo("<p><a href=\"logout.php\">Logout</a></p>");
                
            }
        ?>
        </table>
        
    </div>
    </body>
</html>
