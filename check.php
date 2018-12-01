<?php
include 'database.php'; //データベース情報

if (isset($_SESSION["name"])) {
	echo "Redirecting to the top page in 3 seconds...";
	header("refresh:3;url=index.php");
	exit(1);
}

//時刻をマイクロ秒単位で取得する関数
function getUnixTimeMillSecond(){
    $arrTime = explode('.', microtime(true)); //microtimeを.で分割
    return date('Y-m-d H:i:s', $arrTime[0]) . '.' .$arrTime[1]; //日時＋ミリ秒
}

//半角英数字をそれぞれ1種類以上含む8文字以上100文字以下の正規表現
$pattern = '/\A(?=.*?[a-z])(?=.*?\d)[a-z\d]{8,100}+\z/i';

// エラーメッセージ、結果メッセージの初期化
$errorMessage = "";
$errorMessage2 = "";
$resultMessage = "";

//フォームを隠す
$passEdit = array();

try {

	$pdo = new PDO($dsn, $db[user], $db[pass]);

	try {

		//アカウント情報の確認
		if (!empty($_POST["check"])) {

			$user = $_POST["checkUser"];
			$email = $_POST["checkEmail"];
			$pass = $_POST["checkPass"];
			$pass = hash("sha256", $pass);

			//何も入力されてない場合
			if (empty($_POST["checkUser"]) && empty($_POST["checkEmail"]) && empty($_POST["checkPass"])) {
				throw new RuntimeException("覚えているものを 2つ入力してください");

			//ユーザー名がわからない場合
			} elseif (empty($_POST["checkUser"]) && !empty($_POST["checkEmail"]) && !empty($_POST["checkPass"])) {

				$sql = "SELECT * FROM users WHERE email = '$email'";
				$row = $pdo->query($sql)->fetch(PDO::FETCH_ASSOC);

				if ($row === false) {
					throw new RuntimeException("入力されたメールアドレスは存在しません");
				} elseif ($pass === $row["password"]) {
					$resultMessage = "あなたのユーザー名は " . $row["name"] . " です";
				} else {
					throw new RuntimeException("パスワードが違います");
				}

			//メールアドレスがわからない場合
			} elseif (!empty($_POST["checkUser"]) && empty($_POST["checkEmail"]) && !empty($_POST["checkPass"])) {

				$sql = "SELECT * FROM users WHERE name = '$user'";
				$row = $pdo->query($sql)->fetch(PDO::FETCH_ASSOC);

				if ($row === false) {
					throw new RuntimeException("入力されたユーザー名は存在しません");
				} elseif ($pass === $row["password"]) {
					$resultMessage = "あなたのメールアドレスは " . $row["email"] . " です";
				} else {
					throw new RuntimeException("パスワードが違います");
				}

			//パスワードが分からない場合(再設定)
			} elseif (!empty($_POST["checkUser"]) && !empty($_POST["checkEmail"]) && empty($_POST["checkPass"])) {

				$sql = "SELECT * FROM users WHERE name = '$user'";
				$row = $pdo->query($sql)->fetch(PDO::FETCH_ASSOC);

				if ($row === false) {
					throw new RuntimeException("入力されたユーザー名は存在しません");
				} elseif ($email !== $row["email"]) {
					throw new RuntimeException("入力されたユーザー名に一致するメールアドレスではありません");
				} else {
					$passEdit = array(1); //フォームを出現させる
				}

			//すべて入力されている場合
			} elseif (!empty($_POST["checkUser"]) && !empty($_POST["checkEmail"]) && !empty($_POST["checkPass"])) {
				throw new RuntimeException("全部わかってるんなら書くなや");

			//1つだけ入力されている場合
			} else {
				throw new RuntimeException("覚えているものを「2つ」入力してください");
			}

		}

		//パスワードの再設定
		if (!empty($_POST["passEdit"])) {

			$user = $_POST["checkUser"];
			$newPass = $_POST["newPass"];

			if (empty($_POST["newPass"])) {
				$errorMessage2 = "新しいパスワードを入力してください";
				$passEdit = array(1);
			} elseif (empty($_POST["newPass2"])) {
				$errorMessage2 = "確認用パスワードを入力してください";
				$passEdit = array(1);
			} elseif (!preg_match($pattern, $newPass)) { //パスワードがパターンに一致しない場合
				$errorMessage2 = "パスワードは8文字以上で、半角英字と半角数字をそれぞれ最低1つ含む必要があります";
				$passEdit = array(1);
			} elseif ($_POST["newPass"] !== $_POST["newPass2"]) {
				$errorMessage2 = "パスワードが一致していません";
				$passEdit = array(1);
			} else {

				$new_hashpass = hash("sha256", $newPass);
				$sql = "update users set password='$new_hashpass' where name='$user'";
				$result = $pdo->query($sql);

				//ログインページへ飛ばす
				$_SESSION = array();
				session_destroy();
				setcookie(session_name(), '', time()-42000, '/');
				echo "パスワードが正しく変更されました！".'<br>';
				echo "5 秒後ログインページへリダイレクトします";
				header("refresh:5;url=login-signup.php");
				exit(1);

			}

		}

	} catch (RuntimeException $e) {
		$errorMessage = $e->getMessage();
	}

} catch (PDOException $e) { //$eに例外の情報が格納される
	exit('データベースに接続できませんでした。' . $e->getMessage()) ; //$e->getMessage()で格納されたエラーメッセージを表示
}
?>



<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <title>アカウント確認 | PS1BBS</title>
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
			<li><a href="about.php">ABOUT</a></li>
    		<li class="active"><a href="login-signup.php">投稿する -> LOGIN or SIGNUP</a></li>
        </ul>
      </div>
      <!--/.nav-collapse -->
    </div>
  </div>


  <!-- PORTFOLIO SECTION -->
  <!---
  <div id="dg">
    <div class="container">
      <div class="row centered">
        <h4>投稿一覧</h4>
        <br>
  --->

    <div class="main">

		<?php if (empty($passEdit)) { ?>
			<h2 class="h2h2">どれか覚えているもの 2つを入力してください</h2>
			<form action="" method="post">
				<div class="post-form">
					 <h3>ユーザー名</h3>
					 <input class="text-input-2" type="text" name="checkUser" value="<?php if (!empty($_POST["checkUser"])) {echo h($_POST["checkUser"]);} ?>" placeholder="ユーザー名を入力">
				</div>
				<div class="post-form">
					 <h3>メールアドレス</h3>
					 <input class="text-input-2" type="email" name="checkEmail" value="<?php if (!empty($_POST["checkEmail"])) {echo h($_POST["checkEmail"]);} ?>" placeholder="example@gmail.com">
				</div>
				<div class="post-form">
					 <h3>パスワード</h3>
					 <input class="text-input-2" type="password" name="checkPass" value="" placeholder="パスワードを入力">
				</div>
				<br>
				<div class="errorMessage"><?php echo h($errorMessage); ?></div>
				<div class="successMessage"><?php echo h($resultMessage); ?></div>
				<br>
				<div class="submit"><input type="submit" name="check" value="確認する"></div>
			</form>
		<?php } ?>

		<?php if (!empty($passEdit)) { ?>
			<h2 class="h2h2">パスワードを再設定してください</h2>
			<h4>パスワードは8文字以上で、半角英字と半角数字をそれぞれ最低1つ含む必要があります</h4>
			<form action="" method="post">
				<input type="hidden" name="checkUser" value="<?php if (!empty($_POST["checkUser"])) {echo h($_POST["checkUser"]);} ?>">
				<div class="post-form">
					 <h3>新しいパスワード</h3>
					 <input class="text-input-2" type="password" name="newPass" value="" placeholder="パスワードを入力" required>
				</div>
				<div class="post-form">
					 <h3>パスワードをもう一度入力してください</h3>
					 <input class="text-input-2" type="password" name="newPass2" value="" placeholder="再度パスワードを入力" required>
				</div>
				<br>
				<div class="errorMessage"><?php echo h($errorMessage2); ?></div>
				<br>
				<div class="submit"><input type="submit" name="passEdit" value="再設定する"></div>
			</form>
		<?php } ?>



        <!--
            <a href="#">
              <table>
                  <thead>
                    <tr>
                      <th><i class="fas fa-thumbs-up"></i> 5</th>
                      <td class="tag"><i class="fas fa-tag"></i> 釣りタイトル</td>
                      <td class="username"><i class="fas fa-user"></i> Shoma</td>
                      <td><i class="fas fa-clock"></i> 2018/11/20 4:35:50</td>
                    </tr>
                  </thead>
                  <tbody>
                    <tr>
                      <th><i class="fas fa-thumbs-down"></i> 2</th>
                      <td colspan="2" class="title">新種ポケモン発見</td>
                      <td><i class="fas fa-comments"></i> 2</td>
                    </tr>
                  </tbody>
                </table>
            </a>
        -->


      <!-- /.row -->
    <!--- </div> --->
    <!-- container -->
  </div>

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

  <!-- JavaScript Libraries -->
  <script src="lib/jquery/jquery.min.js"></script>
  <script src="lib/bootstrap/js/bootstrap.min.js"></script>
  <script src="lib/php-mail-form/validate.js"></script>
  <script src="lib/chart/chart.js"></script>

  <!-- Template Main Javascript File -->
  <script src="js/main.js"></script>

</body>
</html>
