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
require 'kategorie.php';
require 'produkty.php';
require 'podstrony.php';

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