<?php
require_once '../bootstrap.php';
require_once 'nav.php';

isUserLoggedIn('ID');

//values get from edit page to retain the trips
$tripId = isset($_GET['id']) ? $_GET['id'] : null;
$fromstation = isset($_GET['fromstation']) ? $_GET['fromstation'] : null;
$tostation = isset($_GET['tostation']) ? $_GET['tostation'] : null;

$db = new Database();
$db->query("SELECT station.id, station.station_name FROM station;");
// $db->execute();
$stations = $db->fetchAll();

$trips = [];

date_default_timezone_set('Asia/Karachi');
if ($_SERVER["REQUEST_METHOD"] == "POST" || (isset($tripId) && isset($fromstation) && isset($tostation))) {
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $fromstation = $_POST["fromstation"];
        $tostation = $_POST["tostation"];
    }
    if ($fromstation === $tostation) {
        $sameStationError = "Source and Destination cannot be same";
    }
    try {
        $db->query("SELECT trip.id, s1.station_name as sou, s2.station_name as des, trip.date_time, trip.total_ac_seat, 
            SUM(CASE WHEN ticket.ticket_type_id=1 THEN 1 ELSE 0 END) AS book_ac_seat,
            trip.ac_seat_price,trip.total_general_seat,
            SUM(CASE WHEN ticket.ticket_type_id = 2 THEN 1 ELSE 0 END) AS book_general_seat,
            trip.general_seat_price, 
            trip.duration_hour,trip.duration_minutes
            FROM trip
            JOIN station as s1 
            on trip.source_id = s1.id
            JOIN station as s2  
            on trip.destination_id = s2.id
            LEFT JOIN ticket
            ON ticket.trip_id=trip.id
            WHERE s1.id = :fromstation AND s2.id = :tostation AND trip.date_time > NOW()
            GROUP BY 
            trip.id, 
            s1.station_name, 
            s2.station_name, 
            trip.date_time, 
            trip.total_ac_seat, 
            trip.ac_seat_price, 
            trip.total_general_seat, 
            trip.general_seat_price, 
            trip.duration_hour,
            trip.duration_minutes;");
        $db->bind(':fromstation', $fromstation);
        $db->bind(":tostation", $tostation);
        $db->execute();
        $results = $db->fetchAll();

        if (empty($results) && $fromstation != $tostation) {
            $NoTripMessage = "No trip is schedual";
        } else {
            $trips = $results;
        }
    } catch (PDOException $e) {
        die("Quer Failed" . $e->getMessage());
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
        <h3>Select Your Trip</h3>
        <label for="fromstation">From</label><br>
        <select name="fromstation" id="fromstation">
            <?php foreach ($stations as $station) : ?>
                <option value="<?php echo htmlspecialchars($station['id']); ?>" <?php echo (isset($fromstation) && $fromstation == $station['id']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($station['station_name']); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <br><br>
        <label for="tostation">To</label><br>
        <select name="tostation" id="tostation">
            <?php foreach ($stations as $station) : ?>
                <option value="<?php echo htmlspecialchars($station['id']); ?>" <?php echo (isset($tostation) && $tostation == $station['id']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($station['station_name']); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <br><br>
        <input type="submit" value="submit">
    </form>
    <?php
    if (isset($_SESSION['TripInsertMess'])) {
        echo $_SESSION['TripInsertMess'];
        unset($_SESSION['TripInsertMess']);
    }
    if (isset($_SESSION['updateMessage'])) {
        echo $_SESSION['updateMessage'];
        unset($_SESSION['updateMessage']);
    }
    ?>
    <?php
    if (isset($_SESSION['deleteMessage'])) {
        echo $_SESSION['deleteMessage'];
        unset($_SESSION['deleteMessage']);
    }
    ?>
    <?php
    if (isset($NoTripMessage)) {
        echo $NoTripMessage;
    }
    ?>
    <?php
    if (isset($sameStationError)) {
        echo $sameStationError;
    }
    ?>
    <h3>Available Trips</h3>
    <table>
        <thead>
            <tr>
                <th>trip id</th>
                <th>Source Name</th>
                <th>Destination Name</th>
                <th>Time</th>
                <th>Total AC Seats</th>
                <th>Booked AC Seats</th>
                <th>AC Seat Price</th>
                <th>Total General Seats</th>
                <th>Booked General Seats</th>
                <th>General Seat Price</th>
                <th>Duration</th>
                <th>Remarks</th>
                <?php if (isset($_SESSION['ROLEID']) && $_SESSION['ROLEID'] == 1) : ?>
                    <th>Edit</th>
                    <th>Delete</th>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($trips as $trip) :  ?>
                <tr>
                    <td><?php echo htmlspecialchars($trip['id']); ?></td>
                    <td><?php echo htmlspecialchars($trip['sou']); ?></td>
                    <td><?php echo htmlspecialchars($trip['des']); ?></td>
                    <td><?php echo htmlspecialchars($trip['date_time']); ?></td>
                    <td><?php echo htmlspecialchars($trip['total_ac_seat']); ?></td>
                    <td><?php echo htmlspecialchars($trip['book_ac_seat']); ?></td>
                    <td><?php echo htmlspecialchars($trip['ac_seat_price']); ?></td>
                    <td><?php echo htmlspecialchars($trip['total_general_seat']); ?></td>
                    <td><?php echo htmlspecialchars($trip['book_general_seat']); ?></td>
                    <td><?php echo htmlspecialchars($trip['general_seat_price']); ?></td>
                    <td><?php echo htmlspecialchars($trip['duration_hour'] . 'hr' . $trip['duration_minutes'] . 'min'); ?></td>
                    <?php if ($trip['total_ac_seat'] == $trip['book_ac_seat'] && $trip['total_general_seat'] == $trip['book_general_seat'] && !empty($trip)) : ?>
                        <td>No ticket is available</td>
                    <?php else : ?>
                        <td><a href="booking.php?id=<?php echo htmlspecialchars($trip['id']) ?>">Booking</a></td>
                    <?php endif; ?>
                    <?php if (isset($_SESSION['ROLEID']) && $_SESSION['ROLEID'] == 1) : ?>
                        <td><a href="edit.php?id=<?php echo $trip['id']  ?> &fromstation=<?php echo $fromstation ?> &tostation=<?php echo $tostation ?>">Edit</a></td>
                        <td><a href="delete.php?id=<?php echo $trip['id'] ?> &fromstation=<?php echo $fromstation ?> &tostation=<?php echo $tostation ?>"" onclick=" return confirm ('Are you sure to delete the trip?');">Delete</a></td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>

</html>