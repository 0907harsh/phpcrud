<?php
  require_once "pdo.php";
  session_start();
  if ( isset($_POST['Cancel'])) {
    header("Location: index.php");
    return;

}

  if ( isset($_POST['delete']) && isset($_GET['profile_id']) ) {
 
      $sql = "DELETE FROM profile WHERE profile_id = :zip";
      $stmt = $pdo->prepare($sql);
      $stmt->execute(array(':zip' => $_GET['profile_id']));
      $_SESSION['success'] = 'Record deleted';
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
      $_SESSION['error'] = $_GET['profile_id'].'Bad value for profile_id';
      header( 'Location: index.php' ) ;
      return;
  }
?>
<html>
    <head>
        <title>Harsh Gupta Delete Profile</title>
        <?php require_once "head.php"; ?>
    </head>
    <body>
      <div class="container">
        <h1>Deleteing Profile</h1>
          <form method="POST" action="delete.php?profile_id=<?= htmlentities($_GET['profile_id']) ?>" >
          <p>First Name:
          <?= htmlentities($row['first_name']) ?></p>
          <p>Last Name:
          <?= htmlentities($row['last_name']) ?></p>
          <input type="hidden" name="profile_id"
          value=<?=  $_GET['profile_id'] ?> 
          />
          <input type="submit" name="delete" value="Delete">
          <input type="submit" name="Cancel" value="Cancel">
          </p>
          </form>
      </div>
    </body>
</html>
