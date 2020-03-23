<?php
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「パスワード変更ページ');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//ログイン認証
require('auth.php');
//=============================================
//画面処理
//=============================================
//DBからユーザーデータを取得
$userData = getUser($_SESSION['user_id']);
debug('取得したユーザー情報:'.print_r($userData,true));

//POST送信されていた場合
if(!empty($_POST)) {
    debug('POST送信があります');
    debug('POST情報:'.print_r($_POST,true));

    //変数に入力情報を代入
    $pass_old = $_POST['pass_old'];
    $pass_new = $_POST['pass_new'];
    $pass_new_re = $_POST['pass_new_re'];

    //未入力チェック
    validRequired($pass_old,'pass_old');
    validRequired($pass_new,'pass_new');
    validRequired($pass_new_re,'pass_new_re');

    if(empty($err_msg)) {
        debug('未入力チェックオーケー');
        //古いパスワードのチェック
        validPass($pass_old,'pass_old');
        //新しいパスワードのチェック
        validPass($pass_new,'pass_new');
        //古いパスワードとデータベース内容との照合
        if(!password_verify($pass_old,$userData['password'])){
            $err_msg['pass_old'] = MSG11;
        }
        //古いパスワードと新しいパスワードが同じかチェック
        if($pass_old === $pass_new) {
            $err_msg['pass_old'] = MSG12;
        }
        //パスワードとパスワード再入力が合っているかチェック
        validMatch($pass_new,$pass_new_re,'pass_new_re');

        if(empty($err_msg)){
            debug('バリデーションオーケー');
            try {
                //DB接続
                $dbh = dbConnect();
                $sql = 'UPDATE users SET password = :pass WHERE id = :id';
                $data = array(':id' => $_SESSION['user_id'],':pass' => password_hash($pass_new,PASSWORD_DEFAULT));
                $stmt = queryPost($dbh,$sql,$data);

                //クエリ成功の場合
                if($stmt) {
                    $_SESSION['msg_success'] = SUC01;

                    //メール送信
                    $username = ($userData['nickName']) ? $userData['nickName'] : '名無し';
                    $from = 'lowrise3641@gmail.com';
                    $to = $userData['email'];
                    $subject = 'パスワード変更通知|MANGA MENTA';
                    $comment = <<<EOT
{$username} さん
パスワードが変更されました。

/////////////////////////////////////
MANGA MENTA カスタマーセンター
/////////////////////////////////////
EOT;
                    sendMail($from,$to,$subject,$comment);

                    header("Location:mypage.php");
                }
            }catch (Exeption $e) {
                error_log('エラー発生:' . $e->getMessage());
                $err_msg['common'] = MSG07;
            }
        }
    }
}

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
        <h1 class="page-title">パスワードを変更する</h1>
    <section id="main">
        <section class="withdraw">
                <form class="valid" action="" method="post">  
                    <div class="area_msg">
                        <?php
                        echo getErrMsg('common');
                        ?>
                    </div>    
                    <label class="<?php if(!empty($err_msg['pass_old'])) echo 'err'; ?>">                
                        <span class="err_msg help_block"></span>
                        <input class="form-control valid-email" type="password" name="pass_old" placeholder="古いパスワード" value="<?php echo getFormData('pass_old'); ?>">
                    </label>
                    <div class="area_msg">
                        <?php
                        echo getErrMsg('pass_old');
                        ?>
                    </div>    
                    <label class="<?php if(!empty($err_msg['pass_new'])) echo 'err'; ?>">
                        <span class="err_msg"></span>
                        <input class="form-control" type="password" name="pass_new" placeholder="新しいパスワード" value="<?php echo getFormData('pass_new'); ?>">
                    </label>
                    <div class="area_msg">
                        <?php
                        echo getErrMsg('pass_new');
                        ?>
                    </div> 
                    <label class="<?php if(!empty($err_msg['pass_new_re'])) echo 'err'; ?>">
                        <span class="err_msg"></span>
                        <input class="form-control" type="password" name="pass_new_re" placeholder="新しいパスワード（再入力）" value="<?php getFormData('pass_new_re'); ?>">
                    </label>
                    <div class="area_msg">
                        <?php
                        echo getErrMsg('pass_new_re');
                        ?>
                    </div> 
                    <input id="submit-btn" type="submit" value="決定">
                </form>
           
        </section>
    </section>
 
     <!--サイドバー-->
     <?php
        require('sidebar.php');
    ?>


    </div>
         <!-- footer -->
         <?php
        require('footer.php');
        ?>

</body>