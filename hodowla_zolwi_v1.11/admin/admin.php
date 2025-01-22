<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Panel administracyjny</title>
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body>
<div class="container">
    <h1>Panel Administracyjny</h1>
    <!-- Pasek nawigacji -->
    <nav class="admin-nav">
        <a href="?manage_pages">Zarządzaj podstronami</a>
        <a href="?manage_categories">Zarządzaj kategoriami</a>
        <a href="?manage_products">Zarządzaj produktami</a>
    </nav>

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
            <h1 class="heading">Panel CMS:</h1>
            <form method="post" name="LoginForm" enctype="multipart/form-data" action="' . $_SERVER['REQUEST_URI'] . '">
                <div class="form-group">
                    <label for="login_email">Email:</label>
                    <input type="text" id="login_email" name="login_email">
                </div>
                <div class="form-group">
                    <label for="login_pass">Hasło:</label>
                    <input type="password" id="login_pass" name="login_pass">
                </div>
                <button type="submit" name="x1_submit" class="btn">Zaloguj</button>
            </form>
        </div>';
    }

    // Logowanie do panelu administratora za pomocą $login i $pass z pliku cfg.php
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_SESSION['logged_in'])) {
        if ($_POST['login_email'] === $login && $_POST['login_pass'] === $pass) {
            $_SESSION['logged_in'] = true;
        } else {
            echo "<p class='error-message'>Nieprawidłowe dane logowania. Spróbuj jeszcze raz.</p>";
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
        die("<p class='error-message'>Nie można połączyć z bazą danych: " . $db->connect_error . "</p>");
    }

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
        echo "<h3 class='success-message'>Pomyślnie usunięto produkt.</h3>";
        ZarzadzajProduktami($db);
    } elseif (isset($_GET['add_product'])) {
        DodajProdukt($db);
    }
    ?>
</div>
</body>
</html>
