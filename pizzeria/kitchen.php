<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user']) || $_SESSION['user'] !== 'kucharz') {
    header('Location: index.php');
    exit();
}

$getOrders = mysqli_query($conn, "
    SELECT o.order_id, o.order_date, o.total_price, c.name AS customer_name
    FROM Orders o
    LEFT JOIN Customers c ON o.customer_id = c.customer_id
    ORDER BY o.order_date DESC
");
?>

<!DOCTYPE html>
<html lang="pl">
<head>
<meta charset="UTF-8" />
<title>Panel kucharza</title>
<style>
body { font-family: Arial; background: #fff8f0; margin: 40px auto; max-width: 900px; }
h2 { color: #e74c3c; text-align: center; }
.order { border: 1px solid #ccc; padding: 10px; margin: 10px 0; border-radius: 8px; background: #fff; }
</style>
</head>
<body>
<h2>👨‍🍳 Panel kucharza – zamówienia</h2>
<a href="index.php">← Powrót</a>

<?php if (mysqli_num_rows($getOrders) === 0): ?>
    <p>Brak zamówień.</p>
<?php else: ?>
    <?php while ($order = mysqli_fetch_assoc($getOrders)): ?>
        <div class="order">
            <strong>Zamówienie #<?= $order['order_id'] ?></strong><br>
            Data: <?= $order['order_date'] ?><br>
            Klient: <?= htmlspecialchars($order['customer_name'] ?? 'Gość') ?><br>
            Wartość: <?= number_format($order['total_price'], 2) ?> zł<br>
            <em>Pozycje:</em>
            <ul>
                <?php
                $order_id = $order['order_id'];
                $getItems = mysqli_query($conn, "
                    SELECT oi.quantity, oi.item_price, p.name 
                    FROM Order_Items oi 
                    JOIN Pizzas p ON oi.pizza_id = p.pizza_id 
                    WHERE oi.order_id = '$order_id'
                ");
                while ($item = mysqli_fetch_assoc($getItems)): ?>
                    <li><?= htmlspecialchars($item['name']) ?> × <?= $item['quantity'] ?> (<?= number_format($item['item_price'], 2) ?> zł)</li>
                <?php endwhile; ?>
            </ul>
        </div>
    <?php endwhile; ?>
<?php endif; ?>
</body>
</html>
