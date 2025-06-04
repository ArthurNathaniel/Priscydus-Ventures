<?php
session_start();
include 'db.php';

// Redirect to login if not logged in
if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit();
}

$admin_name = $_SESSION["admin_name"];

// Generate invoice number
$year = date("y");
$random_number = rand(10000, 99999);
$invoice_number = "PV-$random_number-$year";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $invoice_number = $_POST["invoice_number"];
    $billed_to = $_POST["billed_to"];
    $contact_number = $_POST["contact_number"];
    $total_amount = $_POST["total_amount"];
    $discount = $_POST["discount"];
    $date_created = date("Y-m-d H:i:s"); // Set current date/time

    // Insert invoice data
    $stmt = $conn->prepare("INSERT INTO invoices (invoice_number, billed_to, contact_number, total_amount, discount, date_created) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssdds", $invoice_number, $billed_to, $contact_number, $total_amount, $discount, $date_created);

    if ($stmt->execute()) {
        $invoice_id = $stmt->insert_id; // Get last inserted ID

        // Insert invoice items
        $services = $_POST["service_product"];
        $quantities = $_POST["quantity"];
        $prices = $_POST["price"];

        $stmt_items = $conn->prepare("INSERT INTO invoice_items (invoice_id, service_product, quantity, price, subtotal) VALUES (?, ?, ?, ?, ?)");

        foreach ($services as $index => $service) {
            $quantity = $quantities[$index];
            $price = $prices[$index];
            $subtotal = $quantity * $price;

            $stmt_items->bind_param("isidd", $invoice_id, $service, $quantity, $price, $subtotal);
            $stmt_items->execute();
        }

        echo "<script>alert('Invoice saved successfully!'); window.location.href='create_invoice.php';</script>";
    } else {
        echo "<script>alert('Error saving invoice.');</script>";
    }

    $stmt->close();
    $stmt_items->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Invoice</title>
    <?php include 'cdn.php'; ?>
    <link rel="stylesheet" href="../css/base.css">
</head>

<body>
    <?php include 'sidebar.php'; ?>
    <div class="all">
        <div class="all_box">
            <h2>Create Invoice</h2>
            <form method="post">
                <div class="forms">
                    <label>Invoice Number:</label>
                    <input type="text" name="invoice_number" value="<?php echo $invoice_number; ?>" readonly>
                </div>
                <div class="forms">
                    <label>Billed To:</label>
                    <input type="text" name="billed_to" required>
                </div>
                <div class="forms">
                    <label>Contact Number:</label>
                    <input type="text" name="contact_number" required>
                </div>
                <table id="invoiceTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Service(s)</th>
                            <th>Day(s)</th>
                            <th>Price Per Day (GHC)</th>
                            <th>Subtotal (GHC)</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                    <tfoot>
                        <tr>
                            <td colspan="5" style="text-align: right;"><label>Discount (%):</label></td>
                            <td><input type="number" id="discount" name="discount" value="0" oninput="calculateTotal()"></td>
                        </tr>
                        <tr>
                            <td colspan="4" style="text-align: right;"><strong>Subtotal:</strong></td>
                            <td colspan="2"><span id="subtotalAmount">0.00</span></td>
                        </tr>
                        <tr>
                            <td colspan="4" style="text-align: right;"><strong>NHIL (2.5%), GETfund (2.5%), COVID-19 Levy(1%), VAT(15%):</strong></td>
                            <td colspan="2"><span id="taxAmount">0.00</span></td>
                        </tr>
                        <tr>
                            <td colspan="4" style="text-align: right;"><strong>Final Total:</strong></td>
                            <td colspan="2"><span id="totalAmount">0.00</span></td>
                        </tr>
                    </tfoot>
                </table>
                <div class="forms">
                    <button type="button" onclick="addRow()">Add Item</button>
                </div>
                <input type="hidden" id="totalAmountInput" name="total_amount">
                <div class="forms">
                    <button type="submit">Save Invoice</button>
                </div>
            </form>
        </div>
    </div>
    <script>
        function addRow() {
            let table = document.getElementById("invoiceTable").getElementsByTagName('tbody')[0];
            let row = table.insertRow();
            row.innerHTML = `
                <td></td>
                <td><input type="text" name="service_product[]" required></td>
                <td><input type="number" name="quantity[]" class="qty" min="1" value="1" required oninput="calculateTotal()"></td>
                <td><input type="number" name="price[]" class="price" min="0" value="0" required oninput="calculateTotal()"></td>
                <td class="subtotal">0.00</td>
                <td><button type="button" onclick="removeRow(this)"><i class="fa-solid fa-trash"></i></button></td>
            `;
            updateRowNumbers();
            calculateTotal();
        }

        function removeRow(button) {
            let row = button.parentNode.parentNode;
            row.parentNode.removeChild(row);
            updateRowNumbers();
            calculateTotal();
        }

        function updateRowNumbers() {
            let rows = document.querySelectorAll("#invoiceTable tbody tr");
            rows.forEach((row, index) => {
                row.cells[0].innerText = index + 1;
            });
        }

        function calculateTotal() {
            let subtotal = 0;
            document.querySelectorAll("#invoiceTable tbody tr").forEach(row => {
                let qty = parseFloat(row.querySelector(".qty").value) || 0;
                let price = parseFloat(row.querySelector(".price").value) || 0;
                let rowSubtotal = qty * price;
                row.querySelector(".subtotal").innerText = rowSubtotal.toFixed(2);
                subtotal += rowSubtotal;
            });
            let discount = parseFloat(document.getElementById("discount").value) || 0;
            let discountedSubtotal = subtotal - (subtotal * (discount / 100));
            let taxRate = 21.9 / 100;
            let taxAmount = discountedSubtotal * taxRate;
            let finalTotal = discountedSubtotal + taxAmount;
            document.getElementById("subtotalAmount").innerText = subtotal.toFixed(2);
            document.getElementById("taxAmount").innerText = taxAmount.toFixed(2);
            document.getElementById("totalAmount").innerText = finalTotal.toFixed(2);
            document.getElementById("totalAmountInput").value = finalTotal.toFixed(2);
        }
    </script>
</body>

</html>