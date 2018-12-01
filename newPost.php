<?php
include 'database.php'; //データベース情報

if (!isset($_SESSION["name"])) {
	echo "投稿するにはログインする必要があります".'<br>';
	echo "5 秒後ログインページへリダイレクトします";
	header("refresh:5;url=index.php");
	exit(1);
}

$username = $_SESSION["name"];

//時刻をマイクロ秒単位で取得する関数
function getUnixTimeMillSecond(){
    $arrTime = explode('.', microtime(true)); //microtimeを.で分割
    return date('Y-m-d H:i:s', $arrTime[0]) . '.' .$arrTime[1]; //日時＋ミリ秒
}

try {

	$pdo = new PDO($dsn, $db[user], $db[pass]);

	try {

		//アップロードファイルの例外処理
		switch ($_FILES['upfile']['error']) {
		    case UPLOAD_ERR_OK: // OK
		    case UPLOAD_ERR_NO_FILE: // ファイル未選択
		        break;
			case UPLOAD_ERR_INI_SIZE:  // php.ini定義の最大サイズ超過
		    case UPLOAD_ERR_FORM_SIZE: // フォーム定義の最大サイズ超過 (設定した場合のみ)
		        throw new RuntimeException('ファイルサイズが大きすぎます');
		    default:
		        throw new RuntimeException('その他のエラーが発生しました');
		}

		if (!empty($_POST["submit"])) { //送信ボタンが押されたら

			if (empty($_POST["title"])) {
				throw new RuntimeException('タイトルを入力してください');

			} elseif ($_POST["label"] === "none") {
				throw new RuntimeException('タグを選択してください');

			//YouTube動画とファイル両方に入力がある場合
			} elseif (!empty($_POST["youtube"]) && $_FILES['upfile']['error'] !== 4) {
				throw new RuntimeException('ファイルとYouTube動画を同時に送ることはできません<br>どちらか片方にしてください');

			//YouTube動画のURLが正しくない場合
			} elseif (strpos($_POST["youtube"], "https://www.youtube.com/watch?v=") === false) {
				throw new RuntimeException('入力されたURLはYouTubeの動画ではありません');

			} elseif ($_FILES['upfile']['error'] !== 4) { //UPLOAD_ERR_NO_FILE(4)

				if (!isset($_FILES['upfile']['error']) || !is_int($_FILES['upfile']['error'])) {
					throw new RuntimeException('パラメータが不正です');
				} else {

					$rawData = file_get_contents($_FILES["upfile"]["tmp_name"]); //バイナリデータを取得
					$date = getdate(); //時刻を取得
					$mime = $_FILES["upfile"]["type"] ; //MIMEタイプを判定

					// 拡張子を決定
					switch ($mime) {
						case "image/jpeg":
							$extension = ".jpeg";
							break;
						case "image/png":
							$extension = ".png";
							break;
						case "image/gif":
							$extension = ".gif";
							break;
						case "video/mp4":
							$extension = ".mp4";
							break;
						default:
							throw new RuntimeException("非対応ファイルです");
					}

					// バイナリデータと時刻を合わせてハッシュ化
					$hashname = hash("sha256", $rawData.$date["year"].$date["mon"].$date["mday"].$date["hours"].$date["minutes"].$date["seconds"]);
					$filename = $hashname.$extension ;

					//ファイルを特定のフォルダへ移動
					if (move_uploaded_file($_FILES["upfile"]["tmp_name"], "files/" . $filename)) {

					} else {
				    	throw new RuntimeException("ファイルをアップロードできませんでした");
					}

				} //END ファイルアップロード機構

			}

            //時刻を取得(マイクロ秒まで)
            $datetime = getUnixTimeMillSecond();

			//データベースへの書き込み
			$sql = $pdo -> prepare("INSERT INTO post(user, title, text, filename, thumbnail, datetime, label, rating) VALUES (:user, :title, :text, :filename, :thumbnail, :datetime, :label, :rating)") ;
			$sql -> bindValue(':user', $username, PDO::PARAM_STR) ;
			$sql -> bindValue(':title', $_POST["title"], PDO::PARAM_STR) ;
			$sql -> bindValue(':text', $_POST["text"], PDO::PARAM_STR) ;

			//ファイルが送信された場合
			if ($_FILES['upfile']['error'] !== 4 && empty($_POST["youtube"])) {
				$sql -> bindValue(':filename', $filename, PDO::PARAM_STR) ;

			//YouTube動画のURLが送信された場合
			} elseif ($_FILES['upfile']['error'] === 4 && !empty($_POST["youtube"])) {

				//URLに含まれる時間指定を消したい場合
				//$youtube = explode("=", $_POST["youtube"]);
				//$youtube = $youtube[0] . $youtube[1];

				$sql -> bindValue(':filename', $_POST["youtube"], PDO::PARAM_STR) ;
			}

			$sql -> bindValue(':thumbnail', "disabled", PDO::PARAM_STR) ; //現在未実装
			$sql -> bindValue(':datetime', $datetime, PDO::PARAM_STR) ;
			$sql -> bindValue(':label', $_POST["label"], PDO::PARAM_STR) ;
			$sql -> bindValue(':rating', "0", PDO::PARAM_INT) ;
			$sql -> execute() ;

            header("Location: index.php");
            exit(1);

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
  <title>新規投稿 | PS1BBS</title>
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
			<li class="active"><a href="newPost.php">投稿する</a></li>
			<li><a href="about.php">ABOUT</a></li>
			<li><a href="profile.php">PROFILE</a></li>
			<li><a href="logout.php">LOGOUT</a></li>
        </ul>
      </div>
    </div>
  </div>

    <div class="main">
    	<form action="" method="post" enctype="multipart/form-data">
    		<div class="post-form">
    		     <h3>タイトル*</h3>
                 <input class="text-input" type="text" name="title" value="<?php if (!empty($_POST["title"])) {echo h($_POST["title"]);} ?>" required>
    		</div>
            <div class="post-form">
    		     <h3>コメント</h3>
    			 <textarea name="text" cols="40" rows="4"><?php if (!empty($_POST["text"])) { echo h($_POST["text"]);} ?></textarea>
    		</div>
    		<div class="post-form">
        		<h3>ファイル</h3>
				<!--- ファイルサイズ制限、50MB --->
				<input type="hidden" name="MAX_FILE_SIZE" value="52428800">
                <input class="text-input" type="file" name="upfile">
        		<span id="condition">※条件: 50MB以内, JPEG, PNG, GIF, MP4のいずれか</span>
            </div>
			<div class="post-form">
				 <h3>YouTube動画</h3>
				 <input class="text-input" type="url" name="youtube" placeholder="https://www.youtube.com/watch?v=(固有ID)" value="<?php if (!empty($_POST["youtube"])) {echo h($_POST["youtube"]);} ?>">
				 <br>
				 <span id="condition">※ファイルと一緒には送信できません</span>
			</div>
            <div class="post-form">
        		<h3>タグ*</h3>
    			<select name="label">
    				<option value="none" selected></option>
					<option value="ニュース">ニュース</option>
					<option value="おもしろ">おもしろ</option>
					<option value="プレイ画像">プレイ画像</option>
					<option value="プレイ動画">プレイ動画</option>
					<option value="スクリーンショット">スクリーンショット</option>
    				<option value="釣りタイトル">釣りタイトル</option>
					<option value="質問">質問</option>
    				<option value="議論">議論</option>
					<option value="その他">その他</option>
    			</select>
            </div>

            <div class="errorMessage"><?php echo $errorMessage; ?></div>
            <div class="submit"><input type="submit" name="submit" value="送信"></div>
    	</form>
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
