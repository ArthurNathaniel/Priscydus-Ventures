<?php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate and sanitize inputs
    $invoice_number = isset($_POST['invoice_number']) ? trim($_POST['invoice_number']) : '';
    $billed_to = isset($_POST['billed_to']) ? trim($_POST['billed_to']) : '';
    $contact_number = isset($_POST['contact_number']) ? trim($_POST['contact_number']) : '';
    $discount = isset($_POST['discount']) && $_POST['discount'] !== '' ? (float)$_POST['discount'] : 0;
    $total_amount = isset($_POST['total_amount']) && $_POST['total_amount'] !== '' ? (float)$_POST['total_amount'] : 0;

    // Debugging: Check if total_amount is received
    if ($total_amount == 0) {
        die("Error: Total amount is missing or zero.");
    }

    // Calculate tax (21.9%)
    $tax_rate = 21.9 / 100;
    $tax_amount = $total_amount * $tax_rate;
    $final_total = $total_amount + $tax_amount;

    // Insert invoice details into the invoices table
    $sql = "INSERT INTO invoices (invoice_number, billed_to, contact_number, discount, total_amount, tax_amount, final_total) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Error preparing statement: " . $conn->error);
    }
    
    $stmt->bind_param("sssdddd", $invoice_number, $billed_to, $contact_number, $discount, $total_amount, $tax_amount, $final_total);

    if ($stmt->execute()) {
        $invoice_id = $stmt->insert_id;
        $stmt->close();

        // Insert invoice items into the invoice_items table
        if (!empty($_POST['service_product'])) {
            $sql_items = "INSERT INTO invoice_items (invoice_id, service_product, quantity, price, subtotal) VALUES (?, ?, ?, ?, ?)";
            $stmt_items = $conn->prepare($sql_items);

            if (!$stmt_items) {
                die("Error preparing item statement: " . $conn->error);
            }

            foreach ($_POST['service_product'] as $index => $service_product) {
                $quantity = isset($_POST['quantity'][$index]) ? (int)$_POST['quantity'][$index] : 0;
                $price = isset($_POST['price'][$index]) ? (float)$_POST['price'][$index] : 0;
                $subtotal = $quantity * $price;

                $stmt_items->bind_param("isidd", $invoice_id, $service_product, $quantity, $price, $subtotal);
                $stmt_items->execute();
            }

            $stmt_items->close();
        }

        $conn->close();

        // Redirect to print invoice
        header("Location: print_invoice.php?invoice_id=" . $invoice_id);
        exit();
    } else {
        die("Error executing statement: " . $stmt->error);
    }
} else {
    die("Invalid request");
}