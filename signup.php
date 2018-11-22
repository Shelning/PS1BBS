<?php
include 'database.php'; //データベース情報

// エラーメッセージ、登録完了メッセージの初期化
$errorMessage = "";
$signupMessage = "";

//半角英数字をそれぞれ1種類以上含む8文字以上100文字以下の正規表現
$pattern = '/\A(?=.*?[a-z])(?=.*?\d)[a-z\d]{8,100}+\z/i';

if (!empty($_POST["signup"])) { // 登録ボタンが押された場合
	//空欄チェック
	//必ずサーバー側でチェックする
	if (empty($_POST["username"])) { //ユーザーネームが空
        $errorMessage = 'ユーザーネームを入力してください';
    } else if (empty($_POST["password"])) { //パスワードが空
       	$errorMessage = 'パスワードを入力してください';
    } else if (empty($_POST["password2"])) { //確認用パスワードが空
        $errorMessage = '確認用パスワードを入力してください';
    } else if (!preg_match($pattern, $_POST["password"])) { //パスワードがパターンに一致しない場合
       	$errorMessage = 'パスワードは8文字以上で、半角英字と半角数字をそれぞれ最低1つ含む必要があります';
	}

    if (!empty($_POST["username"]) and !empty($_POST["password"]) and !empty($_POST["password2"]) and $_POST["password"] == $_POST["password2"] and preg_match($pattern, $_POST["password"])) {
        // 入力したユーザネーム、メールアドレス、パスワードを格納
        $username = $_POST["username"];
		$email = $_POST["email"];
        $password = $_POST["password"];

        // エラー処理
        try {
            $pdo = new PDO($dsn, $db[user], $db[pass]);

			//ユーザーネームの重複を確認
			$stmt = "SELECT count(*) FROM users WHERE name = '$username'";
			$count = (int)$pdo->query($stmt)->fetchColumn();
			if ($count > 0) {
 		       $errorMessage = 'そのユーザー名は既に使用されています';
			} else {
            	$sql = $pdo->prepare("INSERT INTO users(name, email, password) VALUES (:name, :email, :password)");
				$sql -> bindParam(':name', $username, PDO::PARAM_STR);
				$sql -> bindParam(':email', $email, PDO::PARAM_STR);
				$hashpass = hash("sha256", $password);
				$sql -> bindParam(':password', $hashpass, PDO::PARAM_STR);
            	$sql->execute();
            	$signupMessage = '登録が完了しました！。';
			}
        } catch (PDOException $e) {
            $errorMessage = 'データベースエラー';
            // $e->getMessage() でエラー内容を参照可能（デバッグ時のみ表示）
            // echo $e->getMessage();
        }
    } else if($_POST["password"] != $_POST["password2"]) {
        $errorMessage = 'パスワードが一致しません';
    }
}
?>


<!doctype html>
<html>
    <head>
		<meta charset="UTF-8">
        <title>新規登録</title>
    </head>
    <body>
        <h1>新規登録画面</h1>
        <form id="loginForm" name="loginForm" action="" method="POST">
            <fieldset>
                <legend>新規登録フォーム</legend>
                <div><font color="#ff0000"><?php echo htmlspecialchars($errorMessage, ENT_QUOTES); ?></font></div>
                <div><font color="#0000ff"><?php echo htmlspecialchars($signupMessage, ENT_QUOTES); ?></font></div>
				パスワードはハッシュ化されていますが、あまり安全ではありません。重要な文字列は入力しないでください。
				<br>
				パスワードは8文字以上で、半角英字と半角数字をそれぞれ最低1つ含む必要があります。
				<br>
				メールアドレスは認証がないので、適当で大丈夫です。
				<br>
                <label for="username">ユーザー名</label><input type="text" id="username" name="username" placeholder="ユーザー名を入力" value="<?php if (!empty($_POST["username"])) {echo htmlspecialchars($_POST["username"], ENT_QUOTES);} ?>" required>
                <br>
                <label for="email">メールアドレス</label><input type="email" id="email" name="email" placeholder="example@gmail.com" value="<?php if (!empty($_POST["email"])) {echo htmlspecialchars($_POST["email"], ENT_QUOTES);} ?>" required>
                <br>
                <label for="password">パスワード</label><input type="password" id="password" name="password" value="" placeholder="パスワードを入力" required>
                <br>
                <label for="password2">パスワード(確認用)</label><input type="password" id="password2" name="password2" value="" placeholder="再度パスワードを入力" required>
                <br>
                <input type="submit" id="signup" name="signup" value="新規登録">
            </fieldset>
        </form>
        <br>
        <form action="login.php">
            <input type="submit" value="ログインページヘ">
        </form>
    </body>
</html>
