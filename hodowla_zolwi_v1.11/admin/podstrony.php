<?php

function ZarzadzajPodstronami($db) {
    $query = "SELECT id, page_title FROM page_list ORDER BY page_title ASC LIMIT 30";
    $result = $db->query($query);
    
    echo "<h2>Lista podstron</h2>";
    echo "<table class='table'>";
    echo "<thead><tr><th>ID</th><th>Tytuł podstrony</th><th>Opcje</th></tr></thead>";
    echo "<tbody>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>
            <td>{$row['id']}</td>
            <td>{$row['page_title']}</td>
            <td>
                <a href='?edit_page&id={$row['id']}' class='btn'>Edytuj</a>
                <a href='?delete_page&id={$row['id']}' class='btn btn-danger'>Usuń</a>
            </td>
        </tr>";
    }
    echo "</tbody>";
    echo "</table>";
    echo "<a href='?add_page' class='btn'>Dodaj nową podstronę</a><br><br>";
}

function EdytujPodstrone($db, $id) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $title = $_POST['page_title'];
        $content = $_POST['page_content'];
        $status = isset($_POST['status']) ? 1 : 0;

        $stmt = $db->prepare("UPDATE page_list SET page_title = ?, page_content = ?, status = ? WHERE id = ? LIMIT 1");
        $stmt->bind_param("ssii", $title, $content, $status, $id);
        $stmt->execute();

        echo "<h3 class='success-message'>Pomyślnie edytowano podstronę.</h3>";
        ZarzadzajPodstronami($db);
    } else {
        $query = "SELECT * FROM page_list WHERE id = $id LIMIT 1";
        $result = $db->query($query)->fetch_assoc();

        echo "
        <h2>Edytuj podstronę</h2>
        <form method='POST' class='form-page'>
            <div class='form-group'>
                <label for='page_title'>Tytuł:</label>
                <input type='text' id='page_title' name='page_title' value='{$result['page_title']}'>
            </div>
            <div class='form-group'>
                <label for='page_content'>Treść strony:</label>
                <textarea id='page_content' name='page_content' style='height: 300px;'>{$result['page_content']}</textarea>
            </div>
            <div class='form-group'>
                <label for='status'>Aktywna:</label>
                <input type='checkbox' id='status' name='status' " . ($result['status'] ? 'checked' : '') . ">
            </div>
            <button type='submit' class='btn'>Zapisz zmiany</button>
            <a href='?manage_pages' class='btn btn-danger'>Anuluj</a>
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

        echo "<h3 class='success-message'>Pomyślnie dodano nową podstronę.</h3>";
        ZarzadzajPodstronami($db);
    } else {
        echo "
        <h2>Dodaj nową podstronę</h2>
        <form method='POST' class='form-page'>
            <div class='form-group'>
                <label for='page_title'>Tytuł:</label>
                <input type='text' id='page_title' name='page_title'>
            </div>
            <div class='form-group'>
                <label for='page_content'>Treść strony:</label>
                <textarea id='page_content' name='page_content' style='height: 300px;'></textarea>
            </div>
            <div class='form-group'>
                <label for='status'>Aktywna:</label>
                <input type='checkbox' id='status' name='status'>
            </div>
            <button type='submit' class='btn'>Dodaj podstronę</button>
            <a href='?manage_pages' class='btn btn-danger'>Anuluj</a>
        </form>";
    }
}

function UsunPodstrone($db, $id) {
    $stmt = $db->prepare("DELETE FROM page_list WHERE id = ? LIMIT 1");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    echo "<h3 class='success-message'>Pomyślnie usunięto podstronę.</h3>";
    ZarzadzajPodstronami($db);
}

?>
