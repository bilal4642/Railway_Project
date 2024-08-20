<?php

    session_start();
    session_unset();
    session_destroy();
    // echo "Logout Successfully";
    header('Location: http://localhost/Railway%20Management%20System/');
    exit();
    

?>