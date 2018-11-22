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

// エラーメッセージ、登録完了メッセージの初期化
$errorMessage = "";

try {

	$pdo = new PDO($dsn, $db[user], $db[pass]);

	//ログイン機構
	if (isset($_POST["login"])) { // ログインボタンが押された場合
		//空欄チェック
		//必ずサーバー側でチェックする
	    if (empty($_POST["username2"])) {
			$errorMessage2 = 'ユーザーネームを入力してください';
		} elseif (empty($_POST["password"])) {
			$errorMessage2 = 'パスワードを入力してください';
		}

		if (!empty($_POST["username2"]) and !empty($_POST["password"])) {
	        // 入力したユーザネーム、パスワードを格納
	        $username = $_POST["username2"];
	        $password = $_POST["password"];
			$hashpass = hash("sha256", $password);

            $sql = "SELECT * FROM users WHERE name = '$username'";
            $row = $pdo->query($sql)->fetch(PDO::FETCH_ASSOC);

			if ($row === false) { //等しく同じ型であるとき
				//該当データなし
				$errorMessage2 = "ユーザー名が間違っているか、登録されていません";
			} else {
				if ($hashpass == $row['password']) {
					//現在のセッションIDを新しく生成したものと置き換える。セキュリティ上重要
					session_regenerate_id(true);

					//セッション変数=ページが遷移しても維持される変数(ブラウザを閉じると破棄)
					$_SESSION["name"] = $username;
					header("Location: index.php");
					exit(1); //上で他のページへ飛んでいるので、ここで処理を終わらせておく
				} else {
					//認証失敗
					$errorMessage2 = "パスワードが間違っています";
				}

			}

	    }
	}
	//ログイン機構終わり

	//新規登録機構
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

				//現在のセッションIDを新しく生成したものと置き換える。セキュリティ上重要
				session_regenerate_id(true);

				//セッション変数=ページが遷移しても維持される変数(ブラウザを閉じると破棄)
				$_SESSION["name"] = $username;
				echo "Your new account has been successfully registered!".'<br>';
				echo "Redirecting to the top page in 5 seconds...";
				exit(1); //上で他のページへ飛んでいるので、ここで処理を終わらせておく
			}

	    } else if($_POST["password"] != $_POST["password2"]) {
	        $errorMessage = 'パスワードが一致しません';
	    }
	}
	//新規登録終わり

} catch (PDOException $e) { //$eに例外の情報が格納される
	exit('データベースに接続できませんでした。' . $e->getMessage()) ; //$e->getMessage()で格納されたエラーメッセージを表示
}
?>



<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <title>ログイン・新規登録 | PS1BBS</title>
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
		  <li><a href="about.php">ABOUT</a></li>
  		  <li class="active"><a href="login-signup.php">投稿する -> LOGIN or SIGNUP</a></li>
        </ul>
      </div>
      <!--/.nav-collapse -->
    </div>
  </div>

	<div class="float-container">

		<div class="login-container">
			<h2 class="h2h2">ログイン</h2>
			<form action="" method="post">
				<div class="post-form">
					 <h3>ユーザー名</h3>
					 <input class="text-input-2" type="text" name="username2" placeholder="ユーザー名を入力" value="<?php if (!empty($_POST["username2"])) {echo h($_POST["username2"]);} ?>" required>
				</div>
				<div class="post-form">
					 <h3>パスワード</h3>
					 <input class="text-input-2" type="password" name="password" value="" placeholder="パスワードを入力" required>
				</div>
				<br>
				<div class="errorMessage"><?php echo h($errorMessage2); ?></div>
				<br>
				<div class="submit"><input type="submit" id="login" name="login" value="ログイン"></div>
			</form>
		</div>

		<div class="signup-container">
			<h2 class="h2h2">新規登録</h2>
			パスワードはハッシュ化されていますが、あまり安全ではありません。重要な文字列は入力しないでください。
			<br>
			パスワードは8文字以上で、半角英字と半角数字をそれぞれ最低1つ含む必要があります。
			<br>
			メールアドレスは認証がないので、適当で大丈夫です。
			<form action="" method="post">
				<div class="post-form">
					 <h3>ユーザー名</h3>
					 <input class="text-input-2" type="text" name="username" placeholder="ユーザー名を入力" value="<?php if (!empty($_POST["username"])) {echo h($_POST["username"]);} ?>" required>
				</div>
				<div class="post-form">
					 <h3>メールアドレス</h3>
					 <input class="text-input-2" type="email" name="email" placeholder="example@gmail.com" value="<?php if (!empty($_POST["email"])) {echo h($_POST["email"]);} ?>" required>
				</div>
				<div class="post-form">
					 <h3>パスワード</h3>
					 <input class="text-input-2" type="password" name="password" value="" placeholder="パスワードを入力" required>
				</div>
				<div class="post-form">
					 <h3>確認パスワード</h3>
					 <input class="text-input-2" type="password" name="password2" value="" placeholder="再度パスワードを入力" required>
				</div>
				<br>
				<div class="errorMessage"><?php echo h($errorMessage); ?></div>
				<br>
				<div class="submit"><input type="submit" id="login" name="signup" value="新規登録"></div>
			</form>
		</div>

	</div>



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

  <!-- DG -->



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

  <!-- JavaScript Libraries -->
  <script src="lib/jquery/jquery.min.js"></script>
  <script src="lib/bootstrap/js/bootstrap.min.js"></script>
  <script src="lib/php-mail-form/validate.js"></script>
  <script src="lib/chart/chart.js"></script>

  <!-- Template Main Javascript File -->
  <script src="js/main.js"></script>

</body>
</html>