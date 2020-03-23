<?php
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「');
debug('「トップページ');
debug('「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//============================
//画面処理
//============================

//画面表示ようデータ取得
//========================
//GERTパラメータwp取得
//=====================
//カーレンとページ
$currentPageNum = (!empty($_GET['m'])) ? $_GET['m'] : 1;//デフォルトは0
//カテゴリー
$category = (!empty($_GET['c_id'])) ? $_GET['c_id'] : '';
//ソート順
$sort = (!empty($_GET['sort'])) ? $_GET['sort'] : '';
//パラメータないに不正な値が入っていないかチェック
if(!is_int($currentPageNum)){
  error_log('エラー発生:指定ページに不正な値が入りました');
  header("Location:index.php");
}
//表示件数
$listSpan =4;
//現在の表示レコード先頭を算出
$currentMinNum = (($currentPageNum-1)*$listSpan);
//DBからメンターデータを取得
//$dbMantorData = getMantorList($currentMinNum,$category,$sort);
//DBからカテゴリデータを取得
$dbCategoryData = getCategory();

debug('画面処理終了>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>');



require('head.php');
?>
<body class="page-home page2colum">
    <!--ヘッダー-->
    <?php
    require('header.php');
    ?>
    <div class="top-img">
    </div>
    <h1 class="top-img-title">夢を叶えよう。</h1>
    <!--メインコンテンツ-->
    <div id="contents" class="site-width">
    <section class="about" id="about">
            <div class="about-text">
                <div class="about-text-inner">
                    <h3 class="about-title ">「マンガ」を教わろう</h3>
                    <p class="about-lead">
                        日本中の漫画を描ける先輩「パイセン」に習えます。
                        自分のジャンルに合った人を探しましょう！
                    </p>
                </div>
            </div>
            <div class="about-img">
                <img src="./img/top_joshi.jpg" alt="" class="js-trigger js-fade__type__img">
            </div>
        </section>
        <!--メンター一覧-->
          <!--<section id="i-main" class="top-mantor-list">
            <h2 class="h2">おすすめのパイセン</h2>
            <div class="panel-list">
            <?php
              //foreach($dbMantorData['data'] as $key => $val):
                //debug('メンターデータ:'.print_r($dbMantorData,true));
            ?>
                <a href="mantorDetail.php<?php// echo (!empty(appendGetParam())) ? appendGetParam().'&m_id='.$val['mantorId'] : '?m_id='.$val['mantorId']; ?>" class="panel">
                  <div class="panel-head">
                    <img src="<?php //echo sanitize($val['prof_pic']); ?>" alt="パイセン">
                  </div>
                  <div class="panel-body">
                    <p class="panel-title">名前:<?php //echo sanitize($val['nickName']); ?> <span class="price">料金:<?php echo sanitize($val['price']); ?>円</span></p>
                  </div>
                </a>
                <?php
                //  endforeach;
                ?>
            </div>
                  <?php// pagination($currentPageNum,$dbMantorData['total_page']); ?>
      </section>
                -->
      </div>
      <section id="about-2" class="about-2">
        <h2 class="h2">マンガ部に入部してみよう！！</h2>
        <p class="about-2-subttl">一人で勉強するよりも、上手な人に教えてもらいませんか？</p>
        <div class="about-2-col">
          <div class="about-2-item">
          <i class="fas fa-home about-2-icon"></i>
            <h3>自宅でできる</h3>
            <p>学校のように通う必要はナシ！<br>オンラインでのやりとりなので自宅でマンガを描きながら習えます!</p>
          </div>
          <div class="about-2-item-center">
          <i class="fas fa-chalkboard-teacher about-2-icon"></i>
            <h3>まるで家庭教師</h3>
            <p>個別に指導してもらえる！<br>教室のようにたくさんの人がいたりしないので自分のペースで、好きなだけ質問できます。</p>
          </div>
          <div class="about-2-item">
          <i class="fas fa-comment-dollar about-2-icon"></i>
            <h3>料金が安い！！</h3>
            <p>マンガの専門学校のように高額な学費や教材費などは必要ありません！<br>マンガ部ならパイセンの設定した部費のみです！</p>
          </div>
        </div>
        <div class="m-b2 FollowArea">
              <a href="signup.php" class="btn hanten">入部してみる</a>
          </div>
      </section>

    </div>

     <!-- footer -->
     <?php
        require('footer.php');
        ?>

</body>