<?php
class Kontakt {

    private $adminEmail = 'admin@kontakt.pl'; // E-mail administratora
    private $adminPassword = 'admin123';

    function PokazKontakt() {
        echo "<h2>Kontakt</h2>";
        echo "<p>Email: kontakt@gmail.com</p>";
        echo "<p>Telefon: +48 123 456 789</p>";
        echo "<p>Adres: ul. Słoneczna 54, 10-710 Olsztyn</p>";

        echo "<form method='POST' action='' class='contact-form'>";

        echo "    <label for='email'>Twój email:</label><br />";
        echo "    <input type='email' id='email' name='email' placeholder='Wpisz swój email' required><br /><br />";
        
        echo "    <label for='temat'>Temat wiadomości:</label><br />";
        echo "    <input type='text' id='temat' name='temat' placeholder='Wpisz temat'><br /><br />";
        
        echo "    <label for='tresc'>Treść wiadomości:</label><br />";
        echo "    <textarea id='tresc' name='tresc' rows='5' placeholder='Wpisz treść wiadomości'></textarea><br /><br />";
        
        echo "    <button type='submit'>Wyślij wiadomość</button>";
        echo "    <button type='submit'>Wyślij przypomnienie</button>";
        echo "</form>";
    }

    function WyslijMailaKontakt($odbiorca) {
        if (empty($_POST['temat']) || empty($_POST['tresc']) || empty($_POST['email'])) {
            echo 'Wypełnij wszystkie pola';
        } else {
            $mail['subject'] = $_POST['temat'];
            $mail['body'] = $_POST['tresc'];
            $mail['sender'] = $_POST['email'];
            $mail['recipient'] = $odbiorca; // czyli my jesteśmy odbiorcą, jeżeli tworzymy formularz kontaktowy
    
            $header = "From: Formularz kontaktowy <" . $mail['sender'] . ">\n";
            $header .= "MIME-Version: 1.0\nContent-Type: text/plain; charset=utf-8\nContent-Transfer-Encoding: 8bit\n";
            $header .= "X-Sender: <" . $mail['sender'] . ">\n";
            $header .= "X-Mailer: PHP/".phpversion()."\n";
            $header .= "X-Priority: 3\n";
            $header .= "Return-Path: <" . $mail['sender'] . ">\n";
    
            mail($mail['recipient'], $mail['subject'], $mail['body'], $header);
    
            echo 'Wiadomość została wysłana';
        }
    }

    // Metoda wysyłająca przypomnienie hasła
    public function PrzypomnijHaslo() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
            $email = $_POST['email'];

            // Sprawdzamy, czy podany e-mail to e-mail administratora
            if ($email === $this->adminEmail) {
                $subject = "Przypomnienie hasła do panelu admina";
                $message = "Twoje hasło do panelu administracyjnego to: " . $this->adminPassword;
                $headers = "From: no-reply@example.com\r\n";
                $headers .= "MIME-Version: 1.0\r\n";
                $headers .= "Content-Type: text/plain; charset=utf-8\r\n";

                // Wysyłamy e-mail
                if (mail($email, $subject, $message, $headers)) {
                    echo "<p>Przypomnienie hasła zostało wysłane na adres $email.</p>";
                } else {
                    echo "<p>Wystąpił błąd podczas wysyłania wiadomości. Spróbuj ponownie.</p>";
                }
            } else {
                echo "<p>Podany adres e-mail nie jest powiązany z kontem administratora.</p>";
            }
        }
    }

}
?>