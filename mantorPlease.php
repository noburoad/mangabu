<?php 
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「「「「「パイセン申込みページ');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//=====================================================
//画面処理
//=====================================================
//GETパラメータ取得
$m_id = (!empty($_GET['m_id'])) ? $_GET['m_id'] : '';
//DBからメンターデータを取得
$viewData = getMantorOne($m_id);
//getパラメータに不正な値が入っていないかチェック
if(empty($viewData)){
  error_log('エラー発生:指定ページに不正な値が入りました。');
  header("Location:index.php");
}
debug('取得したDBデータ:'.print_r($viewData,true));



if(!empty($_POST['submit'])) {
    debug('POST送信があります');
    //ログイン認証
    require('auth.php');
  
    try {
      $dbh = dbConnect();
      $sql = 'INSERT INTO bord (sale_user,buy_user,mantorId,create_date) VALUES (:s_uid,:b_uid,:m_id,:c_date)';
      $data = array(':s_uid' => $viewData['u_id'], ':b_uid' => $_SESSION['user_id'], ':m_id' => $m_id, ':c_date' => date('Y-m-d H:i:s'));
      $stmt = queryPost($dbh,$sql,$data);
      
      if($stmt){
        $_SESSION['msg_success'] = SUC06;
        debug('連絡掲示板へ遷移します。');
        header("Location:msg.php?b_id=".$dbh->lastInsertID());//連絡掲示板へ
      }
    }catch(Exeption $e){
      error_log('エラー発生:'.$e->getMessage());
      $err_msg['common'] = MSG07;
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
        <h1 class="page-title">パイセン申込み</h1>
    <section id="main">
        <!--プロフィール-->
        <section class="my-prof">
            <div class="my-prof-imgArea">
                <div class="prof-img">
                    <img src="<?php echo showImg(sanitize($viewData['prof_pic'])); ?>" alt="プロフィール画像">
                </div>
            </div>
            <div class="my-prof-textArea">
            <p class="prof-title">ペンネーム</p>
            <div class="prof-text-bg"><?php echo sanitize($viewData['nickName']); ?></div>

            <p class="prof-title">ジャンル</p>
            <div class="prof-text-bg"><?php echo sanitize($viewData['category']); ?></div>

            <p class="prof-title">コメント</p>
            <div class="prof-comment"><?php echo sanitize($viewData['mantorComment']); ?></div>
            </div>
        </section>
        <!--申込み-->
        <div class="mentor-please">
            <form action="" method="post">
                <div class="mentor-please-btn">
                    <input class="btn hanten" type="submit" name="submit" value="申込む">
                </div>
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