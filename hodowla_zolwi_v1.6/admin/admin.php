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
function ListaPodstron($db) {
    $query = "SELECT id, page_title FROM page_list ORDER BY page_title ASC LIMIT 30";
    $result = $db->query($query);
    echo "<h2>Lista podstron</h2>";
    echo "<table border='1'><tr><th>id</th><th>Tytuł podstrony</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>
            <td>{$row['id']}</td>
            <td>{$row['page_title']}</td>
            <td><a href='?delete_page&id={$row['id']}'>Usuń</a></td>
            <td><a href='?edit_page&id={$row['id']}'>Edytuj</a></td>
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

        echo "<p>Pomyślnie edytowano podstronę.</p>";
        ListaPodstron($db);
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
            <a href='?list_pages'><button type='button'>Anuluj</button></a>
        </form>";
    }
}


function DodajNowaPodstrone($db) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $title = $_POST['page_title'];
        $content = $_POST['page_content'];
        $status = isset($_POST['status']) ? 1 : 0;

        $stmt = $db->prepare("INSERT INTO page_list (page_title, page_content, status) VALUES (?, ?, ?) LIMIT 1");
        $stmt->bind_param("ssi", $title, $content, $status);
        $stmt->execute();

        echo "<p>Pomyślnie dodano nową podstronę.</p>";
        ListaPodstron($db);
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
            <a href='?list_pages'><button type='button'>Anuluj</button></a>
        </form>";
    }
}


function UsunPodstrone($db, $id) {
    $stmt = $db->prepare("DELETE FROM page_list WHERE id = ? LIMIT 1");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    echo "<p>Pomyślnie usunięto podstronę.</p>";
    ListaPodstron($db);
}


// Zainicjowanie linków do zarządzania podstronami
if (isset($_GET['list_pages'])) { 
    ListaPodstron($db);
} elseif (isset($_GET['edit_page'])) { 
    EdytujPodstrone($db, $_GET['id']);
} elseif (isset($_GET['delete_page'])) { 
    UsunPodstrone($db, $_GET['id']);
} elseif (isset($_GET['add_page'])) { 
    DodajNowaPodstrone($db);
}

?>

</body>
</html>