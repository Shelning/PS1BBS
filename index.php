<?php
include 'database.php'; //データベース情報

//時刻をマイクロ秒単位で取得する関数
function getUnixTimeMillSecond(){
    $arrTime = explode('.', microtime(true)); //microtimeを.で分割
    return date('Y-m-d H:i:s', $arrTime[0]) . '.' .$arrTime[1]; //日時＋ミリ秒
}

$username = $_SESSION["name"];

try {

	$pdo = new PDO($dsn, $db[user], $db[pass]);

} catch (PDOException $e) { //$eに例外の情報が格納される
	exit('データベースに接続できませんでした。' . $e->getMessage()) ; //$e->getMessage()で格納されたエラーメッセージを表示
}
?>



<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <title>PlayStationの記憶 | PS1BBS</title>
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
          <?php
            if (isset($_SESSION["name"])) { ?>
                <li class="active"><a href="newPost.php">投稿する</a></li>
                <li><a href="about.php">ABOUT</a></li>
                <li><a href="profile.php">PROFILE</a></li>
                <li><a href="logout.php">LOGOUT</a></li>
          <?php
            } else { ?>
                <li><a href="about.php">ABOUT</a></li>
        		  <li class="active"><a href="login-signup.php">投稿する -> LOGIN or SIGNUP</a></li>
          <?php } ?>
        </ul>
      </div>
      <!--/.nav-collapse -->
    </div>
  </div>

  <div class="banner">
      <?php include("banner.html"); ?>
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

        <?php
            $sql = 'SELECT * FROM post ORDER BY id DESC' ;
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
            } ;
        ?>

        <!-- 消しちゃった瀬川さんの投稿 -->
        <a href="/PS1BBS/posts/3f22dad9ae7626ed03c6fa87d0segawa.php">
          <table>
              <thead>
                <tr>
                  <th><i class="fas fa-thumbs-up"></i> <i class="fas fa-thumbs-down"></i></th>
                  <td class="tag"><i class="fas fa-tag"></i> 議論</td>
                  <td class="username"><i class="fas fa-user"></i> segawa</td>
                  <td><i class="fas fa-clock"></i> 2018/11/18 以下不明</td>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <th> 1pt</th>
                  <td colspan="2" class="title">野良連合がRogueを破り世界TOP4入り</td>
                  <td><i class="fas fa-comments"></i> 1</td>
                </tr>
              </tbody>
            </table>
        </a>

      <!-- /.row -->
    <!--- </div> --->
    <!-- container -->
  </div>
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
