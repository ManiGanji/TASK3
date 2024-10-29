<?php
$host = 'localhost:3307';
$db = 'imageannotationsdb';
$user = 'root';
$pass = '';
$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$imageId = $_GET['image_id'];

$stmt = $conn->prepare("SELECT * FROM annotations WHERE image_id = ?");
$stmt->bind_param("i", $imageId);
$stmt->execute();
$result = $stmt->get_result();

$annotations = [];
while ($row = $result->fetch_assoc()) {
    $annotations[] = $row;
}
echo json_encode($annotations);

$stmt->close();
$conn->close();
?>
