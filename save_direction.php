<?php
include 'db.php';

if (isset($_GET['dir'])) {
    $dir = $_GET['dir'];
    $sql = "UPDATE robot_control SET direction='$dir' WHERE id=1";
    
    if ($conn->query($sql) === TRUE) {
        echo "Success";
    } else {
        echo "Error: " . $conn->error;
    }
}
$conn->close();
?>
