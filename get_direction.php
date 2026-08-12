<?php
include 'db.php';

$sql = "SELECT direction FROM robot_control WHERE id = 1";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    echo $row['direction'];
} else {
    echo "stop";
}

$conn->close();
?>
