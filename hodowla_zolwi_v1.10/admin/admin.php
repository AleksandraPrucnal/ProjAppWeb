<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Administracyjny</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>

<?php

session_start();
require '../cfg.php';

// Metoda pozwalająca zalogować się do panelu administratora
function FormularzLogowania() {
    echo '
    <div class="logowanie">
        <h1 class="heading">Panel CMS:</h1>';
    echo '
        <div class="logowanie">
            <form method="post" name="LoginForm" enctype="multipart/form-data" action="' . $_SERVER['REQUEST_URI'] . '">
                <table class="logowanie">
                    <tr>
                        <td class="log4_t">[email]</td>
                        <td><input type="text" name="login_email" class="logowanie" /></td>
                    </tr>
                    <tr>
                        <td class="log4_t">[haslo]</td>
                        <td><input type="password" name="login_pass" class="logowanie" /></td>
                    </tr>
                    <tr>
                        <td>&nbsp;</td>
                        <td><input type="submit" name="x1_submit" class="logowanie" value="Zaloguj" /></td>
                    </tr>
                </table>
            </form>
        </div>
    </div>';
}

// Logowanie do panelu administratora za pomocą $login i &pass z pliku cfg.php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_SESSION['logged_in'])) {
    if ($_POST['login_email'] === $login && $_POST['login_pass'] === $pass) {
        $_SESSION['logged_in'] = true;
    } else {
        echo "Nieprawidłowe dane logowania. Spróbuj jeszcze raz.";
        FormularzLogowania();
        exit;
    }
}

// Wykorzystanie zmiennej $_SESSION do zapamiętania informacji o logowaniu
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    FormularzLogowania();
    exit;
}

// Połączenie z bazą danych moja_strona
$db = new mysqli("localhost", "root", "", "moja_strona");
if ($db->connect_error) {
    die("Nie można połączyć z bazą danych: " . $db->connect_error);
}




// Metoda wyświetlająca listę podstron
function ZarzadzajPodstronami($db) {
    $query = "SELECT id, page_title FROM page_list ORDER BY page_title ASC LIMIT 30";
    $result = $db->query($query);
    echo "<h2>Lista podstron</h2>";
    echo "<table border='1'><tr><th>id</th><th>Tytuł podstrony</th><th>Opcje</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>
            <td>{$row['id']}</td>
            <td>{$row['page_title']}</td>
            <td>
                <a href='?edit_page&id={$row['id']}'>Edytuj</a> |
                <a href='?delete_page&id={$row['id']}'>Usuń</a>
            </td>
        </tr>";
    }
    echo "</table>";
    echo "<a href='?add_page'>Dodaj nową podstronę</a><br><br>";
}

function EdytujPodstrone($db, $id) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $title = $_POST['page_title'];
        $content = $_POST['page_content'];
        $status = isset($_POST['status']) ? 1 : 0;

        $stmt = $db->prepare("UPDATE page_list SET page_title = ?, page_content = ?, status = ? WHERE id = ? LIMIT 1");
        $stmt->bind_param("ssii", $title, $content, $status, $id);
        $stmt->execute();

        echo "<h3'>Pomyślnie edytowano podstronę.</h3>";
        ZarzadzajPodstronami($db);
    } else {
        $query = "SELECT * FROM page_list WHERE id = $id LIMIT 1";
        $result = $db->query($query)->fetch_assoc();

        echo "
        <h2>Edytuj podstronę</h2>
        <form method='POST'>
            <label>Tytuł:
                <input type='text' name='page_title' value='{$result['page_title']}'>
            </label><br>
            <label>Treść strony: 
                <textarea name='page_content' style='height: 300px;'>{$result['page_content']}</textarea>
            </label><br>
            <label>Aktywna: 
                <input type='checkbox' name='status' " . ($result['status'] ? 'checked' : '') . ">
            </label><br>
            <input type='submit' value='Zapisz zmiany'>
            <a href='?manage_pages'><button type='button'>Anuluj</button></a>
        </form>";
    }
}


function DodajPodstrone($db) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $title = $_POST['page_title'];
        $content = $_POST['page_content'];
        $status = isset($_POST['status']) ? 1 : 0;

        $stmt = $db->prepare("INSERT INTO page_list (page_title, page_content, status) VALUES (?, ?, ?) LIMIT 1");
        $stmt->bind_param("ssi", $title, $content, $status);
        $stmt->execute();

        echo "<h3>Pomyślnie dodano nową podstronę.</h3>";
        ZarzadzajPodstronami($db);
    } else {
        echo "
        <h2>Dodaj nową podstronę</h2>
        <form method='POST'>
            <label>Tytuł: 
                <input type='text' name='page_title'>
            </label><br>
            <label>Treść strony:
                <textarea name='page_content' style='height: 300px;'></textarea>
            </label><br>
            <label>Aktywna:
                <input type='checkbox' name='status'>
            </label><br>
            <input type='submit' value='Dodaj podstronę'>
            <a href='?manage_pages'><button type='button'>Anuluj</button></a>
        </form>";
    }
}


function UsunPodstrone($db, $id) {
    $stmt = $db->prepare("DELETE FROM page_list WHERE id = ? LIMIT 1");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    echo "<h3>Pomyślnie usunięto podstronę.</h3>";
    ZarzadzajPodstronami($db);
}



function PokazKategorie($parent_id = 0, $db) {
    $query = "SELECT id, parent_id, name FROM categories WHERE parent_id = $parent_id ORDER BY name ASC";
    $result = $db->query($query);

    if ($result->num_rows > 0) {
        echo "<ul>";
        while ($row = $result->fetch_assoc()) {
            echo "<li>";
            echo $row['name'];
            PokazKategorie($row['id'], $db);

            echo "</li>";
        }
        echo "</ul>";
    }
}

function DrzewoKategorii($db) {
    echo "<h2>Drzewo kategorii</h2>";
    PokazKategorie(0, $db);
}


function ZarzadzajKategoriami($db) {
    $query = "SELECT id, parent_id, name FROM categories ORDER BY parent_id ASC LIMIT 30";
    $result = $db->query($query);
    DrzewoKategorii($db);
    echo "<br><h2>Zarządzaj kategoriami</h2>";
    echo "<table border='1'><tr><th>matka</th><th>id</th><th>Nazwa kategorii</th><th>Opcje</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>
            <td>{$row['parent_id']}</td>
            <td>{$row['id']}</td>
            <td>{$row['name']}</td>
            <td>
                <a href='?edit_category&id={$row['id']}'>Edytuj</a> |
                <a href='?delete_category&id={$row['id']}'>Usuń</a>
            </td>
        </tr>";
    }
    echo "</table>";
    echo "<a href='?add_category'><br>Dodaj nową kategorię</a><br><br>";
}


function DodajKategorie($db) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $parent_id = $_POST['parent_id'];
        $name = $_POST['name'];

        $stmt = $db->prepare("INSERT INTO categories (parent_id, name) VALUES (?, ?) LIMIT 1");
        $stmt->bind_param("is",$parent_id, $name);
        $stmt->execute();

        echo "<h3>Pomyślnie dodano nową kategorię.</h3>";
        ZarzadzajKategoriami($db);
    } else {
        echo "
        <h2>Dodaj nową kategorię</h2>
        <form method='POST'>
            <label>matka:
                <input type='number' name='parent_id'>
            </label><br>
            <label>nazwa:
                <input type='text' name='name'>
            </label><br>
            <input type='submit' value='Dodaj kategorię'>
            <a href='?manage_categories'><button type='button'>Anuluj</button></a>
        </form>";
    }
}


function EdytujKategorie($db, $id) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $parent_id = $_POST['parent_id'];
        $name = $_POST['name'];

        $stmt = $db->prepare("UPDATE categories SET parent_id = ?, name = ? WHERE id = ? LIMIT 1");
        $stmt->bind_param("isi",$parent_id, $name, $id);
        $stmt->execute();

        echo "<h3>Pomyślnie edytowano kategorię.</h3>";
        ZarzadzajKategoriami($db);
    } else {
        $query = "SELECT * FROM categories WHERE id = $id LIMIT 1";
        $result = $db->query($query)->fetch_assoc();
        echo "
        <h2>Edytuj kategorię</h2>
        <form method='POST'>
            <label>matka:
                <input type='number' name='parent_id' value='{$result['parent_id']}'>
            </label><br>
            <label>nazwa:
                <input type='text' name='name' value='{$result['name']}'>
            </label><br>
            <input type='submit' value='Edytuj kategorię'>
            <a href='?manage_categories'><button type='button'>Anuluj</button></a>
        </form>";
    }
}


function UsunKategorie($db, $id) {
    $stmt = $db->prepare("DELETE FROM categories WHERE id = ? LIMIT 1");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    echo "<h3>Pomyślnie usunięto kategorię.</h3>";
    ZarzadzajKategoriami($db);
}




// Funkcja zarządzająca produktami
function ZarzadzajProduktami($db) {
    $query = "SELECT products.*, categories.name AS category_name FROM products LEFT JOIN categories ON products.category_id = categories.id ORDER BY category_name ASC";
    $result = $db->query($query);

    echo "<h2>Lista produktów</h2>";
    echo "<table border='1'><tr><th>id</th><th>Kategoria</th><th>Zdjęcie</th><th>Tytuł</th><th>Cena netto</th><th>VAT</th><th>Dostępność</th><th>Opcje</th></tr>";
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



// Pasek nawigacji
echo "<nav>
    <a href='?manage_pages'>Zarządzaj podstronami</a> |
    <a href='?manage_categories'>Zarządzaj kategoriami</a> |
    <a href='?manage_products'>Zarządzaj produktami</a>
</nav><br>";

// Zainicjowanie linków do zarządzania podstronami
if (isset($_GET['manage_pages'])) { 
    ZarzadzajPodstronami($db);
} elseif (isset($_GET['edit_page'])) { 
    EdytujPodstrone($db, $_GET['id']);
} elseif (isset($_GET['delete_page'])) { 
    UsunPodstrone($db, $_GET['id']);
} elseif (isset($_GET['add_page'])) { 
    DodajPodstrone($db);
}

// Zainicjowanie linków do zarządzania kategoriami
elseif (isset($_GET['list_categories'])) {
    DrzewoKategorii($db);
} elseif (isset($_GET['manage_categories'])) {
    ZarzadzajKategoriami($db);
} elseif (isset($_GET['add_category'])) {
    DodajKategorie($db);
} elseif (isset($_GET['edit_category'])) {
    EdytujKategorie($db, $_GET['id']);
} elseif (isset($_GET['delete_category'])) {
    UsunKategorie($db, $_GET['id']);
}


// Zainicjowanie linków do zarządzania produktami
if (isset($_GET['manage_products'])) {
    ZarzadzajProduktami($db);
} elseif (isset($_GET['edit_product'])) {
    EdytujProdukt($db, $_GET['id']);
} elseif (isset($_GET['delete_product'])) {
    $stmt = $db->prepare("DELETE FROM products WHERE id = ?");
    $stmt->bind_param("i", $_GET['id']);
    $stmt->execute();
    echo "<h3>Pomyślnie usunięto produkt.</h3>";
    ZarzadzajProduktami($db);
} elseif (isset($_GET['add_product'])) {
    DodajProdukt($db);
}

?>

</body>
</html>