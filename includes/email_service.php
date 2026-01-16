<?php
/**
 * Email Service
 * SUNDARI TOP STAR S.R.L.
 * Handles email notifications for orders
 */

/**
 * Send emails for a new order
 * @param int $orderId
 * @param string $orderNumber
 * @return bool
 */
function sendOrderEmails($orderId, $orderNumber) {
    // Check if email notifications are enabled
    if (getSetting('email_orders_enabled', '0') !== '1') {
        return false;
    }

    $db = db();

    // Obține detalii comandă
    $stmt = $db->prepare("
        SELECT o.*, oi.product_name, oi.quantity, oi.price, oi.subtotal,
               u.email as user_email, u.name as user_name
        FROM orders o
        LEFT JOIN users u ON o.user_id = u.id
        LEFT JOIN order_items oi ON o.id = oi.order_id
        WHERE o.id = ?
    ");
    $stmt->execute([$orderId]);
    $orderData = $stmt->fetchAll();

    if (empty($orderData)) {
        return false;
    }

    $order = $orderData[0];

    // 1. Email către admin
    $adminEmailResult = sendEmailToAdmin($order, $orderData);

    // 2. Email confirmare către client
    $customerEmailResult = false;
    if (!empty($order['user_email'])) {
        $customerEmailResult = sendEmailToCustomer($order, $orderData);
    }

    return $adminEmailResult || $customerEmailResult;
}

/**
 * Send email to admin about new order
 * @param array $order
 * @param array $orderItems
 * @return bool
 */
function sendEmailToAdmin($order, $orderItems) {
    $adminEmail = getSetting('admin_email');
    if (empty($adminEmail)) {
        return false;
    }

    $subject = "Comandă Nouă #{$order['order_number']} - SUNDARI TOP STAR";

    $message = generateAdminEmailTemplate($order, $orderItems);

    $headers = generateEmailHeaders($adminEmail);

    return mail($adminEmail, $subject, $message, $headers);
}

/**
 * Send confirmation email to customer
 * @param array $order
 * @param array $orderItems
 * @return bool
 */
function sendEmailToCustomer($order, $orderItems) {
    $customerEmail = $order['user_email'];
    if (empty($customerEmail)) {
        return false;
    }

    $subject = "Confirmare Comandă #{$order['order_number']} - SUNDARI TOP STAR";

    $message = generateCustomerEmailTemplate($order, $orderItems);

    $headers = generateEmailHeaders($customerEmail);

    return mail($customerEmail, $subject, $message, $headers);
}

/**
 * Send test email
 * @param string $toEmail
 * @return bool
 */
function sendTestEmail($toEmail) {
    $subject = "Test Email - SUNDARI TOP STAR";
    $message = "Acesta este un email de test pentru a verifica configurația SMTP.\n\n";
    $message .= "Dacă primești acest email, configurația este corectă!\n\n";
    $message .= "O zi bună,\nEchipa SUNDARI TOP STAR";

    $headers = generateEmailHeaders($toEmail);

    return mail($toEmail, $subject, $message, $headers);
}

/**
 * Generate email headers
 * @param string $to
 * @return string
 */
function generateEmailHeaders($to) {
    $fromName = getSetting('email_from_name', 'SUNDARI TOP STAR');
    $fromAddress = getSetting('email_from_address', 'noreply@sundari.ro');

    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "From: " . $fromName . " <" . $fromAddress . ">\r\n";
    $headers .= "Reply-To: " . $fromAddress . "\r\n";
    $headers .= "Return-Path: " . $fromAddress . "\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";

    return $headers;
}

/**
 * Generate admin email template
 * @param array $order
 * @param array $orderItems
 * @return string
 */
function generateAdminEmailTemplate($order, $orderItems) {
    $html = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; font-size: 14px; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background-color: #2c3e50; color: #fff; padding: 20px; text-align: center; border-radius: 5px 5px 0 0; }
            .content { background-color: #f9f9f9; padding: 20px; border: 1px solid #ddd; }
            .order-info { background-color: #fff; padding: 15px; margin: 10px 0; border: 1px solid #ddd; border-radius: 5px; }
            .order-info h3 { margin: 0 0 15px 0; color: #2c3e50; }
            .order-info p { margin: 5px 0; }
            .products-table { width: 100%; border-collapse: collapse; margin: 20px 0; }
            .products-table th { background-color: #2c3e50; color: #fff; padding: 10px; text-align: left; }
            .products-table td { padding: 10px; border: 1px solid #ddd; }
            .products-table tr:nth-child(even) { background-color: #f9f9f9; }
            .total { font-size: 18px; font-weight: bold; color: #e74c3c; text-align: right; margin: 20px 0; }
            .footer { text-align: center; padding: 20px; color: #777; font-size: 12px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>Comandă Nouă!</h1>
                <p>Număr comandă: <strong>{$order['order_number']}</strong></p>
            </div>

            <div class='content'>
                <div class='order-info'>
                    <h3>Client</h3>
                    <p><strong>Nume:</strong> {$order['shipping_name']}</p>
                    <p><strong>Email:</strong> {$order['user_email']}</p>
                    <p><strong>Telefon:</strong> {$order['shipping_phone']}</p>
                </div>

                <div class='order-info'>
                    <h3>Livrare</h3>
                    <p><strong>Adresă:</strong> {$order['shipping_address']}</p>
                    <p><strong>Oraș:</strong> {$order['shipping_city']}</p>
                    <p><strong>Județ:</strong> {$order['shipping_county']}</p>
                    <p><strong>Cod Poștal:</strong> {$order['shipping_postal_code']}</p>
                </div>

                <div class='order-info'>
                    <h3>Observații</h3>
                    <p>" . ($order['notes'] ?: 'Fără observații') . "</p>
                </div>

                <h3>Produse Comandate</h3>
                <table class='products-table'>
                    <thead>
                        <tr>
                            <th>Produs</th>
                            <th>Cantitate</th>
                            <th>Preț</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>";

    foreach ($orderItems as $item) {
        $html .= "
                        <tr>
                            <td>{$item['product_name']}</td>
                            <td>{$item['quantity']}</td>
                            <td>" . number_format($item['price'], 2) . " RON</td>
                            <td>" . number_format($item['subtotal'], 2) . " RON</td>
                        </tr>";
    }

    $html .= "
                    </tbody>
                </table>

                <div class='total'>
                    Total: " . number_format($order['total_amount'], 2) . " RON
                </div>

                <p style='text-align: center; margin: 20px 0;'>
                    <a href='https://sundari.ro/admin/orders.php?action=view&id={$order['id']}' style='background-color: #3498db; color: #fff; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>
                        Vezi Comanda în Admin
                    </a>
                </p>
            </div>

            <div class='footer'>
                <p>Acesta este un email automat generat de SUNDARI TOP STAR.</p>
                <p>Data: " . date('d.m.Y H:i') . "</p>
            </div>
        </div>
    </body>
    </html>";

    return $html;
}

/**
 * Generate customer email template
 * @param array $order
 * @param array $orderItems
 * @return string
 */
function generateCustomerEmailTemplate($order, $orderItems) {
    $html = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; font-size: 14px; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background-color: #27ae60; color: #fff; padding: 20px; text-align: center; border-radius: 5px 5px 0 0; }
            .content { background-color: #f9f9f9; padding: 20px; border: 1px solid #ddd; }
            .order-details { background-color: #fff; padding: 15px; margin: 10px 0; border: 1px solid #ddd; border-radius: 5px; }
            .order-details h3 { margin: 0 0 15px 0; color: #27ae60; }
            .order-details p { margin: 5px 0; }
            .products-table { width: 100%; border-collapse: collapse; margin: 20px 0; }
            .products-table th { background-color: #27ae60; color: #fff; padding: 10px; text-align: left; }
            .products-table td { padding: 10px; border: 1px solid #ddd; }
            .products-table tr:nth-child(even) { background-color: #f9f9f9; }
            .total { font-size: 18px; font-weight: bold; color: #27ae60; text-align: right; margin: 20px 0; }
            .footer { text-align: center; padding: 20px; color: #777; font-size: 12px; }
            .thank-you { text-align: center; font-size: 18px; color: #27ae60; margin: 20px 0; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>Comanda Taia a fost Plasată cu Succes! 🎉</h1>
                <p>Număr comandă: <strong>#{$order['order_number']}</strong></p>
            </div>

            <div class='content'>
                <div class='thank-you'>
                    <p>Mulțumim pentru comandă! Vei primi o confirmare telefonică în curând.</p>
                </div>

                <div class='order-details'>
                    <h3>Detalii Comandă</h3>
                    <p><strong>Data:</strong> " . date('d.m.Y H:i', strtotime($order['created_at'])) . "</p>
                    <p><strong>Livrare la:</strong></p>
                    <p style='margin-left: 15px;'>{$order['shipping_name']}</p>
                    <p style='margin-left: 15px;'>{$order['shipping_address']}, {$order['shipping_city']}</p>
                    <p style='margin-left: 15px;'>Județ {$order['shipping_county']}, Cod Poștal {$order['shipping_postal_code']}</p>
                </div>

                <h3>Produse Comandate</h3>
                <table class='products-table'>
                    <thead>
                        <tr>
                            <th>Produs</th>
                            <th>Cantitate</th>
                            <th>Preț</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>";

    foreach ($orderItems as $item) {
        $html .= "
                        <tr>
                            <td>{$item['product_name']}</td>
                            <td>{$item['quantity']}</td>
                            <td>" . number_format($item['price'], 2) . " RON</td>
                            <td>" . number_format($item['subtotal'], 2) . " RON</td>
                        </tr>";
    }

    $html .= "
                    </tbody>
                </table>

                <div class='total'>
                    Total Comandă: <strong>" . number_format($order['total_amount'], 2) . " RON</strong>
                </div>

                <div style='margin-top: 30px; padding: 15px; background-color: #e8f8f5; border-left: 4px solid #27ae60; border-radius: 5px;'>
                    <p style='margin: 0;'><strong>Următorul:</strong> Un membru al echipei noastre te va contacta telefonic pentru confirmarea comenzii și detalii despre livrare.</p>
                </div>
            </div>

            <div class='footer'>
                <p>SUNDARI TOP STAR S.R.L. - Piese Mașini de Cusut</p>
                <p>Telefon: " . getSetting('contact_phone', '') . " | Email: " . getSetting('contact_email', '') . "</p>
                <p>Data: " . date('d.m.Y H:i') . "</p>
            </div>
        </div>
    </body>
    </html>";

    return $html;
}
