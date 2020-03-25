<?php
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「パスワード再発行ページ「');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//////////////////////////////////
//画面処理
//////////////////////////////////

if(!empty($_POST)) {
    debug('POST情報があります。');
    debug('$_POST内容：'.print_r($_POST,true));

    $email = $_POST['email'];

    //未入力チェック
    validRequired($email,'email');

    if(empty($err_msg)){
        debug('未入力チェックOK。');

        //email形式チェック
        validEmail($email,'email');
        validMaxLen($email,'email');
        
        if(empty($err_msg)){
            debug('バリデーションOK。');

            //例外処理
            try {
                $dbh = dbConnect();
                $sql = 'SELECT count(*) FROM users WHERE email = :email AND delete_flg = 0';
                $data = array('email' => $email);
                $stmt = queryPost($dbh,$sql,$data);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);

                //EmailがDBに登録されている場合
                if($stmt && array_shift($result)) {
                    debug('クエリ成功、DB登録あり');
                    $_SESSION['msg_success'] = SUC03;

                    $auth_key = makeRandKey();

                    //メールを送信
                    $from = '';
                    $to = $email;
                    $subject = '[パスワード再発行認証] | まんが めんた';
                    $comment = <<<EOT
本メールアドレス宛にパスワード再発行のご依頼がありました。
下記のURLにて認証キーをご入力頂くとパスワードが再発行されます。

パスワード再発行認証キー入力ページ: http://localhost:8888/web_output01/passRemindRecieve.php
認証キー:{$auth_key}
※認証キーの有効期限は30分となります

認証キーを再発行されたい場合は下記ページより再度再発行をお願いいたします。
http://localhost:8888/web_output01/passRemindSend.php

/////////////////////////////////////////
マンガ部！！カスタマーセンター
URL: http://mangamenta.com/
/////////////////////////////////////////
EOT;
                    sendMail($from,$to,$subject,$comment);

                    //認証に必要な情報をセッションへ格納
                    $_SESSION['auth_key'] = $auth_key;
                    $_SESSION['auth_email'] = $email;
                    $_SESSION['auth_key_limit'] = time() + (60*30);
                    debug('セッション変数の中身:'.print_r($_SESSION,true));

                    header("Location:passRemindRecieve.php");

                }else{
                    debug('クエリに失敗したかDBに登録のないEmailが入力されました。');
                    $err_msg['common'] = MSG07;
                }
            }catch (Exeption $e) {
                error_log('エラー発生:'.$e->getMessage());
                $err_msg['common'] = MSG07;
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
    <h2 class="page-title">パスワード再発行の申込み</h2>
          <div class="signup-form-container">
            <form action="" method="POST" class="signup-form">
                <p>ご指定のメールアドレス宛にパスワード再発行用のURLと認証キーをお送りします。</p>
              <div class="area-msg">
                <?php
                  if(!empty($err_msg['common'])) echo $err_msg['common'];
                ?>
              </div>
              <label class="<?php if(!empty($err_msg['email'])) echo 'err' ;?>">
                Email
                <input type="text" name="email" value="<?php echo getFormdata('email'); ?>">
              </label>
              <div class="area-msg">
                <?php
                  if(!empty($err_msg['email'])) echo $err_msg['email'];
                ?>
              </div>
              <div class="btn-container">
                <input type="submit" class="btn hanten" value="送信">
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
