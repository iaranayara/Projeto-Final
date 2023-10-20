<?php
  session_start();

  // Check if the user is logged in
  if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'cliente') {
    // Redirect to the login page
    header('Location: login.php');
    exit();
  }

  // Connect to the database
  $conn = new mysqli('localhost', 'root', '', 'store_db');

  // Check for errors
  if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
  }

  // Handle form submission
  if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get the selected products and quantities
    $selected_products = $_POST['product'];
    $selected_quantities = $_POST['quantity'];

    // Check if all selected quantities are zero or
    $all_zero_or_blank = true;
    foreach ($selected_quantities as $quantity) {
      if ($quantity != 0 && $quantity != '') {
        $all_zero_or_blank = false;
        break;
      }
    }

    if ($all_zero) {
      // Display an error message if all selected quantities are zero
      echo '<p>Please select at least one product.</p>';
    } else {
      // Check for blank inputs and set them to zero
      foreach ($selected_quantities as $index => $quantity) {
        if ($quantity == '') {
          $selected_quantities[$index] = 0;
        }
      }

      // Check if the user input is greater than the quantity
      $invalid_input = false;
      foreach ($selected_quantities as $index => $quantity) {
        $product_id = $selected_products[$index];
        $stmt = $conn->prepare('SELECT quantidade FROM produtos WHERE id = ?');
        $stmt->bind_param('i', $product_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stock = $row['quantidade'];
        if ($quantity > $stock) {
          $invalid_input = true;
          break;
        }
      }

      if ($invalid_input) {
        // Display an error message if the user input is greater than the quantity
        echo '<p>The quantity for one or more products is greater than the available stock.</p>';
      } else {
        // Calculate the total purchase value
        $total_value = 0;
        foreach ($selected_products as $index => $product_id) {
          $quantity = $selected_quantities[$index];
          $stmt = $conn->prepare('SELECT preco FROM produtos WHERE id = ?');
          $stmt->bind_param('i', $product_id);
          $stmt->execute();
          $result = $stmt->get_result();
          $row = $result->fetch_assoc();
          $price = $row['preco'];
          $total_value += $price * $quantity;
        }

        // Update the product quantities in the database
        foreach ($selected_products as $index => $product_id) {
          $quantity = $selected_quantities[$index];
          $stmt = $conn->prepare('UPDATE produtos SET quantidade = quantidade - ? WHERE id = ?');
          $stmt->bind_param('ii', $quantity, $product_id);
          $stmt->execute();
        }

        // Redirect to a new page to prevent form resubmission
        header('Location: purchase.php?total_value=' . $total_value);
        exit();
      }
    }
  }

  // Prepare the SQL statement
  $stmt = $conn->prepare('SELECT * FROM produtos');

  // Execute the query
  $stmt->execute();

  // Get the result
  $result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
  <title>Client Page</title>
  <link rel="stylesheet" type="text/css" href="./css/client.css">
</head>
<body>
  <form method="POST">
    <table>
      <thead>
        <tr>
          <th>Name</th>
          <th>Description</th>
          <th>Price</th>
          <th>Quantity</th>
          <th>Select</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($row = $result->fetch_assoc()) { ?>
          <tr>
            <td><?php echo $row['nome']; ?></td>
            <td><?php echo $row['descricao']; ?></td>
            <td><?php echo $row['preco']; ?></td>
            <td><?php echo $row['quantidade']; ?></td>
            <td>
              <?php if ($row['quantidade'] > 0) { ?>
                <input type="number" name="quantity[]" min="0" max="<?php echo $row['quantidade']; ?>">
              <?php } else { ?>
                <p>No stock available</p>
              <?php } ?>
              <input type="hidden" name="product[]" value="<?php echo $row['id']; ?>">
            </td>
          </tr>
        <?php } ?>
      </tbody>
    </table>
    <button type="submit">Finaliar Compra</button>
  </form>
  <?php if (isset($_GET['total_value'])) { ?>
    <p>Purchase completed. Total value: <?php echo $_GET['total_value']; ?></p>
  <?php } ?>
  <script>
    // Calculate the total purchase value
    var quantityInputs = document.querySelectorAll('input[name="quantity[]"]');
    var totalValueSpan = document.querySelector('#total-value');
    var products = <?php echo json_encode($result->fetch_all(MYSQLI_ASSOC)); ?>;
    let allValues = {};
    var totalValue = 0;
    let totalOfAllProducts = 0;
    quantityInputs.forEach(function(input, index) {
      input.addEventListener('input', function() {
        totalValue = 0;
        quantityInputs.forEach(function(input, index) {
          var productPrice = products[index].preco;
          var productQuantity = input.value;
          if (productQuantity == '') {
            productQuantity = 0;
          }
          totalValue += productPrice * productQuantity;
        });
        });
        
        
        const submitButton = document.querySelector('button[type="submit"]');
        quantityInputs.forEach(function(input) {
          input.addEventListener('input', function() {
            const trElement = input.closest('tr');
            const nameElement = trElement.querySelector('td:first-child');
            const descElement = trElement.querySelector('td:nth-child(2)');
            const priceElement = trElement.querySelector('td:nth-child(3)');
            const quantity = parseInt(input.value) || 0;
            console.log('quantity', quantity);
            console.log('nameElement', nameElement);
            console.log('descElement', descElement);
            console.log('priceElement', priceElement);

            if (nameElement && descElement && priceElement) {
              let productName = nameElement.textContent;
              if (!allValues[productName]) {
                allValues[productName] = {};
              }
              allValues[productName].quantity = quantity;
              allValues[productName].price = parseFloat(priceElement.textContent.replace('$', ''));
              allValues[productName].total = allValues[productName].quantity * allValues[productName].price;
              let total = 0;
              for (let product in allValues) {
                if (allValues.hasOwnProperty(product)) {
                  total += allValues[product].total;
                }
              }
              console.log('allValues', allValues);
              console.log('total', total);
              totalOfAllProducts = total;
            }
          });
        });

        submitButton.addEventListener('click', function() {
          // Save the allValues and totalOfAllProducts variables to local storage
          localStorage.setItem('allValues', JSON.stringify(allValues));
          localStorage.setItem('totalOfAllProducts', JSON.stringify(totalOfAllProducts));
        });

      });
  </script>
</body>
</html>