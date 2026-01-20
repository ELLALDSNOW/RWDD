<?php

include 'conn.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = $_POST['uname'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    

    $check_sql = "SELECT email_address FROM user_account WHERE email_address = '$email'";
    $result = mysqli_query($dbConn, $check_sql);
    
    if (mysqli_num_rows($result) > 0) {
        die("<script>alert('Email already registered!');window.history.go(-1);</script>");
    }


    $sql = "INSERT INTO user_account (
                user_name, 
                email_address, 
                password_hash,
                user_type,
                created_time
            ) VALUES (
                '$username',
                '$email',
                '$password',
                'member',
                NOW()
            )";

    $query = mysqli_query($dbConn, $sql);

    if(!$query) {
        $error = mysqli_error($dbConn);
        error_log("Registration failed: " . $error);
        die("<script>alert('Registration Failed: " . $error . "');window.history.go(-1);</script>");
    }

    if(mysqli_affected_rows($dbConn) <= 0) {
        error_log("Registration failed: No rows affected");
        die("<script>alert('Registration Failed');window.history.go(-1);</script>");
    }


    echo "Inserted data:<br>";
    echo "Username: " . $username . "<br>";
    echo "Email: " . $email . "<br>";
    
    echo "<script>
        alert('Registration Successful!');
        window.location.href='login.php';
    </script>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Hive Signup</title>
  <link rel="stylesheet" href="signup.css">
</head>
<body>


  <div class="glow-background" aria-hidden="true">
    <div class="glow-ball glow-1"></div>
    <div class="glow-ball glow-2"></div>
    <div class="glow-ball glow-3"></div>
    <div class="glow-ball glow-4"></div>
    <div class="glow-ball glow-5"></div>
  </div>

  <header class="signup-header">
    <div class="logo-section">
        <a href="index.html">
      <img src="images/Group 24.png" alt="Hive Logo" class="logo">
      </a>
      <span class="brand">HIVE</span>
    </div>
  </header>

  <h1>SIGN UP</h1>

  <main class="signup-container">
    <form id="signupForm" class="signup-form" method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">
      <div class="form-row">
  <div class="form-group">
    <label for="fname"><b>First Name:</b></label>
    <input type="text" id="fname" name="fname" placeholder="First Name" required>
  </div>

  <div class="form-group">
    <label for="lname"><b>Last Name:</b></label>
    <input type="text" id="lname" name="lname" placeholder="Last Name" required>
  </div>
</div>
  <div class="form-group">
    <label for="uname"><b>Username</b>:</label>    
      <input type="text" id="uname" name="uname" placeholder="ImDaGOAT10" required>
      </div>
      <div  class="form-group" >
      <label for="country"><b>Country:</b></label>
<select id="country"  name="country" required >
  <option value="">Select your country</option>
  <option value="afghanistan">Afghanistan</option>
  <option value="albania">Albania</option>
  <option value="algeria">Algeria</option>
  <option value="andorra">Andorra</option>
  <option value="angola">Angola</option>
  <option value="argentina">Argentina</option>
  <option value="armenia">Armenia</option>
  <option value="australia">Australia</option>
  <option value="austria">Austria</option>
  <option value="azerbaijan">Azerbaijan</option>
  <option value="bahamas">Bahamas</option>
  <option value="bahrain">Bahrain</option>
  <option value="bangladesh">Bangladesh</option>
  <option value="barbados">Barbados</option>
  <option value="belarus">Belarus</option>
  <option value="belgium">Belgium</option>
  <option value="belize">Belize</option>
  <option value="benin">Benin</option>
  <option value="bhutan">Bhutan</option>
  <option value="bolivia">Bolivia</option>
  <option value="bosnia">Bosnia and Herzegovina</option>
  <option value="botswana">Botswana</option>
  <option value="brazil">Brazil</option>
  <option value="brunei">Brunei</option>
  <option value="bulgaria">Bulgaria</option>
  <option value="burkina">Burkina Faso</option>
  <option value="burundi">Burundi</option>
  <option value="cambodia">Cambodia</option>
  <option value="cameroon">Cameroon</option>
  <option value="canada">Canada</option>
  <option value="chile">Chile</option>
  <option value="china">China</option>
  <option value="colombia">Colombia</option>
  <option value="costa-rica">Costa Rica</option>
  <option value="croatia">Croatia</option>
  <option value="cuba">Cuba</option>
  <option value="cyprus">Cyprus</option>
  <option value="czech">Czech Republic</option>
  <option value="denmark">Denmark</option>
  <option value="dominican">Dominican Republic</option>
  <option value="ecuador">Ecuador</option>
  <option value="egypt">Egypt</option>
  <option value="el-salvador">El Salvador</option>
  <option value="estonia">Estonia</option>
  <option value="ethiopia">Ethiopia</option>
  <option value="fiji">Fiji</option>
  <option value="finland">Finland</option>
  <option value="france">France</option>
  <option value="gabon">Gabon</option>
  <option value="gambia">Gambia</option>
  <option value="georgia">Georgia</option>
  <option value="germany">Germany</option>
  <option value="ghana">Ghana</option>
  <option value="greece">Greece</option>
  <option value="guatemala">Guatemala</option>
  <option value="haiti">Haiti</option>
  <option value="honduras">Honduras</option>
  <option value="hungary">Hungary</option>
  <option value="iceland">Iceland</option>
  <option value="india">India</option>
  <option value="indonesia">Indonesia</option>
  <option value="iran">Iran</option>
  <option value="iraq">Iraq</option>
  <option value="ireland">Ireland</option>
  <option value="israel">Israel</option>
  <option value="italy">Italy</option>
  <option value="jamaica">Jamaica</option>
  <option value="japan">Japan</option>
  <option value="jordan">Jordan</option>
  <option value="kazakhstan">Kazakhstan</option>
  <option value="kenya">Kenya</option>
  <option value="kuwait">Kuwait</option>
  <option value="kyrgyzstan">Kyrgyzstan</option>
  <option value="laos">Laos</option>
  <option value="latvia">Latvia</option>
  <option value="lebanon">Lebanon</option>
  <option value="lesotho">Lesotho</option>
  <option value="liberia">Liberia</option>
  <option value="libya">Libya</option>
  <option value="lithuania">Lithuania</option>
  <option value="luxembourg">Luxembourg</option>
  <option value="madagascar">Madagascar</option>
  <option value="malawi">Malawi</option>
  <option value="malaysia">Malaysia</option>
  <option value="maldives">Maldives</option>
  <option value="mali">Mali</option>
  <option value="malta">Malta</option>
  <option value="mexico">Mexico</option>
  <option value="moldova">Moldova</option>
  <option value="monaco">Monaco</option>
  <option value="mongolia">Mongolia</option>
  <option value="montenegro">Montenegro</option>
  <option value="morocco">Morocco</option>
  <option value="mozambique">Mozambique</option>
  <option value="myanmar">Myanmar</option>
  <option value="namibia">Namibia</option>
  <option value="nepal">Nepal</option>
  <option value="netherlands">Netherlands</option>
  <option value="new-zealand">New Zealand</option>
  <option value="nicaragua">Nicaragua</option>
  <option value="niger">Niger</option>
  <option value="nigeria">Nigeria</option>
  <option value="north-korea">North Korea</option>
  <option value="north-macedonia">North Macedonia</option>
  <option value="norway">Norway</option>
  <option value="oman">Oman</option>
  <option value="pakistan">Pakistan</option>
  <option value="panama">Panama</option>
  <option value="paraguay">Paraguay</option>
  <option value="peru">Peru</option>
  <option value="philippines">Philippines</option>
  <option value="poland">Poland</option>
  <option value="portugal">Portugal</option>
  <option value="qatar">Qatar</option>
  <option value="romania">Romania</option>
  <option value="russia">Russia</option>
  <option value="rwanda">Rwanda</option>
  <option value="saudi-arabia">Saudi Arabia</option>
  <option value="senegal">Senegal</option>
  <option value="serbia">Serbia</option>
  <option value="singapore">Singapore</option>
  <option value="slovakia">Slovakia</option>
  <option value="slovenia">Slovenia</option>
  <option value="somalia">Somalia</option>
  <option value="south-africa">South Africa</option>
  <option value="south-korea">South Korea</option>
  <option value="spain">Spain</option>
  <option value="sri-lanka">Sri Lanka</option>
  <option value="sudan">Sudan</option>
  <option value="suriname">Suriname</option>
  <option value="sweden">Sweden</option>
  <option value="switzerland">Switzerland</option>
  <option value="syria">Syria</option>
  <option value="taiwan">Taiwan</option>
  <option value="tajikistan">Tajikistan</option>
  <option value="tanzania">Tanzania</option>
  <option value="thailand">Thailand</option>
  <option value="togo">Togo</option>
  <option value="trinidad">Trinidad and Tobago</option>
  <option value="tunisia">Tunisia</option>
  <option value="turkey">Turkey</option>
  <option value="turkmenistan">Turkmenistan</option>
  <option value="uganda">Uganda</option>
  <option value="ukraine">Ukraine</option>
  <option value="uae">United Arab Emirates</option>
  <option value="uk">United Kingdom</option>
  <option value="usa">United States</option>
  <option value="uruguay">Uruguay</option>
  <option value="uzbekistan">Uzbekistan</option>
  <option value="venezuela">Venezuela</option>
  <option value="vietnam">Vietnam</option>
  <option value="yemen">Yemen</option>
  <option value="zambia">Zambia</option>
  <option value="zimbabwe">Zimbabwe</option>
</select>
</div>

      <div  class="form-group">
          <label for="email"><b>Email</b>:</label>
      <input type="email" id="email" name="email" placeholder="messi@goat.com" required>
      </div>
      <div class="form-group">
      <label for="password"><b>Password</b>:</label>      
      <input type="password" id="password" name="password" placeholder=".................." required>
      </div>

      

      <div class="form-actions">
        <button type="submit" class="next-btn">Register</button>
      </div>  
    </form>
  </main>

</body>
</html>
