<?php
    $dbhost = 'localhost';
    $dbuser = 'root';
    $dbpass = '';
    $baza = 'moja_strona';
    $login = "admin";
    $pass = "admin";

    $link = mysqli_connect($dbhost, $dbuser, $dbpass, $baza);

    if (!$link) {
        echo '<b>Przerwane połączenie: ' . mysqli_connect_error() . '</b>';
        exit;
    }
?>
