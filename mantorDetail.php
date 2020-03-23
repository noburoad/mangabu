<?php
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「「「「「パイセン詳細ページ');
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
$follow = getFollow($viewData['u_id']);
$follower = getFollower($viewData['mantorId']);
//POST送信されていた場合
if(!empty($_POST['submit'])) {
  debug('POST送信があります');
  //ログイン認証
  require('auth.php');

}

debug('画面表示処理終了<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');
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
    <h1 class="page-title">パイセン詳細</h1>
        <!--サイドバー-->
    <div id="mentor-sidebar">
      <div class="mentor-side">
        
       <div class="icon size125 wborder" style="background-image: url('<?php echo showImg(sanitize($viewData['prof_pic'])); ?>');"></div>
        
        <p class="name"><a href="" class="link"><?php echo sanitize($viewData['nickName']); ?></a></p>
        <p class="soudan">後輩募集中</p> 
        <div class="followbox m-t1">
            <div class="follow">
                <div class="w50p">
                    <div class="title">フォロー</div>
                    <div class="member"><a href=""><span class="bold"><?php echo $follow ?></span></a>人</a></div>
                </div>
                <div class="w50p">
                    <div class="title">フォロワー</div>
                    <div class="member"><a href=""><span class="bold"><?php echo $follower ?></span></a>人</a></div>
                </div>
                <!--
                <div class="w50p">
                    <div class="title">返信率</div>
                    <div class="percent"><span class="bold">96</span>%</div>
                </div>
                -->
            </div>
        </div>

        <div class="m-b2 FollowArea">
          <span class="btn js-click-like <?php if(isLike($_SESSION['user_id'],$viewData['mantorId'])){echo 'active';} ?>" data-mantorid="<?php echo sanitize($viewData['mantorId']); ?>"><?php if(isLike($_SESSION['user_id'],$viewData['mantorId'])) {echo 'フォロー済み';}else{echo 'フォロー';} ?></span>
        </div>
        
        <div class="hyouka_box">
          <!--
            <div class="icon_menta">後輩の数</div>
            <div class="right">8人</div>
            -->
            <!--
            <div class="icon_heart">評価</div>
            <div class="right"><span class="yellow"><i class="fas fa-star"></i></span><span class="yellow"><i class="fas fa-star"></i></span><span class="yellow"><i class="fas fa-star"></i></span><span class="yellow"><i class="fas fa-star"></i></span></div>
            -->
            <div class="icon_money">料金</div>
            <div class="right"><?php echo sanitize($viewData['price']); ?>円</div>
        </div>

        <div class="m-b1 SideMessageArea">
        <form action="" method="post">
          <div class="m-b2 FollowArea">
              <a href="mantorPlease.php?m_id=<?php echo $m_id; ?>" class="btn hanten">パイセン申込み</a>
          </div>
          </form>
            <div class="SideMessageArea__sub">遠慮なく教えてもらおう！</div>
        </div>
    </div>
</div>
        <!--メンター詳細-->
  <section id="mentor-main" >

    <div class="detail-title">
      <h1><?php echo sanitize($viewData['mantorTitle']); ?></h1>
    </div>
    <div class="product-img-container">
      <div class="md-img-main">
        <img src="<?php echo showImg(sanitize($viewData['pic1'])); ?>" alt="参考画像" class="js-switch-img-main">
      </div>
      <div class="md-img-sub">
        <img src="<?php echo showImg(sanitize($viewData['pic2'])); ?>" alt="参考画像" class="js-switch-img-sub">
        <img src="<?php echo showImg(sanitize($viewData['pic3'])); ?>" alt="参考画像" class="js-switch-img-sub">
      </div>
    </div>
    <div class="product-detail">
      <p>
      <?php echo sanitize($viewData['mantorComment']); ?>
      </p>
    </div>
    <div class="product-buy">
    <form action="" method="post">
      <div class="plz-tearch">
        <a href="mantorPlease.php?m_id=<?php echo $m_id; ?>" class="btn hanten">教えてもらう！</a>
      </div>
    </form>
      <div class="item-left">
        <a href="mantorSearch.php<?php echo appendGetParam(array('m_id')); ?>">&lt; パイセン検索へ</a>
      </div>
      <div class="item-right">
        <p class="price"></p>
      </div>
    </div>

  </section>

    </div>

    </div>

         <!-- footer -->
         <?php
        require('footer.php');
        ?>
</body>