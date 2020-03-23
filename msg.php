<?php
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「');
debug('「掲示板ページ');
debug('「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//=================================================
//画面表示処理
//=================================================
$partnerUserId = '';
$partnerUserInfo = '';
$myUserInfo = '';
$mantorInfo = '';
//GETパラメータを取得
$b_id = (!empty($_GET['b_id'])) ? $_GET['b_id'] : '';
debug('$b_idの中身:'.$b_id);
//DBから掲示板とメッセージデータを取得
$viewData = getMsgsAndBord($b_id);
debug('取得したDBデータ:'.print_r($viewData,true));
//パラメータに不正な値が入っていないかチェック
if(empty($viewData)){
  error_log('エラー発生:指定ページに不正な値が入りました');
  header("Location:index.php");
}
//パイセン情報を取得
$mantorInfo = getMantorOne($viewData[0]['mantorId']);
debug('取得したDBデータ:'.print_r($mantorInfo,true));
//パイセンデータが入っているかチェック
if(empty($mantorInfo)){
  error_log('エラー発生:パイセン情報が取得できませんでした');
  header("Location:index.php");
}
//viewDataから相手のユーザーデータを取り出す
$dealUserIds[] = $viewData[0]['sale_user'];
$dealUserIds[] = $viewData[0]['buy_user'];
if(($key = array_search($_SESSION['user_id'],$dealUserIds)) !== false) {
  unset($dealUserIds[$key]);
}
$partnerUserId = array_shift($dealUserIds);
debug('取得した相手のユーザーID:'.print_r($partnerUserId,true));
//DBから取引相手のユーザー情報を取得
if(isset($partnerUserId)){
  $partnerUserInfo = getUser($partnerUserId);
}
//相手のユーザー情報が取得できたかチェック
if(empty($partnerUserInfo)){
  error_log('エラー発生:相手のユーザー情報が取得できませんdでした');
  header("Location:index.php");
}
//DBから自分のユーザー情報を取得
$myUserInfo = getUser($_SESSION['user_id']);
debug('取得した自分のデータ'.print_r($myUserInfo,true));
//チェック
if(empty($myUserInfo)){
  error_log('エラー発生:自分のユーザーデータが取得できませんでした');
  header("Location:index.php");
}

//post送信あり
if(!empty($_POST)){
  debug('POST送信があります');

  //ログイン認証
  require('auth.php');

  //バリデーションチェック
  $msg = (isset($_POST['msg'])) ? $_POST['msg'] : '';
  //最大文字数チェック
  validMaxLen($msg,'msg',300);
  //未入力チェック
  validRequired($msg,'msg');

  if(empty($err_msg)){
    debug('バリデーションOKです');

    try {
      $dbh = dbConnect();
      $sql = 'INSERT INTO b_message (bord_id,send_date,to_user,from_user,msg,create_date) VALUES(:b_id,:send_date,:to_user,:from_user,:msg,:c_date)';
      $data = array(':b_id'=> $b_id, ':send_date'=> date('Y-m-d H:i:s'), ':to_user'=> $partnerUserId, ':from_user'=> $_SESSION['user_id'], ':msg'=> $msg, ':c_date'=> date('Y-m-d H:i:s'));
      $stmt = queryPost($dbh,$sql,$data);

      if($stmt){
        $_POST = array();//POSTを初期化
        debug('連絡掲示板へ遷移します');
        header("Location:" .$_SERVER['PHP_SELF'].'?b_id='.$b_id);//時画面に遷移
      }
    }catch(Exeption $e){
      error_log('エラー発生:'.$e->getMessage());
      $err_msg['common'] = MSG07;
    }
  }
}
debug('画面表示処理終了<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');

?>



<?php
require('head.php');
?>
<body class="page-home page2colum">
    <!--ヘッダー-->
    <?php
    require('header.php');
    ?>
    <p id="js-show-msg" style="display:none;" class="msg-slide">
        <?php echo getSessionFlash('msg_success'); ?>
    </p>
    <!--メインコンテンツ-->
    <div id="contents" class="site-width">
        <h1 class="page-title">メッセージ掲示板</h1>

      <!-- Main -->
      <section id="main" >
        <!--プロフ表示-->
          <section id="msg-prof">
            <div class="msg-prof">
              <h2 class="msg-title">やりとり中の相手</h2>
                <div class="msg-prof-img" style="background-image: url('<?php echo showImg(sanitize($mantorInfo['prof_pic'])) ?>');">
                </div>
                <div class="msg-prof-sub">
                    <p class="msg-prof-title">パイセン名</p>
                    <div class="msg-prof-bg"><?php echo sanitize($mantorInfo['nickName']); ?></div>
                    <p class="msg-prof-title">料金</p>
                    <div class="msg-prof-bg"><span class="price"><?php echo sanitize($mantorInfo['price']); ?>円</span></div>

                    <p class="msg-prof-title">コメント</p>
                    <div class="msg-prof-bg msg-prof-comment"><?php echo sanitize($mantorInfo['mantorComment']); ?></div>
                </div>
            </div>
          </section>
          <div class="area-bord" id="js-scroll-bottom">
            <?php if(!empty($viewData)){
              foreach($viewData as $key => $val){
                if(!empty($val['from_user']) && $val['from_user'] == $partnerUserId){
              ?>
          <div class="msg-cnt msg-left">
            <div class="avatar">
              <img src="<?php echo showImg(sanitize($partnerUserInfo['pic'])) ?>" alt="パイセン">
            </div>
            <p class="msg-inrTxt">
              <span class="triangle"></span>
              <?php echo sanitize($val['msg']); ?>
            </p>
            <div class="msg-send-date"><?php echo sanitize($val['send_date']); ?></div>
          </div>
          <?php
                }else{
          ?>
          <div class="msg-cnt msg-right <?php if(empty($val['msg'])){ echo 'no-msg';} ?>">
            <div class="avatar">
              <img src="<?php echo showImg(sanitize($myUserInfo['pic'])) ?>" alt="自分" >
            </div>
            <p class="msg-inrTxt">
              <span class="triangle"></span>
              <?php echo sanitize($val['msg']); ?>
            </p>
            <span class="msg-send-date-right"><?php echo sanitize($val['send_date']); ?></span>
          </div>
          <?php
                }
              }
            }else{
              ?>
              <p class="no-msg">メッセージはまだありません。</p>
          <?php
            }
            ?>
        </div>
        <div class="area-send-msg">
          <form action="" method="post">
          <textarea name="msg" id="" cols="30" rows="3"></textarea>
          <input type="submit" value="送信" class="btn btn-send">
          </form>
        </div>
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
<script>
$(function(){
  /////////////掲示板のスクロール用
  $('#js-scroll-bottom').animate({scrollTop: $('#js-scroll-bottom')[0].scrollHeight},'fast');
});
</script>

</body>