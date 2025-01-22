<?php
session_start();

// Połączenie z bazą danych
$db = new mysqli("localhost", "root", "", "moja_strona");
if ($db->connect_error) {
    die("Nie można połączyć z bazą danych: " . $db->connect_error);
}

// Inicjalizacja koszyka, jeśli nie istnieje
if (!isset($_SESSION['koszyk'])) {
    $_SESSION['koszyk'] = [];
}

// Funkcja dodawania do koszyka
function addToCart($productId, $quantity, $productName, $productPrice) {
    if (isset($_SESSION['koszyk'][$productId])) {
        $_SESSION['koszyk'][$productId]['quantity'] += $quantity;
    } else {
        $_SESSION['koszyk'][$productId] = [
            'name' => $productName,
            'price' => $productPrice,
            'quantity' => $quantity
        ];
    }
}

// Funkcja usuwania z koszyka z wyborem ilości
function removeFromCart($productId, $quantity) {
    if (isset($_SESSION['koszyk'][$productId])) {
        $_SESSION['koszyk'][$productId]['quantity'] -= $quantity;
        if ($_SESSION['koszyk'][$productId]['quantity'] <= 0) {
            // Funkcja usuwa produkt, jeśli ilość wynosi 0 lub mniej
            unset($_SESSION['koszyk'][$productId]); 
        }
    }
}

// Obsługa dodawania do koszyka
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    $productId = $_POST['product_id'];
    $quantity = (int)$_POST['quantity'];
    $productName = $_POST['product_name'];
    $productPrice = (float)$_POST['price'];

    addToCart($productId, $quantity, $productName, $productPrice);
}

// Obsługa usuwania z koszyka
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_id'])) {
    $productId = $_POST['remove_id'];
    $quantity = isset($_POST['remove_quantity']) ? (int)$_POST['remove_quantity'] : 1;
    removeFromCart($productId, $quantity);
}

// Obsługa zakupu
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['purchase'])) {
    foreach ($_SESSION['koszyk'] as $productId => $product) {
        $quantity = $product['quantity'];
        // Aktualizacja ilości w bazie danych
        $query = "UPDATE products SET available_quantity = available_quantity - ? WHERE id = ? AND available_quantity >= ?";
        $stmt = $db->prepare($query);
        $stmt->bind_param("iii", $quantity, $productId, $quantity);
        $stmt->execute();
    }
    // Wyczyść koszyk po zakupie
    $_SESSION['koszyk'] = [];
    echo "<p class='success-message'>Zakup zakończony sukcesem!</p>";
}

// Funkcja wyświetlania koszyka
function showCart($db) {
    if (empty($_SESSION['koszyk'])) {
        echo "<p>Koszyk jest pusty.</p>";
    } else {
        // Inicjalizacja całkowitej wartości koszyka
        $totalCartValue = 0; 
        echo "<table class='cart-table'>";
        echo "<tr><th>Zdjęcie</th><th>Nazwa</th><th>Cena brutto</th><th>Ilość</th><th>Łączna wartość</th><th>Akcja</th></tr>";

        foreach ($_SESSION['koszyk'] as $productId => $product) {
            // Pobierz dane produktu z bazy danych
            $query = "SELECT image_url FROM products WHERE id = ?";
            $stmt = $db->prepare($query);
            $stmt->bind_param("i", $productId);
            $stmt->execute();
            $result = $stmt->get_result();
            $productData = $result->fetch_assoc();

            $imageUrl = $productData['image_url'] ?? 'default_image.png'; // Ścieżka do zdjęcia lub domyślne zdjęcie
            $totalPrice = $product['quantity'] * $product['price'];
            $totalCartValue += $totalPrice; // Dodanie do całkowitej wartości koszyka

            echo "<tr>";
            echo "<td><img src='./{$imageUrl}' alt='Zdjęcie produktu'></td>";
            echo "<td>{$product['name']}</td>";
            echo "<td>{$product['price']} PLN</td>";
            echo "<td>{$product['quantity']}</td>";
            echo "<td>$totalPrice PLN</td>";
            echo "<td>
                    <form method='post' style='display: inline;'>
                        <input type='hidden' name='remove_id' value='{$productId}'>
                        <input type='number' name='remove_quantity' value='1' min='1' max='{$product['quantity']}' style='width: 50px;'>
                        <button type='submit' class='cart-button'>Usuń</button>
                    </form>
                  </td>";
            echo "</tr>";
        }

        // Wyświetlenie całkowitej wartości koszyka i przycisku Kup
        echo "<tr>";
        echo "<td colspan='4' class='cart-total'><strong>Całkowita wartość koszyka:</strong></td>";
        echo "<td colspan='2' class='cart-total'><strong>$totalCartValue PLN</strong></td>";
        echo "</tr>";
        echo "</table>";
        echo "<form method='post' style='text-align: right; margin-top: 20px;'>
                <button type='submit' name='purchase' class='cart-purchase-button'>Kup</button>
              </form>";
    }
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Koszyk</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>Twój Koszyk</h1>
    <?php showCart($db); ?>
    <br>
    <a href="?idp=sklep" class="cart-button">Wróć do sklepu</a>
</body>
</html>
