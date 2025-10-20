<?php
session_start();
// --- Połączenie z bazą ---
$conn = mysqli_connect('localhost', 'root', '', 'pizzeria');
if (!$conn) die("❌ Błąd połączenia: " . mysqli_connect_error());
mysqli_set_charset($conn, "utf8mb4");

// --- Rejestracja użytkownika ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['phone'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $login = mysqli_real_escape_string($conn, $_POST['login']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);

    $check = mysqli_query($conn, "SELECT * FROM Customers WHERE login='$login'");
    if (mysqli_num_rows($check) > 0) {
        $error = "Login jest już zajęty!";
    } else {
        $query = "INSERT INTO Customers (name, login, password, email, phone, address)
                  VALUES ('$name', '$login', '$password', '$email', '$phone', '$address')";
        mysqli_query($conn, $query);
        header("Location: index.php?registered=1");
        exit();
    }
}

// --- Logowanie ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login_user'])) {
    $nazwa = mysqli_real_escape_string($conn, $_POST['nazwa']);
    $haslo = $_POST['haslo'];

    if ($nazwa === 'kucharz' && $haslo === '123') {
        $_SESSION['user'] = 'kucharz';
        unset($_SESSION['customer_id']);
    } else {
        $res = mysqli_query($conn, "SELECT * FROM Customers WHERE login='$nazwa'");
        $user = mysqli_fetch_assoc($res);
        if ($user && password_verify($haslo, $user['password'])) {
            $_SESSION['user'] = $user['login'];
            $_SESSION['customer_id'] = $user['customer_id'];
        } else {
            $error = 'Niepoprawne dane logowania.';
        }
    }
}

// --- Wylogowanie ---
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit();
}

// --- Dodawanie do koszyka ---
if (isset($_POST['add_to_cart'])) {
    $pizza_id = (int) $_POST['pizza_id'];
    $quantity = max(1, (int) $_POST['quantity']);
    $res = mysqli_query($conn, "SELECT * FROM Pizzas WHERE pizza_id='$pizza_id'");
    $pizza = mysqli_fetch_assoc($res);

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

// --- Usuwanie z koszyka ---
if (isset($_GET['remove'])) {
    $id = (int) $_GET['remove'];
    unset($_SESSION['cart'][$id]);
}

// --- Składanie zamówienia ---
if (isset($_POST['place_order']) && !empty($_SESSION['cart'])) {
    if (!isset($_SESSION['customer_id'])) {
        $error = "Musisz być zalogowany, aby złożyć zamówienie.";
    } else {
        $customer_id = $_SESSION['customer_id'];
        $total = 0;
        foreach ($_SESSION['cart'] as $i) $total += $i['price'] * $i['quantity'];

        mysqli_query($conn, "INSERT INTO Orders (customer_id, total_price, status, order_date) VALUES ('$customer_id', '$total', 'Nowe', NOW())");
        $order_id = mysqli_insert_id($conn);

        foreach ($_SESSION['cart'] as $pid => $i) {
            mysqli_query($conn, "INSERT INTO Order_Items (order_id, pizza_id, quantity, item_price) VALUES ('$order_id', '$pid', '{$i['quantity']}', '{$i['price']}')");
        }

        unset($_SESSION['cart']);
        $success = "✅ Zamówienie zostało złożone!";
    }
}

// --- Pobierz pizze ---
$pizzas = [];
$res = mysqli_query($conn, "SELECT * FROM Pizzas ORDER BY pizza_id");
while ($row = mysqli_fetch_assoc($res)) $pizzas[] = $row;
?>
<!DOCTYPE html>
<html lang="pl">
<head>
<meta charset="UTF-8">
<title>Pizzeria</title>
<style>
body { font-family:'Segoe UI',Arial,sans-serif;margin:0;background:#fff8f0;color:#333; }
h1 { text-align:center;background:#e74c3c;color:white;padding:20px;margin:0; }
.error{color:red;text-align:center;font-weight:bold;}
.success{color:green;text-align:center;font-weight:bold;}
form{display:flex;justify-content:center;align-items:center;gap:10px;margin:20px auto;}
button{background:#e74c3c;color:white;border:none;border-radius:5px;padding:8px 15px;cursor:pointer;}
button:hover{background:#c0392b;}
.pizza-list{display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:20px;padding:20px;max-width:1200px;margin:0 auto;}
.pizzy{background:white;border-radius:10px;box-shadow:0 3px 8px rgba(0,0,0,0.15);}
img{width:100%;height:200px;object-fit:cover;}
.pizza-info{padding:15px;}
.cart, .register, .login-box{max-width:800px;margin:30px auto;background:white;padding:20px;border-radius:10px;box-shadow:0 3px 8px rgba(0,0,0,0.15);}
.cart table{width:100%;border-collapse:collapse;}
.cart th,.cart td{padding:10px;border-bottom:1px solid #ddd;text-align:center;}
.hidden{display:none;}
.show{display:block;animation:fadeIn .4s ease-in;}
@keyframes fadeIn {from{opacity:0;transform:translateY(-5px);}to{opacity:1;transform:translateY(0);}}
</style>
</head>
<body>

<h1>🍕 Pizzeria Margherita</h1>

<?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
<?php if (isset($success)) echo "<p class='success'>$success</p>"; ?>

<?php if (!isset($_SESSION['user'])): ?>
<div class="login-box" id="loginBox">
    <h2 style="text-align:center;">🔐 Logowanie</h2>
    <form method="POST">
        <input type="text" name="nazwa" placeholder="Login" required>
        <input type="password" name="haslo" placeholder="Hasło" required>
        <button type="submit" name="login_user">Zaloguj</button>
    </form>
    <p style="text-align:center;">
        Nie masz konta?
        <button type="button" onclick="showRegister()">Zarejestruj się</button>
    </p>
</div>

<div class="register hidden" id="registerBox">
    <h2 style="text-align:center;">🧾 Rejestracja</h2>
    <form method="POST" style="flex-direction:column;align-items:center;">
        <input type="text" name="name" placeholder="Imię i nazwisko" required>
        <input type="text" name="login" placeholder="Login" required>
        <input type="password" name="password" placeholder="Hasło" required>
        <input type="email" name="email" placeholder="Email">
        <input type="text" name="phone" placeholder="Telefon" required>
        <input type="text" name="address" placeholder="Adres" required>
        <button type="submit">Zarejestruj się</button>
    </form>
    <p style="text-align:center;">
        Masz już konto?
        <button type="button" onclick="showLogin()">Zaloguj się</button>
    </p>
</div>

<script>
function showRegister() {
  document.getElementById('loginBox').classList.add('hidden');
  document.getElementById('registerBox').classList.remove('hidden');
  document.getElementById('registerBox').classList.add('show');
}
function showLogin() {
  document.getElementById('registerBox').classList.add('hidden');
  document.getElementById('loginBox').classList.remove('hidden');
  document.getElementById('loginBox').classList.add('show');
}
</script>
<?php else: ?>
<p style="text-align:center;">
Zalogowano jako: <strong><?= htmlspecialchars($_SESSION['user']) ?></strong> |
<a href="?logout=1">Wyloguj</a> |
</p>
<?php endif; ?>

<?php
if (isset($_SESSION['user'])) {
    if ($_SESSION['user'] == 'kucharz') {
        # Kucharz
        $getOrders = mysqli_query($conn, "
            SELECT o.order_id, o.order_date, o.total_price, c.name AS customer_name
            FROM Orders o
            LEFT JOIN Customers c ON o.customer_id = c.customer_id
            ORDER BY o.order_date DESC
        ");
        ?>
<?php
// Obsługa formularza "Zrealizowano"
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['complete_order'])) {
    $completed_id = (int)$_POST['order_id'];
    mysqli_query($conn, "UPDATE Orders SET status = 'zrealizowane' WHERE order_id = $completed_id");
}
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
        .completed { opacity: 0.5; }
        form.complete-btn { margin-top: 10px; }
    </style>
</head>
<body>
    <h2>👨‍🍳 Panel kucharza – zamówienia</h2>

    <?php
    // POBIERANIE TYLKO NIEZREALIZOWANYCH ZAMÓWIEŃ
    $getOrders = mysqli_query($conn, "
        SELECT o.order_id, o.order_date, o.total_price, o.status, c.name AS customer_name
        FROM Orders o
        LEFT JOIN Customers c ON o.customer_id = c.customer_id
        WHERE o.status != 'zrealizowane'
        ORDER BY o.order_date DESC
    ");

    if (mysqli_num_rows($getOrders) === 0): ?>
        <p>Brak nowych zamówień.</p>
    <?php else: ?>
        <?php while ($order = mysqli_fetch_assoc($getOrders)): ?>
            <div class="order <?= $order['status'] === 'zrealizowane' ? 'completed' : '' ?>">
                <strong>Zamówienie #<?= $order["order_id"] ?></strong><br>
                Data: <?= $order["order_date"] ?><br>
                Klient: <?= htmlspecialchars($order["customer_name"] ?? "Gość") ?><br>
                Wartość: <?= number_format($order["total_price"], 2) ?> zł<br>
                <em>Pizze:</em>
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
                        <li><?= htmlspecialchars($item["name"]) ?> × <?= $item["quantity"] ?> (<?= number_format($item["item_price"], 2) ?> zł)</li>
                    <?php endwhile; ?>
                </ul>

                <!-- Formularz oznaczania jako zrealizowane -->
                <form method="POST" class="complete-btn">
                    <input type="hidden" name="order_id" value="<?= $order["order_id"] ?>">
                    <button type="submit" name="complete_order">✅ Zrealizowano</button>
                </form>
            </div>
        <?php endwhile; ?>
    <?php endif; ?>
</body>
</html>
        <?php
    } else {
        # Lista pizz
        ?>
        <!DOCTYPE html>
        <html lang="pl">
        <head>
            <meta charset="UTF-8" />
            <title>Menu</title>
            <style>
                body { font-family: Arial; background: #fff8f0; margin: 40px auto; max-width: 900px; }
                .pizza-list { display: flex; flex-wrap: wrap; gap: 20px; }
                .pizzy { border: 1px solid #ccc; border-radius: 8px; padding: 10px; width: 250px; background: #fff; }
                .pizza-info { margin-top: 10px; }
                .cart { margin-top: 30px; }
            </style>
        </head>
        <body>
            <div class="pizza-list">
                <?php foreach ($pizzas as $pizza): ?>
                    <div class="pizzy">
                        <img src="images/<?= htmlspecialchars($pizza['image_url']) ?>" alt="pizza" width="200">
                        <div class="pizza-info">
                            <h2><?= htmlspecialchars($pizza["name"]) ?></h2>
                            <p><?= htmlspecialchars($pizza["description"]) ?></p>
                            <p><strong><?= number_format($pizza["base_price"], 2) ?> zł</strong></p>
                            <form method="POST">
                                <input type="hidden" name="pizza_id" value="<?= $pizza['pizza_id'] ?>">
                                <input type="number" name="quantity" value="1" min="1" style="width:60px;">
                                <button name="add_to_cart">Dodaj</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php if (!empty($_SESSION['cart'])): ?>
                <div id="koszyk" class="cart">
                    <h2>🛒 Twój koszyk</h2>
                    <form method="POST">
                        <table>
                            <tr><th>Pizza</th><th>Cena</th><th>Ilość</th><th>Suma</th><th>Usuń</th></tr>
                            <?php
                            $total = 0;
                            foreach ($_SESSION['cart'] as $id => $item):
                                $sum = $item['price'] * $item['quantity'];
                                $total += $sum;
                                ?>
                                <tr>
                                    <td><?= htmlspecialchars($item["name"]) ?></td>
                                    <td><?= number_format($item["price"], 2) ?> zł</td>
                                    <td><?= $item["quantity"] ?></td>
                                    <td><?= number_format($sum, 2) ?> zł</td>
                                    <td><a href="?remove=<?= $id ?>">❌</a></td>
                                </tr>
                            <?php endforeach; ?>
                            <tr><th colspan="3">Razem:</th><th><?= number_format($total, 2) ?> zł</th><th></th></tr>
                        </table>
                        <br>
                        <button type="submit" name="place_order">Złóż zamówienie</button>
                    </form>
                </div>
            <?php endif; ?>
        </body>
        </html>
        <?php
    }
} else{
        # Lista pizz
        ?>
        <!DOCTYPE html>
        <html lang="pl">
        <head>
            <meta charset="UTF-8" />
            <title>Menu</title>
            <style>
                body { font-family: Arial; background: #fff8f0; margin: 40px auto; max-width: 900px; }
                .pizza-list { display: flex; flex-wrap: wrap; gap: 20px; }
                .pizzy { border: 1px solid #ccc; border-radius: 8px; padding: 10px; width: 250px; background: #fff; }
                .pizza-info { margin-top: 10px; }
                .cart { margin-top: 30px; }
            </style>
        </head>
        <body>
            <div class="pizza-list">
                <?php foreach ($pizzas as $pizza): ?>
                    <div class="pizzy">
                        <img src="images/<?= htmlspecialchars($pizza['image_url']) ?>" alt="pizza" width="200">
                        <div class="pizza-info">
                            <h2><?= htmlspecialchars($pizza["name"]) ?></h2>
                            <p><?= htmlspecialchars($pizza["description"]) ?></p>
                            <p><strong><?= number_format($pizza["base_price"], 2) ?> zł</strong></p>
                            <form method="POST">
                                <input type="hidden" name="pizza_id" value="<?= $pizza['pizza_id'] ?>">
                                <input type="number" name="quantity" value="1" min="1" style="width:60px;">
                                <button name="add_to_cart">Dodaj</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php if (!empty($_SESSION['cart'])): ?>
                <div id="koszyk" class="cart">
                    <h2>🛒 Twój koszyk</h2>
                    <form method="POST">
                        <table>
                            <tr><th>Pizza</th><th>Cena</th><th>Ilość</th><th>Suma</th><th>Usuń</th></tr>
                            <?php
                            $total = 0;
                            foreach ($_SESSION['cart'] as $id => $item):
                                $sum = $item['price'] * $item['quantity'];
                                $total += $sum;
                                ?>
                                <tr>
                                    <td><?= htmlspecialchars($item["name"]) ?></td>
                                    <td><?= number_format($item["price"], 2) ?> zł</td>
                                    <td><?= $item["quantity"] ?></td>
                                    <td><?= number_format($sum, 2) ?> zł</td>
                                    <td><a href="?remove=<?= $id ?>">❌</a></td>
                                </tr>
                            <?php endforeach; ?>
                            <tr><th colspan="3">Razem:</th><th><?= number_format($total, 2) ?> zł</th><th></th></tr>
                        </table>
                        <br>
                        <button type="submit" name="place_order">Złóż zamówienie</button>
                    </form>
                </div>
            <?php endif; ?>
        </body>
        </html>
        <?php
}
?>
