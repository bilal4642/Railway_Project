<?php
require_once ('../bootstrap.php');
require_once 'nav.php';
isUserLoggedIn('ID');
$errors = [];
$userId = $_SESSION['ID'];
$db = new Database();
$db->query("SELECT * FROM passenger where id = :id;");
$db->bind(':id', $userId);
// $db->execute();
$user = $db->fetch();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty(trim($_POST['name']))) {
        $errors[] = "username is required";
    } else {
        $name = htmlspecialchars(trim($_POST['name']));
    }
    if (empty(trim($_POST['email']))) {
        $errors[] = "Email is Required";
    } elseif (!filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    } else {
        $email = htmlspecialchars(trim($_POST['email']));
        $db->query("SELECT id FROM passenger WHERE email = :email AND id != :id ;");
        $db->bind(':email', $email);
        $db->bind(':id', $userId);
        $users = $db->execute();

        if ($db->rowCount() > 0) {
            $errors[] = 'Email is already in use by some other user';
        }
    }
    $profileImage = $user['profile_image'];
    // die(var_dump($profileImage));
    $file = $fileName = $fileType = $fileSize = $fileTmpName = $fileError = "";
    if (!empty($_FILES['profile_image']['name'])) {
        $file = $_FILES['profile_image'];
        $fileName = $_FILES['profile_image']['name'];
        $fileType = $_FILES['profile_image']['type'];
        $fileSize = $_FILES['profile_image']['size'];
        $fileTmpName = $_FILES['profile_image']['tmp_name'];
        $fileError = $_FILES['profile_image']['error'];

        $fileExt = explode('.', $fileName);
        $fileActualExt = strtolower(end($fileExt));

        $allowedExt = ['png', 'pdf', 'jpeg', 'jpg'];

        if (in_array($fileActualExt, $allowedExt)) {
            if ($fileError === 0) {
                if ($fileSize < IMG_UPLOADSIZE) {
                    $fileNewName = uniqid() . '.' . $fileActualExt;
                    $fileDestination = '../uploads/' . $fileNewName;
                    if (move_uploaded_file($fileTmpName, $fileDestination)) {
                        
                        // die(var_dump($profileImage));
                        if ($profileImage && file_exists('../uploads/' . $profileImage)) {
                            unlink('../uploads/' . $profileImage);
                        }
                        $profileImage = $fileDestination;
                        // die(var_dump($profileImage));
                    } else {
                        $errors[] = "Failed to Upload File";
                    }
                } else {
                    $errors[] =  "File size is too large, 50KB is max";
                }
            } else {
                $errors[] = "There was an error in uploading the file";
            }
        } else {
            $errors[] = "FIle extension is not supported";
        }
    }
    if (empty($errors)) {
        $db->query("UPDATE passenger SET name = :name, email = :email, profile_image = :profile_image WHERE id = :id;");
        $db->bind(':name', $name);
        $db->bind(':email', $email);
        $db->bind(':profile_image', $profileImage);
        $db->bind(':id', $userId);
        $db->execute();
        // $stmt = $pdo->prepare("UPDATE passenger SET name = :name, email = :email, profile_image = :profile_image WHERE id = :id;");
        // $stmt->bindParam(':name', $name, PDO::PARAM_STR);
        // $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        // $stmt->bindParam(':profile_image', $profileImage, PDO::PARAM_STR);
        // $stmt->bindParam(':id', $userId, PDO::PARAM_STR);
        // $stmt->execute();
        header('Location: userprofile.php');
        $_SESSION['UpdateMess'] = "Profile update successfully";
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body>
    <form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="POST" enctype="multipart/form-data">
        <label for="profile_image">Profile_Image</label>
        <input type="file" name="profile_image"><br><br>
        <img src="<?php echo '../uploads/'.$user['profile_image'] ?>" alt="profile Image" width="250px"><br><br>

        <label for="name">Username:</label>
        <input type="text" name="name" value="<?php echo $user['name']; ?>"><br><br>

        <label for="email">Email:</label>
        <input type="email" name="email" value="<?php echo $user['email'] ?>"><br><br>

        <input type="submit" value="Update">
    </form>
    <?php
    if (!empty($errors)) {
        foreach ($errors as $err) {
            echo '<p>' . $err . '</p>';
        }
    }
    ?>
</body>

</html>