<?php

function ZarzadzajProduktami($db) {
    $query = "SELECT products.*, categories.name AS category_name FROM products LEFT JOIN categories ON products.category_id = categories.id ORDER BY category_name ASC";
    $result = $db->query($query);

    echo "<h2>Lista produktów</h2>";
    echo "<table class='table table-products'>
        <thead>
            <tr>
                <th>ID</th>
                <th>Kategoria</th>
                <th>Zdjęcie</th>
                <th>Tytuł</th>
                <th>Cena netto</th>
                <th>VAT</th>
                <th>Dostępność</th>
                <th>Opcje</th>
            </tr>
        </thead>
        <tbody>";
        
    while ($row = $result->fetch_assoc()) {
        $dostepnosc = SprawdzDostepnosc($row);
        $image = !empty($row['image_url']) 
            ? "<img src='../" . $row['image_url'] . "' alt='Zdjęcie produktu' class='product-image'>" 
            : "Brak zdjęcia";
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
                <a href='?edit_product&id={$row['id']}' class='btn'>Edytuj</a>
                <a href='?delete_product&id={$row['id']}' class='btn btn-danger'>Usuń</a>
            </td>
        </tr>";
    }
    echo "</tbody></table>";
    echo "<a href='?add_product' class='btn'>Dodaj nowy produkt</a><br><br>";
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
        $category_id = $_POST['category_id'];

        // Obsługa przesyłania zdjęcia
        $image_url = null;
        if (!empty($_FILES['image']['name'])) {
            $image_url = '../' . basename($_FILES['image']['name']);
            move_uploaded_file($_FILES['image']['tmp_name'], '../' . $image_url);
        }

        $stmt = $db->prepare("UPDATE products SET title = ?, description = ?, price_netto = ?, vat = ?, available_quantity = ?, expiration_date = ?, availability_status = ?, category_id = ?, image_url = IFNULL(?, image_url) WHERE id = ?");
        $stmt->bind_param("ssdiissisi", $title, $description, $price_netto, $vat, $quantity, $expiration_date, $status, $category_id, $image_url, $id);
        $stmt->execute();

        echo "<p class='success-message'>Pomyślnie edytowano produkt.</p>";
        ZarzadzajProduktami($db);
    } else {
        $query = "SELECT * FROM products WHERE id = $id LIMIT 1";
        $result = $db->query($query)->fetch_assoc();

        echo "
        <h2>Edytuj produkt</h2>
        <form method='POST' class='form-product' enctype='multipart/form-data'>
            <div class='form-group'>
                <label for='title'>Tytuł:</label>
                <input type='text' id='title' name='title' value='{$result['title']}'>
            </div>
            <div class='form-group'>
                <label for='description'>Opis:</label>
                <textarea id='description' name='description'>{$result['description']}</textarea>
            </div>
            <div class='form-group'>
                <label for='price_netto'>Cena netto:</label>
                <input type='number' step='0.01' id='price_netto' name='price_netto' value='{$result['price_netto']}'>
            </div>
            <div class='form-group'>
                <label for='vat'>VAT:</label>
                <input type='number' step='0.01' id='vat' name='vat' value='{$result['vat']}'>
            </div>
            <div class='form-group'>
                <label for='quantity'>Ilość na magazynie:</label>
                <input type='number' id='quantity' name='quantity' value='{$result['available_quantity']}'>
            </div>
            <div class='form-group'>
                <label for='expiration_date'>Data wygaśnięcia:</label>
                <input type='date' id='expiration_date' name='expiration_date' value='{$result['expiration_date']}'>
            </div>
            <div class='form-group'>
                <label for='category_id'>ID kategorii:</label>
                <input type='number' id='category_id' name='category_id' value='{$result['category_id']}'>
            </div>
            <div class='form-group'>
                <label for='status'>Status dostępności:</label>
                <input type='checkbox' id='status' name='status' " . ($result['availability_status'] === 'available' ? 'checked' : '') . ">
            </div>
            <div class='form-group'>
                <label for='image'>Zdjęcie produktu:</label>
                <input type='file' id='image' name='image'>
            </div>
            <button type='submit' class='btn'>Zapisz zmiany</button>
            <a href='?manage_products' class='btn btn-danger'>Anuluj</a>
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
        $category_id = $_POST['category_id'];

        // Obsługa przesyłania zdjęcia
        $image_url = null;
        if (!empty($_FILES['image']['name'])) {
            $image_url = '../' . basename($_FILES['image']['name']);
            move_uploaded_file($_FILES['image']['tmp_name'], '../' . $image_url);
        }

        $stmt = $db->prepare("INSERT INTO products (title, description, price_netto, vat, available_quantity, expiration_date, availability_status, category_id, image_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdiissis", $title, $description, $price_netto, $vat, $quantity, $expiration_date, $status, $category_id, $image_url);
        $stmt->execute();

        echo "<p class='success-message'>Pomyślnie dodano nowy produkt.</p>";
        ZarzadzajProduktami($db);
    } else {
        echo "
        <h2>Dodaj nowy produkt</h2>
        <form method='POST' class='form-product' enctype='multipart/form-data'>
            <div class='form-group'>
                <label for='title'>Tytuł:</label>
                <input type='text' id='title' name='title'>
            </div>
            <div class='form-group'>
                <label for='description'>Opis:</label>
                <textarea id='description' name='description'></textarea>
            </div>
            <div class='form-group'>
                <label for='price_netto'>Cena netto:</label>
                <input type='number' step='0.01' id='price_netto' name='price_netto'>
            </div>
            <div class='form-group'>
                <label for='vat'>VAT:</label>
                <input type='number' step='0.01' id='vat' name='vat' value='23'>
            </div>
            <div class='form-group'>
                <label for='quantity'>Ilość na magazynie:</label>
                <input type='number' id='quantity' name='quantity'>
            </div>
            <div class='form-group'>
                <label for='expiration_date'>Data wygaśnięcia:</label>
                <input type='date' id='expiration_date' name='expiration_date'>
            </div>
            <div class='form-group'>
                <label for='category_id'>ID kategorii:</label>
                <input type='number' id='category_id' name='category_id'>
            </div>
            <div class='form-group'>
                <label for='status'>Status dostępności:</label>
                <input type='checkbox' id='status' name='status'>
            </div>
            <div class='form-group'>
                <label for='image'>Zdjęcie produktu:</label>
                <input type='file' id='image' name='image'>
            </div>
            <button type='submit' class='btn'>Dodaj produkt</button>
            <a href='?manage_products' class='btn btn-danger'>Anuluj</a>
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
