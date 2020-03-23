<?php

//=======================
//ログイン認証・自動ログアウト
//========================
//ログインしている場合
if(!empty($_SESSION['login_date'])){
    debug('ログイン済みユーザーです');

    //現在日時が、最終ログイン日時＋有効期限、を超えていた場合
    if(($_SESSION['login_date'] + $_SESSION['login_limit']) < time()){
        debug('ログイン有効期限オーバーです。');

        //セッションを削除（ログアウトする）
        session_destroy();
        //ログインページへ
        header("Location:login.php");
    }else{
        debug('ログイン有効期限内です');
        //最終ログイン日時を更新
        $_SESSION['login_date'] = time();
        //現在実行中のスクリプトファイル名がlogin.phpの場合
        //$_SERVER['PHP_SELF']はドメインからのパスを返すため
        //さらにbasename関数を使うことでファイル名だけ取り出せる
        if(basename($_SERVER['PHP_SELF']) === 'login.php'){
            debug('マイページへ遷移します');
            //マイページへ
            header("Location:mypage.php");
        }
    }

}else{
    debug('未ログインユーザーです。');
    if(basename($_SERVER['PHP_SELF']) !== 'login.php'){
        header("Location:login.php");
    }
}


?>