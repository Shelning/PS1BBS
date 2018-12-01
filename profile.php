<?php
include 'database.php'; //データベース情報

//ログインしているかどうか
if (!isset($_SESSION["name"])) {
	echo "3 秒後ログインページへリダイレクトします";
	header("refresh:3;url=login-signup.php");
	exit(1);
}

//セッションからユーザー名を取る
$username = $_SESSION["name"];

try {
	$pdo = new PDO($dsn, $db[user], $db[pass]);
	$sql = "SELECT * FROM users WHERE name = '$username'";
	$row = $pdo->query($sql)->fetch(PDO::FETCH_ASSOC);

	$email = $row['email'];
	$password = $row['password'];

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

  <div class="top-main">
	  <h2 class="h2h2">ログインしているアカウント</h2>
	  <div id="top-main">
		  <table>
		  	<tbody>
		  		<tr>
		  			<th><h4>ユーザー名</h4></th>
					<td><?php echo h($username); ?></td>
		  		</tr>
		  	</tbody>
			<tbody>
				<tr>
					<th><h4>メールアドレス</h4></th>
					<td><?php echo h($email); ?></td>
				</tr>
			</tbody>
			<tbody>
		  		<tr>
		  			<th><h4>パスワード</h4></th>
					<td>******** (セキュリティのため表示されません)</td>
		  		</tr>
		  	</tbody>
		  </table>
	  </div>

	  <h4 class="top-main_a"><a href="accountEdit.php">ユーザー名・メールアドレス・パスワードを変更する</a></h4>
	  <h2 class="h2h2">これまでの投稿</h2>
	  <?php
		  $sql = "SELECT * FROM post WHERE user = '$username' ORDER BY id DESC" ;
		  $results = $pdo -> query($sql) ;
		  foreach ($results as $row) {

			  //変数に代入
			  $id = $row['id'];
			  $title = urlencode($row['title']);

			  //その投稿に対するコメント数を取得
			  $stmt = "SELECT COUNT(*) FROM comment WHERE pageID = '$id'" ;
			  $count = (int)$pdo->query($stmt)->fetchColumn();

			  //時刻のミリ秒を削除+形式を整える
			  $datetime01 = explode("-", $row['datetime']); //年[0], 月[1], 日以下[2]に分割
			  $datetime02 = explode(":", $datetime01[2]); //日+時[0], 分[1], 秒以下[2]に分割
			  $datetimeMinute = $datetime01[0] . "/" . $datetime01[1] . "/" . $datetime02[0] . ":" . $datetime02[1];

			  echo '<a href="/PS1BBS/template.php?id=' . $id . '&title=' . $title . '"><table><thead><tr>' ;
			  echo '<th><i class="fas fa-thumbs-up"></i> <i class="fas fa-thumbs-down"></i></th>';
			  echo '<td class="tag"><i class="fas fa-tag"></i> ' .  $row['label'] . '</td>';
			  echo '<td class="username"><i class="fas fa-user"></i> ' . h($row['user']) . '</td>';
			  echo '<td><i class="fas fa-clock"></i> ' . $datetimeMinute . '</td>';
			  echo '</tr></thead><tbody><tr>' ;
			  echo '<th>' . $row['rating'] . ' pt</th>'; //今の所
			  echo '<td colspan="2" class="title"> ' . h($row['title']) . '</td>';
			  echo '<td><i class="fas fa-comments"></i> ' . $count . '</td>';
			  echo '</tr></tbody></table></a>';
			  echo '<div class="post-delete"><a href="finalAnswer.php?id=' . $id . '">上の投稿を削除する</a></div><br>';
		  } ;
	  ?>
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
