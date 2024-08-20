<?php
require_once '../bootstrap.php';
require_once 'nav.php';
isUserLoggedIn('ID');
$userId = $_SESSION['ID'];
$db = new Database();
$db->query("SELECT * FROM passenger where id = :id;");
$db->bind(':id', $userId);
// $db->execute();
$user = $db->fetch();;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <?php 
    if(isset($_SESSION['UpdateMess'])){
        echo $_SESSION['UpdateMess'];
        unset ($_SESSION['UpdateMess']);
    }
    ?>
    <h3>User Profile:</h3>
    <img src="<?php echo $user['profile_image'] ?>" alt="Profile Image" width="200px">
    <p>Username: <?php echo $user['name']?></p>
    <p>Email: <?php echo $user['email']?></p>

    <a href="editprofile.php">Edit Profile</a>

</head>
<body>
</body>
</html>