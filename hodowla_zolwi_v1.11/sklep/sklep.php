<?php

// Połączenie z bazą danych
$db = new mysqli("localhost", "root", "", "moja_strona");
if ($db->connect_error) {
    die("Nie można połączyć z bazą danych: " . $db->connect_error);
}

// Funkcja wyswietlajaca drzewo kategorii z mozliwoscia wybrania kategorii
function PokazKategorie($db, $parent_id = 0, $selected_id = null) {
    $query = "SELECT id, parent_id, name FROM categories WHERE parent_id = $parent_id ORDER BY name ASC";
    $result = $db->query($query);

    if ($result->num_rows > 0) {
        echo "<ul>";

        // Iteracja po wierszach
        while ($row = $result->fetch_assoc()) {
            $active = ($selected_id == $row['id']) ? "class='active'" : "";

            echo "<li $active><a href='?idp=sklep&category=" . $row['id'] . "'>" .$row['name'] . "</a></li>";

            PokazKategorie($db, $row['id'], $selected_id);
        }
        echo "</ul>";
    }
}

function WybierzKategorie($db, $selected_id = null) {
    echo "<div class='categories'>";
    echo "<h2>Wybierz kategorię</h2>";
    PokazKategorie($db, 0, $selected_id);
    echo "</div>";
}

function WyswietlProdukty($db, $category_id) {
    $query = "SELECT products.*, categories.name AS category_name 
              FROM products 
              LEFT JOIN categories ON products.category_id = categories.id 
              WHERE products.category_id = $category_id 
              ORDER BY products.title ASC";

    $products = $db->query($query);
    echo "<h2>Produkty:</h2>";

    if ($products->num_rows > 0) {
        echo "<div class='wrapper'>";
        while ($row = $products->fetch_assoc()) {
            $image = !empty($row['image_url']) 
                ? "<img src='./" . $row['image_url'] . "' alt='Zdjęcie produktu' style='width: 200px; height: 200px;'>" 
                : "Brak zdjęcia";

            $price_netto = number_format($row['price_netto'], 2);
            $price_brutto = number_format($row['price_netto'] * (1 + $row['vat'] / 100), 2);

            echo "<div class='jedna kolumna'>";
            echo $image;
            echo "<p> <strong>{$row['title']}</strong></p>";
            echo "<p>Cena netto: $price_netto PLN</p>";
            echo "<p>Cena brutto:<strong> $price_brutto PLN</strong></p>";

            // Formularz do dodawania produktu do koszyka
            echo "<form method='POST'  action='?idp=koszyk'>";
            echo "<input type='hidden' name='product_id' value='{$row['id']}'>";
            echo "<input type='hidden' name='product_name' value='" . htmlspecialchars($row['title']) . "'>";
            echo "<input type='hidden' name='price' value='$row[price_netto]'>";
            echo "<label>Ilość:</label>";
            echo "<input type='number' name='quantity' value='1' min='1' style='width: 50px;'>";
            echo "<button type='submit' name='add_to_cart' class='color-button turquoise'>Dodaj do koszyka</button>";
            echo "</form>";

            echo "</div>";
        }
        echo "</div>";
    } else {
        echo "<p>Brak produktów w tej kategorii.</p>";
    }
}

// Sprawdzenie wybranej kategorii
if (isset($_GET['category'])) {
    $category_id = intval($_GET['category']);
} else {
    $category_id = 0;
}

// Wywołanie funkcji
WybierzKategorie($db, $category_id);
if ($category_id) {
    WyswietlProdukty($db, $category_id);
}

?>
