<?php
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「パイセン検索ページ');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//カーレンとページ
$currentPageNum = (!empty($_GET['m'])) ? $_GET['m'] : 1;//デフォルトは0
//カテゴリー
$category = (!empty($_GET['c_id'])) ? $_GET['c_id'] : '';
//ソート順
$sort = (!empty($_GET['sort'])) ? $_GET['sort'] : '';

if(!is_int($currentPageNum)){
    error_log('エラー発生:指定ページに不正な値が入りました');
    header("Location:index.php");
  }
//表示件数
$listSpan = 5;
//現在の表示レコード先頭を算出
$currentMinNum = (($currentPageNum-1)*$listSpan);
//DBからメンターデータを取得
$dbMantorData = getMantorList($currentMinNum,$category,$sort);
//DBからカテゴリデータを取得
$dbCategoryData = getCategory();

debug('画面処理終了>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>');

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
    <h1 class="page-title">パイセンを探す</h1>
        <!--サイドバー-->
        <section id="sidebar">
            <form name="" method="get">
                <h1 class="title">カテゴリー</h1>
            <div class="selectbox">
                <span class="icn_select"></span>
                <select name="c_id">
                    <option value="0" <?php if(getFormData('c_id',true) == 0){echo 'selected';} ?>>選択してください</option>
                    <?php 
                    foreach($dbCategoryData as $key => $val){
                    ?>
                    <option value="<?php echo $val['categoryId'] ?>" <?php if(getFormData('c_id',true) == $val['categoryId']){echo 'selected';} ?>>
                        <?php echo $val['categoryName']; ?>
                    </option>
                    <?php } ?>
                </select>
          </div>
          <h1 class="title">表示順</h1>
          <div class="selectbox">
          <span class="icn_select"></span>
            <select name="sort" id="">
                <option value="0" <?php if(getFormData('sort',true) == 0){echo 'selected';} ?>>選択してください</option>
                <option value="1" <?php if(getFormData('sort',true) == 0){echo 'selected';} ?>>料金が安い順</option>
                <option value="2" <?php if(getFormData('sort',true) == 0){echo 'selected';} ?>>料金が高い順</option>
            </select>
          </div>
          <input type="submit" value="検索">
            </form>
        </section>
        <!--メンター一覧-->
          <section id="main">
            <div class="search-title">
                <div class="search-left"><span class="total-num"><?php echo sanitize($dbMantorData['total']); ?></span>人のパイセンを表示中</div>
                <div class="search-right"><span class="num"><?php echo (!empty($dbMantorData['data'])) ? $currentMinNum+1 : 0 ; ?></span> - <span class="num"><?php echo $currentMinNum+count($dbMantorData['data']); ?></span>人 / <span class="num"><?php echo sanitize($dbMantorData['total']) ;?></span>人中</div>
            </div>
   <div class="panel-list">
       <!--パイセン一覧-->
        <?php
            foreach($dbMantorData['data'] as $key => $val){
        ?>
        <div class="mentorList-card">
                <div class="mentorList-face">
                    <a href="mantorDetail.php<?php echo (!empty(appendGetParam())) ? appendGetParam().'&m_id='.$val['mantorId'] : '?m_id'.$val['mantorId']; ?>">
                        <div class="icon size85" style="background-image: url('<?php echo sanitize($val['prof_pic']); ?>');"></div>
                    </a>
                </div>
                <div class="mentorList-body">
                <div class="mentorList-body-name">
                    
                    <a href="mentorDetail.php<?php echo (!empty(appendGetParam())) ? appendGetParam().'&m_id='.$val['mantorId'] : '?m_id'.$val['mantorId']; ?>" class="name"><?php echo sanitize($val['nickName']); ?></a>
                    <div class="mentorList-genre">
                        <?php echo sanitize(getCategoryName($val['category_id'])); ?>
                    </div>
                </div>
                <div class="mentorList-body-title"><a href="mentorDetail.php<?php echo (!empty(appendGetParam())) ? appendGetParam().'&m_id='.$val['mantorId'] : '?m_id'.$val['mantorId']; ?>"><?php echo sanitize($val['mantorTitle']); ?></a></div>
                    <div class="mentorList-body-price">
                        料金  <span class="black"><?php echo sanitize($val['price']); ?>円</span>
                    </div>
                </div>

                <div class="mentorList-btn">
                    
                    <div>
                        <a href="mantorDetail.php<?php echo (!empty(appendGetParam())) ? appendGetParam().'&m_id='.$val['mantorId'] : '?m_id='.$val['mantorId']; ?>" class="btn hanten"><i class="far fa-envelope"></i> 詳細を見る</a>
                    </div>
                </div>
            </div>
    <?php } ?>

    </div>

    <?php pagination($currentPageNum,$dbMantorData['total_page']); ?>
        

        </div>
        </section>
      </section>

    </div>

    </div>
     <!-- footer -->
     <?php
        require('footer.php');
        ?>

</body>