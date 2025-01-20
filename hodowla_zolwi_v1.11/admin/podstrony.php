<?php

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

?>