<?php
class users{
    private $userId;
    private $password;

    public function updatePwd($userId, $password) {
        echo "Function to update password". $userId . "for password". $password;

    }
}

$obj  = new users();
$obj->updatePwd("bilal", "12345");


?>