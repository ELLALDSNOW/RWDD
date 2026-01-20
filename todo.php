<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>To Do Page</title>
    <link rel="stylesheet" href="todo.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body>
    <header class="header">




    <div class="left-section">
        
      <a href="personal.php">
        <img src="images/Group 24.png" alt="Hive Logo" class="logo">
      </a>

      <span class="brand">HIVE</span>

      <a href="personal.php"><button class="home-button">HOME</button></a>

    </div>





    <div class="right-section">



      <div class="hamburger" id="hamburger">

        <span></span>

        <span></span>

        <span></span>

      </div>

    </div>

  </header>





  <nav class="dropdown" id="dropdown">

    <a href="organizations.php"><i class="fa-solid fa-user-plus"></i> Join Org</a>

    <a href="index.html"><i class="fa-solid fa-gem"></i> Landing Page</a>

      <a href="organizations.php"><i class="fa-solid fa-building-columns"></i> Organizations</a>

    <a href="profile.php"><i class="fa-solid fa-user"></i> Profile</a>

    <a href="login.php" id="logout-link"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
  </nav>
    <main class="todo-main">
        <div class="todo-header">
            <span>Home &gt; To Do</span>
            <button id="create-todo-page">Create New To-Do Page</button>
        </div>
        <section class="todo-board" id="todo-board"></section>
    </main>
    <script src="todo.js"></script>
</body>
</html>
