<!DOCTYPE html>
<html>
<head>
  <title>Purchase Page</title>
  <link rel="stylesheet" href="./css/purchase.css">
</head>
<body>
  <?php
    // Define a function to sanitize user input
    function sanitize_input($input) {
      $input = trim($input);
      $input = stripslashes($input);
      $input = htmlspecialchars($input);
      return $input;
    }

    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
      // Get the user's name, date of birth, and address
      $name = sanitize_input($_POST['name']);
      $dob = sanitize_input($_POST['dob']);
      $address = sanitize_input($_POST['address']);

      // Validate the user's name
      if (empty($name)) {
        // Display an error message if the user's name is empty
        echo '<p>Please enter your name.</p>';
      } else if (!preg_match('/^[a-zA-Z ]*$/', $name)) {
        // Display an error message if the user's name contains invalid characters
        echo '<p>Please enter a valid name.</p>';
      }

      // Validate the user's date of birth
      if (empty($dob)) {
        // Display an error message if the user's date of birth is empty
        echo '<p>Please enter your date of birth.</p>';
      } else {
        // Calculate the user's age
        $age = date_diff(date_create($dob), date_create('today'))->y;

        if ($age < 18) {
          // Display an alert if the user is under 18 years old
          echo '<script>alert("You must be 18 years or older to complete this transaction.");</script>';
        }
      }

      // Validate the user's address
      if (empty($address)) {
        // Display an error message if the user's address is empty
        echo '<p>Please enter your address.</p>';
      }

      // Retrieve data from local storage
      $allValues = json_decode($_POST['allValues'], true);
      $totalOfAllProducts = $_POST['totalOfAllProducts'];

      // Fill the orders table in the database
      $servername = "localhost";
      $username = "root";
      $password = "";
      $dbname = "store_db";

      // Create connection
      $conn = new mysqli($servername, $username, $password, $dbname);

      // Check connection
      if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
      }

      // Prepare and bind the SQL statement
      $stmt = $conn->prepare("INSERT INTO encomendas (id_utilizador, id_produto, quantidade, preco_total, nome, data_nascimento, morada) VALUES (?, ?, ?, ?, ?, ?, ?)");
      $stmt->bind_param("iiddsss", $id_utilizador, $id_produto, $quantidade, $preco_total, $nome, $data_nascimento, $morada);


      // Set the user ID to 1 for this example
      $id_utilizador = 1;

      // Loop over the allValues object and insert each product into the orders table
      // Loop over the allValues object and insert each product into the orders table
      foreach ($allValues as $productName => $productData) {
        // Get the product ID from the database
        $sql = "SELECT id FROM produtos WHERE nome = '$productName'";
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
          $row = $result->fetch_assoc();
          $id_produto = $row['id'];
        } else {
          // Display an error message if the product ID cannot be found
          echo '<p>Product not found: ' . $productName . '</p>';
          continue;
        }

        // Set the user's name, date of birth, and address
        $nome = $name;
        $data_nascimento = $dob;
        $morada = $address;

        // Set the quantity and total price of the product
        $quantidade = $productData['quantity'];
        $preco_total = $productData['total'];

        // Check if the order already exists in the database
        $sql = "SELECT id FROM encomendas WHERE id_utilizador = $id_utilizador AND id_produto = $id_produto";
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
          // Update the existing order instead of inserting a new one
          $row = $result->fetch_assoc();
          $order_id = $row['id'];
          $stmt = $conn->prepare("UPDATE encomendas SET quantidade = ?, preco_total = ?, nome = ?, data_nascimento = ?, morada = ? WHERE id = ?");
          $stmt->bind_param("ddsssi", $quantidade, $preco_total, $nome, $data_nascimento, $morada, $order_id);
        } else {
          // Insert a new order into the database
          $stmt = $conn->prepare("INSERT INTO encomendas (id_utilizador, id_produto, quantidade, preco_total, nome, data_nascimento, morada) VALUES (?, ?, ?, ?, ?, ?, ?)");
          $stmt->bind_param("iiddsss", $id_utilizador, $id_produto, $quantidade, $preco_total, $nome, $data_nascimento, $morada);
        }

        // Execute the SQL statement
        try {
          $stmt->execute();
        } catch (mysqli_sql_exception $e) {
          // Handle duplicate primary key error
          if ($e->getCode() == 1062) {
            echo '<p>There was an error processing your order. Please try again later.</p>';
          } else {
            throw $e;
          }
        }
      }

      // Close the database connection
      $stmt->close();
      $conn->close();

      // Close the database connection
      // $stmt->close();
      // $conn->close();
    }
  ?>
  <p>Total value: <?php echo $_GET['total_value']; ?></p>
  <form method="POST" action="">
    <label for="name">Name:</label>
    <input type="text" id="name" name="name" required><br>
    <label for="dob">Date of Birth:</label>
    <input type="date" id="dob" name="dob" required><br>
    <label for="address">Address:</label>
    <textarea id="address" name="address" required></textarea><br>
    <input type="hidden" id="allValues" name="allValues">
    <input type="hidden" id="totalOfAllProducts" name="totalOfAllProducts">
    <button type="submit">Complete Transaction</button>
  </form>
  <script>
    // Set the allValues and totalOfAllProducts variables from local storage
    var allValues = JSON.parse(localStorage.getItem('allValues'));
    var totalOfAllProducts = localStorage.getItem('totalOfAllProducts');

    // Set the allValues and totalOfAllProducts hidden input values
    document.getElementById('allValues').value = JSON.stringify(allValues);
    document.getElementById('totalOfAllProducts').value = totalOfAllProducts;

    // Add an event listener to the form submit button
    document.querySelector('form').addEventListener('submit', function(event) {
      // Prevent the form from submitting
      event.preventDefault();

      // Show an alert
      alert('Compra efetuada com sucesso!');
      
      // Submit the form
      this.submit();
      
      // Clear local storage
      localStorage.clear();
    });

  </script>
</body>
</html>

