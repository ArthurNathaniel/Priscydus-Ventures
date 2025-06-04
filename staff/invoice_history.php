<?php
include 'db.php';

// Fetch all invoices
$sql = "SELECT * FROM invoices ORDER BY date_created DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoices</title>
    <?php include 'cdn.php'; ?>
    <link rel="stylesheet" href="../css/base.css">
</head>

<body>
    <?php include 'sidebar.php'; ?>
    <div class="all">
        <div class="all_box">
            <h2>Invoices</h2>
            <table border="1" cellpadding="10" cellspacing="0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Invoice Number</th>
                        <th>Billed To</th>
                        <th>Contact</th>
                        <th>Discount (%)</th>
                        <th>Total Amount</th>
                        <th>Created At</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $count = 1;
                    while ($invoice = $result->fetch_assoc()) {
                        // Calculate final total after discount (assuming no tax is needed)
                        $final_total = $invoice['total_amount'] - ($invoice['total_amount'] * ($invoice['discount'] / 100));
                    ?>
                        <tr>
                            <td><?php echo $count++; ?></td>
                            <td><?php echo $invoice['invoice_number']; ?></td>
                            <td><?php echo $invoice['billed_to']; ?></td>
                            <td><?php echo $invoice['contact_number']; ?></td>
                            <td><?php echo $invoice['discount']; ?>%</td>
                            <td><?php echo number_format($final_total, 2); ?></td>
                            <td><?php echo $invoice['date_created']; ?></td>
                            <td><a href="print_invoice.php?invoice_id=<?php echo $invoice['id']; ?>">Print</a></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>

<?php $conn->close(); ?>