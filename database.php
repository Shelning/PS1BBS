<?php
session_start();
setcookie("jeff", "", time() + 604800); //クッキーの有効期限を7日間に設定

$dsn = "" ;
$db[user] = "" ;
$db[pass] = "" ;

/* HTML特殊文字をエスケープする関数 */
function h($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}
?>
