<?php

require 'barcode.php';
$pdo = new PDO('mysql:host=localhost;dbname=barcode_db', 'root', '');  // Database connection

// Initialize variables
$productName = '';
$price = '';
$seriesNo = '';

// Fetch products for the dropdown, including series number
$stmt = $pdo->query("SELECT product_name, price, series_no FROM products");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['clear'])) {
        // Clear the quantity input
        unset($_POST['quantity']);
    } else {
        // Get selected product information
        $selectedSeriesNo = $_POST['product'];  // Use series_no as the unique identifier
        $quantity = intval($_POST['quantity']);

        // Fetch product details from the database using series_no
        $stmt = $pdo->prepare("SELECT product_name, price, series_no FROM products WHERE series_no = ?");
        $stmt->execute([$selectedSeriesNo]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($product) {
            $productName = $product['product_name'];  // Assign product name
            $price = $product['price'];              // Assign product price
            $seriesNo = $product['series_no'];      // Assign product series number
            
            // Generate barcodes
            for ($i = 1; $i <= $quantity; $i++) {
                $barcodeText = $seriesNo . ' ';  // Barcode text includes series_no
                $barcode = new Barcode();
                $barcode->generate($barcodeText, "barcode_$i.jpg"); // Generate each barcode with unique filename
            }
        } else {
            // Handle the case where the product was not found
            echo "Product not found.";
        }
    }
}
?>

<div class="con" style="font-size: 14px; font-family: tahoma; text-align: center; max-width: 600px; margin: auto;">
    <h3>BARCODE GENERATOR</h3>
    <link rel="stylesheet" href="styles.css">
    <form method="post" action="index.php">

        <!-- Dropdown to select product -->
        <select name="product" style="padding: 5px; width: 100%; margin-bottom: 10px;">
            <option value="">Select Product</option>
            <?php foreach ($products as $product): ?>
                <option value="<?= htmlspecialchars($product['series_no']) ?>">
                    <?= $product['product_name'] ?> - PHP <?= number_format($product['price'], 2) ?> - <?= htmlspecialchars($product['series_no']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <!-- Input for quantity -->
        <input autocomplete="off" placeholder="Quantity" style="padding: 5px; width: 100%; margin-bottom: 10px;" 
               required type="number" value="<?= $_POST['quantity'] ?? '' ?>" name="quantity" min="1">
        
        <!-- ADD PRODUCT REDIRECT FROM ANOTHER FILE -->
        <br>
        <a href="add_product.php"><button type="button">Add New Product</button></a>
        <button type="submit" style="padding: 5px; cursor: pointer;">Generate Barcodes</button>
        <button type="submit" style="padding: 5px; cursor: pointer;" name="clear">Clear Barcodes</button>
    </form>

    <br>
    <p class="print-instructions">Press <strong>Ctrl+P</strong> (or <strong>Cmd+P</strong> on Mac) to print barcodes.</p>
    <br>
    <hr>
    <?php if (isset($_POST['quantity']) && !isset($_POST['clear'])): ?>
        <?php 
            $quantity = intval($_POST['quantity']); 
            for ($i = 1; $i <= $quantity; $i++): 
                $barcodeFileName = "barcode_$i.jpg"; // Unique filename for each barcode
        ?>
       <div style="text-align: center; margin-bottom: 20px; border: 1px solid #FFFFFF; padding: 10px; display: inline-block; background-color: #ffffff;">
            <p><strong>Item: </strong><?= htmlspecialchars($productName) ?></p>  <!-- Display product name -->
            <img src="<?= htmlspecialchars($barcodeFileName) ?>?<?= rand(0, 9999) ?>" alt="Barcode <?= $i ?>" style="width: 100%; border: 1px solid #FFFFFF; margin-bottom: 5px;" />
            <br>
            <p><strong>Price:</strong> PHP <?= htmlspecialchars($price) ?> </p> <!-- Price showed upon generating -->
        </div>

        <?php endfor; ?>
    <?php endif; ?>
</div>
