<?php
include 'db.php';

if (isset($_POST['spoken_text'])) {
    $spoken_text = $conn->real_escape_string($_POST['spoken_text']);
    $sql = "INSERT INTO speech_data (spoken_text) VALUES ('$spoken_text')";
    
    if ($conn->query($sql) === TRUE) {
        echo "Saved";
    } else {
        echo "Error: " . $conn->error;
    }
}
$conn->close();
?>
