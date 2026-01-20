<?php
  include("conn.php");
?>
<!DOCTYPE html>
<html>
<head>
    <title>View Contacts</title>
    <style>
        table {
            border-collapse: collapse;
            width: 100%;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        button {
            padding: 5px 10px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Phone</th>
                <th>Email</th>
                <th>Address</th>
                <th>Gender</th>
                <th>Relationship</th>
                <th>Date of Birth</th>
                <th>Edit</th>
                <th>Delete</th>
            </tr>
        </thead>
        <tbody>
<?php
  $sql = "SELECT * FROM contacts ORDER BY id";  // Added ORDER BY to see clear ordering
  $result = mysqli_query($dbConn, $sql);
  
  // Debug: Let's see the IDs to verify they are different
  if ($result) {
      echo "<!-- Debug: Showing record IDs -->\n";
      while ($debug_row = mysqli_fetch_assoc($result)) {
          echo "<!-- Record ID: " . $debug_row['id'] . " -->\n";
      }
      // Reset the result pointer to the beginning
      mysqli_data_seek($result, 0);
  }

  if (mysqli_num_rows($result) <= 0) {
    die("<script>alert('No records found in the database.');</script>");
  }

  while ($row = mysqli_fetch_assoc($result)) {
    echo "<tr>";
    // Add ID column for debugging
    echo "<td><small style='color:gray'>ID: " . $row['id'] . "</small><br>" . $row['contact_name'] . "</td>";
    echo "<td>" . $row['contact_phone'] . "</td>";
    echo "<td>" . $row['contact_email'] . "</td>";
    echo "<td>" . $row['contact_address'] . "</td>";
    echo "<td>" . $row['contact_gender'] . "</td>";
    echo "<td>" . $row['contact_relationship'] . "</td>";
    echo "<td>" . $row['contact_dob'] . "</td>";

    // Print the full query string in the button for debugging
    echo  "<td><a href='edit.php?id=" . $row['id'] . "'><button>Edit ID:" . $row['id'] . "</button></a></td>";
    echo  "<td><a href='delete.php?id=" . $row['id'] . "'><button>Delete ID:" . $row['id'] . "</button></a></td>";
  }
?>
        </tbody>
    </table>
</body>
</html>
