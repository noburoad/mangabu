<?php
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「マイページ');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//ログイン認証
require('auth.php');

//画面表示用データを取得
$dbFormData = getUser($_SESSION['user_id']);
$u_id = $_SESSION['user_id'];
$mantorData = getMyMantor($u_id);
$bordData = getMyMsgsAndBord($u_id);
$myFollowData = getMyFollowMantor($u_id);
$myTeachUser = getMyTeachUser($u_id);

$currentPageNum = (!empty($_GET['m'])) ? $_GET['m'] : 1;//デフォルトは0
//表示件数
$listSpan =4;
//現在の表示レコード先頭を算出
$currentMinNum = (($currentPageNum-1)*$listSpan);
//DBからメンターデータを取得
$dbMantorData = getMantorList($currentMinNum,$category,$sort);


$pic = (!empty($_FILES['pic']['name'])) ? uploadImg($_FILES['pic'],'pic') : '';
$pic = (empty($pic1) && !empty($dbFormData['pic'])) ? $dbFormData['pic']: $pic;

debug('取得したパイセン情報:'.print_r($mantorData,true));
debug('取得したフォロー情報:'.print_r($myFollowData,true));
debug('取得した掲示板データ:'.print_r($bordData,true));


debug('画面表示処理終了<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<')
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
        <h1 class="page-title">マイページ</h1>
    <section id="main">
        <!--プロフィール-->
        <section class="my-prof">
            <div class="my-prof-imgArea">
                <h2 class="title">プロフィール</h2>
                <div class="prof-img">
                    <img src="<?php  echo showImg(sanitize($dbFormData['pic'])); ?>" alt="プロフィール画像">
                    <a href="prof_edit.php">編集する</a>
                </div>
                
            </div>
            <div class="my-prof-textArea">
            <p class="prof-title">ペンネーム</p>
            <div class="prof-text-bg"><?PHP echo $dbFormData['nickName']; ?></div>

            <p class="prof-title">ジャンル</p>
            <div class="prof-text-bg"><?PHP echo getCategoryName($dbFormData['like_j']); ?></div>

            <p class="prof-title">コメント</p>
            <div class="prof-comment"><?PHP echo $dbFormData['profComment']; ?></div>
            </div>
        </section>
        <!--パイセン一覧-->
        <section class="list panel-list">
            <h2 class="title">フォローしてるパイセン</h2>
            <?php
            if(empty($myFollowData)){
                ?>
                <p class="no-user">まだフォローしているパイセンはいません<br>気になるパイセンを見つけてフォローしましょう！</p>

                <?php
            }
            ?>
            <?php
                if(!empty($myFollowData)){
                    foreach($myFollowData as $key => $val){
            ?>
            <a href="mantorDetail.php<?php echo (!empty(appendGetParam())) ? appendGetParam().'&m_id='.$val['mantorId'] : '?m_id='.$val['mantorId']; ?>" class="panel">
                <div class="panel-head">
                    <img src="<?php echo showImg(sanitize($val['prof_pic'])); ?>" alt="<?php echo sanitize($val['nickName']); ?>">
                </div>
                <div class="panel-body">
                    <p class="panel-title"><?php echo sanitize($val['nickName']); ?><span class="price"><?php echo sanitize($val['price']); ?>円</span></p>
                </div>
            </a>
            <?php
                    }
                }
            ?>
            
        </section>
        <!--連絡掲示板-->
        <section class="list list-table">
            <h2 class="title">連絡掲示板</h2>
            <table class="table">
                <thead>
                    <th>最新送信日時</th>
                    <th>掲示板のパイセン</th>
                    <th>メッセージ</th>
                </thead>
                <tbody>
                    <?php 
                    $i = 0;
                    if(empty($bordData)){
                    ?>
                        <tr>
                        <td>-- --</td>
                        <td>-- --</td>
                        <td>まだ掲示板はありません</td>
                    </tr>
                    <?php
                    }
                    if(!empty($bordData)){
                        foreach($bordData as $key => $val){
                            if($i >= 5){
                            break;
                            }
                            if(!empty($val['msg'])){
                                $msg = array_shift($val['msg']);
                                debug('$msgの中身'.print_r($msg,true));
                    ?>
                    <tr>
                        <td><?php echo sanitize(date('Y.m.d H:i:s',strtotime($msg['send_date']))); ?></td>
                        <td><?php echo getBordPartnerName($val['sale_user']); ?>さん</td>
                        <td><a href="msg.php?b_id=<?php echo sanitize($val['id']); ?>"><?php echo mb_substr(sanitize($msg['msg']),0,20);?>...</a></td>
                    </tr>
                    <?php  
                        $i++;
                    ?>
                    <?php
                            }else{
                    ?>
                    <tr>
                        <td>-- --</td>
                        <td><?php echo getBordPartnerName($val['sale_user']); ?></td>
                        <td><a href="msg.php?b_id=<?php echo sanitize($val['id']); ?>">まだメッセージはありません</a</td>
                    </tr>
                    <?php  
                        $i++;
                    ?>
                    <?php
                        }
                    }
                }
                        ?>

                </tbody>
            </table>
            
        </section>
        <section class="list panel-list">
            <h2 class="title">教えている後輩</h2>
            <!--生徒たち-->
            <?php 
                    $i = 0;
                    
                    if(empty($myTeachUser)){
                        ?>
                        <p class="no-user">まだ後輩はいません<br>上手になったら後輩に教えてあげましょう！</p>

                        <?php
                    }
                    ?>
                    <?php
                    if(!empty($myTeachUser)){
                        foreach($myTeachUser as $key => $val){
                            if($i >= 4){
                            break;
                        }
                    ?>
            <a href="" class="panel">
                <div class="panel-head">
                    <img src="<?php echo showImg(sanitize($val['pic'])); ?>" alt="後輩">
                </div>
                <div class="panel-body">
                    <p class="panel-title"><?php echo sanitize($val['nickName']); ?></p>
                    <p class="panel-comment"><?php echo mb_substr(sanitize($val['profComment']),0,12);?>...</p>
                </div>
            </a>
            <?php  
                        $i++;
                    ?>
                    <?php
                        }
                    }
                        ?>
            
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