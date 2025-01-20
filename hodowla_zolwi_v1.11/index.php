<?php
include('cfg.php');
error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);
require_once 'contact.php';


?>

<!doctype html>
<html>

<head>
	<meta http-equiv="Content-type" content="text/html; charset=UTF-8" />
	<link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="kolorujtlo.js" type="text/javascript"></script>
    <script src="timedate.js" type="text/javascript"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
	<meta http-equiv="Content-Language" content="pl" />
	<meta name="Author" content="Aleksandra Prucnal" />
	<title>Hodowla żółwia wodnego</title>
</head>

<body onload="startclock()">

    <div id="animacjaTestowa1" class="test-block">Kliknij, a się powiększę</div>
    <script>
        $("#animacjaTestowa1").on("click", function(){
            $(this).animate({
                width: "500px",
                opacity: 0.4,
                fontsize: "3em",
                borderwidth: "10px"
            }, 1500);
        });
    </script>


    <form method="post" name="background" class="right-container">
        <div class="clock">
            <h4> Aktualny czas:</h4>
            <div id="data"></div>
            <div id="zegarek"></div>
        </div>        
        <input type="button" value="biały" onclick="changeBackground('#FFFFFF')" class="color-button white">
        <input type="button" value="żółty" onclick="changeBackground('#DAFDBA')" class="color-button yellow">
        <input type="button" value="turkusowy" onclick="changeBackground('#6DB1A8')" class="color-button turquoise">
        <input type="button" value="ciemny" onclick="changeBackground('#237272')" class="color-button dark">
    </form>

	<div class="top">
        <h1>Hodowla żółwia wodnego</h1>
    </div>


	
	<nav class="menu">
        <a href="index.php?idp=glowna">Strona Główna</a> |
        <a href="index.php?idp=gatunki">Gatunki żółwi wodnych</a> |
        <a href="index.php?idp=zywienie">Żywienie</a> |
        <a href="index.php?idp=terrarium">Terrarium</a> |
        <a href="index.php?idp=oswietlenie">Oświetlenie</a> |
        <a href="index.php?idp=ogrzewanie">Ogrzewanie</a> |
        <a href="index.php?idp=filmy">Filmy</a> |
        <a href="index.php?idp=kontakt">Kontakt</a> |
        <a href="index.php?idp=sklep" class="color-button white">SKLEP</a>
        <a href="index.php?idp=koszyk" class="color-button dark">KOSZYK</a>
        
    </nav>

	
    <?php
    $strona = 'html/glowna.html';

    if (isset($_GET['idp'])) {
        if ($_GET['idp'] == 'gatunki') {
            $strona = 'html/gatunki.html';
        } elseif ($_GET['idp'] == 'zywienie') {
            $strona = 'html/zywienie.html';
        } elseif ($_GET['idp'] == 'terrarium') {
            $strona = 'html/terrarium.html';
        } elseif ($_GET['idp'] == 'oswietlenie') {
            $strona = 'html/oswietlenie.html';
        } elseif ($_GET['idp'] == 'ogrzewanie') {
            $strona = 'html/ogrzewanie.html';
        } elseif ($_GET['idp'] == 'filmy') {
            $strona = 'html/filmy.html';
        } elseif ($_GET['idp'] == 'kontakt') {
            $strona = 'html/kontakt.html';
        } elseif ($_GET['idp'] == 'sklep') {
            $strona = 'sklep/sklep.php';
        }elseif ($_GET['idp'] == 'koszyk') {
            $strona = 'sklep/koszyk.php';
        }
    }

    if (file_exists($strona)) {
        include($strona);
    } else {
        echo "<p>Strona nie istnieje.</p>";
    }
    ?>
	
	<footer class="footer clear">
        <?php
        $kontakt = new Kontakt();
        $kontakt->PokazKontakt();
        $odbiorca = 'kontakt@gmail.com';
        $kontakt->WyslijMailaKontakt($odbiorca);
        $kontakt->PrzypomnijHaslo();
        ?>

        <p>&copy; 2024 Hodowla żółwia wodnego</p>

        <?php
        $nr_indeksu = '169353';
        $nrGrupy = '3';
        echo 'Autor: Aleksandra Prucnal '.$nr_indeksu.' grupa '.$nrGrupy.' <br /><br />';
        ?>
    </footer>

    

</body>

</html>