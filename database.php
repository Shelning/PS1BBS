<?php
session_start();
setcookie("jeff", "", time() + 604800); //クッキーの有効期限を7日間に設定

$dsn = "mysql:dbname=tt_576_99sv_coco_com;host=localhost" ;
$db[user] = "tt-576.99sv-coco" ;
$db[pass] = "tN7ZhXGc" ;

/* HTML特殊文字をエスケープする関数 */
function h($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}
?>
