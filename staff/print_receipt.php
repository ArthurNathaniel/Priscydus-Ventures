<?php
include 'db.php';

if (isset($_GET['receipt_id'])) {
    $receipt_id = $_GET['receipt_id'];

    $query = "SELECT * FROM receipts WHERE receipt_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $receipt_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $receipt = $result->fetch_assoc();
    } else {
        die("Receipt not found.");
    }

    $stmt->close();
} else {
    die("Receipt ID is required.");
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Print Receipt</title>
    <?php include 'cdn.php'; ?>
    <link rel="stylesheet" href="../css/base.css">
    <link rel="stylesheet" href="../css/print.css">

    <!-- ✅ html2canvas and jsPDF -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
</head>

<body>
<?php include 'sidebar.php'; ?>

<div class="receipt_all" id="receipt-content">
    <div class="logo"></div>
    <div class="receipt_heading">
        <p><strong>Location:</strong> Mama Junction Adjacent Daybreak Off
Sawaba to Anologa Junction Road</p>
        <p><strong>Tel:</strong> +233 54 369 8318 / +233 20 910 7701</p>
        <p><strong>Email:</strong> <span style="text-transform: lowercase;">info@priscydus.com</span></p>
    </div>
    <br>
    <div class="official_center"><span>Official Receipt</span></div>
    <br>
    <div class="official_flex">
        <p><strong>Receipt ID:</strong> <?php echo $receipt['receipt_id']; ?></p>
        <p><strong>Date:</strong> <?php echo $receipt['receipt_date']; ?></p>
    </div>

    <div class="official_details">
        <p><strong>Received From:</strong> <span><?php echo $receipt['received_from']; ?></span></p>
        <p><strong>The Sum Amount Of:</strong> <span><?php echo $receipt['sum_amount']; ?></span></p>
        <p><strong>Being:</strong> <span><?php echo $receipt['being']; ?></span></p>
    </div>
    <br>
    <div class="official_flexs">

        
        <p><strong>Amount in Figure:</strong> <br><br> 
           <span><circle>GHS <?php echo number_format($receipt['amount_figure'], 2); ?></circle></span></p>
           
           <p><i>Thank You!</i></p>
          
    </div>
    <div class="receipt-footer buttons">  
    <button onclick="window.print()">Print Receipt</button>
    <button onclick="downloadPDF()">Download PDF</button>
</div>

</div>


<script>
function downloadPDF() {
    const { jsPDF } = window.jspdf;

    // Hide footer buttons
    document.querySelector(".receipt-footer").style.display = "none";

    const element = document.getElementById("receipt-content");

    html2canvas(element, {
        scale: 1.5,
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
        pdf.save("receipt_<?php echo $receipt['receipt_id']; ?>.pdf");

        // Show footer again
        document.querySelector(".receipt-footer").style.display = "block";
    });
}
</script>

</body>
</html>
