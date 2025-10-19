<?php
session_start();
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pizza_id'], $_POST['quantity'])) {
    $pizza_id = (int) $_POST['pizza_id'];
    $quantity = max(1, (int) $_POST['quantity']);

    $pizzaResult = mysqli_query($conn, "SELECT * FROM Pizzas WHERE pizza_id='$pizza_id'");
    $pizza = mysqli_fetch_assoc($pizzaResult);

    if ($pizza) {
        if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
        if (isset($_SESSION['cart'][$pizza_id])) {
            $_SESSION['cart'][$pizza_id]['quantity'] += $quantity;
        } else {
            $_SESSION['cart'][$pizza_id] = [
                'name' => $pizza['name'],
                'price' => $pizza['base_price'],
                'quantity' => $quantity
            ];
        }
    }
}

header('Location: index.php');
exit();
