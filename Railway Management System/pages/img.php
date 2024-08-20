<?php
$file = $fileName = $fileTmpName = $fileSize = $fileError = $fileType = '';
if (isset($_POST['submit'])) {
    $file = $_FILES['file'];
    // print_r($file);
    $fileName = $_FILES['file']['name'];
    $fileTmpName = $_FILES['file']['tmp_name'];
    $fileSize = $_FILES['file']['size'];
    $fileError = $_FILES['file']['error'];
    $fileType = $_FILES['file']['type'];

    $fileExt = explode('.' , $fileName);
    $fileActualExt = strtolower(end($fileExt));

    $allowed = array('jpg', 'pdf', 'png', 'jpeg');

    if(in_array($fileActualExt, $allowed)){
        if($fileError === 0 ){
            if($fileSize < 5000000){
                $fileNewName = uniqid('', true). "." .$fileActualExt;
                $fileDestination = '../uploads/' . $fileNewName;
                move_uploaded_file($fileTmpName, $fileDestination);
                echo "Image Upload Successfully";
            }else{
                echo "File size is too big";
            }
        }else{
            echo "There was an error uplaoding the file";
        }
    } else{
        echo "File Type is not supported";
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

        <input type="file" name="file"><br><br>
        <button type="submit" name="submit">Upload</button>
    </form>
</body>

</html>