<?php

function PokazKategorie($db, $parent_id = 0) {
    $query = "SELECT id, parent_id, name FROM categories WHERE parent_id = $parent_id ORDER BY name ASC";
    $result = $db->query($query);

    if ($result->num_rows > 0) {
        echo "<ul>";
        while ($row = $result->fetch_assoc()) {
            echo "<li>";
            echo $row['name'];
            PokazKategorie($db, $row['id']);

            echo "</li>";
        }
        echo "</ul>";
    }
}

function DrzewoKategorii($db) {
    PokazKategorie($db, 0);
}


function ZarzadzajKategoriami($db) {
    $query = "SELECT id, parent_id, name FROM categories ORDER BY parent_id ASC LIMIT 30";
    $result = $db->query($query);
    echo "<h2>Drzewo kategorii</h2>";
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

?>