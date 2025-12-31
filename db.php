<?php
$serverName = "10.23.1.229"; // SQL Server name
$connectionOptions = [
    "Database" => "AEN_Portal", // SQL Database name
    "Uid" => "sa",
    "PWD" => "Alps4lp!n3",
    "TrustServerCertificate" => true // helps avoid SSL cert issues sometimes
];

$conn = sqlsrv_connect($serverName, $connectionOptions);

if ($conn === false) {
    die(print_r(sqlsrv_errors(), true));
}
?>