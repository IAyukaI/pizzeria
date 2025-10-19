<?php
session_start();
require 'db.php';

$cart = $_SESSION['cart'] ?? [];

// 🗑️ Usuwanie produktu
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove'])) {
    $remove_id = $_POST['remove'];
    if (isset($_SESSION['cart'][$remove_id])) {
        unset($_SESSION['cart'][$remove_id]);
    }
    header("Location: cart.php");
    exit;
}

// ➕➖ Zmiana ilości
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_quantity'])) {
    $pizza_id = $_POST['update_quantity'];
    $action = $_POST['action'];

    if (isset($_SESSION['cart'][$pizza_id])) {
        if ($action === 'increase') {
            $_SESSION['cart'][$pizza_id]['quantity']++;
        } elseif ($action === 'decrease') {
            $_SESSION['cart'][$pizza_id]['quantity']--;
            if ($_SESSION['cart'][$pizza_id]['quantity'] <= 0) {
                unset($_SESSION['cart'][$pizza_id]);
            }
        }
    }
    header("Location: cart.php");
    exit;
}

// ✅ Składanie zamówienia
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order']) && !empty($cart)) {
    if (!isset($_SESSION['customer_id'])) {
        $error = "Musisz być zalogowany, aby złożyć zamówienie.";
    } else {
        $customer_id = $_SESSION['customer_id'];
        $total = 0;
        foreach ($cart as $item) {
            $total += $item['price'] * $item['quantity'];
        }

        $queryOrder = "INSERT INTO Orders (customer_id, total_price) VALUES ('$customer_id', '$total')";
        mysqli_query($conn, $queryOrder);
        $order_id = mysqli_insert_id($conn);

        foreach ($cart as $pizza_id => $item) {
            $name = mysqli_real_escape_string($conn, $item['name']);
            $price = $item['price'];
            $quantity = $item['quantity'];
            mysqli_query($conn, "INSERT INTO Order_Items (order_id, pizza_id, quantity, item_price) VALUES ('$order_id', '$pizza_id', '$quantity', '$price')");
        }

        unset($_SESSION['cart']);
        $success = "Zamówienie zostało złożone! Dziękujemy :)";
        $cart = [];
    }
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
<meta charset="UTF-8">
<title>Koszyk</title>
<style>
body {
    font-family: Arial, sans-serif;
    background: #fff8f0;
    max-width: 800px;
    margin: 40px auto;
}
h2 { color: #e74c3c; text-align: center; }
table { width: 100%; border-collapse: collapse; margin-top: 20px; background: white; }
th { background: #e74c3c; color: white; padding: 10px; }
td { border: 1px solid #ddd; padding: 8px; text-align: center; }
button { cursor: pointer; border: none; border-radius: 5px; padding: 6px 10px; }
.remove { background: #e74c3c; color: white; }
.order { background: #27ae60; color: white; display: block; margin: 20px auto; padding: 10px 20px; font-size: 16px; }
a { text-decoration: none; color: #2980b9; }
</style>
</head>
<body>
<h2>🛒 Twój koszyk</h2>

<?php if (!empty($error)) echo "<p style='color:red;'>$error</p>"; ?>
<?php if (!empty($success)) echo "<p style='color:green;'>$success</p>"; ?>

<?php if (empty($cart)): ?>
    <p>Twój koszyk jest pusty.</p>
<?php else: ?>
<form method="POST">
    <table>
        <tr><th>Pizza</th><th>Cena</th><th>Ilość</th><th>Razem</th><th>Akcje</th></tr>
        <?php $total = 0; foreach ($cart as $pizza_id => $item):
            $sum = $item['price'] * $item['quantity']; $total += $sum; ?>
        <tr>
            <td><?= htmlspecialchars($item['name']) ?></td>
            <td><?= number_format($item['price'], 2) ?> zł</td>
            <td><?= $item['quantity'] ?></td>
            <td><?= number_format($sum, 2) ?> zł</td>
            <td><button name="remove" value="<?= $pizza_id ?>" class="remove">Usuń</button></td>
        </tr>
        <?php endforeach; ?>
        <tr><td colspan="3"><strong>Razem</strong></td><td colspan="2"><strong><?= number_format($total, 2) ?> zł</strong></td></tr>
    </table>
    <button type="submit" name="order" class="order">Złóż zamówienie</button>
</form>
<?php endif; ?>

<p><a href="index.php">← Powrót do menu</a></p>
</body>
</html>
