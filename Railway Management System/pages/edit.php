<?php
require_once ('../bootstrap.php');
require_once 'nav.php';
isUserLoggedIn('ID');
isUserAdmin('ROLEID');
$db = new Database();
$db->query("SELECT station.id, station.station_name FROM station;");
$db->execute();
$stations = $db->fetchAll();

$tripId = $_GET['id'];
$db->query("SELECT * FROM trip where trip.id = :id");
$db->bind(':id', $tripId);
$db->execute();
$trip = $db->fetch();
if (!$trip) {
    $_SESSION['tripNotMess'] = "Trip not found";
    header('Location: trip.php');
    exit();
}
$error = [
    'fromStationError' => '',
    'toStationError' => '',
    'tripDateError' => '',
    'acSeatError' => '',
    'acPriceError' => '',
    'generalSeatError' => '',
    'generalPriceError' => '',
    'durationError' => '',
    'valueError' => '',
    'sourceDestinationError' => ''
];
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $tripId = $_POST['id'];
    $fromstation = $_POST["fromstation"];
    $tostation = $_POST["tostation"];
    $tripdatetime = $_POST["tripdatetime"];
    $acseat = $_POST["acseat"];
    $acprice = $_POST["acprice"];
    $generalseat = $_POST["generalseat"];
    $generalprice = $_POST["generalprice"];
    $durationhour = $_POST["durationhour"];
    $durationminute = $_POST["durationminute"];
    date_default_timezone_set('Asia/Karachi');
    $currentDateTime = date('Y-m-d H:i:s');
    $selectDateTime = date('Y-m-d H:i:s', strtotime($tripdatetime));
    if (empty($fromstation)) {
        $error['fromStationError'] = "Source Station is empty";
    }
    if (empty($tostation)) {
        $error['toStationError'] = "Destination Station is empty";
    }
    if (empty($tripdatetime)) {
        $error['tripDateError'] = "Please select the trip date";
    } elseif ($selectDateTime < $currentDateTime) {
        $error['tripPastDateError'] = "Please select the future date";
    }
    if (empty($acseat)) {
        $error['acSeatError'] = "Please Enter total seats";
    }
    if (empty($acprice)) {
        $error['acPriceError'] = "Please Enter AC Seat Price";
    }
    if (empty($generalseat)) {
        $error['generalSeatError'] = "Please Enter Total General Seats";
    }
    if (empty($generalprice)) {
        $error['generalPriceError'] = "Please Enter general seat price";
    }
    if ((is_numeric(($durationhour)) && ($durationhour) >= 0)) {
        $durationhour = $_POST["durationhour"];
    }
    if ((is_null(($durationminute))) || (is_numeric(($durationminute)) && ($durationminute) >= 0)) {
        $durationminute = $_POST["durationminute"];
    } elseif (($durationminute) > 59) {
        $error['durationMinuteGreaterError'] = "Duration minutes cannot exceed 59";
    }
    if (empty($durationhour) && (empty($durationminute))) {
        $error['EmptyHourMinError'] = "Hour and Minutes cannot be Empty";
    }
    if ($acseat < 0 || $acprice < 0 || $generalseat < 0 || $generalprice < 0) {
        $error['valueError'] = "Value cannot be negative";
    }
    if ($fromstation === $tostation) {
        $error['sourceDestinationError'] = "Source and Destination cannot be same";
    }
    // $error = ['error1' =>""];
    // die(var_dump($error, empty($error['error1'])));
    // die(var_dump($error));
    if (!array_filter($error)) {
        try {
            $db->query("UPDATE trip SET source_id = :fromstation, destination_id = :tostation, date_time = :tripdatetime, total_ac_seat = :acseat, ac_seat_price = :acprice, total_general_seat = :generalseat, general_seat_price = :generalprice, duration_hour = :durationhour, duration_minutes = :durationminute
         WHERE trip.id = :id;");
            $db->bind(':fromstation', $fromstation);
            $db->bind(':tostation', $tostation);
            $db->bind(':tripdatetime', $tripdatetime);
            $db->bind(':acseat', $acseat);
            $db->bind(':acprice', $acprice);
            $db->bind(':generalseat', $generalseat);
            $db->bind(':generalprice', $generalprice);
            $db->bind(':durationhour', $durationhour);
            $db->bind(':durationminute', $durationminute);
            $db->bind(':id', $tripId);
            $db->execute();
            $_SESSION['updateMessage'] = "Trip record is updated";
            header('Location: trip.php?id=' . $tripId . '&fromstation=' . $fromstation . '&tostation=' . $tostation);
            exit();
        } catch (PDOException $e) {
            die("Query Failed") . $e->getMessage();
            //throw $th;
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
    <h4>Edit the Trip</h4>
    <form action="<?php echo $_SERVER['PHP_SELF'] . '?id=' . $_GET['id'] . '&fromstation=' . $_GET['fromstation'] ?>" method="post">
        <input type="hidden" name="id" value="<?php echo $trip['id'] ?>">
        <?php
        // die(var_dump($trip));
        ?>
        <label for="fromstation">From</label><br>
        <select name="fromstation" id="fromstation">
            <?php foreach ($stations as $stat) : ?>

                <option value="<?php echo ($stat['id']); ?>" <?php echo (isset($fromstation) && $fromstation == $stat['id']) || ($trip['source_id'] == $stat['id']) ? 'selected' : '' ?>>
                    <?php echo htmlspecialchars($stat['station_name']); ?>
                </option>
            <?php endforeach; ?>
        </select><br><br>
        <?php if (!empty($error['fromStationError'])) :  ?>
            <?php echo $error['fromStationError'] ?>
        <?php endif; ?>
        <label for="tostation">To</label><br>
        <select name="tostation" id="tostation">
            <?php foreach ($stations as $stat) : ?>
                <option value="<?php echo $stat['id']; ?>" <?php echo (isset($tostation) && $tostation == $stat['id']) || ($trip['destination_id'] == $stat['id']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($stat['station_name']); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php if (!empty($error['toStationError'])) :  ?>
            <?php echo $error['toStationError'] ?>
        <?php endif; ?>
        <?php if (!empty($error['sourceDestinationError'])) :  ?>
            <?php echo $error['sourceDestinationError'] ?>
        <?php endif; ?><br><br>
        <label for="datetime">Date:</label>
        <input type="datetime-local" id="date" name="tripdatetime" value="<?php echo  isset($tripdatetime) ? $tripdatetime : $trip['date_time'] ?>" min="">
        <?php if (!empty($error['tripDateError'])) :  ?>
            <?php echo $error['tripDateError'] ?>
        <?php endif; ?>
        <?php if (!empty($error['tripPastDateError'])) :  ?>
            <?php echo $error['tripPastDateError'] ?>
        <?php endif; ?><br><br>
        <label for="acseat">Total ac seats:</label>
        <input type="number" id="acseat" name="acseat" min="1" value="<?php echo isset($acseat) ? $acseat : $trip['total_ac_seat'] ?>">
        <?php if (!empty($error['acSeatError'])) :  ?>
            <?php echo $error['acSeatError'] ?>
        <?php endif; ?>
        <br><br>
        <label for="acprice">Ac seat price:</label>
        <input type="number" id="acprice" name="acprice" min="1" value="<?php echo isset($acprice) ? $acprice : $trip['ac_seat_price'] ?>">
        <?php if (!empty($error['acPriceError'])) :  ?>
            <?php echo $error['acPriceError'] ?>
        <?php endif; ?>
        <br><br>
        <label for="generalseat">Total general seats:</label>
        <input type="number" id="generalseat" name="generalseat" min="1" value="<?php echo isset($generalseat) ? $generalseat : $trip['total_general_seat'] ?>">
        <?php if (!empty($error['generalSeatError'])) :  ?>
            <?php echo $error['generalSeatError'] ?>
        <?php endif; ?>
        <br><br>
        <label for="generalprice">General seat price:</label>
        <input type="number" id="generalprice" name="generalprice" min="1" value="<?php echo isset($generalprice) ? $generalprice : $trip['general_seat_price'] ?>">
        <?php if (!empty($error['generalPriceError'])) :  ?>
            <?php echo $error['generalPriceError'] ?>
        <?php endif; ?>
        <br><br>
        <label for="duration">Duration Hour:</label>
        <input type="number" id="durationhour" name="durationhour" min="0" placeholder="Enter duration hour" value="<?php echo isset($durationhour) ? $durationhour : $trip['duration_hour'] ?>">
        <?php if (!empty($error['valueError'])) :  ?>
            <?php echo $error['valueError'] ?>
        <?php endif; ?>
        <br><br>

        <label for="duration">Duration Minutes:</label>
        <input type="number" id="durationminute" name="durationminute" min="1" placeholder="Enter duration Minutes" value="<?php echo isset($durationminute) ? $durationminute : $trip['duration_minutes'] ?>" max="59">
        <?php if (!empty($error['EmptyHourMinError'])) :  ?>
            <?php echo $error['EmptyHourMinError'] ?>
        <?php endif; ?>
        <?php if (!empty($error['durationMinuteGreaterError'])) :  ?>
            <?php echo $error['durationMinuteGreaterError'] ?>
        <?php endif; ?>
        <br><br>
        <input type="submit" value="Update">
    </form>
</body>
</html>