<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_prod = intval($_POST['product_id']);
    $product_name = htmlspecialchars($_POST['product_name']);
    $price = floatval($_POST['price']);
    $quantity = intval($_POST['quantity']);

    // Inicjalizacja koszyka w sesji
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    // Dodanie produktu do koszyka lub aktualizacja ilości
    if (isset($_SESSION['cart'][$id_prod])) {
        $_SESSION['cart'][$id_prod]['quantity'] += $quantity;
    } else {
        $_SESSION['cart'][$id_prod] = [
            'name' => $product_name,
            'price' => $price,
            'quantity' => $quantity,
        ];
    }

    // Przekierowanie na stronę koszyka
    header('Location: koszyk.php');
    exit;
}

// Wyświetlanie koszyka
echo "<h2>Koszyk</h2>";

if (isset($_SESSION['cart']) && count($_SESSION['cart']) > 0) {
    echo "<table border='1'>
        <tr>
            <th>Nazwa produktu</th>
            <th>Cena jednostkowa netto</th>
            <th>Ilość</th>
            <th>Łączna cena</th>
            <th>Opcje</th>
        </tr>";
    
    $total = 0;

    foreach ($_SESSION['cart'] as $id => $product) {
        $subtotal = $product['price'] * $product['quantity'];
        $total += $subtotal;

        echo "<tr>
            <td>{$product['name']}</td>
            <td>{$product['price']} PLN</td>
            <td>{$product['quantity']}</td>
            <td>" . number_format($subtotal, 2) . " PLN</td>
            <td><a href='koszyk.php?remove=$id'>Usuń</a></td>
        </tr>";
    }

    echo "<tr>
        <td colspan='3'>Łączna cena:</td>
        <td colspan='2'>" . number_format($total, 2) . " PLN</td>
    </tr>";
    echo "</table>";
    echo "<a href='?return'>Powrót do sklepu</a>";

} else {
    echo "<p>Twój koszyk jest pusty.</p>";
}

// Usuwanie produktów z koszyka
if (isset($_GET['remove'])) {
    $id_to_remove = intval($_GET['remove']);
    unset($_SESSION['cart'][$id_to_remove]);
    header('Location: koszyk.php');
    exit;
}
elseif (isset($_GET['return'])) {
     $strona = 'sklep.php';
} 
?>
