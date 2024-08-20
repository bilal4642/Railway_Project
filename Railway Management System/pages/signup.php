<?php
require_once '../bootstrap.php';
$errors = [];
$username = $email = $password = $signup_message = $uploadFile ="";
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (empty(trim($_POST['username']))) {
        $errors[] = "username is required";
    } else {
        $username = htmlspecialchars(trim($_POST['username']));
    }
    if (empty(trim($_POST['email']))) {
        $errors[] = "Email is Required";
    } elseif (!filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    } else {
        $email = htmlspecialchars(trim($_POST['email']));
    }
    if (empty(trim($_POST['password']))) {
        $errors[] = "Password is required";
    } else {
        $password = htmlspecialchars(trim($_POST['password']));
    }

    if(isset($_FILES['profileimage']) && $_FILES['profileimage']['error'] == 0){
        $allowedExtension = ['jpg', 'png', 'pdf'];
        $fileName = $_FILES['profileimage']['name'];
        $fileTemp = $_FILES['profileimage']['tmp_name'];
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    }
    // Handle profile image upload
    if (isset($_FILES['profileimage']) && $_FILES['profileimage']['error'] == 0) {
        $allowedExtension = ['jpg', 'png', 'pdf'];
        $fileName = $_FILES['profileimage']['name'];
        $fileTemp = $_FILES['profileimage']['tmp_name'];
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if (!in_array($fileExt, $allowedExtension)) {
            $errors[] = "Invalid file type. Only JPG, PNG, and PDF are allowed.";
        } elseif ($_FILES['profileimage']['size'] > IMG_UPLOADSIZE) {
            $errors[] = "File size must be up to 50 kb.";
        } else {
            $newFileName = uniqid() . '.' . $fileExt;
            $uploadDir = '../uploads/';
            $uploadFile = $uploadDir . $newFileName;

            if (!move_uploaded_file($fileTemp, $uploadFile)) {
                $errors[] = "Error uploading the profile image.";
            }
        }
    } else {
        $errors[] = "Profile image is required.";
    }
    // no errors
    $role_id = 2;  //default 2= user
    $hasshed_password = password_hash($password, PASSWORD_DEFAULT);
    
    if (empty($errors)) {
        try {
            $db = new Database("localhost", "railway_system", "root", "");
            $db->query("INSERT INTO passenger (name, email, password, role_id, profile_image) VALUES (:username, :email, :password, :role_id, :profileimage);");
            $db->bind(':username', $username);
            $db->bind(':email', $email);
            $db->bind(':password', $hasshed_password);
            $db->bind(':role_id', $role_id);
            $db->bind(':profileimage', $uploadFile);
            $db->execute();
            header("Location: ../index.php");
            $_SESSION['SignupMessage'] = "Signup Successfully";
            exit();
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $errors[] = "Username or email already exit";
            }
        }
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
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" enctype="multipart/form-data">
        <h3>SignUp</h3>
        <label for="email"></label>
        <input type="username" id="username" name="username" placeholder="Enter username" value="<?php echo $username; ?>" required><br><br>
        <input type="email" id="email" name="email" placeholder="Enter your email" value="<?php echo $email ?>" required><br><br>
        <input type="password" id="password" name="password" placeholder="Enter your password" required><br><br>
        <label for="profileimage">Profile Image:</label>
        <input type="file" id="profileimage" name="profileimage" accept="image/*" required><br>
        <input type="submit" value="submit">
    </form>
    <p>Already have an account? <a href="../index.php">Login</a></p>
    <?php
    if (!empty($errors)) {
        foreach ($errors as $err) {
            echo $err;
        }
    }
    ?>
</body>

</html>