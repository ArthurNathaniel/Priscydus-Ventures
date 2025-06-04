<?php
include 'db.php'; // connect to your database
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>All Receipts</title>
    <?php include 'cdn.php'; ?>
    <link rel="stylesheet" href="../css/base.css">
</head>

<body>
    <?php include 'sidebar.php'; ?>
    <div class="all">
        <div class="all_box">
            <h2>All Receipts</h2>
            <div class="container">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Receipt ID</th>
                            <th>Date</th>
                            <th>Received From</th>
                            <th>Sum Amount (Words)</th>
                            <th>Being</th>
                            <th>Amount (GHS)</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $query = "SELECT * FROM receipts ORDER BY receipt_date DESC";
                        $result = $conn->query($query);
                        $count = 1;

                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                echo "<tr>
                                        <td>{$count}</td>
                                        <td>{$row['receipt_id']}</td>
                                        <td>{$row['receipt_date']}</td>
                                        <td>{$row['received_from']}</td>
                                        <td>{$row['sum_amount']}</td>
                                        <td>{$row['being']}</td>
                                        <td>GHS " . number_format($row['amount_figure'], 2) . "</td>
                                        <td><a href='print_receipt.php?receipt_id={$row['receipt_id']}' class='print-btn' target='_blank'>Print</a></td>
                                      </tr>";
                                $count++;
                            }
                        } else {
                            echo "<tr><td colspan='7'>No receipts found.</td></tr>";
                        }

                        $conn->close();
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>

</html>