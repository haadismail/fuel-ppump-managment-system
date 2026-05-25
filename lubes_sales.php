<?php

$conn = new mysqli("localhost", "root", "", "petrol_pump");

if ($conn->connect_error) {
    die("Database Connection Failed");
}

/* =========================
   SAVE SALE
========================= */
if(isset($_POST['save_sale'])){

    $customer = trim($_POST['customer_name']);
    $product  = trim($_POST['product_name']);
    $quantity = floatval($_POST['quantity']);
    $price    = floatval($_POST['price']);
    $payment  = trim($_POST['payment_method']);
    $date     = $_POST['sale_date'];

    $total = $quantity * $price;

    /* INSERT SALE */
    $stmt = $conn->prepare("
        INSERT INTO lubricants_sales
        (
            customer_name,
            product_name,
            quantity,
            price_per_liter,
            total,
            payment_method,
            sale_date
        )
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->bind_param(
        "ssdddss",
        $customer,
        $product,
        $quantity,
        $price,
        $total,
        $payment,
        $date
    );

    $stmt->execute();
    $stmt->close();

    /* STOCK DEDUCT */
    $update = $conn->prepare("
        UPDATE lubricants_inventory
        SET stock = stock - ?
        WHERE name = ?
    ");

    $update->bind_param(
        "ds",
        $quantity,
        $product
    );

    $update->execute();
    $update->close();

    header("Location: lubes_sales.php");
    exit();
}

/* =========================
   DELETE SALE
========================= */
if(isset($_GET['delete'])){

    $id = intval($_GET['delete']);

    /* GET SALE DATA */
    $sale = $conn->query("
        SELECT product_name, quantity
        FROM lubricants_sales
        WHERE id = $id
    ");

    if($sale->num_rows > 0){

        $row = $sale->fetch_assoc();

        $product = $row['product_name'];
        $quantity = $row['quantity'];

        /* RETURN STOCK */
        $conn->query("
            UPDATE lubricants_inventory
            SET stock = stock + $quantity
            WHERE name = '$product'
        ");
    }

    /* DELETE SALE */
    $conn->query("
        DELETE FROM lubricants_sales
        WHERE id = $id
    ");

    header("Location: lubes_sales.php");
    exit();
}

/* =========================
   FILTER
========================= */
$product_filter = $_GET['product'] ?? '';
$date_filter    = $_GET['date'] ?? '';

$sql = "
    SELECT *
    FROM lubricants_sales
    WHERE 1=1
";

if($product_filter != ''){

    $sql .= "
        AND product_name='$product_filter'
    ";
}

if($date_filter != ''){

    $sql .= "
        AND sale_date='$date_filter'
    ";
}

$sql .= " ORDER BY id DESC";

$result = $conn->query($sql);

/* =========================
   FETCH PRODUCTS
========================= */
$products = $conn->query("
    SELECT *
    FROM lubricants_inventory
    ORDER BY name ASC
");

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1.0">

<title>Lubricants Sales</title>

<link rel="stylesheet" href="lubes_sales.css">

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

</head>

<body>

<div class="wrapper">

    <!-- SIDEBAR -->
    <div class="sidebar">

        <div class="brand">

            <i class="fa-solid fa-oil-can"></i>

            <div>
                <h2>Makkah Usmania</h2>
                <small>Petrol Pump System</small>
            </div>

        </div>

        <nav>

            <a href="dashboard.php">
                <i class="fa fa-chart-line"></i>
                Dashboard
            </a>

            <a href="sales.php">
                <i class="fa fa-gas-pump"></i>
                Fuel Sales
            </a>

            <a href="inventory.php">
                <i class="fa fa-database"></i>
                Fuel Inventory
            </a>

            <a href="lubes.php">
                <i class="fa fa-oil-can"></i>
                Lubricants
            </a>

            <a href="lubes_sales.php" class="active">
                <i class="fa fa-cart-shopping"></i>
                Lubricants Sales
            </a>

            <a href="khata.php">
                <i class="fa fa-book"></i>
                Digital Khata
            </a>

        </nav>

    </div>

    <!-- MAIN -->
    <div class="main">

        <!-- TOPBAR -->
        <div class="topbar">

            <h1>Lubricants Sales</h1>

            <p>
                Manage lubricant sales,
                customer records and stock.
            </p>

        </div>

        <!-- SALE FORM -->
        <form method="POST" class="form-grid">

            <!-- CUSTOMER -->
            <input
                type="text"
                name="customer_name"
                placeholder="Customer Name"
                required
            >

            <!-- PRODUCT -->
            <select name="product_name" required>

                <option value="">
                    Select Product
                </option>

                <?php
                while($product = $products->fetch_assoc()){
                ?>

                <option value="<?= $product['name'] ?>">

                    <?= $product['name'] ?>
                    -
                    Stock:
                    <?= number_format($product['stock'],2) ?> L

                </option>

                <?php } ?>

            </select>

            <!-- QUANTITY -->
            <input
                type="number"
                step="0.01"
                min="0"
                name="quantity"
                placeholder="Quantity (Can)"
                required
            >

            <!-- PRICE -->
            <input
                type="number"
                step="0.01"
                min="0"
                name="price"
                placeholder="Price per Can"
                required
            >

            <!-- PAYMENT -->
            <select name="payment_method" required>

                <option value="">
                    Payment Method
                </option>

                <option value="Cash">
                    Cash
                </option>

                <option value="Card">
                    Card
                </option>

                <option value="Credit">
                    Credit
                </option>

            </select>

            <!-- DATE -->
            <input
                type="date"
                name="sale_date"
                required
            >

            <!-- BUTTON -->
            <button type="submit" name="save_sale">

                <i class="fa fa-save"></i>
                Save Sale

            </button>

        </form>

        <!-- FILTER -->
        <form method="GET" class="filter-bar">

            <input
                type="date"
                name="date"
                value="<?= $date_filter ?>"
            >

            <select name="product">

                <option value="">
                    All Products
                </option>

                <?php

                $products2 = $conn->query("
                    SELECT name
                    FROM lubricants_inventory
                    ORDER BY name ASC
                ");

                while($p = $products2->fetch_assoc()){
                ?>

                <option
                value="<?= $p['name'] ?>"
                <?= $product_filter == $p['name'] ? 'selected' : '' ?>>

                    <?= $p['name'] ?>

                </option>

                <?php } ?>

            </select>

            <button type="submit">

                <i class="fa fa-filter"></i>
                Filter

            </button>

        </form>

        <!-- TABLE -->
        <div class="table-wrapper">

            <table>

                <tr>

                    <th>ID</th>

                    <th>Date</th>

                    <th>Customer</th>

                    <th>Product</th>

                    <th>Quantity</th>

                    <th>Rate</th>

                    <th>Total</th>

                    <th>Payment</th>

                    <th>Action</th>

                </tr>

                <?php
                if($result && $result->num_rows > 0){

                    while($row = $result->fetch_assoc()){
                ?>

                <tr>

                    <td>
                        <?= $row['id'] ?>
                    </td>

                    <td>
                        <?= $row['sale_date'] ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($row['customer_name']) ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($row['product_name']) ?>
                    </td>

                    <td>
                        <?= number_format($row['quantity'],2) ?> L
                    </td>

                    <td>
                        PKR <?= number_format($row['price_per_liter'],2) ?>
                    </td>

                    <td>
                        PKR <?= number_format($row['total'],2) ?>
                    </td>

                    <td>

                        <span class="status-paid">

                            <?= $row['payment_method'] ?>

                        </span>

                    </td>

                    <td class="action-buttons">

                        <a
                        href="lubes_sales.php?delete=<?= $row['id'] ?>"
                        onclick="return confirm('Delete this sale?')">

                            <button
                            type="button"
                            class="delete-btn">

                                Delete

                            </button>

                        </a>

                    </td>

                </tr>

                <?php
                    }

                } else {
                ?>

                <tr>

                    <td colspan="9" class="empty-row">

                        No Sales Found

                    </td>

                </tr>

                <?php } ?>

            </table>

        </div>

    </div>

</div>

</body>
</html>