<?php
require_once ('../bootstrap.php');
isUserLoggedIn('ID');
isUserAdmin('ROLEID');

$fromstation = isset($_GET['fromstation'])? $_GET['fromstation'] : null;
$tostation = isset($_GET['tostation'])? $_GET['tostation'] : null;

if($_SERVER['REQUEST_METHOD'] == "GET" && isset($_GET['id'])){
    $tripId = $_GET['id'];

    try {
        $db = new Database();
        $db->query("DELETE FROM trip 
               WHERE trip.id = :id");
        $db->bind(':id', $tripId);       
        $db->execute();
        $_SESSION['deleteMessage'] = "Trip Deleted Successfully";
        header('Location: trip.php?id=' .$tripId .'&fromstation='. $fromstation . '&tostation=' .$tostation);
        // echo $_SESSION['deleteMessage'];

    } catch (PDOException $e) {
        die("Query Failed") . $e->getMessage();
    }
}
?>