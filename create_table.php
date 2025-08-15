<!DOCTYPE html>
<html lang='ja'>
    <head>
        <meta charset='UTF-8'>
        <title>mission-5_create-table</title>
    </head>
    <body>
        <?php
            // connect DB
            $dsn = 'mysql:dbname=XXXDB;host=localhost';
            $user = 'XXXUSER';
            $password = 'XXXPASSWORD';
            $pdo = new PDO($dsn, $user, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));

            // create table
            $sql = 'CREATE TABLE IF NOT EXISTS message_bord'
                   .'('
                   .'id INT AUTO_INCREMENT PRIMARY KEY,'
                   .'name CHAR(32),'
                   .'comment TEXT,'
                   .'password VARCHAR(255) NOT NULL,'
                   .'date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,'
                   .'is_edited BOOLEAN DEFAULT FALSE'
                   .');';
            $stmt = $pdo->query($sql);
            echo 'created message_bord'
        ?>
    </body>
</html>
