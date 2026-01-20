<?php







$name = $_POST['name'];
$phone = $_POST['phone_num'];
$email = $_POST['email_address'];
$address = $_POST['home_address'];
$gender = $_POST['gender'];
$relationship = $_POST['relationship'];
$dob = $_POST['dob'];   

include 'conn.php';


$sql = "INSERT INTO contacts(contact_name, contact_phone, contact_email, contact_address, contact_gender, contact_relationship, contact_dob)
 VALUES ('$name','$phone','$email','$address','$gender','$relationship','$dob')";  


mysqli_query($dbConn, $sql);

if(mysqli_affected_rows($dbConn)<=0){
die("<script>alert('Data Insertion Failed');window.hisstory.go(-1);</script>");
}

echo "<script>alert('Data Insertion Successful');</script>";


echo $name;
echo "<br>";
echo $phone;
echo "<br>";
echo $email;
echo "<br>";
echo $address;
echo "<br>";
echo $gender;
echo "<br>";
echo $relationship;
echo "<br>";
echo $dob;





?>