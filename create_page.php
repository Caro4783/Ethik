<?php
// Datenbankverbindung herstellen
$servername = "rdbms.strato.de";
$username = "dbu4527972";  // replace with your MySQL username
$password = "Ethik/DeineMutter123";  // replace with your MySQL password
$dbname = "dbs13032693";

$conn = new mysqli($servername, $username, $password, $dbname);

// Verbindung prüfen
if ($conn->connect_error) {
    die("Verbindung fehlgeschlagen: " . $conn->connect_error);
}

// Formularwerte abrufen
$docs_link = $_POST['docs_link'];
$username = $_POST['username'];
$password = $_POST['password'];
$status = $_POST['status'];
$auflagen = $_POST['auflagen'];

// Benutzer authentifizieren
$sql = "SELECT * FROM users WHERE username='$username' AND password='$password'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Ermitteln Sie die höchste aktuelle Seitennummer
    $max_sql = "SELECT MAX(CAST(SUBSTRING(page_name, 6) AS UNSIGNED)) as max_page FROM documents WHERE page_name LIKE 'seite%'";
    $max_result = $conn->query($max_sql);
    $row = $max_result->fetch_assoc();
    $next_page_num = isset($row['max_page']) ? $row['max_page'] + 1 : 2;
    $page_name = "Test" . $next_page_num;

    // Seite erstellen
    $page_dir = "seiten/" . $page_name;
    if (!file_exists($page_dir)) {
        mkdir($page_dir, 0777, true);
    }

    // Set the Open Graph image URL based on status
    $og_image_url = "";
    if ($status == "Angenommen") {
        $og_image_url = "https://caro-ist-cool.de/angenommen/approved.png"; // Replace with the actual image URL for Angenommen
    } elseif ($status == "Abgelehnt") {
        $og_image_url = "https://caro-ist-cool.de/abgelehnt/DISAPPROVED.png"; // Replace with the actual image URL for Abgelehnt
    } elseif ($status == "Ab ins KB") {
        $og_image_url = "https://caro-ist-cool.de/KB/KB.png"; // Replace with the actual image URL for Ab ins KB
    }

    $page_url = "URL_ZUR_SEITE"; // Replace with the actual URL of the created page

    $page_content = "
    <!DOCTYPE html>
    <html lang='de'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Bee Approved!</title>
        <!-- Open Graph Meta Tags -->
        <meta property='og:title' content='" . htmlspecialchars($page_name) . "'>
        <meta property='og:description' content='Auflagen: " . nl2br(htmlspecialchars($auflagen)) . "'>
        <meta property='og:image' content='$og_image_url'>
        <meta property='og:type' content='website'>
        <meta property='og:url' content='$page_url'>

        <style>
            body {
                background-color: #1e2124;
                margin: 0;
                padding: 0;
                font-family: Arial, sans-serif;
                display: flex;
                justify-content: center;
                align-items: flex-start;
                height: 100vh;
                position: relative;
            }
            .container {
                display: flex;
                justify-content: center;
                align-items: flex-start;
                width: 90%;
                max-width: 1000px;
                background-color: #f4f4f9;
                padding: 20px;
                box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
                box-sizing: border-box;
            }
            .iframe-doc {
                width: 100%;
                height: 940px;
                border: none;
            }
            .user-info {
                width: 20%;
                padding: 20px;
                color: #333;
                background-color: #fff;
                border-left: 2px solid #ccc;
                box-sizing: border-box;
                display: flex;
                flex-direction: column;
                align-items: center;
                text-align: center;
                position: absolute;
                top: 20px;
                right: 20px;
                z-index: 10;
            }
            .stamp-table {
                width: 100%;
                border-collapse: collapse;
            }
            .stamp-field {
                width: 100%;
                height: 200px;
                border: 2px solid black;
                display: flex;
                align-items: center;
                justify-content: center;
                position: relative;
                margin-bottom: 20px;
            }
            .stamp-field img {
                max-width: 80%;
                max-height: 80%;
                position: absolute;
                transform-origin: center center;
                pointer-events: none; /* Prevent clicking */
                user-select: none; /* Prevent selection */
            }
            .stamp-text {
                position: absolute;
                top: 0;
                left: 0;
                margin: 5px;
                font-weight: bold;
                z-index: 1;
                pointer-events: none; /* Prevent clicking */
                user-select: none; /* Prevent selection */
            }
        </style>
        <script>
            function randomRotate(element) {
                const randomDegree = Math.floor(Math.random() * 91) - 45;
                element.style.transform = 'rotate(' + randomDegree + 'deg)';
            }

            document.addEventListener('DOMContentLoaded', function() {
                const decisionStamp = document.getElementById('decision-stamp');
                const committeeStamp = document.getElementById('committee-stamp');
                randomRotate(decisionStamp);
                randomRotate(committeeStamp);
            });
        </script>
    </head>
    <body>
        <div class='container'>
            <iframe class='iframe-doc' src='" . htmlspecialchars($docs_link) . "'></iframe>
            <div class='user-info'>
                <table class='stamp-table'>
                    <tr>
                        <td class='stamp-field' id='decision'>
                            <div class='stamp-text'>Decision</div>
                            <img id='decision-stamp' src='../../" . strtolower($status) . ".png' alt='Decision Stamp'>
                        </td>
                    </tr>
                    <tr>
                        <td class='stamp-field' id='committee'>
                            <div class='stamp-text'>Committee Member</div>
                            <img id='committee-stamp' src='../../" . strtolower($username) . ".png' alt='Committee Member Stamp'>
                        </td>
                    </tr>
                </table>
                <p>Auflagen: " . nl2br(htmlspecialchars($auflagen)) . "</p>
            </div>
        </div>
    </body>
    </html>
    ";

    file_put_contents($page_dir . "/index.html", $page_content);

    // Daten in die Tabelle einfügen
    $insert_sql = "INSERT INTO documents (page_name, docs_link, username, status, auflagen) VALUES ('$page_name', '$docs_link', '$username', '$status', '$auflagen')";
    if ($conn->query($insert_sql) === TRUE) {
        header("Location: seiten/$page_name/index.html");
        exit();
    } else {
        echo "Fehler beim Speichern der Daten: " . $conn->error;
    }
} else {
    echo "Authentifizierung fehlgeschlagen. Bitte &uuml;berpr&uuml;fen Sie Ihren Benutzernamen und Ihr Passwort.";
}

$conn->close();
?>
