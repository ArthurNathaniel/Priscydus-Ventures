<?php
include 'db.php';

$year = date("y");
$random = str_pad(rand(1, 999999), 6, '0', STR_PAD_LEFT);
$receipt_id = "PV-$random-$year";
$date = date("Y-m-d");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $received_from = $_POST['received_from'];
    $sum_amount = $_POST['sum_amount'];
    $being = $_POST['being'];
    $amount_figure = $_POST['amount_figure'];

    $stmt = $conn->prepare("INSERT INTO receipts (receipt_id, receipt_date, received_from, sum_amount, being, amount_figure) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssd", $receipt_id, $date, $received_from, $sum_amount, $being, $amount_figure);

    if ($stmt->execute()) {
        echo "<script>alert('Receipt saved successfully!'); window.location.href='receipts_history.php';</script>";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Generate Receipt</title>
    <?php include 'cdn.php'; ?>
    <link rel="stylesheet" href="../css/base.css">
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="all">
        <div class="all_box">
            <h2>Receipt Generator</h2>
            <form method="POST">
           <div class="forms">
    <label>Receipt ID:</label>
    <input type="text" name="receipt_id" value="<?php echo $receipt_id; ?>" readonly>
</div>

<div class="forms">
    <label>Date:</label>
    <input type="text" name="date" value="<?php echo $date; ?>" readonly>
</div>

                <div class="forms">
                    <label>Received From:</label>
                    <input type="text" name="received_from" required>
                </div>

                <div class="forms">
                    <label>The Sum Amount Of (words):</label>
                    <input type="text" name="sum_amount" id="sum_amount" readonly>
                </div>

                <div class="forms">
                    <label>Being (Purpose of Payment):</label>
                    <input type="text" name="being" required>
                </div>

                <div class="forms">
                    <label>Amount in Figure:</label>
                    <input type="number" step="0.01" name="amount_figure" id="amount_figure" required oninput="updateAmountInWords()">
                </div>

                <div class="forms">
                    <button type="submit">Save Receipt</button>
                </div>
            </form>
        </div>
    </div>

<script>
function numberToWords(num) {
    const ones = ["", "One", "Two", "Three", "Four", "Five", "Six", "Seven", "Eight", "Nine", "Ten",
        "Eleven", "Twelve", "Thirteen", "Fourteen", "Fifteen", "Sixteen",
        "Seventeen", "Eighteen", "Nineteen"
    ];
    const tens = ["", "", "Twenty", "Thirty", "Forty", "Fifty", "Sixty", "Seventy", "Eighty", "Ninety"];
    const scales = ["", "Thousand", "Million", "Billion", "Trillion", "Quadrillion", "Quintillion", "Sextillion", "Septillion", "Octillion", "Nonillion", "Decillion"];

    function chunkToWords(n) {
        let str = '';
        if (n >= 100) {
            str += ones[Math.floor(n / 100)] + " Hundred";
            n %= 100;
            if (n > 0) str += " and ";
        }
        if (n >= 20) {
            str += tens[Math.floor(n / 10)];
            if (n % 10 > 0) str += " " + ones[n % 10];
        } else if (n > 0) {
            str += ones[n];
        }
        return str;
    }

    function inWords(n) {
        if (n === 0) return "Zero";
        let chunks = [];
        while (n > 0) {
            chunks.push(n % 1000);
            n = Math.floor(n / 1000);
        }

        if (chunks.length > scales.length) return "Amount too large";

        let words = [];
        for (let i = 0; i < chunks.length; i++) {
            if (chunks[i] > 0) {
                words.unshift(chunkToWords(chunks[i]) + (scales[i] ? " " + scales[i] : ""));
            }
        }

        return words.join(" ");
    }

    let [cedis, pesewas] = num.toFixed(2).split(".");
    let words = inWords(parseInt(cedis)) + " Ghana Cedis";
    if (parseInt(pesewas) > 0) {
        words += " and " + inWords(parseInt(pesewas)) + " Pesewas";
    }
    return words;
}

function updateAmountInWords() {
    const amountField = document.getElementById('amount_figure');
    const sumAmountField = document.getElementById('sum_amount');
    const amount = parseFloat(amountField.value);
    if (!isNaN(amount)) {
        sumAmountField.value = numberToWords(amount);
    } else {
        sumAmountField.value = '';
    }
}
</script>

</body>
</html>
