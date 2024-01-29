<?php
session_start();

$dsn = "mysql:host=courses;dbname=z1948457";

try {
    $pdo = new PDO($dsn, "z1948457", "2002Feb05");
} catch (PDOexception $e) {
    echo "Connection to database failed: " . $e->getMessage();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['submit_order'])) {
        try {
            // Get user info
            $address = $_POST['address'];
            $cardNumber = $_POST['card_number'];
            $email = $_POST['email'];
            $phoneNumber = $_POST['phone_number']; // Added line
            $name = $_POST['name']; // Added line

            // create an order id
            $orderID = rand(10000000, 99999999);

            // Get date
            $date = date("Y-m-d");

            // Retrieve total and item count from the previous cart page
            $total = isset($_SESSION['total']) ? $_SESSION['total'] : 0;
            $itemCount = isset($_SESSION['items']) ? $_SESSION['items'] : 0;

            // Insert or update user information
            $insertUserSQL = "INSERT INTO User (Email, Phone, Name) 
                              VALUES ('$email', '$phoneNumber', '$name')
                              ON DUPLICATE KEY UPDATE Name = '$name'";
            $pdo->query($insertUserSQL);

            // Directly insert values into the SQL query
            $insertOrderSQL = "INSERT INTO Orders (OrderID, Address, Total, BillingInfo, Datee, ItemCount, Email) 
                               VALUES ('$orderID', '$address', '$total', '$cardNumber', '$date', '$itemCount', '$email')";
            $pdo->query($insertOrderSQL);

            // put into placed order table
            $trackingNo = rand(100000, 999999);
            $status = "Received";
            $insertPlacedOrderSQL = "INSERT INTO PlacedOrder (Email, OrderID, TrackingNo, Status) 
                                    VALUES ('$email', '$orderID', '$trackingNo', '$status')";

            // Insert product information into the Product table
            foreach ($_SESSION['Add'] as $product_id => $quantity) {
                $insertProductSQL = "INSERT INTO ProductStored (OrderID, ProductID, Quantity) 
                                     VALUES ('$orderID', '$product_id', '$quantity')";
                $pdo->query($insertProductSQL);
                $updateProductSQL = "UPDATE Product SET Quantity = Quantity - '$quantity' WHERE ProductID = '$product_id'";
                $pdo->query($updateProductSQL);
            }

            $pdo->query($insertPlacedOrderSQL);

            session_unset();
            session_destroy();

            echo "<h1>Thanks for your purchase!</h1>";
            echo "<a href=\"website.php\">HOME</a>";
            exit();
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }
}
?>

<html>
<head>
    <title>Checkout</title>

    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
</head>
<body>

	<nav class="navbar navbar-expand-lg navbar-dark" style="background-color: #643843">
   	 		<a class="navbar-brand" href="website.php">Funky Shop</a>
    		<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        		<span class="navbar-toggler-icon"></span>
    		</button>
    		<div class="collapse navbar-collapse" id="navbarNav">
        		<ul class="navbar-nav ml-auto">
            		<li class="nav-item">
                		<a class="nav-link" href="website.php">Home</a>
            		</li>
            		<li class="nav-item">
               			 <a class="nav-link" href="login.php">Employee Login</a>
            		</li>
            		<li class="nav-item">
                		<a class="nav-link" href="trackOrder.php">Track Your Order</a>
            		</li>
            		<li class="nav-item">
                		<a class="nav-link" href="cartPage.php">Cart</a>
        			</li>
        		</ul>
    		</div>
		</nav>

		<!-- Bootstrap JS and dependencies -->
		<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
		<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>


    <h2>Checkout</h2>
    <p>Total: <?php echo isset($_SESSION['total']) ? $_SESSION['total'] : 0; ?></p>
    <form action="checkout.php" method="post">
        <label for="name">Name:</label>
        <input type="text" name="name" required><br>

        <label for="phone_number">Phone Number:</label>
        <input type="text" name="phone_number" required><br>

        <label for="address">Address:</label>
        <input type="text" name="address" required><br>

        <label for="card_number">Card Number:</label>
        <input type="text" name="card_number" required><br>

        <label for="email">Email:</label>
        <input type="email" name="email" required><br>

        <input type="submit" name="submit_order" value="Place Order">
    </form>
    <br>
    <form action="cartPage.php" method="get">
        <input type="submit" value="Back to Cart">
    </form>
</body>
</html>
    
    
