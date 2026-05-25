<?php
$conn = new mysqli("localhost", "root", "", "petrol_pump");

if ($conn->connect_error) {
    die("Database Connection Failed");
}

/* =========================
   SAVE BASE (TOTAL LITERS TILL NOW - MANUAL)
========================= */
if (isset($_POST['update_base_sales'])) {

    $base = $_POST['base_sales'] ?? 0;

    $check = $conn->query("SELECT id FROM system_settings LIMIT 1");

    if ($check && $check->num_rows > 0) {

        $row = $check->fetch_assoc();
        $id = $row['id'];

        $conn->query("
            UPDATE system_settings 
            SET total_sales_base='$base'
            WHERE id=$id
        ");

    } else {

        $conn->query("
            INSERT INTO system_settings (total_sales_base)
            VALUES ('$base')
        ");
    }
}

/* =========================
   GET BASE VALUE
========================= */
$base_sales = 0;
$settings_id = 0;

$res = $conn->query("
    SELECT id, total_sales_base 
    FROM system_settings 
    LIMIT 1
");

if ($res && $res->num_rows > 0) {

    $row = $res->fetch_assoc();

    $base_sales = $row['total_sales_base'];
    $settings_id = $row['id'];
}

/* =========================
   CURRENT METER
========================= */
$current = $conn->query("
    SELECT SUM(liters) as current_liters 
    FROM fuel_sales
")->fetch_assoc();

$current_liters = $current['current_liters'] ?? 0;
/* =========================
   YOUR MAIN LOGIC
========================= */

$meter_sale = $current_liters - $base_sales;

if ($meter_sale < 0) {
    $meter_sale = 0;
}

/* =========================
   AUTO STOCK UPDATE
========================= */
if ($meter_sale > 0 && $current_liters > 0) {

    $conn->query("
        UPDATE fuel_inventory 
        SET stock = stock - $meter_sale
    ");

    if ($settings_id > 0) {

        $conn->query("
            UPDATE system_settings
            SET total_sales_base = $current_liters
            WHERE id = $settings_id
        ");

    } else {

        $conn->query("
            INSERT INTO system_settings (total_sales_base)
            VALUES ($current_liters)
        ");
    }

    $base_sales = $current_liters;
}

/* =========================
   MONTH
========================= */
$currentMonth = date("Y-m");

/* =========================
   TOTAL MONTHLY
========================= */
$monthly = $conn->query("
    SELECT 
        SUM(total) as total_sale,
        SUM(total_profit) as total_profit,
        SUM(liters) as total_liters
    FROM fuel_sales
    WHERE DATE_FORMAT(sale_date, '%Y-%m') = '$currentMonth'
")->fetch_assoc();

/* =========================
   TODAY TOTAL
========================= */
$today = $conn->query("
    SELECT 
        SUM(total) as total_sale,
        SUM(total_profit) as total_profit,
        SUM(liters) as liters
    FROM fuel_sales
    WHERE sale_date = CURDATE()
")->fetch_assoc();

$today_sales = $today['total_sale'] ?? 0;
$today_profit = $today['total_profit'] ?? 0;
$today_liters = $today['liters'] ?? 0;

/* =========================
   YESTERDAY
========================= */
$yesterday = $conn->query("
    SELECT SUM(liters) as liters
    FROM fuel_sales
    WHERE sale_date = DATE_SUB(CURDATE(), INTERVAL 1 DAY)
")->fetch_assoc();

$yesterday_liters = $yesterday['liters'] ?? 0;

$daily_difference = $today_liters - $yesterday_liters;

/* =========================
   PETROL TODAY
========================= */
$petrol_today = $conn->query("
    SELECT 
        SUM(total) as total_sale,
        SUM(total_profit) as total_profit,
        SUM(liters) as liters
    FROM fuel_sales
    WHERE fuel_type='Petrol'
    AND sale_date = CURDATE()
")->fetch_assoc();

$petrol_today_sale   = $petrol_today['total_sale'] ?? 0;
$petrol_today_profit = $petrol_today['total_profit'] ?? 0;
$petrol_today_liters = $petrol_today['liters'] ?? 0;

/* =========================
   DIESEL TODAY
========================= */
$diesel_today = $conn->query("
    SELECT 
        SUM(total) as total_sale,
        SUM(total_profit) as total_profit,
        SUM(liters) as liters
    FROM fuel_sales
    WHERE fuel_type='Diesel'
    AND sale_date = CURDATE()
")->fetch_assoc();

$diesel_today_sale   = $diesel_today['total_sale'] ?? 0;
$diesel_today_profit = $diesel_today['total_profit'] ?? 0;
$diesel_today_liters = $diesel_today['liters'] ?? 0;

/* =========================
   PETROL MONTHLY
========================= */
$petrol_monthly = $conn->query("
    SELECT 
        SUM(total) as total_sale,
        SUM(total_profit) as total_profit,
        SUM(liters) as liters
    FROM fuel_sales
    WHERE fuel_type='Petrol'
    AND DATE_FORMAT(sale_date, '%Y-%m') = '$currentMonth'
")->fetch_assoc();

$petrol_month_sale   = $petrol_monthly['total_sale'] ?? 0;
$petrol_month_profit = $petrol_monthly['total_profit'] ?? 0;
$petrol_month_liters = $petrol_monthly['liters'] ?? 0;

/* =========================
   DIESEL MONTHLY
========================= */
$diesel_monthly = $conn->query("
    SELECT 
        SUM(total) as total_sale,
        SUM(total_profit) as total_profit,
        SUM(liters) as liters
    FROM fuel_sales
    WHERE fuel_type='Diesel'
    AND DATE_FORMAT(sale_date, '%Y-%m') = '$currentMonth'
")->fetch_assoc();

$diesel_month_sale   = $diesel_monthly['total_sale'] ?? 0;
$diesel_month_profit = $diesel_monthly['total_profit'] ?? 0;
$diesel_month_liters = $diesel_monthly['liters'] ?? 0;

/* =========================
   INVENTORY
========================= */
$fuel_inventory = $conn->query("
    SELECT 
        fi.tank_name,
        fi.fuel_type,
        fi.stock,
        COALESCE(SUM(fs.liters),0) AS sold_liters,
        (fi.stock - COALESCE(SUM(fs.liters),0)) AS remaining_stock
    FROM fuel_inventory fi
    LEFT JOIN fuel_sales fs 
        ON fs.fuel_type = fi.fuel_type
    GROUP BY fi.id, fi.tank_name, fi.fuel_type, fi.stock
");

/* =========================
   LUBRICANTS
========================= */


/* =========================
   KHATA (TODAY REMAINING)
========================= */
$khata_today = $conn->query("
    SELECT SUM(remaining_amount) AS total_due
    FROM khata
    WHERE entry_date = CURDATE()
")->fetch_assoc();

$today_khata = $khata_today['total_due'] ?? 0;

$lube_inventory = $conn->query("
    SELECT * FROM lubricants_inventory
");
?>

<!DOCTYPE html>
<html>
<head>

<meta charset="UTF-8">

<title>Makkah Usmania Petroleum</title>

<link rel="stylesheet" href="dashboard.css">

<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

</head>

<body>

<div class="wrapper">

<!-- SIDEBAR -->
<div class="sidebar">

    <div class="brand">
        <i class="fa fa-gas-pump"></i>
        <h2>Makkah Usmania Petroleum</h2>
    </div>

    <nav>
        <a href="dashboard.php" class="active">Dashboard</a>
        <a href="sales.php">Fuel Sales</a>
        <a href="inventory.php">Fuel Inventory</a>
        <a href="lubes.php">Lubricants</a>
        <a href="khata.php">Digital Khata</a>
    </nav>

</div>

<!-- MAIN -->
<div class="main">

<div class="topbar">
    <h1>Makkah Usmania Petroleum</h1>
    <p>Fuel & Lubricants Management System</p>
</div>

<!-- INPUT -->
<div style="margin:20px 0; padding:15px; background:#f8fafc; border-radius:10px;">

    <form method="POST">

        <label>
            <b>Total Liters Till Now (Meter Reading)</b>
        </label>

        <br><br>

        <input 
            type="number"
            name="base_sales"
            value="<?= $base_sales ?>"
            style="padding:8px; width:250px;"
        >

        <button type="submit" name="update_base_sales">
            Save
        </button>

    </form>

</div>

<!-- CARDS -->
<div class="cards">

    <div class="card">
        <h3>Today Total Revenue</h3>
        <p>PKR <?= $today_sales ?></p>
    </div>

    <div class="card">
        <h3>Today Total Profit</h3>
        <p>PKR <?= $today_profit ?></p>
    </div>

    <div class="card">
        <h3>Today Liters Sold</h3>
        <p><?= $today_liters ?> L</p>
        <small><?= $daily_difference ?> L vs yesterday</small>
    </div>

    <!-- PETROL -->

    <div class="card">
        <h3>Petrol Sale Today</h3>
        <p>PKR <?= $petrol_today_sale ?></p>
        <small><?= $petrol_today_liters ?> L Sold</small>
    </div>

    <div class="card">
        <h3>Petrol Profit Today</h3>
        <p>PKR <?= $petrol_today_profit ?></p>
    </div>

    <div class="card">
        <h3>Petrol Monthly Sale</h3>
        <p>PKR <?= $petrol_month_sale ?></p>
        <small><?= $petrol_month_liters ?> L</small>
    </div>

    <div class="card">
        <h3>Petrol Monthly Profit</h3>
        <p>PKR <?= $petrol_month_profit ?></p>
    </div>

    <!-- DIESEL -->

    <div class="card">
        <h3>Diesel Sale Today</h3>
        <p>PKR <?= $diesel_today_sale ?></p>
        <small><?= $diesel_today_liters ?> L Sold</small>
    </div>

    <div class="card">
        <h3>Diesel Profit Today</h3>
        <p>PKR <?= $diesel_today_profit ?></p>
    </div>

    <div class="card">
        <h3>Diesel Monthly Sale</h3>
        <p>PKR <?= $diesel_month_sale ?></p>
        <small><?= $diesel_month_liters ?> L</small>
    </div>

    <div class="card">
        <h3>Diesel Monthly Profit</h3>
        <p>PKR <?= $diesel_month_profit ?></p>
    </div>

    <!-- TOTAL -->

    <div class="card">
        <h3>Total Monthly Revenue</h3>
        <p>PKR <?= $monthly['total_sale'] ?? 0 ?></p>
    </div>

    <div class="card">
        <h3>Total Monthly Profit</h3>
        <p>PKR <?= $monthly['total_profit'] ?? 0 ?></p>
    </div>

    <div class="card">
        <h3>Total Liters Till Now</h3>
        <p><?= $base_sales ?> L</p>
        <small>Auto Updated Meter</small>
    </div>

    <div class="card">
    <h3>Today Khata Pending</h3>
    <p>PKR <?= number_format($today_khata,2) ?></p>
    <small>Remaining Amount (Daily)</small>
</div>

</div>

<!-- INVENTORY -->
<section class="table-section">

<h2>Fuel Inventory</h2>

<table>

<tr>
    <th>Tank</th>
    <th>Fuel</th>
    <th>Stock</th>
    <th>Sold</th>
    <th>Remaining</th>
</tr>

<?php while($row = $fuel_inventory->fetch_assoc()) { ?>

<tr>

    <td><?= $row['tank_name'] ?></td>

    <td><?= $row['fuel_type'] ?></td>

    <td><?= $row['stock'] ?> L</td>

    <td><?= $row['sold_liters'] ?> L</td>

    <td>
        <b><?= $row['remaining_stock'] ?> L</b>
    </td>

</tr>

<?php } ?>

</table>

</section>

<!-- LUBRICANTS -->
<!-- LUBRICANTS -->
<section class="table-section">

<h2>Lubricants Inventory</h2>

<table>

<tr>
    <th>Name</th>
    <th>Brand</th>
    <th>Stock</th>
    <th>Purchase Price</th>
    <th>Sale Price</th>
    <th>Status</th>
</tr>

<?php while($row = $lube_inventory->fetch_assoc()) { ?>

<tr>

    <td><?= $row['name'] ?></td>

    <td><?= $row['brand'] ?></td>

    <td><?= $row['stock'] ?> cans</td>

    <td>PKR <?= $row['purchase_price'] ?></td>

    <td>PKR <?= $row['sale_price'] ?></td>

    <td><?= $row['status'] ?></td>

</tr>

<?php } ?>

</table>

</section>

</div>
</div>

</body>
</html>