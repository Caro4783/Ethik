<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dokumenten&uuml;bersicht</title>
    <style>
        body {
            background-color: #1e2124;
            color: white;
            margin: 0;
            padding: 20px;
            font-family: Arial, sans-serif;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
            border: 1px solid #4f545c;
        }
        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #4f545c;
        }
        th {
            background-color: #2c2f33;
        }
        .accepted {
            background-color: #2e7d32;
            color: white;
        }
        .rejected {
            background-color: #c62828;
            color: white;
        }
        .kb {
            background-color: #f9a825;
            color: black;
        }
        a {
            color: #7289da;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <h1>Dokumenten&uuml;bersicht</h1>
    <table>
        <thead>
            <tr>
                <th>Dokumentname</th>
                <th>Link</th>
                <th>Erstellt von</th>
                <th>Status</th>
                <th>Auflagen</th>
                <th>Erstellt am</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $servername = "rdbms.strato.de";
            $username = "dbu4527972";  
            $password = "Ethik/DeineMutter123";  
            $dbname = "dbs13032693";

            $conn = new mysqli($servername, $username, $password, $dbname);

            if ($conn->connect_error) {
                die("Verbindung fehlgeschlagen: " . $conn->connect_error);
            }

            $sql = "SELECT d.page_name, d.docs_link, g.greeting, d.status, d.auflagen, d.created_at 
                    FROM documents d
                    JOIN greetings g ON d.username = g.username";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    $status_class = '';
                    switch ($row['status']) {
                        case 'Angenommen':
                            $status_class = 'accepted';
                            break;
                        case 'Abgelehnt':
                            $status_class = 'rejected';
                            break;
                        case 'Ab ins KB':
                            $status_class = 'kb';
                            break;
                    }
                    echo "<tr class='{$status_class}'>
                            <td>{$row['page_name']}</td>
                            <td><a href='{$row['docs_link']}' target='_blank'>Dokument</a></td>
                            <td>{$row['greeting']}</td>
                            <td>{$row['status']}</td>
                            <td>{$row['auflagen']}</td>
                            <td>{$row['created_at']}</td>
                          </tr>";
                }
            } else {
                echo "<tr><td colspan='6'>Keine Dokumente gefunden</td></tr>";
            }

            $conn->close();
            ?>
        </tbody>
    </table>
</body>
</html>
