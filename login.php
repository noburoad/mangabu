<?php
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「ログインページ');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//ログイン認証
require('auth.php');

/////////////////////////////
//ログイン画面処理
/////////////////////////////

//POST送信されていた場合
if(!empty($_POST)){
  debug('POST送信があります');

  //変数にユーザー情報を代入
  $email = $_POST['email'];
  $pass = $_POST['pass'];
  $pass_save = (!empty($_POST['pass_save'])) ? true : false; //ショートハンド

  //バリデーション
  //emailのチェック
  validEmail($email,'email');
  validMaxLen($email,'email');
  //パスワードのチェック
  validHalf($pass,'pass');
  validMaxLen($pass,'pass');
  validMinLen($pass,'pass');

  //未入力チェック
  validRequired($email,'email');
  validRequired($pass,'pass');

  if(empty($err_msg)){
    debug('バリデーションOKです');

    //例外処理
    try {
      $dbh = dbConnect();
      $sql = 'SELECT password,id FROM users WHERE email = :email AND delete_flg = 0';
      $data = array('email' => $email);

      $stmt = queryPost($dbh,$sql,$data);
      $result = $stmt->fetch(PDO::FETCH_ASSOC);

      debug('クエリ結果の中身'.print_r($result,true));

      //パスワード照合
      if(!empty($result) && password_verify($pass,array_shift($result))){
        debug('パスワードがマッチしました。');

        //ログイン有効期限
        $sesLimit = 60*60;
        //最終ログイン日時を現在に
        $_SESSION['login_date'] = time();
        
        //ログイン保持にチェックがある場合
        if($pass_save){
          debug('ログイン保持にチェックがあります。');
          //ログイン有効期限を30日にセット
          $_SESSION['login_limit'] = $sesLimit *24 *30;
        }else{
          debug('ログイン保持にチェックはありません。');
          $_SESSION['login_limit'] = $sesLimit;
        }
        //ユーザーIDを格納
        $_SESSION['user_id'] = $result['id'];

        $_SESSION['msg_success'] = SUC08;
        debug('セッション変数の中身'.print_r($_SESSION,true));
        debug('マイページへ遷移します。');
        header("Location:mypage.php");
      }else{
        debug('パスワードがアンマッチです。');
        $err_msg['common'] = MSG09 ;
      }
      }catch(Exeption $e){
        error_log('エラー発生'.$e->getMessage());
        $err_msg['common'] = MSG07;
      
    }
  }
}
debug('画面処理終了<<<<<<<<<<<<<<<<<<<<<<');
?>
<?php
require('head.php');
?>
<body class="page-home page1colum">
    <!--ヘッダー-->
    <?php
    require('header.php');
    ?>
    <p id="js-show-msg" style="display:none;" class="msg-slide">
      <?php echo getSessionFlash('msg_success'); ?>
    </p>
    <!--メインコンテンツ-->
    <div id="contents" class="site-width">
    <section class="main">
    <h2 class="page-title">ログイン</h2>
          <div class="signup-form-container">
            <form action="login.php" method="POST" class="signup-form">
              <div class="area-msg">
                <?php
                  if(!empty($err_msg['common'])) echo $err_msg['common'];
                ?>
              </div>
              <label class="<?php if(!empty($err_msg['email'])) echo 'err' ;?>">
                Email
                <input type="text" name="email" value="<?php if(!empty($_POST['email'])) echo $_POST['email']; ?>">
              </label>
              <div class="area-msg">
                <?php
                  if(!empty($err_msg['email'])) echo $err_msg['email'];
                ?>
              </div>
              <label class="<?php if(!empty($err_msg['pass'])) echo 'err' ; ?>">
                パスワード
                <input type="password" name="pass" value="<?php if(!empty($_POST['pass'])) echo $_POST['pass']; ?>">
              </label>
              <div class="area-msg">
                <?php
                  if(!empty($err_msg['pass'])) echo $err_msg['pass'];
                ?>
              </div>
              <label>
                <input type="checkbox" name="pass_save">次回ログインを省略する
              </label>
              <div class="btn-container">
                <input type="submit" class="btn hanten" value="ログイン">
              </div>
              パスワードを忘れた方は<a href="passRemindSend.php">コチラ</a>
            </form>
          </div>
            </section>
        
        
      </section>

    </div>

    </div>
     <!-- footer -->

         <!-- footer -->
         <?php
        require('footer.php');
        ?>


