<?php
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('メンター登録ページ');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//ログイン認証
require('auth.php');

///////////////////////////////////////
//////画面処理
///////////////////////////////////////

//画面表示用データ取得
//===================================
$u_id = $_SESSION['user_id'];
$mantorData = getMyMantor($u_id);
$userData = getUser($u_id);
$nickName = $userData['nickName'];
$prof_img = $userData['pic'];

//GETデータを格納
$m_id = (!empty($_GET['m_id'])) ? $_GET['m_id'] : '' ;
//DBからメンター情報を取得
$dbFormData = (!empty($mantorData)) ? $mantorData : '';
//プロフィールデータ読み込み
$myProfData = getProfData($_SESSION['user_id']);
//新規登録画面か編集画面なのかを判別するフラグ
$edit_flg = (empty($dbFormData)) ? true : false ;
//DBからカテゴリデータを取得
$dbCategoryData = getCategory();
debug('メンターID:'.$dbFormData['mantorId']);
debug('フォーム用DBデータ'.print_r($dbFormData,true));
debug('カテゴリデータ'.print_r($dbCategoryData,true));

//パラメータ改竄チェック
//==========================================
//GETパラメータはあるが改竄されている場合、マイページへ遷移させる
if(!empty($m_id) && empty($dbFormData)){
  debug('GETパラメータのメンターIDが違います');
  header("Location:mypage.php");//マイページへ
}

//POST送信時処理
//=============================================
if(!empty($_POST)){
  debug('POST送信があります');
  debug('POST情報'.print_r($_POST,true));
  debug('FILE情報'.print_r($_FILES,true));

  //変数にユーザー情報を代入
  $category = $_POST['category_Id'];
  $price = (!empty($_POST['price'])) ? $_POST['price'] : 0;
  $mantorTitle= $_POST['mantorTitle'];
  $mantorComment = $_POST['mantorComment'];

  //画像をアップロードし、パスを格納
  $pic1 = (!empty($_FILES['pic1']['name'])) ? uploadImg($_FILES['pic1'],'pic1') : '';
  //画像をPOSTしていない(登録していない)が既にDBに登録してある場合、DBnpパスを入れる
  $pic1 = (empty($pic1) && !empty($dbFormData['pic1'])) ? $dbFormData['pic1']: $pic1;
  $pic2 = (!empty($_FILES['pic2']['name'])) ? uploadImg($_FILES['pic2'],'pic2') : '';
  $pic2 = (empty($pic2) && !empty($dbFormData['pic2'])) ? $dbFormData['pic2'] : $pic2;
  $pic3 = (!empty($_FILES['pic3']['name'])) ? uploadImg($_FILES['pic3'],'pic3') : '';
  $pic3 = (empty($pic3) && !empty($dbFormData['pic3'])) ? $dbFormData['pic3'] : $pic3;

  //更新の場合、DB情報と入力情報が異なるならバリデーションチェックを行う
  if(empty($dbFormData)){
    //セレクトボックスチェック
    validSelect($category,'category');
    //最大文字数チェック
    validMaxLen($mantorComment,'mantorComment',300);
    validMaxLen($mantorTitle,'mantorTitle',30);
    //未入力チェック
    validRequired($price,'price');
    validRequired($mantorTitle,'mantorTitle');
    //半角数字チェック
    validNumber($price,'price');
  }else{
    if($dbFormData['category_id'] !== $category){
      validSelect($category,'category_id');
    }
    if($dbFormData['mantorTitle'] !== $mantorTitle){
      validMaxLen($mantorTitle,'mantorTitle',30);
    }
    if($dbFormData['mantorComment'] !== $mantorComment){
      validMaxLen($mantorComment,'mantorComment',300);
    }
    if($dbFormData['price'] != $price){
      validRequired($price,'price');
      validNumber($preice,'price');
    }
  }

  if(empty($err_msg)){
    debug('バリデーションOKです');
    //例外処理
    try{
      $dbh = dbConnect();
      //編集画面の場合はUPDATE文、新規登録画面の場合はINSERT文
      if(!$edit_flg){
        debug('DB更新です');
        $sql = 'UPDATE mantor SET category_id=:category, price=:price, mantorTitle=:mantorTitle,mantorComment=:mantorComment, pic1=:pic1, pic2=:pic2, pic3=:pic3 WHERE u_id=:u_id AND mantorId=:m_id';
        $data = array(':category'=>$category, ':price'=>$price,':mantorTitle'=>$mantorTitle, ':mantorComment'=>$mantorComment,':pic1'=>$pic1, ':pic2'=>$pic2, ':pic3'=>$pic3,':u_id'=>$_SESSION['user_id'], ':m_id'=>$mantorData['mantorId']);
      }else{
        debug('DB新規登録です');
        $sql = 'INSERT INTO mantor(nickName,prof_pic,category_id,price,mantorTitle,mantorComment,pic1,pic2,pic3,u_id,create_date) VALUES(:nickName,:prof_pic,:category,:price,:mantorTitle,:mantorComment,:pic1,:pic2,:pic3,:u_id,:create_date)';
        $data = array(':nickName'=>$nickName, ':prof_pic'=> $prof_img,':category'=>$category, ':price'=>$price, ':mantorTitle'=>$mantorTitle, ':mantorComment'=>$mantorComment,':pic1'=>$pic1, ':pic2'=>$pic2, ':pic3'=>$pic3,':u_id'=>$_SESSION['user_id'], ':create_date'=>date('Y-m-d H:i:s'));
      }
      debug('SQL:'.$sql);
      debug('流し込みデータ'.print_r($data,true));
      //クエリ実行
      $stmt = queryPost($dbh,$sql,$data);

      //クエリ成功の場合
      if($stmt){
        if($stmt && $edit_flg){
        $_SESSION['msg_success'] = SUC04;
        }elseif($stmt && !$edit_flg){
          $_SESSION['msg_success'] = SUC05;
        }
        debug('マイページへ遷移します');
        header("Location:mypage.php");//マイページへ
      }
    }catch(Exeption $e){
      error_log('エラー発生'.$e->getMessage());
      $err_msg['common'] = MSG07;
    }
  }
}
debug('画面表示処理終了<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');

?>



<?php
require('head.php');
?>
<body class="page-home page2colum page-logined">
    <!--ヘッダー-->
    <?php
    require('header.php');
    ?>

    <!--メインコンテンツ-->
<div id="contents" class="site-width">
    <h1 class="page-title"><?php echo (!$mantorData) ? 'パイセン登録する' : 'パイセン情報を編集する'; ?></h1>
      <!-- Main -->
      <section id="main" >
      
        <div class="form-container">
          <!-- プロフィール-->
        <section class="my-prof">
            <div class="my-prof-imgArea">
                <h2 class="title">プロフィール</h2>
                <div class="prof-img">
                    <img src="<?php echo showImg(sanitize($myProfData['pic'])); ?>" alt="プロフィール画像">
                </div>
            </div>
            <div class="my-prof-textArea">
            <p class="prof-title">ニックネーム</p>
            <div class="prof-text-bg"><?PHP echo $myProfData['nickName']; ?></div>

            <p class="prof-title">好きなジャンル</p>
            <div class="prof-text-bg"><?PHP echo getCategoryName($myProfData['like_j']); ?></div>

            <p class="prof-title">コメント</p>
            <div class="prof-comment"><?PHP echo $myProfData['profComment']; ?></div>
            </div>
        </section>
         <h2 class="sub-ttl">パイセン情報</h2>
          <form action="" method="post" class="form" enctype="multipart/form-data">
            <div class="area-msg">
              <?php 
              if(!empty($err_msg['common'])) echo $err_msg['common'];
              ?>
            </div>
            <label class="<?php if(!empty($err_msg['mantorTitle'])) echo 'err' ;?>"> 
                    パイセンタイトル<span class="label-require">必須</span>
                        <input class="p-form-input" type="text" name="mantorTitle" value="<?PHP echo $mantorData['mantorTitle']; ?>">
                    </label>
                    <div class="area-msg">
                        <?php
                        if(!empty($err_msg['mantorTitle'])) echo $err_msg['mantorTitle'];
                        ?>
                    </div>

            <label class="<?php if(!empty($err_msg['category_id'])) echo 'err'; ?>">
              カテゴリ<span class="label-require">必須</span>
              <select name="category_Id" id="">
                <option value="0" <?php if($mantorData['category_id'] == 0 ){ echo 'selected'; } ?> >選択してください</option>
                <?php
                  foreach($dbCategoryData as $key => $val){
                ?>
                  <option value="<?php echo $val['categoryId'] ?>" <?php if($mantorData['category_id'] == $val['categoryId'] ){ echo 'selected'; } ?> >
                    <?php echo $val['categoryName']; ?>
                  </option>
                  <?php
                  }
                ?>
              </select>
            </label>
            <div class="area-msg">
              <?php 
              if(!empty($err_msg['category_id'])) echo $err_msg['category_id'];
              ?>
            </div>
            <label class="<?php if(!empty($err_msg['mantorComment'])) echo 'err'; ?>">
              習えること<span class="label-require">必須</span>
              <textarea class="mentorComment" name="mantorComment" id="js-count" cols="50" rows="10"><?php echo $mantorData['mantorComment']; ?></textarea>
              
            </label>
            <div class="area-msg">
              <?php 
              if(!empty($err_msg['mantorComment'])) echo $err_msg['mantorComment'];
              ?>
            </div>
            <p class="counter-text"><span id="js-count-view">0</span>/500文字</p>
            <label class="<?php if(!empty($err_msg['price'])) echo 'err'; ?>" style="text-align:left;">
              金額(月額)<span class="label-require">必須</span>
              <div class="form-group">
                <input type="text" name="price" class="mentor" value="<?php echo $mantorData['price']; ?>" style="width:150px" placeholder="5,000"><span class="option">円</span>
              </div>
            </label>
            <div class="imgDrop-container" style="overflow:hidden;">
          
            <p>参考画像メイン</p>
              <div class="imgDrop-pic-main">
            <label>
              <div class="mentor-area-drop-main area-drop <?php if(!empty($err_msg['pic1'])) echo 'err'; ?>">
              <input type="hidden" name="MAX_FILE_SIZE" value="3145728" >
              <input type="file" name="pic1" class="input-file">
              <img src="<?php echo $mantorData['pic1'] ?>" alt="参考画像" class="prev-img" style="<?php if(empty($mantorData['pic1'])) echo 'display:none;' ?>">
                ドラッグ＆ドロップ
              </div>
            </label>
            <div class="area-msg">
                  <?php 
                  if(!empty($err_msg['pic1'])) echo $err_msg['pic1'];
                  ?>
                </div>
              </div>
              <p>参考画像サブ</p>
            <div class="imgDrop-inner">
            <div class="imgDrop-sub">
            
            <label>
              <div class="mentor-area-drop-sub area-drop">
              <input type="hidden" name="MAX_FILE_SIZE" value="3145728" >
              <input type="file" name="pic2" class="input-file">
              <img src="<?php echo $mantorData['pic2'] ?>" alt="参考画像" class="prev-img" style="<?php if(empty($mantorData['pic2'])) echo 'display:none;' ?>">
                ドラッグ＆ドロップ
              </div>
            </label>
            <div class="area-msg">
                  <?php 
                  if(!empty($err_msg['pic2'])) echo $err_msg['pic2'];
                  ?>
                </div>
              </div>
            <div class="imgDrop-sub">
            <label>
              <div class="mentor-area-drop-sub area-drop">
              <input type="hidden" name="MAX_FILE_SIZE" value="3145728" >
              <input type="file" name="pic3" class="input-file">
              <img src="<?php echo $mantorData['pic3'] ?>" alt="参考画像" class="prev-img" style="<?php if(empty($mantorData['pic3'])) echo 'display:none;' ?>">
                ドラッグ＆ドロップ
              </div>
            </label>
            <div class="area-msg">
                  <?php
                  if(!empty($err_msg['pic3'])) echo $err_msg['pic3'];
                  ?>
                </div>
                </div>
                </div>
                  
            <div class="btn-container">
              <input type="submit" class="btn btn-mid hanten" value="登録する">
            </div>
          </form>
        </div>
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