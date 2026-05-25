<?php
$conn = new mysqli("localhost", "root", "", "petrol_pump");

if ($conn->connect_error) {
    die("Database Connection Failed");
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
        SELECT * FROM fuel_inventory WHERE id=$id
    ");

    $edit_row = $result_edit->fetch_assoc();
}

/* =========================
   UPDATE INVENTORY
========================= */
if(isset($_POST['update_inventory'])){

    $id = $_POST['id'];

    $tank = $_POST['tank_name'];
    $fuel = $_POST['fuel_type'];
    $capacity = $_POST['capacity'];
    $stock = $_POST['stock'];
    $status = $_POST['status'];

    $conn->query("
        UPDATE fuel_inventory SET
            tank_name='$tank',
            fuel_type='$fuel',
            capacity='$capacity',
            stock='$stock',
            status='$status'
        WHERE id=$id
    ");

    header("Location: inventory.php");
    exit();
}

/* =========================
   ADD INVENTORY
========================= */
if ($_SERVER["REQUEST_METHOD"] == "POST" && !isset($_POST['update_inventory'])) {

    $tank = $_POST['tank_name'] ?? '';
    $fuel = $_POST['fuel_type'] ?? '';
    $capacity = $_POST['capacity'] ?? 0;
    $stock = $_POST['stock'] ?? 0;
    $status = $_POST['status'] ?? '';

    $conn->query("
        INSERT INTO fuel_inventory 
        (tank_name, fuel_type, capacity, stock, status)
        VALUES 
        ('$tank','$fuel','$capacity','$stock','$status')
    ");

    header("Location: inventory.php");
    exit();
}

/* =========================
   DELETE INVENTORY
========================= */
if (isset($_GET['delete'])) {

    $id = intval($_GET['delete']);

    $conn->query("DELETE FROM fuel_inventory WHERE id=$id");

    header("Location: inventory.php");
    exit();
}

/* =========================
   FETCH DATA
========================= */
$result = $conn->query("SELECT * FROM fuel_inventory ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fuel Inventory</title>

    <link rel="stylesheet" href="inventory.css">
    <link rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body>

<div class="wrapper">

    <!-- SIDEBAR -->
    <div class="sidebar">

        <div class="brand">
            <i class="fa-solid fa-gas-pump"></i>

            <div>
                <h2>Makkah Usmania</h2>
                <small>Petrol Pump System</small>
            </div>
        </div>

        <nav>

            <a href="dashboard.php">Dashboard</a>
            <a href="sales.php">Fuel Sales</a>
            <a href="inventory.php" class="active">Fuel Inventory</a>
            <a href="lubes.php">Lubricants</a>
            <a href="khata.php">Digital Khata</a>

        </nav>

    </div>

    <!-- MAIN -->
    <div class="main">

        <div class="topbar">
            <h1>Fuel Inventory</h1>
            <p>Manage tanks, stock levels and fuel inventory records.</p>
        </div>

        <!-- FORM -->
        <form method="POST" class="form-grid">

            <?php if($edit){ ?>
                <input type="hidden" name="id" value="<?= $edit_row['id'] ?>">
            <?php } ?>

            <input 
                type="text" 
                name="tank_name" 
                placeholder="Tank Name"
                value="<?= $edit_row['tank_name'] ?? '' ?>"
                required
            >

            <select name="fuel_type" required>
                <option value="">Select Fuel Type</option>
                <option <?= ($edit_row['fuel_type'] ?? '')=='Petrol'?'selected':'' ?>>Petrol</option>
                <option <?= ($edit_row['fuel_type'] ?? '')=='Diesel'?'selected':'' ?>>Diesel</option>
            </select>

            <input 
                type="number" 
                name="capacity" 
                placeholder="Tank Capacity"
                value="<?= $edit_row['capacity'] ?? '' ?>"
                required
            >

            <input 
                type="number" 
                name="stock" 
                placeholder="Current Stock"
                value="<?= $edit_row['stock'] ?? '' ?>"
                required
            >

            <select name="status" required>
                <option value="">Select Status</option>
                <option <?= ($edit_row['status'] ?? '')=='Normal'?'selected':'' ?>>Normal</option>
                <option <?= ($edit_row['status'] ?? '')=='Low'?'selected':'' ?>>Low</option>
                <option <?= ($edit_row['status'] ?? '')=='Critical'?'selected':'' ?>>Critical</option>
            </select>

            <button type="submit" name="<?= $edit ? 'update_inventory' : '' ?>">
                <?= $edit ? 'Update Inventory' : 'Add Inventory' ?>
            </button>

        </form>

        <!-- TABLE -->
        <div class="table-wrapper">

            <table>

                <tr>
                    <th>ID</th>
                    <th>Tank Name</th>
                    <th>Fuel Type</th>
                    <th>Capacity</th>
                    <th>Stock</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>

                <?php while($row = $result->fetch_assoc()) { ?>

                <tr>

                    <td><?= $row['id'] ?></td>
                    <td><?= $row['tank_name'] ?></td>
                    <td><?= $row['fuel_type'] ?></td>
                    <td><?= $row['capacity'] ?> L</td>
                    <td><?= $row['stock'] ?> L</td>

                    <td class="
                        <?= 
                        $row['status'] == 'Normal' ? 'status-normal' : 
                        ($row['status'] == 'Low' ? 'status-low' : 'status-critical') 
                        ?>
                    ">
                        <?= $row['status'] ?>
                    </td>

                    <td>

                        <a href="inventory.php?edit=<?= $row['id'] ?>">
                            <button type="button" class="edit-btn">
                                Edit
                            </button>
                        </a>

                        <a href="inventory.php?delete=<?= $row['id'] ?>"
                        onclick="return confirm('Delete this inventory record?')">

                            <button 
                            type="button"
                            class="delete-btn">

                                Delete

                            </button>

                        </a>

                    </td>

                </tr>

                <?php } ?>

            </table>

        </div>

    </div>

</div>

</body>
</html>