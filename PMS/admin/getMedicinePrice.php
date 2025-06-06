<?php
include '../dbconn.php';

$medicine_id = $_GET['medicine_id'];

// Fetch medicine price
$query = "SELECT price, quantity FROM medicines WHERE medicine_id = $medicine_id";
$result = $conn->query($query);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    echo json_encode(['price' => $row['price'], 'quantity' => $row['quantity']]);
} else {
    echo json_encode(['price' => null]);
}

$conn->close();
?>