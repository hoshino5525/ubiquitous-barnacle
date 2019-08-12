<html lang="ja"> 
<head> 
     <meta charset="utf-8"> 
     <title>簡易掲示板</title> 
</head> 

<?php
$name = '';
$comment = '';
$pre_name = '名前';
$pre_comment = 'コメント';
$edit_No = '編集番号';
$delete_No = '削除番号';
$states = '';
$PW = 'パスワード';
$PassWord = '削除・編集用のパスワード';
$dsn = 'データベース名';
$user = 'ユーザー名';
$password = 'パスワード';
$pdo = new PDO($dsn, $user, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));

$sql = "CREATE TABLE IF NOT EXISTS tbtest"
." ("
. "id INT AUTO_INCREMENT PRIMARY KEY,"
. "name char(32),"
. "comment TEXT"
.");";
//$sql = "DROP TABLE IF EXISTS tbtest";
$stmt = $pdo->query($sql);
$sql ='SHOW TABLES';
$result = $pdo -> query($sql);
foreach ($result as $row){
	echo $row[0];
	echo '<br>';
}
echo "<hr>";
//テーブルが作られているか確認
$sql ='SHOW CREATE TABLE tbtest';
	$result = $pdo -> query($sql);
	foreach ($result as $row){
	echo $row[1];
	}
	
if (!empty($_POST['inputPW']) && $_POST['inputPW']==$PassWord) {
//正しいパスワードが入力されている場合
	if (!empty($_POST['editNo'])) {
		//編集のフラグを立てる
		$edit_id = $_POST['editNo']; //変更したい投稿番号(編集フラグ)
		$sql = 'SELECT * FROM tbtest';
		$stmt = $pdo->query($sql);
		$results = $stmt->fetchAll();
		foreach ($results as $row) {
		//$rowの中にはテーブルのカラム名が入る
			if ($row['id']==$edit_id) {
				$pre_name = $row['name'];
				$pre_comment = $row['comment'];
			}
		}
		$states = '編集します';
	} elseif (!empty($_POST['deleteNo'])) {
		//削除
		$id = $_POST['deleteNo'];
		$sql = 'delete from tbtest where id=:id';
		$stmt = $pdo->prepare($sql);
		$stmt->bindParam(':id', $id, PDO::PARAM_INT);
		$stmt->execute();
		$states = '削除しました';
	} elseif (!empty($_POST['deleteNo']) && !empty($_POST['editNo'])){
	//削除番号と編集番号の両方がある場合
		$states = '削除と編集は両立できません';
	} 
} elseif (!empty($_POST['inputPW']) && $_POST['inputPW']!=$PassWord) {
//誤ったパスワードが入力されている場合
	$states = 'パスワードが間違っています';
} else {
//パスワードが入力されていない場合
	if (ctype_digit($_POST['edit_mode'])) {
	//編集フラグがある場合(編集実行)
		$id = $_POST['edit_mode'];//変更する投稿番号
		$name = $_POST['name'];
		$comment = $_POST['comment']; 
		$sql = 'update tbtest set name=:name,comment=:comment where id=:id';
		$stmt = $pdo->prepare($sql);
		$stmt->bindParam(':name', $name, PDO::PARAM_STR);
		$stmt->bindParam(':comment', $comment, PDO::PARAM_STR);
		$stmt->bindParam(':id', $id, PDO::PARAM_INT);
		$stmt->execute();
		$states = '編集しました';
	} elseif (!ctype_digit($_POST['edit_mode'])) {
	//フラグがない場合(投稿)
		$name = $_POST['name'];
		$comment = $_POST['comment'];
		if (!empty($name) && !empty($comment)) {
			$sql = $pdo -> prepare("INSERT INTO tbtest (name, comment) VALUES (:name, :comment)");
			$sql -> bindParam(':name', $name, PDO::PARAM_STR);
			$sql -> bindParam(':comment', $comment, PDO::PARAM_STR);
		
			$sql -> execute();
			$states = '投稿しました';
		} elseif (!empty($_POST['deleteNo']) || !empty($_POST['editNo'])) {
		//パスワードが入力されず、削除番号や編集番号が入力されている場合
			$states = 'パスワードが入力されていません';
		} else {
		$states = '入力データに誤りがあります';
		}
	} else {
		$states = '未投稿';
	}
}
//表示
$sql = 'SELECT * FROM tbtest';
$stmt = $pdo->query($sql);
$results = $stmt->fetchAll();
foreach ($results as $row){
//$rowの中にはテーブルのカラム名が入る
	echo $row['id'].',';
	echo $row['name'].',';
	echo $row['comment'].'<br>';
echo "<hr>";
}
?>

<form action="" method="POST">
        <input type="text" name="states" value="<?php echo $states; ?>" disabled="disabled">
	</form>

	<form action="" method="POST">
	<input type="hidden" name="edit_mode" value="<?php echo $edit_id; ?>"></br>
	<input type="text" name="name" placeholder="<?php echo $pre_name; ?>"> </br>
	<input type="text" name="comment" placeholder="<?php echo $pre_comment; ?>"> 
	<input type="submit" name="post" value="投稿"></br>
	</form>
	
	<form action="" method="POST"> 
	<input type="text" name="deleteNo" placeholder="<?php echo $delete_No; ?>"></br> 
	<input type="text" name="editNo" placeholder="<?php echo $edit_No; ?>"> </br>
	<input type="text" name="inputPW" placeholder="<?php echo $PW; ?>">
	<input type="submit" name="post" value="送信"> 
	</form>
	<hr>
</body>
</html>
