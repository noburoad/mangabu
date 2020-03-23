<?php
require('function.php');


debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「プロフィール編集ページ');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//ログイン認証
require('auth.php');

/////////////////////////////
//ログイン画面処理
/////////////////////////////

$dbFormData = getUser($_SESSION['user_id']);
$dbCategoryData = getCategory();
debug('取得したユーザー情報'.print_r($dbFormData,true));


//POST送信されていた場合
if(!empty($_POST)){
    debug('POST送信があります');
    debug('POST中身'.print_r($_POST,true));
    debug('FILE中身'.print_r($_FILES,true));
    //変数に入力情報を格納
    $nickName = $_POST['nickName'];
    $like_j = $_POST['like_j'];
    $profComment = $_POST['profComment'];

    //画像をアップし、パスを格納
    $pic = (!empty($_FILES['pic']['name'])) ? uploadImg($_FILES['pic'],'pic'):'';
    debug('ピクチャー内容:'.print_r($pic,true));
    //画像をPOSTしていない(登録していない)が既にDBに登録されている場合、DBのパスを入れる(POSTには反映されないので)
    $pic = (empty($pic) && !empty($dbFormData['pic'])) ? $dbFormData['pic'] : $pic;

    //DBの情報と入力情報が異なる場合、バリデーションを行う
    if($dbFormData['nickName'] !== $_POST['nickName']){
        validMaxLen($nickName,'nickName');
    }
    if($dbFormData['like_j'] !== $_POST['like_j']){
        validMaxLen($like_j,'like_j');
    }
    if($dbFormData['profComment'] !== $_POST['profComment']){
        validMaxLen($profComment,'profComment');
    }


    if(empty($err_msg)){
        debug('バリデーションOKです');
        //例外処理
            try{
                $dbh = dbConnect();
                $sql = 'UPDATE users SET nickName = :nickName , like_j = :like_j, profComment = :profComment, pic = :pic WHERE id = :u_id';
                $data = array(':nickName' => $nickName,':like_j' => $like_j,':profComment' => $profComment,':pic' => $pic,':u_id' => $dbFormData['id']);
                //クエリ実行
                $stmt = queryPost($dbh,$sql,$data);

                //クエリ成功の場合
                if($stmt){
                    debug('クエリ内容'.print_r($stmt,true));
                    debug('マイページへ遷移します。');
                    header("Location:mypage.php");
                }
            }catch(Exeption $e){
                error_log('エラー発生'.$e->getMessage());
                $err_msg['common']= MSG07;
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
        <h1 class="page-title">プロフィール編集</h1>
    <section id="main">
        <section class="prof-edit">
        <form class="p-form" action="prof_edit.php" method="post" enctype="multipart/form-data">
            <label class="p-form-imgArea <?php if(!empty($err_msg['pic'])) echo 'err'; ?>"> 
                <div class="p-form-imgArea-drop area-drop">
                    <input type="hidden" name="MAX_FILE_SIZE" value="3145728">
                    <input type="file" name="pic" class="prof-input-file input-file">
                    <img src="<?php echo showImg(sanitize($dbFormData['pic']));?>" alt="プロフィール画像" class="prev-img">
                </div>
            </label>
                   <div class="p-form-profArea">
                    <label class="<?php if(!empty($err_msg['nickName'])) echo 'err' ;?>"> 
                    ペンネーム
                        <input class="p-form-input" type="text" name="nickName" value="<?PHP echo $dbFormData['nickName']; ?>">
                    </label>
                    <div class="area-msg">
                        <?php
                        if(!empty($err_msg['nickName'])) echo $err_msg['nickName'];
                        ?>
                    </div>
                    <!--
                    <label class="<?php if(!empty($err_msg['like_j'])) echo 'err' ;?>">
                    ジャンル
                        <input class="p-form-input" type="text" name="like_j" value="<?PHP echo $dbFormData['like_j']; ?>">
                    </label>
                    <div class="area-msg">
                        <?php
                        if(!empty($err_msg['like_l'])) echo $err_msg['like_j'];
                        ?>
                    </div>
                    -->
                    <label class="<?php if(!empty($err_msg['category_id'])) echo 'err'; ?>">
                    ジャンル
                    <select name="like_j" id="">
                        <option value="0" <?php if($dbFormData['like_j'] == 0 ){ echo 'selected'; } ?> >選択してください</option>
                        <?php
                        foreach($dbCategoryData as $key => $val){
                        ?>
                        <option value="<?php echo $val['categoryId'] ?>" <?php if($dbFormData['like_j'] == $val['categoryId'] ){ echo 'selected'; } ?> >
                            <?php echo $val['categoryName']; ?>
                        </option>
                        <?php
                        }
                        ?>
                    </select>
                    </label>
                    <label class="<?php if(!empty($err_msg['profComment'])) echo 'err' ;?>">
                    コメント
                        <textarea class="p-form-textArea" name="profComment" cols="50" rows="10" ><?PHP echo $dbFormData['profComment']; ?></textarea>
                    </label>
                    <div class="area-msg">
                        <?php
                        if(!empty($err_msg['profComment'])) echo $err_msg['profComment'];
                        ?>
                    </div>
                    <input id="submit-btn" type="submit" value="決定">
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

         <!-- footer -->
         <?php
        require('footer.php');
        ?>
</body>