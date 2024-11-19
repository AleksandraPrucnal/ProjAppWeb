<?php
$dbhost = 'localhost';
$dbuser = 'root';
$dbpass = '';
$baza = 'moja_strona';

$link = mysqli_connect($dbhost, $dbuser, $dbpass, $baza);

if (!$link) {
    echo '<b>Przerwane połączenie: ' . mysqli_connect_error() . '</b>';
    exit;
}
?>
