<?php
chdir("/home/tt-576.99sv-coco.com/public_html/PS1BBS"); //ディレクトリ移動
include 'database.php'; //データベース情報
// chdir("/home/tt-576.99sv-coco.com/public_html/posts"); //元のディレクトリ

$username = $_SESSION["name"];

//このページのIDを置換してもらう
$pageID = "strPageID";

try {

    $pdo = new PDO($dsn, $db[user], $db[pass]);

    try {

        if (!empty($_POST["submit"])) { //送信ボタンが押されたら

			if (empty($_POST["text"])) {
				throw new RuntimeException('コメントを入力してください');
			} elseif (empty($_POST["rating"])) {
				throw new RuntimeException('評価をしてください');
			}

            //コメントをデータベースに保存
            $sql = $pdo -> prepare("INSERT INTO comment(pageID, user, text, datetime) VALUES (:pageID, :user, :text, :datetime)") ;
            $datetime = new DateTime() ;
            $datetime = $datetime->format('Y-m-d H:i:s');
            $sql -> bindValue(':pageID', $pageID, PDO::PARAM_INT) ; //どの投稿(ID)に対するコメントなのか
            $sql -> bindValue(':user', $username, PDO::PARAM_STR) ;
            $sql -> bindValue(':text', $_POST["text"], PDO::PARAM_STR) ;
            $sql -> bindValue(':datetime', $datetime, PDO::PARAM_STR) ;
            $sql -> execute() ;

            //投稿に対する評価値を更新
            $rating = $_POST["rating"];
            $sql = "update post set rating=rating+'$rating' where id='$pageID'";
            $result = $pdo->query($sql);

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
  <title><%pageTitle> | PS1BBS</title>
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <meta content="" name="keywords">
  <meta content="" name="description">

  <!-- Favicons -->
  <link href="../img/favicon.ico" rel="icon">
  <link href="../img/favicon.ico" rel="apple-touch-icon">

  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css?family=Lato:300,400,700,900|Raleway:400,300,700,900" rel="stylesheet">

  <!-- Bootstrap CSS File -->
  <link href="../lib/bootstrap/css/bootstrap.min.css" rel="stylesheet">

  <!-- Libraries CSS Files -->
  <!---
  <link href="lib/font-awesome/css/font-awesome.min.css" rel="stylesheet">
  --->

  <link href="https://use.fontawesome.com/releases/v5.0.6/css/all.css" rel="stylesheet">

  <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script><script type="text/javascript" src="../js/footerFixed.js"></script>

  <!-- Main Stylesheet File -->
  <link href="../css/style.css" rel="stylesheet">

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
          <a class="navbar-brand" href="../index.php">PlayStationの記憶</a>
        </div>
        <div class="navbar-collapse collapse">
            <ul class="nav navbar-nav navbar-right">
              <?php
                if (isset($_SESSION["name"])) { ?>
                    <li class="active"><a href="../newPost.php">投稿する</a></li>
                    <li><a href="../about.php">ABOUT</a></li>
                    <li><a href="../profile.php">PROFILE</a></li>
                    <li><a href="../logout.php">LOGOUT</a></li>
              <?php
                } else { ?>
                    <li><a href="../about.php">ABOUT</a></li>
            		  <li class="active"><a href="../login-signup.php">投稿する -> LOGIN or SIGNUP</a></li>
              <?php } ?>
            </ul>
        </div>
        <!--/.nav-collapse -->
      </div>
    </div>


    <div class="banner">
        <?php include("banner.html"); //PHPの中では PS1BBS がカレント ?>
    </div>


    <div class="main">

        <h2><%pageTitle></h2>

        <div id="text">
            <%pageText>
        </div>

        <div class="uploaded-file">
            <?php //幅・高さを整える必要がある
                $format = 'strFormat';
                $filename = "strFilename";
                //sprintf(フォーマットしたいもの, 1つ目の%sなどに入れるもの, 2つ目, etc.)
                echo sprintf($format, $filename, $filename);
            ?>
        </div>

        <br>

        <div class="comment-table">
            <table>
                <thead>
                    <tr>
                        <th><h4>コメント一覧</h4></th>
                        <th><h4>コメントする</h4></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <div class="posted-comment">

                                <?php
                                $sql = "SELECT COUNT(*) FROM comment WHERE pageID = '$pageID'" ;
                                $count = (int)$pdo->query($sql)->fetchColumn(); //コメント数をカウント

                                if ($count === 0) {
                                    echo '<div class="no-comment">' . "まだコメントがありません" . '</div>';
                                } else {
                                    $sql = "SELECT * FROM comment WHERE pageID = '$pageID' ORDER BY id DESC" ;
                                    $results = $pdo -> query($sql) ;
                                    foreach ($results as $row) {
                                        $comment = h($row['text']);
                                        $comment = nl2br($comment);
                                        // echo $row['id'].',' ;
                                        // echo $row['pageID'].',';
                                        echo '<table><tbody><tr><td>' . h($row['user']) . '</td>';
                                        echo '<td>' . $row['datetime'] . '</td></tr></tbody>';
                                        echo '<tbody><tr><td colspan="2" class="posted-comment-comment">' . $comment . '</td></tr></tbody></table>';
                                    }
                                }
                                ?>

                                <!--
                                <table>
                                    <tbody>
                                        <tr>
                                            <td>しょーま</td>
                                            <td>2018-11-18 23:15:20</td>
                                        </tr>
                                    </tbody>
                                    <tbody>
                                        <tr>
                                            <td colspan="2" class="posted-comment-comment">heyhey<br>heheheh</td>
                                        </tr>
                                    </tbody>
                                </table>
                            -->

                            </div>
                        </td>
                        <td>
                            <?php
                              if (isset($_SESSION["name"])) { ?>
                            	<form action="" method="post" enctype="multipart/form-data">
                                    <div class="post-form">
                            		     <h3>コメント*</h3>
                            			 <textarea name="text" cols="40" rows="4" required></textarea>
                            		</div>
                                    <div class="post-form">
                            		     <h3>評価*</h3>
                            			 <input class="radio-button" type="radio" name="rating" value="1" required> 高評価
                                         <br>
                            			 <input class="radio-button" type="radio" name="rating" value="-1"> 低評価
                            		</div>
                                    <div class="errorMessage"><font color="red"><?php echo h($errorMessage); ?></font></div>
                            		<div class="submit"><input type="submit" name="submit" value="送信"></div>
                            	</form>
                            <?php
                              } else { ?>
                                  <div class="no-comment">
                                      <div class="submit">コメントするには<a href="../login.php">ログイン</a>か<a href="../signup.php">新規登録</a></div>
                                  </div>
                            <?php } ?>
                        </td>
                    </tr>
                </tbody>
            </table>
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

    <!-- JavaScript Libraries -->
    <script src="lib/jquery/jquery.min.js"></script>
    <script src="lib/bootstrap/js/bootstrap.min.js"></script>
    <script src="lib/php-mail-form/validate.js"></script>
    <script src="lib/chart/chart.js"></script>

    <!-- Template Main Javascript File -->
    <script src="js/main.js"></script>

</body>
</html>
