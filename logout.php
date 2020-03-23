<?php
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「ログアウトページ');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

debug('ログアウトします');

//セッションを削除(ログアウト)
session_destroy();

debug('トップページに遷移します');

header("Location:index.php");


?>