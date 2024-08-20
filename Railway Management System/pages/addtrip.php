<?php
require_once '../bootstrap.php';
require_once 'nav.php';

isUserLoggedIn('ID');
isUserAdmin('REOLEID');

$db = new Database();
$db->query("SELECT station.id, station.station_name FROM station;");
$stations = $db->fetchAll();

$db->query("SELECT id, name from  train;");
// $db->execute();
$train = $db->fetchAll();
$error = [
    'fromStationError' => '',
    'toStationError' => '',
    'trainError' => '',
    'tripDateError' => '',
    'acSeatError' => '',
    'acPriceError' => '',
    'generalSeatError' => '',
    'generalPriceError' => '',
    'durationError' => '',
    'valueError' => '',
    'sourceDestinationError' => ''
];
date_default_timezone_set('Asia/Karachi');

$acseat = $acprice = $generalseat = $generalprice = $duration = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fromstation = $_POST["fromstation"];
    $tostation = $_POST["tostation"];
    $selecttrain = $_POST["selecttrain"];
    $tripdatetime = $_POST["tripdatetime"];
    $acseat = $_POST["acseat"];
    $acprice = $_POST["acprice"];
    $generalseat = $_POST["generalseat"];
    $generalprice = $_POST["generalprice"];
    $durationhour = $_POST["durationhour"];
    $durationminute = $_POST["durationminute"];
    $currentDateTime = date('Y-m-d H:i:s');
    $selectDateTime = date('Y-m-d H:i:s', strtotime($tripdatetime));

    if (empty($fromstation)) {
        $error['fromStationError'] = "Source Station is empty";
    }
    if (empty($tostation)) {
        $error['toStationError'] = "Destination Station is empty";
    }
    if (empty($selecttrain)) {
        $error['trainError'] = "Please select the train";
    }
    if (empty($tripdatetime)) {
        $error['tripDateError'] = "Please select the trip date";
    } elseif ($selectDateTime <= $currentDateTime) {
        $error['tripPastDateError'] = "Please select the future date and time";
    } else {
        $tripdatetime = $_POST["tripdatetime"];
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
    if ((is_numeric($durationhour) && ($durationhour) >= 0)) {
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
    if (!(array_filter($error))) {
        try {
            $db->query("INSERT into trip (date_time, source_id, destination_id, train_id, total_ac_seat, total_general_seat, ac_seat_price, general_seat_price, duration_hour, duration_minutes) VALUES 
        (:tripdatetime, :fromstation, :tostation, :selecttrain, :acseat, :generalseat, :acprice, :generalprice, :durationhour, :durationminute);");
            $db->bind(':tripdatetime', $tripdatetime);
            $db->bind(':fromstation', $fromstation);
            $db->bind(':tostation', $tostation);
            $db->bind(':selecttrain', $selecttrain);
            $db->bind(':acseat', $acseat);
            $db->bind(':generalseat', $generalseat);
            $db->bind(':acprice', $acprice);
            $db->bind(':generalprice', $generalprice);
            $db->bind(':durationhour', $durationhour);
            $db->bind(':durationminute', $durationminute);

            $db->execute();
            $_SESSION['TripInsertMess'] = "Trip Inserted Successfully";
            header('Location: trip.php');
        } catch (Exception $e) {
            die('Query Failed' . $e->getMessage());
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
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
        <h3>Add New Trip</h3>
        <label for="fromstation">From</label><br>
        <select name="fromstation" id="fromstation">
            <?php foreach ($stations as $station) : ?>
                <option value="<?php echo htmlspecialchars($station['id']); ?>" <?php echo (isset($fromstation) && $fromstation == $station['id']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($station['station_name']); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php if (!empty($error['fromStationError'])) :  ?>
            <?php echo $error['fromStationError'] ?>
        <?php endif; ?>
        <br>
        <label for="tostation">To</label><br>
        <select name="tostation" id="tostation">
            <?php foreach ($stations as $station) : ?>
                <option value="<?php echo htmlspecialchars($station['id']); ?>" <?php echo (isset($tostation) && $tostation == $station['id']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($station['station_name']); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php if (!empty($error['toStationError'])) :  ?>
            <?php echo $error['toStationError'] ?>
        <?php endif; ?>

        <?php if (!empty($error['sourceDestinationError'])) :  ?>
            <?php echo $error['sourceDestinationError'] ?>
        <?php endif; ?>
        <br>
        <label for="selecttrain">Train</label><br>
        <select name="selecttrain" id="selecttrain">
            <?php foreach ($train as $tra) : ?>
                <option value="<?php echo htmlspecialchars($tra['id']); ?>" <?php echo (isset($selecttrain) && $selecttrain == $tra['id']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($tra['name']); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php if (!empty($error['trainError'])) :  ?>
            <?php echo $error['trainError'] ?>
        <?php endif; ?>
        <br>
        <label for="tripdatetime">Trip Date Time</label>
        <input type="datetime-local" id="tripdatetime" name="tripdatetime" value="<?php echo $tripdatetime ?>" placeholder="Enter Date" min="<?php echo date('Y-m-d') ?>" required>
        <?php if (!empty($error['tripDateError'])) :  ?>
            <?php echo $error['tripDateError'] ?>
        <?php endif; ?>
        <?php if (!empty($error['tripPastDateError'])) :  ?>
            <?php echo $error['tripPastDateError'] ?>
        <?php endif; ?>
        <br><br>
        <label for="acseat">Total Ac Seats</label>
        <input type="number" id="acseat" name="acseat" value="<?php echo isset($acseat) ? $acseat : ' ' ?>" placeholder="Enter Value" min="1" required>
        <?php if (!empty($error['acSeatError'])) :  ?>
            <?php echo $error['acSeatError'] ?>
        <?php endif; ?>
        <br><br>
        <label for="acprice">Ac Seat Price</label>
        <input type="number" id="acprice" name="acprice" value="<?php echo isset($acprice) ? $acprice : ' ' ?>" placeholder="Enter Price" min="1">
        <?php if (!empty($error['acPriceError'])) :  ?>
            <?php echo $error['acPriceError'] ?>
        <?php endif; ?>
        <br><br>
        <label for="generalseat">Total General Seats</label>
        <input type="number" id="generalseat" name="generalseat" value="<?php echo isset($generalseat) ? $generalseat : ' ' ?>" placeholder="Enter Value" min="1">
        <?php if (!empty($error['generalSeatError'])) :  ?>
            <?php echo $error['generalSeatError'] ?>
        <?php endif; ?>
        <br><br>
        <label for="generalprice">General Seat Price</label>
        <input type="number" id="generalprice" name="generalprice" value="<?php echo isset($generalprice) ? $generalprice : '' ?>" placeholder="Enter Price" min="1">
        <?php if (!empty($error['generalPriceError'])) :  ?>
            <?php echo $error['generalPriceError'] ?>
        <?php endif; ?>
        <br><br>
        <label for="duration">Duration Hour</label>
        <input type="number" id="durationhour" name="durationhour" value="<?php echo isset($durationhour) ? $durationhour : ' ' ?>" placeholder="Enter Duration" min="0">
        <?php if (!empty($error['valueError'])) :  ?>
            <?php echo $error['valueError'] ?>
        <?php endif; ?>
        <br><br>
        <label for="duration">Duration Minute</label>
        <input type="number" id="durationminute" name="durationminute" value="<?php echo isset($durationminute) ? $durationminute : ' ' ?>" min="1" placeholder="Enter Duration" max="59">
        <?php if (!empty($error['durationMinuteGreaterError'])) :  ?>
            <?php echo $error['durationMinuteGreaterError'] ?>
        <?php endif; ?>
        <?php if (!empty($error['EmptyHourMinError'])) :  ?>
            <?php echo $error['EmptyHourMinError'] ?>
        <?php endif; ?>
        <br><br>
        <input type="submit" value="submit">
    </form>
</body>
</html>