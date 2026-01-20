<?php
include("conn.php");

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // Delete the record
    $sql = "DELETE FROM contacts WHERE id = $id";
    
    if (mysqli_query($dbConn, $sql)) {
        header("Location: view.php");
        exit();
    } else {
        echo "Error deleting record: " . mysqli_error($dbConn);
    }
} else {
    echo "No ID specified";
}
?>