<?php
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「');
debug('「パスワード再発行認証キー入力ページ');
debug('「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//SESSIONに認証キーがあるか確認、なければリダイレクト
if(empty($_SESSION['auth_key'])) {
    header("Location:passRemindSend.php");
}
//////////////////////////////////////////////
//画面処理
//////////////////////////////////////////////
//post
if(!empty($_POST)) {
    debug('POST送信があります。');
    debug('POST内容:'.print_r($_POST,true));
    
    $auth_key = $_POST['token'];

    //未入力チェック
    validRequired($auth_key,'auth_key');

    if(empty($err_msg)){
        debug('未入力チェックOK。');

        //固定長チェック
        validLength($auth_key,'auth_key');
        //半角チェック
        validHalf($auth_key,'auht_key');

        if(empty($err_msg)){
             debug('バリデーションOK');
              
             if($auth_key !== $_SESSION['auth_key']) {
                 $err_msg['common'] = MSG13;
             }
             if(time() > $_SESSION['auth_key_limit']){
                 $err_msg['common'] = MSG14;
             }
             if(empty($err_msg)){
                 debug('認証OK。');

                 $pass = makeRandKey(); //パスワード生成

                 //例外処理
                 try {
                     $dbh = dbConnect();
                     $sql = 'UPDATE users SET password = :pass WHERE email = :email AND delete_flg = 0 ';
                     $data = array(':email' => $_SESSION['auth_email'],':pass' => password_hash($pass,PASSWORD_DEFAULT));
                     $stmt = queryPost($dbh,$sql,$data);
                     
                     //クエリ成功
                     if($stmt){
                         debug('クエリ成功');

                         //メールを送信
                         $from = 'lowrise3641@gmail.com';
                         $to = $_SESSION['auth_email'];
                         $subject = 'パスワード再発行完了';
                         $comment = <<<EOT
本メールアドレス宛にパスワードの再発行を致しました。
下記のURLにて再発行パスワードをご入力頂き、ログインください。

ログインページ: http://localhost:8888/web_output01/passRemindSend.php
再発行パスワード: {$pass}
※ログイン後、パスワードの変更をお願いいたします。

/////////////////////////////////////////////
まんが めんたカスタマーセンター
URL: http://mangamenta.com/
/////////////////////////////////////////////
EOT;
                        sendMail($from,$to,$subject,$comment);

                        //セッション削除
                        session_unset();
                        $_SESSION['msg_success'] = SUC03;
                        debug('セッション変数の中身:'.print_r($_SESSION,true));

                        header("Location:login.php");
                     }else{
                         debug('クエリに失敗しました');
                         $err_msg['common'] = MSG07;
                     }
                 }catch (Exeption $e) {
                     error_log('エラー発生:'.$e->getMessage());
                     $err_msg['common'] = MSG07;
                 }
             }
        }
    }
}

?>



<?php
require('head.php');
?>
<body class="page-home page1colum">
    <!--ヘッダー-->
    <?php
    require('header.php');
    ?>
    <!--メインコンテンツ-->
    <div id="contents" class="site-width">
    <section class="main">
    <h2 class="page-title">認証キーの入力</h2>
          <div class="signup-form-container">
            <form action="" method="POST" class="signup-form">
                <p>ご指定のメールアドレスにお送りした認証キーをご入力ください。</p>
              <div class="area-msg">
                <?php
                  if(!empty($err_msg['common'])) echo $err_msg['common'];
                ?>
              </div>
              <label class="<?php if(!empty($err_msg['token'])) echo 'err' ;?>">
                認証キー
                <input type="text" name="token" value="<?php echo getFormdata('token'); ?>">
              </label>
              <div class="area-msg">
                <?php
                  if(!empty($err_msg['token'])) echo $err_msg['token'];
                ?>
              </div>
              <div class="btn-container">
                <input type="submit" class="btn hanten" value="再発行">
              </div>
              
            </form>
          </div>
      </section>
    </div>

     <!-- footer -->

         <!-- footer -->
         <?php
        require('footer.php');
        ?>