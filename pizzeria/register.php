<?php
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
?>

<!DOCTYPE html>
<html lang="pl">
<head>
<meta charset="UTF-8" />
<title>Rejestracja</title>
<style>
body { font-family: Arial; background: #fff8f0; text-align: center; margin-top: 40px; }
form { background: white; display: inline-block; padding: 20px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
input { display: block; width: 250px; margin: 8px auto; padding: 8px; border: 1px solid #ccc; border-radius: 5px; }
button { background: #27ae60; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; }
a { color: #2980b9; text-decoration: none; }
</style>
</head>
<body>
<h2>🧾 Rejestracja klienta</h2>
<?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
<form method="POST">
    <input type="text" name="name" placeholder="Imię i nazwisko" required>
    <input type="text" name="login" placeholder="Login" required>
    <input type="password" name="password" placeholder="Hasło" required>
    <input type="email" name="email" placeholder="Email">
    <input type="text" name="phone" placeholder="Telefon" required>
    <input type="text" name="address" placeholder="Adres" required>
    <button type="submit">Zarejestruj się</button>
</form>
<p><a href="index.php">← Powrót do logowania</a></p>
</body>
</html>
