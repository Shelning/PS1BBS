<?php
include 'database.php'; //データベース情報

//ログインしているかどうか
if (!isset($_SESSION["name"])) {
	echo "3 秒後ログインページへリダイレクトします";
	header("refresh:3;url=login-signup.php");
	exit(1);
}

// エラーメッセージ、成功メッセージの初期化
$errorMessage = "";
$errorMessage2 = "";
$successMessage = "";

//半角英数字をそれぞれ1種類以上含む8文字以上100文字以下の正規表現
$pattern = '/\A(?=.*?[a-z])(?=.*?\d)[a-z\d]{8,100}+\z/i';

//セッションからユーザー名を取る
$username = $_SESSION["name"];

try {
	$pdo = new PDO($dsn, $db[user], $db[pass]);
	$sql = "SELECT * FROM users WHERE name = '$username'";
	$row = $pdo->query($sql)->fetch(PDO::FETCH_ASSOC);

	$email = $row['email'];

	//ユーザー名・メールアドレス編集機構
	if (!empty($_POST["edit02"])) { //ユーザー名・メールアドレスの送信があったら

		$password = $row['password'];
		$hashpass = hash("sha256", $_POST["password"]);

		//ユーザー名に @ が含まれているかどうか
		$temp = strpos($_POST["newusername"], "@");

		if (empty($_POST["newusername"])) {
			$errorMessage2 = "新しいユーザー名を記入してください";

		} elseif ($temp !== false) { //ユーザー名に @ が含まれる場合
			$errorMessage2 = 'ユーザー名に @ は使えません';

		} elseif (empty($_POST["newemail"])) {
			$errorMessage2 = "新しいメールアドレスを記入してください";

		} elseif (!strpos($_POST["newemail"], "@")) { //メールアドレスに @ が含まれない場合
			$errorMessage2 = '有効なメールアドレスを入力してください';

		} elseif ($username === $_POST["newusername"] && $email === $_POST["newemail"]) {
			$errorMessage2 = 'ユーザー名・メールアドレスともに現在のものと一緒です';

		} elseif (empty($_POST["password"])) {
			$errorMessage2 = "現在のパスワードを記入してください";

		} elseif ($password !== $hashpass) {
			$errorMessage2 = "パスワードが違います";

		} else {

			$newname = $_POST["newusername"];
			$newemail = $_POST["newemail"];

			//ユーザーネームの重複を確認
			$stmt = "SELECT * FROM users WHERE name = '$newname'";
			$count = (int)$pdo->query($stmt)->fetchColumn();

			//メールアドレスの重複を確認
			$stmt = "SELECT count(*) FROM users WHERE email = '$newemail'";
			$countEmail = (int)$pdo->query($stmt)->fetchColumn();

			if ($count > 0 && $username !== $newname) { //重複かつ違うユーザー名を指定した場合
				$errorMessage2 = 'そのユーザー名は既に使用されています';

			} elseif ($countEmail > 0 && $email !== $newemail) { //重複かつ違うメールアドレスを指定した場合
				$errorMessage2 = 'そのメールアドレスは既に使用されています';

			} else {

				// 編集させる
				$sql = "update users set name='$newname', email='$newemail' where name='$username'";
				$result = $pdo->query($sql);

				//現在のセッションIDを新しく生成したものと置き換える。セキュリティ上重要
				session_regenerate_id(true);

				$_SESSION["name"] = $newname;
				$username = $_SESSION["name"];
				$email = $newemail;
				$successMessage = "正しく変更されました！";

			} //END ユーザー名・メールアドレスの重複確認

		} //END 項目チェック

	}

	//パスワード編集機構
	if (!empty($_POST["passedit02"])) { //パスワードの送信があったら

		$password = $row['password'];
		$hashpass = hash("sha256", $_POST["password"]);
		$newHashpass = hash("sha256", $_POST["newpassword"]);

		if (empty($_POST["password"])) {
			$errorMessage = "現在のパスワードを記入してください";

		} elseif ($password !== $hashpass) {
			$errorMessage = "パスワードが違います";

		} elseif (empty($_POST["newpassword"])) {
			$errorMessage = "新しいパスワードを記入してください";

		} elseif ($password === $newHashpass) {
			$errorMessage = "新しいパスワードが現在のと一緒です";

		} elseif (empty($_POST["checkNewPass"])) {
			$errorMessage = "新しいパスワードをもう一度記入してください";

		} elseif ($_POST["newpassword"] !== $_POST["checkNewPass"]) {
			$errorMessage = "新しいパスワードが一致していません";

		} elseif (!preg_match($pattern, $_POST["newpassword"])) { //パスワードがパターンに一致しない場合
			$errorMessage = "パスワードは8文字以上で、半角英字と半角数字をそれぞれ最低1つ含む必要があります";

		} else {

			// 編集させる
			$sql = "update users set password='$newHashpass' where name='$username'";
			$result = $pdo->query($sql);

		    //強制ログアウト
			$_SESSION = array();
			session_destroy();
			setcookie(session_name(), '', time()-42000, '/');
			echo "パスワードが正しく変更されました！".'<br>';
			echo "5 秒後ログインページへリダイレクトします";
			header("refresh:5;url=login-signup.php");
			exit(1);

		}
	}

} catch (PDOException $e) {
	$errorMessage = 'データベースエラー';
	// $e->getMessage() でエラー内容を参照可能
	// echo $e->getMessage();
} //try終了



?>

<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <title>プロフィール | PS1BBS</title>
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <meta content="" name="keywords">
  <meta content="" name="description">

  <!-- Favicons -->
  <link href="img/favicon.ico" rel="icon">
  <link href="img/favicon.ico" rel="apple-touch-icon">

  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css?family=Lato:300,400,700,900|Raleway:400,300,700,900" rel="stylesheet">

  <!-- Bootstrap CSS File -->
  <link href="lib/bootstrap/css/bootstrap.min.css" rel="stylesheet">

  <!-- Libraries CSS Files -->
  <!---
  <link href="lib/font-awesome/css/font-awesome.min.css" rel="stylesheet">
  --->

  <link href="https://use.fontawesome.com/releases/v5.0.6/css/all.css" rel="stylesheet">

  <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script><script type="text/javascript" src="js/footerFixed.js"></script>

  <!-- Main Stylesheet File -->
  <link href="css/style.css" rel="stylesheet">

  <!-- =======================================================
    Template Name: Spot
    Template URL: https://templatemag.com/spot-bootstrap-freelance-template/
    Author: TemplateMag.com
    License: https://templatemag.com/license/
  ======================================================= -->
</head>

<body>

  <!-- Fixed navbar -->
  <div class="navbar navbar-inverse navbar-fixed-top">
    <div class="container">
      <div class="navbar-header">
        <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
        <a class="navbar-brand" href="index.php">PlayStationの記憶</a>
      </div>
      <div class="navbar-collapse collapse">
        <ul class="nav navbar-nav navbar-right">
			<li class="active"><a href="newPost.php">投稿する</a></li>
			<li><a href="about.php">ABOUT</a></li>
			<li><a href="profile.php">PROFILE</a></li>
			<li><a href="logout.php">LOGOUT</a></li>
        </ul>
      </div>
      <!--/.nav-collapse -->
    </div>
  </div>

  <div class="float-container2" id="accountEdit">

	  <div class="login-container">
		  <h2 class="h2h2">ユーザー名・メールアドレスの変更</h2>
		  メールアドレスは認証がないので、適当で大丈夫です。
		  <form action="" method="post">
			  <div class="post-form">
				   <h3>新しいユーザー名</h3>
				   <input class="text-input-2" type="text" name="newusername" placeholder="新しいユーザー名を入力" value="<?php if (empty($_POST["newusername"])) { echo h($username);
							  } else {
								echo h($_POST["newusername"]);}
						?>">
			  </div>
			  <div class="post-form">
				   <h3>新しいメールアドレス</h3>
				   <input class="text-input-2" type="email" name="newemail" placeholder="新しいメールアドレスを入力" value="<?php if (empty($_POST["newemail"])) {
							   echo h($email);
						 } else {
						   echo h($_POST["newemail"]);}
				   ?>">
			  </div>
			  <div class="post-form">
				   <h3>現在のパスワード</h3>
				   <input class="text-input-2" type="password" name="password" value="" placeholder="現在のパスワードを入力" required>
			  </div>
			  <br>
			  <div class="errorMessage"><?php echo h($errorMessage2); ?></div>
			  <div class="successMessage"><?php echo h($successMessage); ?></div>
			  <br>
			  <div class="submit"><input type="submit" name="edit02" value="変更する"></div>
		  </form>
	  </div>

	  <div class="signup-container">
		  <h2 class="h2h2">パスワードの変更</h2>
		  パスワードはハッシュ化されていますが、あまり安全ではありません。重要な文字列は入力しないでください。
		  <br>
		  パスワードは8文字以上で、半角英字と半角数字をそれぞれ最低1つ含む必要があります。
		  <form action="" method="post">
			  <div class="post-form">
				   <h3>現在のパスワード</h3>
				   <input class="text-input-2" type="password" name="password" value="" placeholder="現在のパスワードを入力" required>
			  </div>
			  <div class="post-form">
				   <h3>新しいパスワード</h3>
				   <input class="text-input-2" type="password" name="newpassword" value="" placeholder="新しいパスワードを入力" required>
			  </div>
			  <div class="post-form">
				   <h3>新しいパスワードをもう一度入力</h3>
				   <input class="text-input-2" type="password" name="checkNewPass" value="" placeholder="新しいパスワードを再度入力" required>
			  </div>
			  <br>
			  <div class="errorMessage"><?php echo h($errorMessage); ?></div>
			  <br>
			  <div class="submit"><input type="submit" name="passedit02" value="変更する"></div>
		  </form>
	  </div>

  </div>

	<div id="footer">
	  <div id="copyrights">
		<div class="container">
		  <div class="credits">
			<!--
			  You are NOT allowed to delete the credit link to TemplateMag with free version.
			  You can delete the credit link only if you bought the pro version.
			  Buy the pro version with working PHP/AJAX contact form: https://templatemag.com/spot-bootstrap-freelance-template/
			  Licensing information: https://templatemag.com/license/
			-->
			Created with Spot template by <a href="https://templatemag.com/">TemplateMag</a>
		  </div>
		</div>
	  </div>
	</div>

</body>
</html>
