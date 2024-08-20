<?php
function isUserLoggedIn(){
    if (!isset($_SESSION['ID'])) {
        header('Location: ../index.php');
        // header('Location:'. BASE_FILE_PATH . '/index.php');
        exit();
    }
}
function isUserAdmin(){
    if (!isset($_SESSION['ROLEID']) || $_SESSION['ROLEID'] != 1) {
        header('Location: ../pages/nav.php');
        exit;
    }

}



