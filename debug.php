<?php
// Test database connection
include 'config.php';

echo "Testing database connection...<br>";

if (!$conn) {
    echo "Database connection failed: " . mysqli_connect_error();
} else {
    echo "Database connected successfully!<br>";
    
    // Test query
    $result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM pengunjung");
    if (!$result) {
        echo "Query failed: " . mysqli_error($conn);
    } else {
        $data = mysqli_fetch_assoc($result);
        echo "Total pengunjung: " . $data['total'] . "<br>";
    }
}
?>