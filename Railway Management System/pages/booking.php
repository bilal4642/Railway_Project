<?php
require_once('../bootstrap.php');
require_once 'nav.php';

isUserLoggedIn('ID');
$loginId = $_SESSION['ID'];
$passenger_Id = $tripId = $ticket_type_id = $available_seats = '';
$id  = [];
if (isset($_GET['id'])) {
    $tripId = $_GET['id'];
} else {
    header('Location: trip.php');
}
if (isset($_SESSION['ID'])) {
}
$passenger_Id = $_SESSION['ID'];

$db = new Database();
$db->query("SELECT trip.id, trip.total_ac_seat,trip.total_general_seat, trip.ac_seat_price, trip.general_seat_price,
SUM(CASE WHEN ticket.ticket_type_id=1 THEN 1 ELSE 0 END) AS book_ac_seat,
        trip.ac_seat_price,trip.total_general_seat,
SUM(CASE WHEN ticket.ticket_type_id = 2 THEN 1 ELSE 0 END) AS book_general_seat
FROM trip
        JOIN station as s1
        ON trip.source_id = s1.id
        JOIN station as s2
        ON trip.destination_id = s2.id
        LEFT JOIN ticket
        ON trip.id = ticket.trip_id
        WHERE trip.id = :id
        GROUP BY trip.id, trip.total_ac_seat, trip.total_general_seat, trip.ac_seat_price, trip.general_seat_price;");
$db->bind(':id', $tripId);
// $db->execute();
$ticket = $db->fetchAll();
// die(var_dump($ticket));
if (empty($ticket)) {
    header('Location: trip.php');
}
//ticket_type
$db->query("SELECT id, ticket_type_name FROM ticket_type;");
// $db->execute();
$ticket_type = $db->fetchAll();
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $passenger_Id = $_SESSION['ID'];        //1
    if (isset($_POST['tripId'])) {
        $tripId = $_POST['tripId'];
    }
    if (isset($_POST['ticket'])) {
        $ticket_type_id = $_POST['ticket'];
        foreach ($ticket as $tic)
            if ($ticket_type_id == 1) {
                if (isset($tic['total_ac_seat'])) {
                    $available_seats = $tic['total_ac_seat'] - $tic['book_ac_seat'];
                }
                // die(var_dump($tic['total_ac_seat']));
            } elseif ($ticket_type_id == 2) {
                if (isset($tic['total_general_seat']) && isset(($tic['book_general_seat']))) {
                    $available_seats = $tic['total_general_seat'] - $tic['book_general_seat'];
                }
            }
        if (isset($available_seats)) {
            if ($available_seats > 0) {
                try {
                    $db->query("INSERT INTO ticket (trip_id, passenger_id, ticket_type_id) VALUES
                     (:trip_id, :passenger_id, :ticket_type_id);");
                    $db->bind(':trip_id', $tripId);
                    $db->bind(':passenger_id', $passenger_Id);
                    $db->bind(':ticket_type_id', $ticket_type_id);
                    $db->execute();
                    $_SESSION['BookMessage'] = 'Ticket Book Successfully';
                    header('Location: bookingrecord.php');
                } catch (PDOException $e) {
                    die("Query Failed:" . $e->getMessage());
                }
            }
        } else {
        }
        echo "Seat not Available";
    }
    if ($available_seats < 0) {
        if (isset($_SESSION['ID']) && isset($_SESSION['BookMessage'])) {
            echo $_SESSION['BookMessage'];
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
    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']) . '?id=' . $_GET['id'] ?>" method="POST">
        <h2>Book Your Ticket</h2>
        <?php foreach ($ticket_type as $type) : ?>
            <?php foreach ($ticket as $tick) ?>

            <?php if ($type['ticket_type_name'] === 'Ac' && $tick['book_ac_seat'] < 10):  ?>
                <input type="radio" id="<?php echo ($type['ticket_type_name']); ?>" name="ticket" value="<?php echo ($type['id']) ?>">
                <label for="<?php echo ($type['ticket_type_name']); ?>">
                    <?php echo ($type['ticket_type_name']); ?>
                </label>
                <span>Price: <?php echo htmlspecialchars($tick['ac_seat_price']); ?></span>
            <?php elseif ($type['ticket_type_name'] === 'General' && $tick['book_general_seat'] < 10) : ?>
                <input type="radio" id="<?php echo ($type['ticket_type_name']); ?>" name="ticket" value="<?php echo ($type['id']) ?>">
                <label for="<?php echo ($type['ticket_type_name']); ?>">
                    <?php echo ($type['ticket_type_name']); ?>
                </label>
                <span>Price: <?php echo htmlspecialchars($tick['general_seat_price']); ?></span>

            <?php endif; ?>
        <?php endforeach; ?>
        <br><input type="submit">
    
    </form>
</body>

</html>