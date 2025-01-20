<?php

// Funkcja zarządzająca produktami
function ZarzadzajProduktami($db) {
    $query = "SELECT products.*, categories.name AS category_name FROM products LEFT JOIN categories ON products.category_id = categories.id ORDER BY category_name ASC";
    $result = $db->query($query);

    echo "<h2>Lista produktów</h2>";
    echo "<table border='1'>
        <tr>
            <th>id</th>
            <th>Kategoria</th>
            <th>Zdjęcie</th>
            <th>Tytuł</th>
            <th>Cena netto</th>
            <th>VAT</th>
            <th>Dostępność</th>
            <th>Opcje</th>
        </tr>";
        
    while ($row = $result->fetch_assoc()) {
        $dostepnosc = SprawdzDostepnosc($row);
        $image = !empty($row['image_url']) ? "<img src='../" . $row['image_url'] . "' alt='Zdjęcie produktu' style='width: 100px; height: 100px;'>" : "Brak zdjęcia";
        $category = !empty($row['category_name']) ? $row['category_name'] : "Brak kategorii";
        echo "<tr>
            <td>{$row['id']}</td>
            <td>{$category}</td>
            <td>{$image}</td>
            <td>{$row['title']}</td>
            <td>{$row['price_netto']} PLN</td>
            <td>{$row['vat']}%</td>
            <td>{$dostepnosc}</td>
            <td>
                <a href='?edit_product&id={$row['id']}'>Edytuj</a> |
                <a href='?delete_product&id={$row['id']}'>Usuń</a>
            </td>
        </tr>";
    }
    echo "</table>";
    echo "<a href='?add_product'>Dodaj nowy produkt</a><br><br>";
}

function EdytujProdukt($db, $id) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $title = $_POST['title'];
        $description = $_POST['description'];
        $price_netto = $_POST['price_netto'];
        $vat = $_POST['vat'];
        $quantity = $_POST['quantity'];
        $expiration_date = $_POST['expiration_date'];
        $status = isset($_POST['status']) ? 'available' : 'unavailable';

        $stmt = $db->prepare("UPDATE products SET title = ?, description = ?, price_netto = ?, vat = ?, available_quantity = ?, expiration_date = ?, availability_status = ? WHERE id = ? LIMIT 1");
        $stmt->bind_param("ssdiissi", $title, $description, $price_netto, $vat, $quantity, $expiration_date, $status, $id);
        $stmt->execute();

        echo "<h3>Pomyślnie edytowano produkt.</h3>";
        ZarzadzajProduktami($db);
    } else {
        $query = "SELECT * FROM products WHERE id = $id LIMIT 1";
        $result = $db->query($query)->fetch_assoc();

        echo "
        <h2>Edytuj produkt</h2>
        <form method='POST'>
            <label>Tytuł:
                <input type='text' name='title' value='{$result['title']}'>
            </label><br>
            <label>Opis:
                <textarea name='description' style='height: 150px;'>{$result['description']}</textarea>
            </label><br>
            <label>Cena netto:
                <input type='number' step='0.01' name='price_netto' value='{$result['price_netto']}'>
            </label><br>
            <label>VAT:
                <input type='number' step='0.01' name='vat' value='{$result['vat']}'>
            </label><br>
            <label>Ilość na magazynie:
                <input type='number' name='quantity' value='{$result['available_quantity']}'>
            </label><br>
            <label>Data wygaśnięcia:
                <input type='date' name='expiration_date' value='{$result['expiration_date']}'>
            </label><br>
            <label>Status dostępności:
                <input type='checkbox' name='status' " . ($result['availability_status'] === 'available' ? 'checked' : '') . ">
            </label><br>
            <input type='submit' value='Zapisz zmiany'>
            <a href='?manage_products'><button type='button'>Anuluj</button></a>
        </form>";
    }
}

function DodajProdukt($db) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $title = $_POST['title'];
        $description = $_POST['description'];
        $price_netto = $_POST['price_netto'];
        $vat = $_POST['vat'];
        $quantity = $_POST['quantity'];
        $expiration_date = $_POST['expiration_date'];
        $status = isset($_POST['status']) ? 'available' : 'unavailable';

        $stmt = $db->prepare("INSERT INTO products (title, description, price_netto, vat, available_quantity, expiration_date, availability_status) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdiiss", $title, $description, $price_netto, $vat, $quantity, $expiration_date, $status);
        $stmt->execute();

        echo "<h3>Pomyślnie dodano nowy produkt.</h3>";
        ZarzadzajProduktami($db);
    } else {
        echo "
        <h2>Dodaj nowy produkt</h2>
        <form method='POST'>
            <label>Tytuł:
                <input type='text' name='title'>
            </label><br>
            <label>Opis:
                <textarea name='description' style='height: 150px;'></textarea>
            </label><br>
            <label>Cena netto:
                <input type='number' step='0.01' name='price_netto'>
            </label><br>
            <label>VAT:
                <input type='number' step='0.01' name='vat' value='23'>
            </label><br>
            <label>Ilość na magazynie:
                <input type='number' name='quantity'>
            </label><br>
            <label>Data wygaśnięcia:
                <input type='date' name='expiration_date'>
            </label><br>
            <label>Status dostępności:
                <input type='checkbox' name='status'>
            </label><br>
            <input type='submit' value='Dodaj produkt'>
            <a href='?manage_products'><button type='button'>Anuluj</button></a>
        </form>";
    }
}

function SprawdzDostepnosc($product) {
    $today = date('Y-m-d');
    if ($product['availability_status'] === 'unavailable' || $product['available_quantity'] <= 0 || $product['expiration_date'] < $today) {
        return 'Niedostępny';
    }
    return 'Dostępny';
}

?>