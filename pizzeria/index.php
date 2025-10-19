<?php
session_start();
require 'db.php';

// Obsługa logowania
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nazwa'], $_POST['haslo'])) {
    $nazwa = mysqli_real_escape_string($conn, $_POST['nazwa']);
    $haslo = $_POST['haslo'];

    if ($nazwa === 'kucharz' && $haslo === '123') {
        $_SESSION['user'] = 'kucharz';
        unset($_SESSION['customer_id']);
    } else {
        $loginQuery = mysqli_query($conn, "SELECT * FROM Customers WHERE login='$nazwa'");
        $user = mysqli_fetch_assoc($loginQuery);

        if ($user && password_verify($haslo, $user['password'])) {
            $_SESSION['user'] = $user['login'];
            $_SESSION['customer_id'] = $user['customer_id'];
        } else {
            $error = 'Niepoprawne dane logowania.';
        }
    }
}

// Pobierz pizze
$pizzaQuery = mysqli_query($conn, "SELECT * FROM Pizzas ORDER BY pizza_id");
$pizzas = [];
while ($row = mysqli_fetch_assoc($pizzaQuery)) {
    $pizzas[] = $row;
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
<meta charset="UTF-8" />
<title>Pizzeria</title>
<style>
body { 
    font-family: 'Segoe UI', Arial, sans-serif; 
    margin:0; 
    background:#fff8f0; 
    color:#333; 
}

h1 { 
    text-align:center; 
    background:#e74c3c; 
    color:white; 
    padding:20px; 
    margin:0; 
    font-size:2em; 
    box-shadow:0 2px 6px rgba(0,0,0,0.2); 
}

p { 
    text-align:center; 
    margin-top:15px; 
}

a { 
    color:#e74c3c; 
    text-decoration:none; 
    font-weight:bold; 
}

a:hover { 
    text-decoration:underline; 
}

form { 
    display:flex; 
    justify-content:center; 
    align-items:center; 
    gap:10px; 
    margin:20px auto; 
}

form input[type="text"], form input[type="password"], form input[type="number"] { 
    padding:8px; 
    border-radius:5px; 
    border:1px solid #ccc; 
}

form button { 
    background:#e74c3c; 
    color:white; 
    border:none; 
    border-radius:5px; 
    padding:8px 15px; 
    cursor:pointer; 
    transition:0.2s; 
}

form button:hover { 
    background:#c0392b; 
}

.pizza-list { 
    display:grid; 
    grid-template-columns:repeat(auto-fit, minmax(280px, 1fr)); 
    gap:20px; 
    padding:20px; 
    max-width:1200px; 
    margin:0 auto; 
}

.pizzy { 
    display:flex; 
    flex-direction:column; 
    background:white; 
    border-radius:10px; 
    box-shadow:0 3px 8px rgba(0,0,0,0.15); 
    overflow:hidden; 
    transition:0.2s; 
}

.pizzy:hover { 
    transform:translateY(-4px); 
    box-shadow:0 5px 15px rgba(0,0,0,0.25); 
}

img { 
    width:100%; 
    height:200px; 
    object-fit:cover; 
}

.pizza-info { 
    padding:15px; 
    flex:1; 
    display:flex; 
    flex-direction:column; 
    justify-content:space-between; 
}

.pizza-info h2 { 
    margin:0; 
    color:#e74c3c; 
}

.pizza-info p { 
    margin:8px 0; 
}

.pizza-info strong { 
    color:#333; 
}

.error { 
    color:red; 
    text-align:center; 
    font-weight:bold; 
}

footer { 
    text-align:center; 
    padding:15px; 
    background:#f9eae1; 
    color:#555; 
    margin-top:30px; 
    font-size:0.9em; 
}

</style>
</head>
<body>

<h1>🍕 Witamy w Pizzerii</h1>

<?php if (!isset($_SESSION['user'])): ?>
<form method="POST">
    <label>Login: <input type="text" name="nazwa" required></label>
    <label>Hasło: <input type="password" name="haslo" required></label>
    <button type="submit">Zaloguj</button>
</form>
<?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
<p>Nie masz konta? <a href="register.php">Zarejestruj się</a></p>
<?php else: ?>
<p>Zalogowano jako: <strong><?= htmlspecialchars($_SESSION['user']) ?></strong> |
<a href="logout.php">Wyloguj</a> |
<a href="cart.php">🛒 Koszyk</a> |
<?php if ($_SESSION['user'] === 'kucharz'): ?>
<a href="kitchen.php">👨‍🍳 Panel kucharza</a>
<?php endif; ?>
</p>
<?php endif; ?>

<div class="pizza-list">
<?php foreach ($pizzas as $pizza): ?>
<div class="pizzy">
    <img src="images/<?= htmlspecialchars($pizza['image_url']) ?>" alt="<?= htmlspecialchars($pizza['name']) ?>">
    <div class="pizza-info">
        <h2><?= htmlspecialchars($pizza['name']) ?></h2>
        <p><?= htmlspecialchars($pizza['description']) ?></p>
        <p><strong>Cena:</strong> <?= number_format($pizza['base_price'],2) ?> zł</p>
        <form action="add_to_cart.php" method="POST">
            <input type="hidden" name="pizza_id" value="<?= $pizza['pizza_id'] ?>">
            <input type="number" name="quantity" value="1" min="1" style="width:60px;">
            <button type="submit">Dodaj do koszyka</button>
        </form>
    </div>
</div>
<?php endforeach; ?>
</div>

<footer>
© <?= date('Y') ?> Pizzeria — Wszystkie prawa zastrzeżone 🍕
</footer>
</body>
</html>
