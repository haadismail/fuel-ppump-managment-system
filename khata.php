<?php

$conn = new mysqli("localhost", "root", "", "petrol_pump");

if ($conn->connect_error) {
    die("DB Connection Failed");
}

$today = date("Y-m-d");

/* =========================
   DELETE
========================= */
if(isset($_GET['delete'])) {

    $id = intval($_GET['delete']);

    $conn->query("
        DELETE FROM khata
        WHERE id = $id
    ");

    header("Location: khata.php");
    exit();
}

/* =========================
   MARK REMINDER DONE
========================= */
if(isset($_GET['done'])) {

    $id = intval($_GET['done']);

    $conn->query("
        UPDATE khata
        SET reminder_sent = 1
        WHERE id = $id
    ");

    header("Location: khata.php");
    exit();
}

/* =========================
   INSERT
========================= */
if(isset($_POST['save_khata'])) {

    $person_name      = trim($_POST['person_name']);
    $contact_number   = trim($_POST['contact_number']);
    $address          = trim($_POST['address']);
    $purchase_item    = trim($_POST['purchase_item']);
    $quantity         = floatval($_POST['quantity']);
    $advance_amount   = $_POST['advance_amount'] === '' ? 0 : floatval($_POST['advance_amount']);
    $remaining_amount = floatval($_POST['remaining_amount']);
    $promise_date     = $_POST['promise_date'];
    $entry_date       = $_POST['entry_date'];

    $stmt = $conn->prepare("
        INSERT INTO khata
        (
            person_name,
            contact_number,
            address,
            purchase_item,
            quantity,
            advance_amount,
            remaining_amount,
            promise_date,
            entry_date,
            reminder_sent
        )
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 0)
    ");

    $stmt->bind_param(
        "ssssidsss",
        $person_name,
        $contact_number,
        $address,
        $purchase_item,
        $quantity,
        $advance_amount,
        $remaining_amount,
        $promise_date,
        $entry_date
    );

    $stmt->execute();
    $stmt->close();

    header("Location: khata.php");
    exit();
}

/* =========================
   UPDATE
========================= */
if(isset($_POST['update_khata'])) {

    $id = intval($_POST['id']);

    $person_name      = trim($_POST['person_name']);
    $contact_number   = trim($_POST['contact_number']);
    $address          = trim($_POST['address']);
    $purchase_item    = trim($_POST['purchase_item']);
    $quantity         = floatval($_POST['quantity']);
    $advance_amount   = $_POST['advance_amount'] === '' ? 0 : floatval($_POST['advance_amount']);
    $remaining_amount = floatval($_POST['remaining_amount']);
    $promise_date     = $_POST['promise_date'];
    $entry_date       = $_POST['entry_date'];

    $stmt = $conn->prepare("
        UPDATE khata SET
            person_name = ?,
            contact_number = ?,
            address = ?,
            purchase_item = ?,
            quantity = ?,
            advance_amount = ?,
            remaining_amount = ?,
            promise_date = ?,
            entry_date = ?
        WHERE id = ?
    ");

    $stmt->bind_param(
        "ssssidsssi",
        $person_name,
        $contact_number,
        $address,
        $purchase_item,
        $quantity,
        $advance_amount,
        $remaining_amount,
        $promise_date,
        $entry_date,
        $id
    );

    $stmt->execute();
    $stmt->close();

    header("Location: khata.php");
    exit();
}

/* =========================
   EDIT FETCH
========================= */
$edit = false;
$edit_row = [];

if(isset($_GET['edit'])) {

    $edit = true;

    $id = intval($_GET['edit']);

    $edit_row = $conn->query("
        SELECT *
        FROM khata
        WHERE id = $id
    ")->fetch_assoc();
}

/* =========================
   FILTER SYSTEM
========================= */
$filter = $_GET['filter'] ?? '';
$search = trim($_GET['search'] ?? '');

$sql = "
    SELECT *
    FROM khata
    WHERE 1=1
";

if($filter == "pending") {

    $sql .= "
        AND remaining_amount > 0
    ";
}

if($filter == "promise") {

    $sql .= "
        AND promise_date <= CURDATE()
        AND remaining_amount > 0
    ";
}

if($search != '') {

    $sql .= "
        AND person_name LIKE '%$search%'
    ";
}

$sql .= " ORDER BY id DESC";

$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1.0">

<title>Digital Khata</title>

<link rel="stylesheet" href="khata.css">

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

</head>

<body>

<div class="wrapper">

    <!-- SIDEBAR -->
    <div class="sidebar">

        <div class="brand">

            <i class="fa fa-book"></i>

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

            <a href="khata.php" class="active">
                <i class="fa fa-book"></i>
                Digital Khata
            </a>

        </nav>

    </div>

    <!-- MAIN -->
    <div class="main">

        <!-- TOPBAR -->
        <div class="topbar">

            <h1>Digital Khata</h1>

            <p>
                Manage customer pending payments
                and reminders easily.
            </p>

        </div>

        <!-- FILTER -->
        <form method="GET" class="filter-bar">

            <input
                type="text"
                name="search"
                placeholder="Search Customer Name"
                value="<?= htmlspecialchars($search) ?>"
            >

            <select name="filter">

                <option value="">
                    All Records
                </option>

                <option
                value="pending"
                <?= $filter=="pending" ? 'selected' : '' ?>>

                    Pending Payments

                </option>

                <option
                value="promise"
                <?= $filter=="promise" ? 'selected' : '' ?>>

                    Promise Due

                </option>

            </select>

            <button type="submit">

                <i class="fa fa-filter"></i>
                Filter

            </button>

        </form>

        <!-- FORM -->
        <form method="POST" class="form-grid">

            <?php if($edit){ ?>

            <input
                type="hidden"
                name="id"
                value="<?= $edit_row['id'] ?>"
            >

            <?php } ?>

            <input
                type="text"
                name="person_name"
                placeholder="Customer Name"
                required
                value="<?= $edit_row['person_name'] ?? '' ?>"
            >

            <input
                type="text"
                name="contact_number"
                placeholder="Contact Number"
                required
                value="<?= $edit_row['contact_number'] ?? '' ?>"
            >

            <input
                type="text"
                name="address"
                placeholder="Address"
                required
                value="<?= $edit_row['address'] ?? '' ?>"
            >

            <input
                type="date"
                name="entry_date"
                required
                value="<?= $edit_row['entry_date'] ?? '' ?>"
            >

            <input
                type="text"
                name="purchase_item"
                placeholder="Purchase Item"
                required
                value="<?= $edit_row['purchase_item'] ?? '' ?>"
            >

            <input
                type="number"
                step="0.01"
                name="quantity"
                placeholder="Quantity"
                required
                value="<?= $edit_row['quantity'] ?? '' ?>"
            >

            <!-- ADVANCE PAYMENT -->
            <input
                type="number"
                step="0.01"
                name="advance_amount"
                placeholder="Advance Payment"
                value="<?= $edit_row['advance_amount'] ?? '' ?>"
            >

            <input
                type="number"
                step="0.01"
                name="remaining_amount"
                placeholder="Remaining Amount"
                required
                value="<?= $edit_row['remaining_amount'] ?? '' ?>"
            >

            <input
                type="date"
                name="promise_date"
                required
                value="<?= $edit_row['promise_date'] ?? '' ?>"
            >

            <button
            type="submit"
            name="<?= $edit ? 'update_khata' : 'save_khata' ?>">

                <?= $edit ? 'Update Khata' : 'Save Khata' ?>

            </button>

        </form>

        <!-- TABLE -->
        <div class="table-wrapper">

            <table>

                <tr>

                    <th>ID</th>

                    <th>Name</th>

                    <th>Contact</th>

                    <th>Address</th>

                    <th>Item</th>

                    <th>Qty</th>

                    <th>Advance</th>

                    <th>Remaining</th>

                    <th>Promise Date</th>

                    <th>Status</th>

                    <th>WhatsApp</th>

                    <th>Actions</th>

                </tr>

                <?php
                if($result && $result->num_rows > 0){

                    while($row = $result->fetch_assoc()){

                    $overdue =
                    (
                        $row['promise_date'] < $today
                        &&
                        $row['remaining_amount'] > 0
                    );

                    $phone = preg_replace(
                        '/^0/',
                        '',
                        $row['contact_number']
                    );

                    $phone = "92".$phone;
                ?>

                <tr class="<?= $overdue ? 'overdue-row' : '' ?>">

                    <td><?= $row['id'] ?></td>

                    <td><?= htmlspecialchars($row['person_name']) ?></td>

                    <td><?= htmlspecialchars($row['contact_number']) ?></td>

                    <td><?= htmlspecialchars($row['address']) ?></td>

                    <td><?= htmlspecialchars($row['purchase_item']) ?></td>

                    <td><?= number_format($row['quantity'],2) ?></td>

                    <!-- ADVANCE SHOW -->
                    <td>
                        PKR <?= number_format($row['advance_amount'],2) ?>
                    </td>

                    <td>
                        PKR <?= number_format($row['remaining_amount'],2) ?>
                    </td>

                    <td><?= $row['promise_date'] ?></td>

                    <td>

                        <?=
                        $row['remaining_amount'] <= 0
                        ?
                        '<span class="status-paid">PAID</span>'
                        :
                        '<span class="status-pending">PENDING</span>'
                        ?>

                    </td>

                    <td>

                        <?php
                        if(
                            $row['remaining_amount'] > 0
                            &&
                            $row['reminder_sent'] == 0
                        ){
                        ?>

            <a
target="_blank"
href="https://wa.me/<?= $phone ?>?text=This%20is%20Haji%20Muhammad%20Ashiq%20from%20Makkah%20Usmania.%20Dear%20<?= urlencode($row['person_name']) ?>,%20you%20have%20purchased%20<?= urlencode($row['purchase_item']) ?>%20on%20<?= $row['entry_date'] ?>%20with%20advance%20payment%20of%20<?= $row['advance_amount'] > 0 ? 'PKR%20'.$row['advance_amount'] : 'N/A' ?>%20and%20remaining%20amount%20of%20PKR%20<?= $row['remaining_amount'] ?>.%20Kindly%20clear%20the%20due%20till%20<?= $row['promise_date'] ?>.%20Thank%20you.">

    <button
    type="button"
    class="whatsapp-btn">

        Send

    </button>

</a>
                        <?php } else { ?>

                        <button
                        type="button"
                        class="done-btn"
                        disabled>

                            Done

                        </button>

                        <?php } ?>

                    </td>

                    <td class="action-buttons">

                        <a href="?edit=<?= $row['id'] ?>">

                            <button
                            type="button"
                            class="edit-btn">

                                Edit

                            </button>

                        </a>

                        <a
                        href="?delete=<?= $row['id'] ?>"
                        onclick="return confirm('Delete this record?')">

                            <button
                            type="button"
                            class="delete-btn">

                                Delete

                            </button>

                        </a>

                        <a href="?done=<?= $row['id'] ?>">

                            <button
                            type="button"
                            class="ok-btn">

                                OK

                            </button>

                        </a>

                    </td>

                </tr>

                <?php
                    }

                } else {
                ?>

                <tr>

                    <td colspan="12" class="empty-row">

                        No Records Found

                    </td>

                </tr>

                <?php } ?>

            </table>

        </div>

    </div>

</div>

</body>
</html>