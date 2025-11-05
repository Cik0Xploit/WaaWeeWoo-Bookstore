<?php
include 'function/connectdb.php';
$result = $conn->query('DESCRIBE categories');
while($row = $result->fetch_assoc()) {
    echo $row['Field'] . ' - ' . $row['Type'] . PHP_EOL;
}
?>
