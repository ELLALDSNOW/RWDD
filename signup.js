document.querySelector(".signup-form").addEventListener("submit", function(e) {
  e.preventDefault();


  const fname = document.getElementById("fname").value.trim();
  const lname = document.getElementById("lname").value.trim();
  const username = document.getElementById("uname").value.trim(); 
  const email = document.getElementById("email").value.trim();
  const password = document.getElementById("password").value.trim();
  const country = document.getElementById("country").value;

  let valid = true;
  let missingFields = [];


  if (!fname) { missingFields.push("First Name"); valid = false; }
  if (!lname) { missingFields.push("Last Name"); valid = false; }
  if (!username) { missingFields.push("Username"); valid = false; }
  if (!email) { missingFields.push("Email"); valid = false; }
  if (!password) { missingFields.push("Password"); valid = false; }
  if (!country) { missingFields.push("Country"); valid = false; }

  if (!valid) {
    alert("❌ Please fill out the following fields:\n\n- " + missingFields.join("\n- "));
  } else {
    alert("✅ Signup successful! Redirecting to login...");
    window.location.href = "login.html";
  }
});
