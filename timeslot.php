<?php
session_start();
include 'conn.php';
include 'nav.php';

$ground_id = $_GET['ground_id'];
$selectedDate = $_GET['selectedDate'];

// Generate the time slots (from 8 AM to 8 PM, with an interval of 1 hour)
$timeSlots = array();
$startHour = 8;
$endHour = 20;
for ($hour = $startHour; $hour <= $endHour; $hour++) {
    $timeSlots[] = date('H:i:s', strtotime($hour . ':00'));

}

// Fetch the booked time slots from the database for the selected date and ground
$query = "SELECT booking_time,status FROM booking WHERE ground_id = $ground_id AND booking_date = '$selectedDate'";
$result = mysqli_query($con, $query);
$bookedTimeSlots = array();
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $bookedTimeSlots[] = $row;
        $status = $row['status'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Time Slots</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            color: #333;
        }

        .container {
            width: 80%;
            margin: 0 auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            font-family: 'Gill Sans', 'Gill Sans MT', Calibri, 'Trebuchet MS', sans-serif;
            text-align: center;
            color: #333;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 12px;
            text-align: center;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #f2f2f2;
            color: #333;
        }

        .available {
            color: green;
        }

        .booked {
            color: red;
        }

        .pending {
            color: orange;
        }

        .btn-pay {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .btn-pay:hover {
            background-color: #45a049;
        }

        .status-text {
            display: inline-block;
            margin-right: 10px;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Time Slots for <?php echo htmlspecialchars($selectedDate); ?></h1>
        <table>
            <tr>
                <th>Time Slot</th>
                <th>Status</th>
            </tr>
            <?php
            date_default_timezone_set('Asia/Kathmandu');

            $currentDate = date('Y-m-d');

            foreach ($timeSlots as $timeSlot) {
                $isCurrentDate = ($selectedDate === $currentDate);

                $status = 'Available';
                if ($isCurrentDate) {
                    $currentTime = date('H:i:s');
                    if ($timeSlot < $currentTime) {
                        $status = 'Not Available';
                    }
                }

                foreach ($bookedTimeSlots as $bookedTimeSlot) {
                    if ($bookedTimeSlot['booking_time'] === $timeSlot && $bookedTimeSlot['status'] === 'Verified') {
                        $status = 'Booked';
                    } elseif ($bookedTimeSlot['booking_time'] === $timeSlot && $bookedTimeSlot['status'] === 'pending') {
                        $status = 'Pending';
                        break;
                    }
                }

                $class = '';
                if ($status === 'Available') {
                    $class = 'available';
                } elseif ($status === 'Booked') {
                    $class = 'booked';
                } elseif ($status === 'Pending') {
                    $class = 'pending';
                }

                $button = '';
                if ($status === 'Available') {
                    $button = '<a class="btn-pay" href="payment.php?ground_id=' . $ground_id . '&selectedDate=' . urlencode($selectedDate) . '&selectedTimeSlot=' . urlencode($timeSlot) . '">Book Now</a>';
                } elseif ($status === 'Pending') {
                    $button = ''; // Empty button for Pending status
                }

                echo "<tr><td>$timeSlot</td><td class=\"$class\"><span class=\"status-text\">$status</span>$button</td></tr>";
            }
            ?>
        </table>
    </div>
    <?php include 'footer.php'; ?>
</body>

</html>