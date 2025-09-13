<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/helpers.php';

try {
    $sql = "ALTER TABLE quotations ADD COLUMN total_amount DECIMAL(10,2) DEFAULT 0";
    $pdo->exec($sql);
} catch (PDOException $e) {
    // Ignore error if column already exists
    if (strpos($e->getMessage(), 'Duplicate column name') === false) {
        error_log("Error adding total_amount column: " . $e->getMessage());
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $customer_name = sanitize($_POST['customer_name']);
        $customer_email = sanitize($_POST['customer_email']);
        $customer_phone = sanitize($_POST['customer_phone']);
        $company = isset($_POST['company']) ? sanitize($_POST['company']) : '';
        $requirements = isset($_POST['requirements']) ? sanitize($_POST['requirements']) : '';

        // Combine company and requirements into notes
        $notes = '';
        if (!empty($company)) {
            $notes .= "Company: " . $company . "\n";
        }
        if (!empty($requirements)) {
            $notes .= "Requirements: " . $requirements;
        }

        // Insert quotation
        $stmt = $pdo->prepare("INSERT INTO quotations (customer_name, customer_email, customer_phone, notes) VALUES (?, ?, ?, ?)");
        $stmt->execute([$customer_name, $customer_email, $customer_phone, $notes]);
        $quotation_id = $pdo->lastInsertId();

        // Calculate total amount for quotation items
        $total_amount = 0;

        // Insert items if cart_data is provided
        if (isset($_POST['cart_data'])) {
            $cart_items = json_decode($_POST['cart_data'], true);
            if (is_array($cart_items)) {
                foreach ($cart_items as $item) {
                    $stmt = $pdo->prepare("INSERT INTO quotation_items (quotation_id, product_id, product_name, qty, price, wholesale_price, moq) VALUES (?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([
                        $quotation_id,
                        $item['id'] ?? '',
                        $item['title'] ?? '',
                        $item['qty'] ?? $item['quantity'] ?? 1,
                        $item['price'] ?? 0,
                        $item['wholesale_price'] ?? null,
                        $item['moq'] ?? 1
                    ]);
                    $qty = $item['qty'] ?? $item['quantity'] ?? 1;
                    $price = $item['price'] ?? 0;
                    $total_amount += $qty * $price;
                }
            }
        }

        // Update total_amount in quotations table
        $updateStmt = $pdo->prepare("UPDATE quotations SET total_amount = ? WHERE id = ?");
        $updateStmt->execute([$total_amount, $quotation_id]);

        echo json_encode(['success' => true, 'message' => 'Quotation request sent successfully!']);
    } catch (Exception $e) {
        error_log('Quotation submission error: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
