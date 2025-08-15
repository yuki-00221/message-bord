<!-- functions -->
<?php
    function reset_values($values){
        foreach($values as &$value){
            $value = '';
        }
        unset($value);
        return $values;
    }

    function write($pdo, $values, $message){
        if((isset($_POST['name']) && $_POST['name'] !== '') &&
            (isset($_POST['comment']) && $_POST['comment'] !== '')){
            if(isset($_POST['id']) && $_POST['id'] !== ''){
                $id = (int)$_POST['id'];
                $sql = 'UPDATE message_bord SET name=:name, comment=:comment, is_edited=TRUE WHERE id = :id';
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':name', $_POST['name'], PDO::PARAM_STR);
                $stmt->bindParam(':comment', $_POST['comment'], PDO::PARAM_STR);
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                $stmt->execute();
                $values = reset_values($values);
                $message = 'ID: ' . $id . ' を更新しました<br />';
            }else{
                if(isset($_POST['password']) && $_POST['password'] !== ''){
                    $hashed_pass = password_hash($_POST['password'], PASSWORD_DEFAULT);
                    $sql = 'INSERT INTO message_bord (name, comment, password) VALUES (:name, :comment, :password)';
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindParam(':name', $_POST['name'], PDO::PARAM_STR);
                    $stmt->bindParam(':comment', $_POST['comment'], PDO::PARAM_STR);
                    $stmt->bindParam(':password', $hashed_pass);
                    $stmt->execute();
                    $values = reset_values($values);
                    $message = '新規コメントを受け付けました<br />';
                } else {
                    $values['name'] = isset($_POST['name']) ? $_POST['name'] : '';
                    $values['comment'] = isset($_POST['comment']) ? $_POST['comment'] : '';
                    $message = 'パスワードが入力されていません<br />';
                }
            }
        }else{
            $values['name'] = isset($_POST['name']) ? $_POST['name'] : '';
            $values['comment'] = isset($_POST['comment']) ? $_POST['comment'] : '';
            $message = '名前またはコメントが入力されていません<br />';
        }
        return [$values, $message];
    }

    function update($pdo, $values, $message){
        if((isset($_POST['update_id']) && $_POST['update_id'] !== '') &&
            (isset($_POST['update_pass']) && $_POST['update_pass'] !== '')){
            $id = $_POST['update_id'];
            $update_pass = $_POST['update_pass'];
            $sql = 'SELECT * FROM message_bord WHERE id = :id';
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            if ($stmt->rowCount() <= 0) {
                $message = 'ID: ' . $id . ' のコメントは存在しません<br />';
                $values = reset_values($values);
            }else{
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                if(password_verify($update_pass, $row['password'])){
                    $values['id'] = $row['id'];
                    $values['name'] = $row['name'];
                    $values['comment'] = $row['comment'];
                    $message = 'ID: ' . $id . ' のコメントを更新中（パスワード欄の入力は反映されません）<br />';
                } else {
                    $values['update_id'] = isset($_POST['update_id']) ? $_POST['update_id'] : '';
                    $message = 'IDまたはパスワードが間違っています<br />';
                }
            }
        } else {
            $values['update_id'] = isset($_POST['update_id']) ? $_POST['update_id'] : '';
            $message = 'IDまたはパスワードが入力されていません<br />';
        }
        return [$values, $message];
    }

    function delete($pdo, $values, $message){
        if((isset($_POST['delete_id']) && $_POST['delete_id'] !== '') &&
            (isset($_POST['delete_pass']) && $_POST['delete_pass'] !== '')){
            $id = $_POST['delete_id'];
            $delete_pass = $_POST['delete_pass'];
            $sql = 'SELECT * FROM message_bord WHERE id = :id';
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            if ($stmt->rowCount() <= 0) {
                $values = reset_values($values);
                $message = 'ID: ' . $id . ' のコメントは存在しません<br />';
            } else {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                if(password_verify($delete_pass, $row['password'])){
                    $sql = 'DELETE FROM message_bord WHERE id = :id';
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                    $stmt->execute();
                    $message = 'ID: ' . $id . ' のコメントを削除しました<br />'; 
                } else {
                    $values['delete_id'] = isset($_POST['delete_id']) ? $_POST['delete_id'] : '';
                    $message = 'IDまたはパスワードが間違っています<br />';
                }       
            }
            $values = reset_values($values);
        } else {
            $values['delete_id'] = isset($_POST['delete_id']) ? $_POST['delete_id'] : '';
            $message = 'IDまたはパスワードが入力されていません<br />';
        }
        return [$values, $message];
    }

    function select($pdo){
        $sql = 'SELECT * FROM message_bord ORDER BY id DESC';
        $stmt = $pdo->query($sql);
        $results = $stmt->fetchALL();

        foreach($results as $row){
            echo $row['id'].'. ';
            echo '<strong>'.$row['name'].'</strong> ';
            echo '<small style="color: #888;">'.$row['date'];
            if ($row['is_edited']) {
                echo '（編集済み）';
            }
            echo '</small><br />';
            echo str_repeat('&nbsp;', 2).$row['comment'].'<br />';
            echo '<hr>';
        }
        return;
    }
?>
<!-- start -->
<?php
    session_start();
    $dsn = 'mysql:dbname=XXXDB;host=localhost';
    $user = 'XXXUSER';
    $password = 'XXXPASSWORD';
    $pdo = new PDO($dsn, $user, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));

    $values = array(
        'id' => '',
        'name' => '',
        'comment' => '',
        'update_id' => '',
        'delete_id' => '',
    );
    $message = '<br />';
    $processed = false;

    if ($_SERVER['REQUEST_METHOD'] === 'POST'){
        if(isset($_POST['submit_input'])){
            [$values, $message] = write($pdo, $values, $message);
            $processed = true;
        }

        if(isset($_POST['submit_update'])){
            [$values, $message] = update($pdo, $values, $message);
            $processed = true;
        }

        if(isset($_POST['submit_delete'])){
            [$values, $message] = delete($pdo, $values, $message);
            $processed = true;
        }
    }

    if ($processed) {
        $_SESSION['values'] = $values;
        $_SESSION['message'] = $message;
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }

    if (isset($_SESSION['values'])) {
        $values = $_SESSION['values'];
        unset($_SESSION['values']);
    }

    if (isset($_SESSION['message'])) {
        $message = $_SESSION['message'];
        unset($_SESSION['message']);
    }
?>

<!DOCTYPE html>
<html lang='ja'>
    <head>
        <meta charset='UTF-8'>
        <title>mission_5-4</title>
        <style>
            form {
                display: flex;
                gap: 8px;
                align-items: center;
                margin-bottom: 10px;
            }

            input[type='text'][name='name'] {
                width: 100px;
                padding: 5px;
            }

            input[type='text'][name='comment'] {
                width: 300px;
                padding: 5px;
            }

            input[type='password'] {
                width: 100px;
                padding: 5px;
            }

            input[type='number'] {
                width: 100px;
                padding: 5px;
            }

            input[type='submit'] {
                padding: 5px 12px;
            }
        </style>
    </head>
    <body>
        <!-- input form -->
        <form action='' method='post'>
            <input type='hidden' name='id' placeholder='番号' value='<?php echo $values['id']; ?>'>
            <input type='text' name='name' placeholder='名前' value='<?php echo $values['name']; ?>'>
            <input type='password' name='password' placeholder='パスワード'>
            <input type='text' name='comment' placeholder='コメント' value='<?php echo $values['comment']; ?>'>
            <input type='submit' name='submit_input' value='送信' >
        </form>

        <!-- update form -->
         <form action='' method='post'>
            <input type='number' name='update_id' placeholder='更新対象番号' value='<?php echo $values['update_id']; ?>'>
            <input type='password' name='update_pass' placeholder='パスワード'>
            <input type='submit' name='submit_update' value='更新' >
        </form>

        <!-- delete form -->
         <form action='' method='post'>
            <input type='number' name='delete_id' placeholder='削除対象番号' value='<?php echo $values['delete_id']; ?>'>
            <input type='password' name='delete_pass' placeholder='パスワード'>
            <input type='submit' name='submit_delete' value='削除' >
        </form>
        
        <?php
            echo $message.'<br />';
            select($pdo);
        ?>
    </body>
</html>
