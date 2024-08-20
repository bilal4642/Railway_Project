<?php
require_once('../bootstrap.php');
require_once 'nav.php';
isUserLoggedIn('ID');

$passenger_id = $_SESSION['ID'];
$roleId = $_SESSION['ROLEID'];

$db = new Database();
$db->query("SELECT station.id, station.station_name FROM station;");
// $db->execute();
$stations = $db->fetchAll();

$sortby = isset($_GET['sortby']) ? $_GET['sortby'] : 'trip_Id';
$orderby = isset($_GET['orderby']) ? $_GET['orderby'] : 'ASC';

$fromstationId = $tostationId = null;
$fromstationId = isset($_GET['fromstation']) ? $_GET['fromstation'] : null;
$tostationId = isset($_GET['tostation']) ? $_GET['tostation'] : null;


if ($fromstationId !== null && $tostationId !== null && $fromstationId == $tostationId) {
    $error_message = 'Source and destination cannot be the same.';
} else {
    try {
        //code...
        $query = "SELECT ticket.passenger_id AS user_Id,trip.id as trip_Id, trip.train_id AS train_No , s1.station_name as source, s2.station_name as destination, ticket_type.ticket_type_name AS Seat_Type,
     CASE 
       WHEN ticket_type.ticket_type_name ='AC' THEN trip.ac_seat_price
       WHEN ticket_type.ticket_type_name = 'General' THEN trip.general_seat_price
       ELSE null
       END AS ticket_price,
       trip.duration_hour, trip.duration_minutes
     FROM trip
     INNER JOIN station as s1
     on s1.id = trip.source_id
     INNER JOIN station as s2
     on s2.id = trip.destination_id
     INNER JOIN ticket
     on ticket.trip_id = trip.id
     INNER JOIN ticket_type
     ON ticket.ticket_type_id = ticket_type.id
     WHERE 1 = 1 ";
        if ($roleId != 1) {
            $query .= " AND ticket.passenger_id = :ID";
        }
        if ($fromstationId != null) {
            $query .= " AND trip.source_id = :fromstation";
        }
        if ($tostationId != null) {
            $query .= " AND trip.destination_id = :tostation";
        }
        $query .= " ORDER BY $sortby $orderby";
        $db->query($query);
        if ($roleId != 1) {
            $db->bind(':ID', $passenger_id);
        }
        if ($fromstationId != null) {
            $db->bind(':fromstation', $fromstationId);
        }
        if ($tostationId != null) {
            $db->bind(':tostation', $tostationId);
        }
        // $db->execute();
        $tickets = $db->fetchAll();
        // die(var_dump($tickets));
    } catch (PDOException $e) {
        die("Query Failed") . $e->getMessage();
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
    <h3>User Bookings</h3>
    <form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="get">
        <label for="sortby">Sort by:</label>
        <select name="sortby" id="sortby">
            <option value="user_Id" <?php echo isset($_GET['sortby']) && $_GET['sortby'] == 'user_Id' ? 'selected' : ''; ?>>User Id</option>
            <option value="trip_Id" <?php echo isset($_GET['sortby']) && $_GET['sortby'] == 'trip_Id' ? 'selected' : ''; ?>>Trip Id</option>
            <option value="train_No" <?php echo isset($_GET['sortby']) && $_GET['sortby'] == 'train_No' ? 'selected' : ''; ?>>Train No</option>
            <option value="source" <?php echo isset($_GET['sortby']) && $_GET['sortby'] == 'source' ? 'selected' : ''; ?>>Source</option>
            <option value="destination" <?php echo isset($_GET['sortby']) && $_GET['sortby'] == 'destination' ? 'selected' : ''; ?>>Destination</option>
            <option value="Seat_Type" <?php echo isset($_GET['sortby']) && $_GET['sortby'] == 'Seat_Type' ? 'selected' : ''; ?>>Seat Type</option>
            <option value="ticket_price" <?php echo isset($_GET['sortby']) && $_GET['sortby'] == 'ticket_price' ? 'selected' : ''; ?>>Ticket Price</option>
            <option value="duration_hour" <?php echo isset($_GET['sortby']) && $_GET['sortby'] == 'duration_hour' ? 'selected' : ''; ?>>Duration</option>
        </select>
        <label for="orderby">Order:</label>
        <select name="orderby" id="orderby">
            <option value="ASC" <?php echo isset($_GET['orderby']) && $_GET['orderby'] == 'ASC' ? 'selected' : ''; ?>>Ascending</option>
            <option value="DESC" <?php echo isset($_GET['orderby']) && $_GET['orderby'] == 'DESC' ? 'selected' : ''; ?>>Descending</option>
        </select>
        <input type="submit" value="Sort">
    </form>
    <!-- filter data-->
    <form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="get">
        <label for="fromstation">Source</label><br>
        <select name="fromstation" id="fromstation">
            <?php foreach ($stations as $station) : ?>
                <option value="<?php echo htmlspecialchars($station['id']); ?>"
                    <?php echo $fromstationId == $station['id'] ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($station['station_name']); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <br><br>
        <label for="tostation">To</label><br>
        <select name="tostation" id="tostation">
            <?php foreach ($stations as $station) : ?>
                <option value="<?php echo htmlspecialchars($station['id']); ?>"
                    <?php echo $tostationId == $station['id'] ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($station['station_name']); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <br><br>
        <input type="submit" value="filter">
    </form>
    <!-- No trips -->
    <?php if (isset($error_message)): ?>
        <p>Source and Destination cannot same:</p>

    <?php elseif (empty($tickets)): ?>
        <p>No bookings found for the selected trips:</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>User Id</th>
                    <th>Trip Id</th>
                    <th>Train No</th>
                    <th>Source</th>
                    <th>Destination</th>
                    <th>Seat Type</th>
                    <th>Ticket Price</th>
                    <th>Date</th>
                    <th>Duration</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tickets as $tick) :  ?>
                    <tr>
                        <td><?php echo $tick['user_Id']; ?></td>
                        <td><?php echo $tick['trip_Id']; ?></td>
                        <td><?php echo $tick['train_No']; ?></td>
                        <td><?php echo $tick['source']; ?></td>
                        <td><?php echo $tick['destination']; ?></td>
                        <td><?php echo $tick['Seat_Type']; ?></td>
                        <td><?php echo $tick['ticket_price']; ?></td>
                        <td><?php echo $tick['duration_hour']; ?></td>
                        <td><?php echo $tick['duration_hour'] . "hr" . $tick['duration_minutes'] . "min"; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</body>
</html>