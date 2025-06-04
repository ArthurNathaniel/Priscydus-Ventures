<?php
include 'db.php';

if (!isset($_GET['invoice_id']) || empty($_GET['invoice_id'])) {
    echo "Invalid invoice ID";
    exit();
}

$invoice_id = intval($_GET['invoice_id']);

// Fetch invoice details
$sql = "SELECT * FROM invoices WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $invoice_id);
$stmt->execute();
$result = $stmt->get_result();
$invoice = $result->fetch_assoc();
$stmt->close();

if (!$invoice) {
    echo "Invoice not found";
    exit();
}

// Fetch invoice items
$sql_items = "SELECT * FROM invoice_items WHERE invoice_id = ?";
$stmt_items = $conn->prepare($sql_items);
$stmt_items->bind_param("i", $invoice_id);
$stmt_items->execute();
$result_items = $stmt_items->get_result();
$items = [];
while ($row = $result_items->fetch_assoc()) {
    $items[] = $row;
}
$stmt_items->close();
$conn->close();

// Calculate subtotal
$subtotal = array_sum(array_column($items, 'subtotal'));

// Tax calculation (6.25%)
$tax_rate = 21.9 / 100;
$tax_amount = $subtotal * $tax_rate;

// Final total after tax
$final_total = $subtotal + $tax_amount;

// Invoice details
$invoice_number = htmlspecialchars($invoice['invoice_number']);
$client_name = htmlspecialchars($invoice['billed_to']);
$contact_number = htmlspecialchars($invoice['contact_number']);

// Format SMS message
$sms_items = "";
foreach ($items as $index => $item) {
    $sms_items .= ($index + 1) . ". " . htmlspecialchars($item['service_product']) . " x" . $item['quantity'] . " - GHC " . number_format($item['subtotal'], 2) . "\n";
}

$sms_message = "Hello $client_name,\n\n"
    . "Invoice #$invoice_number\n"
    . "----------------------\n"
    . "$sms_items"
    . "----------------------\n"
    . "SubTotal: GHC " . number_format($subtotal, 2) . "\n"
    . "Tax Amount: GHC " . number_format($tax_amount, 2) . "\n"
    . "Total: GHC " . number_format($final_total, 2) . "\n"
    . "Thank you for your business!";
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Invoice</title>
    <?php include 'cdn.php'; ?>
    <link rel="stylesheet" href="../css/base.css">
    <link rel="stylesheet" href="../css/print.css">

</head>

<body>
    <?php include 'sidebar.php'; ?>
    <div class="all" id="invoice-content">
        <div class="all_box">

            <!-- START OF INVOICE CONTENT -->

            <div class="invoice_header">
                <div class="logo"></div>
                <div class="invoice_header_info">
                    <h1>INVOICE</h1>
                    <p><strong>Tel:</strong> +233 54 369 8318 / +233 20 910 7701</p>
                    <!-- <p><strong>Tel:</strong> </p> -->
                    <p><strong>Loc:</strong> Mama Junction Adjacent Daybreak Off
                    <br> Sawaba to Anologa Junction Road
                    </p>
                </div>
            </div>

            <div class="invoice_info">
                <p><strong>Invoice Number:</strong> <?php echo $invoice_number; ?></p>
                <p><strong>Billed To:</strong> <?php echo $client_name; ?></p>
                <p><strong>Contact Number:</strong> <?php echo $contact_number; ?></p>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Service(s)</th>
                        <th>Day(s)</th>
                        <th>Price Per Day (GHC)</th>
                        <th>Subtotal (GHC)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $index => $item) { ?>
                        <tr>
                            <td><?php echo $index + 1; ?></td>
                            <td><?php echo htmlspecialchars($item['service_product']); ?></td>
                            <td><?php echo $item['quantity']; ?></td>
                            <td><?php echo number_format($item['price'], 2); ?></td>
                            <td><?php echo number_format($item['subtotal'], 2); ?></td>
                        </tr>
                    <?php } ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="4" style="text-align: right;"><strong>Subtotal:</strong></td>
                        <td><strong>GHC <?php echo number_format($subtotal, 2); ?></strong></td>
                    </tr>
                    <tr>
                        <td colspan="4" style="text-align: right;"><strong>NHIL (2.5%), GETfund (2.5%), COVID-19 Levy(1%), VAT(15%):</strong></td>
                        <td><strong>GHC <?php echo number_format($tax_amount, 2); ?></strong></td>
                    </tr>
                    <tr>
                        <td colspan="4" style="text-align: right;"><strong>Final Total:</strong></td>
                        <td><strong>GHC <?php echo number_format($final_total, 2); ?></strong></td>
                    </tr>
                </tfoot>
            </table>

            <div class="payment_details">
                <!-- <div class="momo">
                    <h2>Payment Info:</h2>
                    <h4>MTN Momo</h4>
                    <p>Mobile Money Number: +233 53 0529 610</p>
                    <p>Mobile Money Name: Ogazy G. Car Rentals</p>
                </div> -->
                <div class="bank">
                    <h4>Bank Transfer:</h4>
                    <p><strong>Bank Name:</strong> Ecobank Ghana</p>
                    <p><strong>Account Name:</strong> Priscydus Ventures</p>
                    <p><strong>Account Number:</strong> 1441004714646</p>
                    <p><strong>Bank Branch:</strong> Harper Road</p>
                </div>
            </div>

            <!-- END OF INVOICE CONTENT -->

            <div class="buttons">
                <button onclick="window.print()">Print Invoice</button>
                <button onclick="downloadPDF()">Download PDF</button>
                <a href="sms:<?php echo $contact_number; ?>?body=<?php echo urlencode($sms_message); ?>">
                    <button>Send SMS</button>
                </a>
            </div>

        </div>
    </div>

    <script>
        function downloadPDF() {
            const {
                jsPDF
            } = window.jspdf;

            // Hide the buttons
            document.querySelector(".buttons").style.display = "none";

            const element = document.getElementById("invoice-content");

            html2canvas(element, {
                scale: 2,
                useCORS: true,
                allowTaint: true,
                backgroundColor: "#ffffff"
            }).then(canvas => {
                const imgData = canvas.toDataURL("image/png");
                const pdf = new jsPDF("p", "mm", "a4");
                const imgProps = pdf.getImageProperties(imgData);
                const pdfWidth = pdf.internal.pageSize.getWidth();
                const pdfHeight = (imgProps.height * pdfWidth) / imgProps.width;

                pdf.addImage(imgData, "PNG", 0, 0, pdfWidth, pdfHeight);
                pdf.save("<?php echo $invoice_number; ?>.pdf");

                // Restore the buttons after PDF download
                document.querySelector(".buttons").style.display = "block";
            });
        }
    </script>
    <style>
        @media print {
            body {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                background-color: #fff;
            }

            .all {
                padding: 0 5% !important;
                margin-block: 50px !important;
              
            }

            /* Optional: Hide buttons or navigation when printing */
            .buttons,
            .sidebar {
                display: none !important;
            }

            /* Optional: Force page break after invoice if needed */
            .invoice-page-break {
                page-break-after: always;
            }
        }
    </style>

</body>

</html>