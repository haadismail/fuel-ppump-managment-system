<?php

$conn = new mysqli("localhost", "root", "", "petrol_pump");

if ($conn->connect_error) {
    die("Database connection failed");
}

/* =========================
   EDIT FETCH
========================= */
$edit = false;
$edit_row = [];

if(isset($_GET['edit'])){

    $edit = true;
    $id = intval($_GET['edit']);

    $result_edit = $conn->query("
        SELECT * FROM fuel_sales WHERE id = $id
    ");

    $edit_row = $result_edit->fetch_assoc();
}

/* =========================
   UPDATE SALE
========================= */
if(isset($_POST['update_sale'])){

    $id        = $_POST['id'];
    $fuel      = $_POST['fuel'];
    $liters    = $_POST['liters'];
    $price     = $_POST['price'];
    $payment   = $_POST['payment'];
    $nozzle    = $_POST['nozzle'];
    $date      = $_POST['date'];
    $profit    = $_POST['profit'];

    $total = $liters * $price;
    $total_profit = $liters * $profit;

    $conn->query("
        UPDATE fuel_sales SET
            fuel_type='$fuel',
            liters='$liters',
            price_per_liter='$price',
            total='$total',
            payment_method='$payment',
            nozzle_number='$nozzle',
            profit_per_liter='$profit',
            total_profit='$total_profit',
            sale_date='$date'
        WHERE id=$id
    ");

    header("Location: sales.php");
    exit();
}

/* =========================
   SAVE SALE + UPDATE STOCK
========================= */
if(isset($_POST['save_sale'])){

    $fuel      = $_POST['fuel'];
    $liters    = $_POST['liters'];
    $price     = $_POST['price'];
    $payment   = $_POST['payment'];
    $nozzle    = $_POST['nozzle'];
    $date      = $_POST['date'];
    $profit    = $_POST['profit'];

    $total = $liters * $price;
    $total_profit = $liters * $profit;

    $conn->query("
        INSERT INTO fuel_sales
        (
            fuel_type,
            liters,
            price_per_liter,
            total,
            payment_method,
            nozzle_number,
            profit_per_liter,
            total_profit,
            sale_date
        )
        VALUES
        (
            '$fuel',
            '$liters',
            '$price',
            '$total',
            '$payment',
            '$nozzle',
            '$profit',
            '$total_profit',
            '$date'
        )
    ");

    $conn->query("
        UPDATE fuel_inventory
        SET stock = stock - $liters
        WHERE fuel_type = '$fuel'
    ");

    header("Location: sales.php");
    exit();
}

/* =========================
   DELETE SALE
========================= */
if(isset($_GET['delete'])){

    $id = intval($_GET['delete']);

    $conn->query("
        DELETE FROM fuel_sales
        WHERE id = $id
    ");

    header("Location: sales.php");
    exit();
}

/* =========================
   FILTERS
========================= */
$fuel_filter   = $_GET['fuel'] ?? '';
$nozzle_filter = $_GET['nozzle'] ?? '';
$date_filter   = $_GET['date'] ?? '';

$sql = "SELECT * FROM fuel_sales WHERE 1=1";

if($fuel_filter != ''){
    $sql .= " AND fuel_type='$fuel_filter'";
}

if($nozzle_filter != ''){
    $sql .= " AND nozzle_number='$nozzle_filter'";
}

if($date_filter != ''){
    $sql .= " AND sale_date='$date_filter'";
}

$sql .= " ORDER BY id DESC";

$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html>

<head>

    <meta charset="UTF-8">
    <title>Fuel Sales</title>

    <link rel="stylesheet" href="sales.css">

    <link rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

</head>

<body>

<div class="wrapper">

    <div class="sidebar">

        <div class="brand">
            <i class="fa-solid fa-gas-pump"></i>
            <h2>Makkah Usmania Petroleum</h2>
        </div>

        <nav>

            <a href="dashboard.php">Dashboard</a>
            <a href="sales.php" class="active">Fuel Sales</a>
            <a href="inventory.php">Fuel Inventory</a>
            <a href="lubes.php">Lubricants</a>
            <a href="khata.php">Digital Khata</a>

        </nav>

    </div>

    <div class="main">

        <div class="topbar">
            <h1>Fuel Sales Management</h1>
        </div>

        <!-- FILTER -->
        <form method="GET" class="filter-form">

            <input type="date" name="date">

            <select name="fuel">
                <option value="">All Fuel</option>
                <option value="Petrol">Petrol</option>
                <option value="Diesel">Diesel</option>
            </select>

            <select name="nozzle">
                <option value="">All Nozzles</option>
                <?php for($i=1; $i<=8; $i++){ ?>
                    <option value="<?= $i ?>">Nozzle <?= $i ?></option>
                <?php } ?>
            </select>

            <button type="submit">Filter</button>

        </form>

        <!-- FORM -->
        <div class="form-box">

            <form method="POST" class="form-grid">

                <?php if($edit){ ?>
                    <input type="hidden" name="id" value="<?= $edit_row['id'] ?>">
                <?php } ?>

                <select name="fuel" required>
                    <option value="">Select Fuel</option>
                    <option value="Petrol" <?= ($edit_row['fuel_type'] ?? '')=='Petrol'?'selected':'' ?>>Petrol</option>
                    <option value="Diesel" <?= ($edit_row['fuel_type'] ?? '')=='Diesel'?'selected':'' ?>>Diesel</option>
                </select>

                <input type="number" step="0.01" name="liters" placeholder="Liters"
                value="<?= $edit_row['liters'] ?? '' ?>" required>

                <input type="number" step="0.01" name="price" placeholder="Price Per Liter"
                value="<?= $edit_row['price_per_liter'] ?? '' ?>" required>

                <select name="payment" required>
                    <option value="Cash" <?= ($edit_row['payment_method'] ?? '')=='Cash'?'selected':'' ?>>Cash</option>
                    <option value="Card" <?= ($edit_row['payment_method'] ?? '')=='Card'?'selected':'' ?>>Card</option>
                </select>

                <select name="nozzle" required>
                    <?php for($i=1; $i<=8; $i++){ ?>
                        <option value="<?= $i ?>" <?= ($edit_row['nozzle_number'] ?? '')==$i?'selected':'' ?>>
                            Nozzle <?= $i ?>
                        </option>
                    <?php } ?>
                </select>

                <input type="date" name="date"
                value="<?= $edit_row['sale_date'] ?? '' ?>" required>

                <input type="number" step="0.01" name="profit" placeholder="Profit Per Liter"
                value="<?= $edit_row['profit_per_liter'] ?? '' ?>" required>

                <button type="submit" name="<?= $edit ? 'update_sale' : 'save_sale' ?>">
                    <?= $edit ? 'Update Sale' : 'Save Sale' ?>
                </button>

            </form>

        </div>

        <!-- TABLE -->
        <section class="table-section">

            <h2>Sales Records</h2>

            <table>

                <tr>
                    <th>ID</th>
                    <th>Date</th>
                    <th>Fuel</th>
                    <th>Nozzle</th>
                    <th>Liters</th>
                    <th>Rate</th>
                    <th>Total</th>
                    <th>Profit</th>
                    <th>Payment</th>
                    <th>Action</th>
                </tr>

                <?php while($row = $result->fetch_assoc()) { ?>

                <tr>

                    <td><?= $row['id'] ?></td>
                    <td><?= $row['sale_date'] ?></td>
                    <td><?= $row['fuel_type'] ?></td>
                    <td><?= $row['nozzle_number'] ?></td>
                    <td><?= $row['liters'] ?></td>
                    <td>PKR <?= $row['price_per_liter'] ?></td>
                    <td>PKR <?= $row['total'] ?></td>
                    <td>PKR <?= $row['total_profit'] ?></td>
                    <td><?= $row['payment_method'] ?></td>

                    <td>

                        <a href="sales.php?edit=<?= $row['id'] ?>">
                            <button type="button" style="background:orange;color:white;">
                                Edit
                            </button>
                        </a>

                        <a href="sales.php?delete=<?= $row['id'] ?>"
                        onclick="return confirm('Delete this sale?')">
                            <button type="button" style="background:red;color:white;">
                                Delete
                            </button>
                        </a>

                    </td>

                </tr>

                <?php } ?>

            </table>

        </section>

    </div>

</div>

</body>
</html>