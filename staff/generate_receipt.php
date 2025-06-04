<?php
include 'db.php';

if (isset($_GET['invoice_number'])) {
    $invoice_number = $_GET['invoice_number'];

    // Fetch invoice details
    $stmt = $conn->prepare("SELECT * FROM invoices WHERE invoice_number = ?");
    $stmt->bind_param("s", $invoice_number);
    $stmt->execute();
    $result = $stmt->get_result();
    $invoice = $result->fetch_assoc();

    if (!$invoice) {
        die("Invoice not found.");
    }
} else {
    die("No invoice number provided.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Receipt</title>
    <?php include 'cdn.php'; ?>
    <link rel="stylesheet" href="../css/base.css">
</head>
<body>
    <div class="all">
        <div class="all_box">
            <h2>Generate Receipt</h2>
            <form method="post" action="save_receipt.php">
                <div class="forms">
                    <label>Invoice Number:</label>
                    <input type="text" name="invoice_number" value="<?php echo $invoice['invoice_number']; ?>" readonly>
                </div>

                <div class="forms">
                    <label>Billed To:</label>
                    <input type="text" value="<?php echo $invoice['billed_to']; ?>" readonly>
                </div>

                <div class="forms">
                    <label>Contact Number:</label>
                    <input type="text" value="<?php echo $invoice['contact_number']; ?>" readonly>
                </div>

                <div class="forms">
                    <label>Total Amount:</label>
                    <input type="text" value="<?php echo number_format($invoice['total_amount'], 2); ?>" readonly>
                </div>

                <div class="forms">
                    <label>Amount Paid:</label>
                    <input type="number" name="amount_paid" required>
                </div>

                <div class="forms">
                    <button type="submit">Save Receipt</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>