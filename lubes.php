<?php
$conn = new mysqli("localhost", "root", "", "petrol_pump");

if ($conn->connect_error) {
    die("Database Connection Failed");
}


if (isset($_POST['add_lube'])) {

    $name = trim($_POST['name'] ?? '');
    $brand = trim($_POST['brand'] ?? '');
    $stock = floatval($_POST['stock'] ?? 0);
    $purchase = floatval($_POST['purchase_price'] ?? 0);
    $sale = floatval($_POST['sale_price'] ?? 0);
    $status = trim($_POST['status'] ?? '');

    if (
        !empty($name) &&
        !empty($brand) &&
        $stock >= 0 &&
        $purchase >= 0 &&
        $sale >= 0 &&
        !empty($status)
    ) {

        $stmt = $conn->prepare("
            INSERT INTO lubricants_inventory
            (name, brand, stock, purchase_price, sale_price, status)
            VALUES (?, ?, ?, ?, ?, ?)
        ");

        $stmt->bind_param(
            "ssddds",
            $name,
            $brand,
            $stock,
            $purchase,
            $sale,
            $status
        );

        $stmt->execute();
        $stmt->close();

        header("Location: lubes.php");
        exit();
    }
}


if(isset($_GET['delete'])) {

    $id = intval($_GET['delete']);

    $stmt = $conn->prepare("DELETE FROM lubricants_inventory WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    header("Location: lubes.php");
    exit();
}


$edit = false;
$edit_row = [];

if(isset($_GET['edit'])) {

    $edit = true;

    $id = intval($_GET['edit']);

    $stmt = $conn->prepare("SELECT * FROM lubricants_inventory WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    $result_edit = $stmt->get_result();
    $edit_row = $result_edit->fetch_assoc();

    $stmt->close();
}


$result = $conn->query("
    SELECT * FROM lubricants_inventory
    ORDER BY id DESC
");
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Lubricants Management</title>

    <link rel="stylesheet" href="lubes.css">
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

            <a href="dashboard.php">Dashboard</a>
            <a href="sales.php">Fuel Sales</a>
            <a href="inventory.php">Fuel Inventory</a>
            <a href="lubes.php" class="active">Lubricants</a>
            <a href="khata.php">Digital Khata</a>

        </nav>

    </div>

    <!-- MAIN -->
    <div class="main">

        <div class="topbar">
            <h1>Lubricants Management</h1>
        </div>

        <!-- FORM -->
        <form method="POST" class="form-grid">

            <input type="text" name="name" placeholder="Product Name" required
            value="<?= $edit_row['name'] ?? '' ?>">

            <input type="text" name="brand" placeholder="Brand Name" required
            value="<?= $edit_row['brand'] ?? '' ?>">

            <input type="number" step="0.01" name="stock" placeholder="Stock"
            value="<?= $edit_row['stock'] ?? '' ?>" required>

            <input type="number" step="0.01" name="purchase_price" placeholder="Purchase Price"
            value="<?= $edit_row['purchase_price'] ?? '' ?>" required>

            <input type="number" step="0.01" name="sale_price" placeholder="Sale Price"
            value="<?= $edit_row['sale_price'] ?? '' ?>" required>

            <select name="status" required>

                <option value="">Select Status</option>

                <option value="Available"
                    <?= (isset($edit_row['status']) && $edit_row['status']=='Available') ? 'selected' : '' ?>>
                    Available
                </option>

                <option value="Low Stock"
                    <?= (isset($edit_row['status']) && $edit_row['status']=='Low Stock') ? 'selected' : '' ?>>
                    Low Stock
                </option>

                <option value="Out of Stock"
                    <?= (isset($edit_row['status']) && $edit_row['status']=='Out of Stock') ? 'selected' : '' ?>>
                    Out of Stock
                </option>

            </select>

            <button type="submit" name="add_lube">
                <?= $edit ? 'Update (Add New Entry)' : 'Add Lubricant' ?>
            </button>

            <?php if($edit){ ?>
                <a href="lubes.php">
                    <button type="button" style="background:red;color:white;">
                        Cancel Edit
                    </button>
                </a>
            <?php } ?>

        </form>

        <!-- TABLE -->
        <div class="table-wrapper">

            <table>

                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Brand</th>
                    <th>Stock</th>
                    <th>Purchase</th>
                    <th>Sale</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>

                <?php if ($result && $result->num_rows > 0) {

                    while($row = $result->fetch_assoc()) { ?>

                    <tr>

                        <td><?= $row['id'] ?></td>
                        <td><?= htmlspecialchars($row['name']) ?></td>
                        <td><?= htmlspecialchars($row['brand']) ?></td>
                        <td><?= number_format($row['stock'], 2) ?></td>
                        <td><?= number_format($row['purchase_price'], 2) ?></td>
                        <td><?= number_format($row['sale_price'], 2) ?></td>

                        <td><?= htmlspecialchars($row['status']) ?></td>

                        <td>

                            <a href="lubes.php?edit=<?= $row['id'] ?>">
                                <button type="button" style="background:orange;color:white;">
                                    Edit
                                </button>
                            </a>

                            <a href="lubes.php?delete=<?= $row['id'] ?>"
                               onclick="return confirm('Delete this item?')">

                                <button type="button" style="background:red;color:white;">
                                    Delete
                                </button>

                            </a>

                        </td>

                    </tr>

                <?php } } else { ?>

                    <tr>
                        <td colspan="8">No Lubricants Found</td>
                    </tr>

                <?php } ?>

            </table>

        </div>

    </div>

</div>

</body>
</html>
