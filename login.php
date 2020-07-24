<?php // Do not put any HTML above this line
    require_once "pdo.php";
    session_start();
    $salt = 'XyZzy12*_';
    $stored_hash = '1a52e17fa899cf40fb04cfc42e6352f1';  // Pw is php123

    $q='@';
    // Check to see if we have some POST data, if we do process it
    if ( isset($_POST['email']) && isset($_POST['pass']) ) {
        $check = hash('md5', $salt.$_POST['pass']);
        $stmt = $pdo->prepare('SELECT user_id, name FROM users WHERE email = :em AND password = :pw');
        $stmt->execute(array( ':em' => $_POST['email'], ':pw' => $check));
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ( $row !== false ) {
            $_SESSION['name'] = $row['name'];
            $_SESSION['user_id'] = $row['user_id'];
            $_SESSION['success'] = "Login Successful";
            // Redirect the browser to index.php
            header("Location: index.php");
            return;
         } else {
            $_SESSION['error'] = "Incorrect password";
            header("Location: login.php");
            return;
        }
    }

// Fall through into the View
?>
<!DOCTYPE html>
<html>
    <head>
        
        <title>Harsh Gupta Login Page</title>
        <?php require_once "head.php"; ?>
    </head>
    <body>
        <div class="container">
        <h1>Please Log In</h1>
        <?php
            // Note triple not equals and think how badly double
            // not equals would work here...
            if ( isset($_SESSION['error']) ) {
                echo('<p style="color: red;">'.htmlentities($_SESSION['error'])."</p>\n");
                unset($_SESSION['error']);
            }
        ?>
        <form method="POST">
            <label for="email">Email</label>
            <input type="text" name="email" id="email"><br>
            <label for="id_1723">Password</label>
            <input type="text" name="pass" id="id_1723"><br>
            <input type="submit" onclick="return doValidate();" value="Log In">
            <a href="index.php">Cancel</a>
        </form>

        <p>
        For a password hint, view source and find a password hint
        in the HTML comments.
        <!-- Hint:
            id - umsi@umich.edu
         The password is the  php (all lower case) followed by 123. -->
        </p>
        </div>
        <script>
            function doValidate() {
                console.log('Validating...');
                try {
                    addr = document.getElementById('email').value;
                    pw = document.getElementById('id_1723').value;
                    console.log("Validating addr="+addr+" pw="+pw);
                    if (addr == null || addr == "" || pw == null || pw == "") {
                        alert("Both fields must be filled out");
                        return false;
                    }
                    if ( addr.indexOf('@') == -1 ) {
                        alert("Invalid email address");
                        return false;
                    }
                    return true;
                } catch(e) {
                    return false;
                }
                return false;
            }
        </script>
    </body>
</html>