<?php
session_start();
setcookie("jeff", "", time() + 604800); //クッキーの有効期限を7日間に設定
$username = $_SESSION["name"];
?>

<!doctype html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <title>ログアウト | PS1BBS</title>
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

  <link href="css/background-image.css" rel="stylesheet">

  <!-- =======================================================
	Template Name: Spot
	Template URL: https://templatemag.com/spot-bootstrap-freelance-template/
	Author: TemplateMag.com
	License: https://templatemag.com/license/
  ======================================================= -->
</head>
<body id="background-images">

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

    <div class="about">
        <h2>このサイトについて</h2>
        このサイトは初代 PlayStation のゲームのみを扱った総合掲示板です。<br>
        多種多様な投稿を見れるほか、ログインすることで自らも投稿したりコメントしたりできます。<br>
        みなさんの懐かしい記憶をどんどん載せていってください。<br>
        <h3>一時的な措置</h3>
        現在一時的に初代 PlayStation に限らないあらゆる投稿を認めています。<br>
        ぜひ臆せず色々投稿してみてください！
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
