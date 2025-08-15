<!DOCTYPE html>
<html lang='ja'>
    <head>
        <meta charset='UTF-8'>
        <title>mission-5_drop-table</title>
    </head>
    <body>
        <?php
            // connect DB
            $dsn = 'mysql:dbname=XXXDB;host=localhost';
            $user = 'XXXUSER';
            $password = 'XXXPASSWORD';
            $pdo = new PDO($dsn, $user, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));

            $sql = 'DROP TABLE message_bord';
            $stmt = $pdo->query($sql);
            echo 'deleted messege_bord<br />';
        ?>
    </body>
</html>
