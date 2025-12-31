<?php
include 'db.php'; // your SQL Server connection file

header('Content-Type: application/json');

// Get message from frontend
$userMessage = strtolower(trim($_POST['message']));

// Search for matching keyword
$sql = "SELECT TOP 1 Response FROM ChatResponses WHERE LOWER(Keyword) = ?";
$params = array($userMessage);
$stmt = sqlsrv_query($conn, $sql, $params);

$responseText = "Sorry, I don’t understand that yet. 🤖";

if ($stmt && $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $responseText = $row['Response'];
}

echo json_encode(["response" => $responseText]);
sqlsrv_close($conn);
?>
