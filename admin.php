<!DOCTYPE html>
<html>
<head>
  <title>Admin Page</title>
  <link rel="stylesheet" type="text/css" href="./css/admin.css">
</head>
<body>
  <h1>Welcome, Admin!</h1>
  <p>You have access to the admin features.</p>

  <?php
  // Establish a connection to the database
  $servername = "localhost";
  $username = "root";
  $password = "";
  $dbname = "store_db";

  $conn = mysqli_connect($servername, $username, $password, $dbname);

  // Check connection
  if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
  }

  // Handle form submission to insert new product
  if (isset($_POST['submit_new_product'])) {
    $product_name = $_POST['product_name'];
    $quantity_available = $_POST['quantity_available'];
    $price_per_unit = $_POST['price_per_unit'];

    $sql = "INSERT INTO Produtos (nome, quantidade, preco) VALUES ('$product_name', '$quantity_available', '$price_per_unit')";
    if (mysqli_query($conn, $sql)) {
      echo "New product added successfully.";
    } else {
      echo "Error: " . $sql . "<br>" . mysqli_error($conn);
    }
  }

  // Handle form submission to update existing product
  if (isset($_POST['submit_update_product'])) {
    $product_id = $_POST['product_id'];
    $quantity_available = $_POST['quantity_available'];
    $price_per_unit = $_POST['price_per_unit'];

    $sql = "UPDATE Produtos SET quantidade='$quantity_available', preco='$price_per_unit' WHERE id='$product_id'";
    if (mysqli_query($conn, $sql)) {
      echo "Product updated successfully.";
    } else {
      echo "Error: " . $sql . "<br>" . mysqli_error($conn);
    }
  }

  // Execute a SELECT query to retrieve the orders from the "encomendas" table
  $sqlEnc = "SELECT * FROM encomendas";
  $resultEnc = mysqli_query($conn, $sqlEnc);

  // Loop through the results and display them in a table format
  if (mysqli_num_rows($resultEnc) > 0) {
    echo "<h2>Encomendas</h2>";
    echo "<table>";
    echo "<tr><th>ID</th><th>Nome do cliente</th><th>Morada</th></tr>";
    while($row = mysqli_fetch_assoc($resultEnc)) {
      echo "<tr><td>" . $row["id"] . "</td><td>" . $row["nome"] . "</td><td>" . $row["morada"] . "</td></tr>";
    }
    echo "</table>";
  } else {
    echo "No orders found.";
  }

  // Execute a SELECT query to retrieve the products from the "Produtos" table
  $sqlProd = "SELECT * FROM Produtos";
  $resultProd = mysqli_query($conn, $sqlProd);

  // Loop through the results and display them in a table format
  if (mysqli_num_rows($resultProd) > 0) {
    echo "<h2>Products</h2>";
    echo "<table>";
    echo "<tr><th>ID</th><th>Nome</th><th>Quantidade</th><th>Preco</th></tr>";
    while($row = mysqli_fetch_assoc($resultProd)) {
      echo "<tr><td>" . $row["id"] . "</td><td>" . $row["nome"] . "</td><td>" . $row["quantidade"] . "</td><td>" . $row["preco"] . "</td></tr>";
    }
    echo "</table>";

    // Add form to insert new product
    echo "<h3>Add New Product</h3>";
    echo "<form method='post'>";
    echo "<label for='product_name'>Product Name:</label>";
    echo "<input type='text' id='product_name' name='product_name'><br>";
    echo "<label for='quantity_available'>Quantity Available:</label>";
    echo "<input type='number' id='quantity_available' name='quantity_available'><br>";
    echo "<label for='price_per_unit'>Price per Unit:</label>";
    echo "<input type='text' id='price_per_unit' name='price_per_unit'><br>";
    echo "<input type='submit' name='submit_new_product' value='Add Product'>";
    echo "</form>";

    // Add form to update existing product
    echo "<h3>Update Product</h3>";
    echo "<form method='post'>";
    echo "<label for='product_id'>Product ID:</label>";
    echo "<input type='number' id='product_id' name='product_id'><br>";
    echo "<label for='quantity_available'>Quantity Available:</label>";
    echo "<input type='number' id='quantity_available' name='quantity_available'><br>";
    echo "<label for='price_per_unit'>Price per Unit:</label>";
    echo "<input type='text' id='price_per_unit' name='price_per_unit'><br>";
    echo "<input type='submit' name='submit_update_product' value='Update Product'>";
    echo "</form>";
  } else {
    echo "No products found.";
  }

  // Close the database connection
  mysqli_close($conn);
  ?>
</body>
</html>