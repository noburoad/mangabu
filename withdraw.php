<?php

require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「退会ページ「「');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//ログイン認証
require('auth.php');

//=================================
//画面処理
//=================================
//POST送信されていた場合
if(!empty($_POST)){
    debug('POST送信あります。');
    //文字数チェック
    $withdraw = $_POST['withdraw_comment'];
    validMaxLen($withdraw,'withdraw_comment');

    if(empty($err_msg)){
        debug('バリデーションOKです');
    //例外処理
    try {
        //DB接続
        $dbh = dbConnect();
        //SQL
        $sql1 = 'UPDATE users SET delete_flg = 1 , withdraw_comment = :withdraw_comment WHERE id = :u_id AND delete_flg = 0';
        $sql2 = 'UPDATE mantor SET delete_flg = 1 WHERE id = :u_id AND delete_flg = 0';
        $sql3 = 'UPDATE follow SET delete_flg = 1 WHERE id = :u_id AND delete_flg = 0';
        //データ流し込み
        $data = array(':withdraw_comment' => $withdraw , ':u_id' => $_SESSION['user_id']);
        //クエリ実行
        $stmt1 = queryPost($dbh,$sql1,$data);
        $stmt2 = queryPost($dbh,$sql2,$data);
        $stmt3 = queryPost($dbh,$sql3,$data);
    
    //クエリ成功の場合(usersテーブルのみ成功してれば今回はよし)
    if($stmt1) {
        //セッション削除
        session_destroy();
        debug('セッション変数の中身:'.print_r($_SESSION,true));
        debug('トップページへ遷移します。');
        header("Location:index.php");
    }else{
        debug('クエリが失敗しました。');
        $err_msg['common'] = MSG07;
    }
}catch (Exeption $e) {
    error_log('エラー発生:'.$e->getMessage());
    $err_msg['common'] = MSG07;
    }
}
}
debug('画面表示処理終了 <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');
?>



<?php
require('head.php');
?>
<body class="page-home page2colum">
    <!--ヘッダー-->
    <?php
    require('header.php');
    ?>
    <!--メインコンテンツ-->
    <div id="contents" class="site-width">
        <h1 class="page-title">退会する</h1>
    <section id="main">
        <section class="withdraw">
                <form class="valid" action="" method="post">      
                
                    <label>
                        <div class="err_msg"><?php if(!empty($err_msg['common'])) echo $err_msg['common']; ?></div>
                        <textarea name="withdraw_comment" id="" cols="50" rows="10" placeholder="退会理由"></textarea>
                    </label>
                    <input id="submit-btn" type="submit" value="退会" name="withdraw">
                </form>
           
        </section>
    </section>
 
     <!--サイドバー-->
     <?php
        require('sidebar.php');
    ?>


    </div>
         <!-- footer -->
         <footer>
      Copyright <a href="">さぶかる　めんた！！</a>. All Rights Reserved.
    </footer>

</body>