<?php
require_once ('bootstrap.php');
$errors = [];
$username = $email = $password = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (!isset($_POST['email']) || empty(trim($_POST['email']))) {
        $errors[] = "Email is Required";
    } elseif (!filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    } else {
        $email = htmlspecialchars(trim($_POST['email']));
    }
    if (!isset($_POST['password']) || empty(trim($_POST['password']))) {
        $errors[] = "Password is required";
    } else {
        $password = htmlspecialchars(trim($_POST['password']));
    }
    // no errors

    if (empty($errors)) {
        try {
            $db = new Database("localhost", "railway_system", "root", "");
            $db->query("SELECT id, name, email,role_id, password from passenger
                                 WHERE email =:email;");
            $db->bind(':email', $email);
            $db->execute();
            $user = $db->fetch();
            if ($user) {
                if (password_verify($password, $user['password'])) {
                    $_SESSION['ID'] = $user['id'];
                    $_SESSION['USERNAME'] = $user['name'];
                    $_SESSION['EMAIL'] = $user['email'];
                    $_SESSION['ROLEID'] = $user['role_id'];
                    $_SESSION['Message'] = 'Successfully logIn';
                    header("location: pages/nav.php");
                } else {
                    $errors[] = "Invalid email or Password provided.";
                }
            } else {
                $errors[] = "Invalid email or pwd provided.";
            }
        } catch (PDOException $e) {
            die("Query Failed:" . $e->getMessage());
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
    <?php
    // die(var_dump($_SESSION['SignupMessage']));
    if (isset($_SESSION['SignupMessage'])) {
        echo $_SESSION['SignupMessage'];
        unset($_SESSION['SignupMessage']);
    }
    ?>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
        <h3>Login</h3>
        <!-- <label for="email"></label> -->
        <input type="email" id="email" name="email" placeholder="Enter your email" value="<?php echo $email; ?>"><br><br>
        <input type="password" id="password" name="password" placeholder="Enter your password"><br><br>
        <input type="submit" value="Login">
    </form>
    <p>Don't have an account? <a href="pages/signup.php">Sign Up</a></p>
    <?php
    if (!empty($errors)) {
        foreach ($errors as $err) {
            echo $err;
        }
    }
    ?>
</body>

</html>