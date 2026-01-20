<?php
include("conn.php");

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
 
    $sql = "SELECT * FROM contacts WHERE id = $id";
    $result = mysqli_query($dbConn, $sql);
    $row = mysqli_fetch_assoc($result);
    
    if (!$row) {
        die("Record not found");
    }
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];
    $name = $_POST['contact_name'];
    $phone = $_POST['contact_phone'];
    $email = $_POST['contact_email'];
    $address = $_POST['contact_address'];
    $gender = $_POST['contact_gender'];
    $relationship = $_POST['contact_relationship'];
    $dob = $_POST['contact_dob'];
    
    $sql = "UPDATE contacts SET 
            contact_name = '$name',
            contact_phone = '$phone',
            contact_email = '$email',
            contact_address = '$address',
            contact_gender = '$gender',
            contact_relationship = '$relationship',
            contact_dob = '$dob'
            WHERE id = $id";
            
    if (mysqli_query($dbConn, $sql)) {
        header("Location: view.php");
        exit();
    } else {
        echo "Error updating record: " . mysqli_error($dbConn);
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Contact</title>
    <style>
        form {
            max-width: 500px;
            margin: 20px auto;
            padding: 20px;
            border: 1px solid #ddd;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
        }
        input[type="text"],
        input[type="email"],
        input[type="date"] {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        input[type="submit"] {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <form method="POST">
        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
        
        <div class="form-group">
            <label>Name:</label>
            <input type="text" name="contact_name" value="<?php echo $row['contact_name']; ?>" required>
        </div>
        
        <div class="form-group">
            <label>Phone:</label>
            <input type="text" name="contact_phone" value="<?php echo $row['contact_phone']; ?>" required>
        </div>
        
        <div class="form-group">
            <label>Email:</label>
            <input type="email" name="contact_email" value="<?php echo $row['contact_email']; ?>" required>
        </div>
        
        <div class="form-group">
            <label>Address:</label>
            <input type="text" name="contact_address" value="<?php echo $row['contact_address']; ?>" required>
        </div>
        
        <div class="form-group">
            <label>Gender:</label>
            <input type="text" name="contact_gender" value="<?php echo $row['contact_gender']; ?>" required>
        </div>
        
        <div class="form-group">
            <label>Relationship:</label>
            <input type="text" name="contact_relationship" value="<?php echo $row['contact_relationship']; ?>" required>
        </div>
        
        <div class="form-group">
            <label>Date of Birth:</label>
            <input type="date" name="contact_dob" value="<?php echo $row['contact_dob']; ?>" required>
        </div>
        
        <input type="submit" value="Update Contact">
    </form>
</body>
</html>