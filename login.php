<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $username = $_POST['nome_usuario'];
  $password = $_POST['senha'];
  // show on HTML $username and $password
  echo $username . ' ' . $password;

  // Connect to the database
  $conn = new mysqli('localhost', 'root', '', 'store_db');

  // Check for errors
  if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
  }

  // Prepare the SQL statement
  $stmt = $conn->prepare('SELECT id, funcao FROM Utilizadores WHERE nome = ? AND senha = ?');

  // Bind the parameters
  $stmt->bind_param('ss', $username, $password);

  // Execute the query
  $stmt->execute();

  // Get the result
  $result = $stmt->get_result();

  // Check if the user exists
  if ($result->num_rows == 1) {
    // Get the user's ID and role
    $row = $result->fetch_assoc();
    $user_id = $row['id'];
    $user_role = $row['funcao'];

    // Store the user's ID and role in the session
    $_SESSION['user_id'] = $user_id;
    $_SESSION['user_role'] = $user_role;

    // Redirect to the appropriate page based on the user's role
    if ($user_role == 'admin') {
      header('Location: admin.php');
    } else {
      header('Location: client.php');
    }
    exit();
  } else {
    // Display an error message
    echo 'Invalid username or password.';
  }

  // Close the statement and connection
  $stmt->close();
  $conn->close();
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Login</title>
  <link rel="stylesheet" type="text/css" href="./css/login.css">
</head>
<body>
  <form method="POST">
    <label>Username:</label>
    <input type="text" name="nome_usuario" required><br>
    <label>Password:</label>
    <input type="password" name="senha" required><br>
    <button type="submit">Login</button>
  </form>
</body>
</html>