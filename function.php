<?php
require('config.php');
require('msg.php');
//ログをとるか
ini_set('log_errors','on');
//ログの出力ファイルを指定
ini_set('error_log','php.log');

//デバッグフラグ
$debug_flg = true;
//デバッグ関数
function debug($str){
    global $debug_flg;
    if(!empty($debug_flg)){
       error_log('デバッグ:'.$str);
    }
}
//==========================
//セッションの有効期限を伸ばす
//===========================
//セッションファイル置き場を変更
session_save_path("/var/tmp");
//ガーベージコレクションが削除する有効期限を設定
ini_set('session.gc_maxlifetime',60*60*24*30);
//ブラウザが閉じても削除されないようにクッキー自体の有効期限を伸ばす
ini_set('session.cookie_lifetime',60*60*24*30);

session_start();
//現在のセッシオンIDを新しく生成したものと置き換える（なりすましのセキュリティ対策）
session_regenerate_id();

//====================
//画面表示処理開始ログ吐き出し関数
//======================
function debugLogStart(){
    debug('>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>画面表示処理開始');
    debug('セッションID:'.session_id());
    debug('セッション変数の中身'.print_r($_SESSION,true));
    debug('現在日時タイムスタンプ'.time());
    if(!empty($_SESSION['login_date']) && !empty($_SESSION['login_limit'])){
        debug('ログイン期限日時タイムスタンプ:'.($_SESSION['login_date'] + $_SESSION['login_limit']));
    }
}


//エラーメッセージ格納用の配列
$err_msg = array();
//バリデーション関数
//未入力チェック
function validRequired($str,$key){
    if(empty($str)){
        global $err_msg;
        $err_msg[$key] = MSG01;
    }
}
//Email形式チェック
function validEmail($str,$key){
    if(!preg_match("/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/",$str)){
        global $err_msg;
        $err_msg[$key] = MSG02;
    }
}
//Email重複チェック
function validEmailDup($email){
    global $err_msg;
    //例外処理
    try{
        //DBへ接続
        $dbh = dbConnect();
        //SQL文作成
        $sql = 'SELECT count(*) FROM users WHERE email = :email AND delete_flg = 0';
        $data  = array(':email' => $email);
        //クエリ実行
        $stmt = queryPost($dbh,$sql,$data);
        //クエリ結果の値を取得
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if(!empty(array_shift($result))){
            $err_msg['email'] = MSG08;
        }
    }catch(Exeption $e){
        error_log('エラー発生'.$e->getMessage());
        $err_msg['common'] = MSG07;
    }
}
//パスワード同値チェック
function validMatch($str1,$str2,$key){
    if($str1 !== $str2){
        global $err_msg;
        $err_msg[$key] = MSG03;
    }
}
//最小文字数チェック
function validMinLen($str,$key,$min = 6){
    if(mb_strlen($str) < $min){
    global $err_msg;
    $err_msg[$key] = MSG05;
    }
}
//最大文字数チェック
function validMaxLen($str,$key,$max = 255){
    if(mb_strlen($str) > $max){
        global $err_msg;
        $err_msg[$key] = MSG06;
    }
}
//半角チェック
function validHalf($str,$key){
    if(!preg_match("/^[a-zA-Z0-9]+$/",$str)){
        global $err_msg;
        $err_msg[$key] = MSG04;
    }
}
//半角数字チェック
function validNumber($str,$key){
    if(!preg_match("/^[0-9]+$/",$str)){
        global $err_msg;
        $err_msg[$key] = MSG10;
    }
}
//固定長チェック
function validLength($str,$key,$len = 8){
    if(mb_strlen($str) !== $len){
        global $err_msg;
        $err_msg[$key] = $len.MSG15;
    }
}
//セレクトボックスチェック
function validSelect($str,$key){
    if(!preg_match("/^[0-9]+$/",$str)){
        global $err_msg;
        $err_msg[$key] = MSG09;
    }
}
//パスワードチェック
function validPass($str,$key) {
    validHalf($str,$key);
    validMaxLen($str,$key);
    validMinLen($str,$key);
}
//DB接続関数
function dbConnect(){
    //DBへの接続準備

    $dsn = 
    $user = 
    $password = 
    $options = array(
        //SQL実行失敗時にはエラ〜コードのみ設定
        PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING,
        //デフォルトフェッチモードを連想配列形式に設定
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        //バッファードクエリを使う(一度に結果セットを全て取得し、サーバー負荷を軽減)
        //SELECTで得た結果に対してもrowCountメソッドを使えるようにする
        PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
    );
    //PDOオブジェクト生成(DBへ接続)
    $dbh = new PDO($dsn,$user,$password,$options);
    return $dbh;
}

//SQL実行関数
function queryPost($dbh,$sql,$data){
    //クエリ作成
    $stmt = $dbh->prepare($sql);
    //プレースホルダーに値をセットし、SQL文を実行
    if(!$stmt->execute($data)){
        debug('クエリに失敗しました');
        debug('失敗したSQL:'.print_r($stmt,true));
        $err_msg['common'] = MSG07;
        return 0;
    }
    debug('クエリ成功');
    return $stmt;
}

//ユーザー情報を取得する関数
function getUser($u_id){
    debug('ユーザー情報を取得します。');
    //例外処理
    try {
        $dbh = dbConnect();
        $sql = 'SELECT * FROM users WHERE id = :u_id AND delete_flg = 0';
        $data = array(':u_id' => $u_id);
        //クエリ実行
        $stmt = queryPost($dbh,$sql,$data);

        //クエリ結果のデータを１レコード返却
        if($stmt){
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }else{
            return false;
        }
    }catch(Exeption $e){
        error_log('エラー発生'. $e->getMessage());
    }
}
//メンター情報を取得
function getMyMantor($u_id) {
    debug('メンター情報を取得します。');
    debug('ユーザー ID:'.print_r($u_id,true));
    //例外処理
    try {
        $dbh = dbConnect();
        $sql = 'SELECT * FROM mantor WHERE u_id = :u_id AND delete_flg = 0';
        $data = array(':u_id' => $u_id);
        $stmt = queryPost($dbh,$sql,$data);

        if($stmt){
            //結果を全レコード返す
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }else{
            return false;
        }
    }catch(Exeption $e){
        error_log('エラー発生:'.$e->getMessage());
    }
}
//プロフ画像取得
function getProfImg($u_id){
    debug('プロフィール画像を取得します。');
    debug('ユーザーID:'.print_r($u_id,true));
    try{
        $dbh = dbConnect();
        $sql = 'SELECT pic FROM users WHERE id = :$u_id AND delete_flg = 0';
        $data = array(':$u_id' => $u_id);
        $stmt = queryPost($dbh,$sql,$data);
        if($stmt){
            return $stmt->fetchColumn();
        }else{
            false;
        }
    }catch(Exeption $e){
        error_log('エラー発生:'.$e->getMessage());
    }
}
//ニックネーム取得
function getNickName($u_id){
    debug('ニックネームを取得します。');
    debug('ユーザーID:'.print_r($u_id,true));
    try{
        $dbh = dbConnect();
        $sql = 'SELECT nickName FROM users WHERE id = :$u_id AND delete_flg = 0';
        $data = array(':$u_id' => $u_id);
        $stmt = queryPost($dbh,$sql,$data);
        if($stmt){
            return $stmt->fetchColumn();
        }else{
            false;
        }
    }catch(Exeption $e){
        error_log('エラー発生:'.$e->getMessage());
    }
}
//メンターIDの取得
function getMantorId($u_id){
    debug('メンターIDを取得します。');
    debug('ユーザーID:'.print_r($u_id,true));
    try{
        $dbh = dbConnect();
        $sql = 'SELECT mantorId FROM mantor WHERE u_id = :$u_id AND delete_flg = 0';
        $data = array(':$u_id'=>$u_id);
        $stmt = queryPost($dbh,$sql,$data);
        if($stmt){
            return $stmt->fetchColumn();
        }else{
            false;
        }
    }catch(Exeption $e){
        error_log('エラー発生:'.$e->getMessage());
    }
}
//カテゴリーネーム取得
function getCategoryName($c_id){
    debug('カテゴリー名を取得します。');
    debug('カテゴリーID:'.print_r($c_id,true));
    try {
        $dbh = dbConnect();
        $sql = 'SELECT categoryName FROM category WHERE categoryId = :c_id AND delete_flg = 0';
        $data = array(':c_id'=> $c_id);
        $stmt = queryPost($dbh,$sql,$data);
        if($stmt){
            return $stmt->fetchColumn();
        }else{
            return false;
        }
    }catch(Exeption $e){
        error_log('エラー発生:'.$e->getMessage());
    }
}
//ページネーション用
function getMantorList($currentMinNum = 1, $category,$sort,$span = 4){
    debug('メンター情報を一覧表示');
    //例外処理
    try {
        $dbh = dbConnect();
        $sql = 'SELECT mantorId FROM mantor';
        //件数、条件によりSQL追加
        if(!empty($category)) $sql .= ' WHERE category_id = '.$category;
        if(!empty($sort)){
            switch($sort){
                case 1:
                    $sql .= ' ORDER BY price ASC';
                break;
                case 2:
                    $sql .= ' ORDER BY price DESC';
                break;
            }
        }
        $data = array();
        //クエリ実行
        $stmt = queryPost($dbh,$sql,$data);
        $rst['total'] = $stmt->rowCount();//総レコード数
        $rst['total_page'] = ceil($rst['total']/$span);//そうページ数
        if(!$stmt){
            return false;
        }

        //ページング用のSQL
        $sql = 'SELECT * FROM mantor ';
        if(!empty($category)) $sql .= ' WHERE category_id = '.$category;
        if(!empty($sort)){
            switch($sort){
                case 1:
                    $sql .= ' ORDER BY price ASC';
                break;
                case 2:
                    $sql .= ' ORDER BY price DESC';
                break;
            }
        }
        $sql .= ' LIMIT '.$span.' OFFSET '.$currentMinNum;
        $data = array();
        debug('SQL:'.$sql);
        //クエリ実行
        $stmt = queryPost($dbh,$sql,$data);

        if($stmt){
            //クエリ結果のデータを全レコード格納
            $rst['data'] = $stmt->fetchAll();
            return $rst;
        }else{
            return false;
        }
    }catch(Exeption $e){
        error_log('エラー発生:'.$e->getMessage());
    }
}
//エラ〜メッセージ取得
function getErrMsg($key) {
    global $err_msg;
    if(!empty($err_msg[$key])) {
        return $err_msg[$key];
    }
}
//サニタイズ
function sanitize($str){
    return htmlspecialchars($str,ENT_QUOTES);
}
//フォーム入力保持
function getFormData($str,$flg = false){
    if($flg){
        $method = $_GET;
    }else{
        $method = $_POST;
    }
    global $dbFormData;
    //ユーザーデータがある場合
    if(!empty($dbFormData)){
        //フォームのエラーがある場合
        if(!empty($err_msg[$str])){
            //POSTにデータがある場合
            if(isset($method[$str])){
                return sanitize($method[$str]);
            }
        }else{
            //POSTにデータがあり、DBの情報と違う場合
            if(isset($method[$str]) && $method[$str] !== $dbFormData[$str]){
                return sanitize($method[$str]);
            }else{
                return sanitize($dbFormData[$str]);
            }
        }
    }else{
        if(isset($method[$str])){
            return sanitize($method[$str]);
        }
    }
}
//メール送信
function sendMail($from,$to,$subject,$comment) {
    if(!empty($to) && !empty($subject) && !empty($comment)) {
        //文字化けしないよう(おきまりパターン)
        mb_language("japanese");
        mb_internal_encoding("UTF-8");

        //メールを送信(結果はtrueかfalseで帰ってくる)
        $result = mb_send_mail($to,$subject,$comment,"From: ".$from);
        //送信結果を判定
        if($result) {
            debug('メールを送信しました');
        }else{
            debug('エラー発生、メールの送信に失敗しました。');
        }
    }
}
//SESSIONを１回だけ取得
function getSessionFlash($key) {
    if(!empty($_SESSION[$key])){
        $data = $_SESSION[$key];
        $_SESSION[$key] = '';
        return $data;
    }
}
//認証キーを作成
function makeRandKey($length = 8) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $str = '';
    for($i = 0; $i < $length; ++$i){
        $str .= $chars[mt_rand(0,61)];
    }
    return $str;
}
//画像アップロード処理
function uploadImg($file,$key){
    debug('画像アップロード処理開始');
    debug('FILE情報：'.print_r($file,true));

    if(isset($file['error']) && is_int($file['error'])){
        try{
            //バリデーション
            //$file['error']の値を確認。配列内には「UPLOAD_ERR_OK」などの定数が入っている
            //「UPLOAD_ERR_OK」などの定数はphpでファイルアップロード時に自動的に定義される。定数には値として0や1が入っている
            switch($file['error']){
                case UPLOAD_ERR_OK:
                    break;
                case UPLOAD_ERR_NO_FILE: //ファイル未選択の場合
                    throw new RuntimeExeption('ファイルが選択されていません');
                case UPLOAD_ERR_INI_SIZE://php.ini定義の最大サイズを超過した場合
                case UPLOAD_ERR_FORM_SIZE://フォーム定義の最大サイズを超過した場合
                    throw new RuntimeExeption('ファイルサイズが大きすぎます');
                default: //その他の場合
                    throw new RuntimeExeption('その他のエラーが発生しました');
            }
            //$file['mime']の値はブラウザ側で偽装可能なので、MIMEタイプを自前でチェックする
            //exif_imagetype関数は「IMAGETYPE_GIF」 「IMAGETYPE_JPEG」などの定数を返す
            $type = @exif_imagetype($file['tmp_name']);
            if(!in_array($type,[IMAGETYPE_GIF,IMAGETYPE_JPEG,IMAGETYPE_PNG],true)){ //第三引数にはtrueを設定すると厳密にチェックしてくれる
                throw new RuntimeExeption('画像形式が未対応です');
            }
            //ファイルデータからSHA-1ハッシュをとってファイル名を決定し、ファイルを保存する
            //ハッシュ化しておかないとアップロードされたファイル名そのまま保存してしまうと同じファイル名の画像がアップされる可能性あり
            //DBにパスを保存した場合、どっちの画像のパスなのか判断がつかなくなってしまう
            //image_type_to_extension関数はファイルの拡張子を取得するモノ
            $path = 'uploads/'.sha1_file($file['tmp_name']).image_type_to_extension($type);
            if(!move_uploaded_file($file['tmp_name'],$path)){//ファイルを移動する
                throw new RuntimeExeption('ファイル保存時にエラーが発生しました');
            }
            //保存したファイルパスのパーミッション（権限）を変更する
            chmod($path,0644);

            debug('ファイルは正常にアップされました');
            debug('ファイルパス:'.$path);
            return $path;
        }catch(RuntimeExeption $e){
            debug($e->getMessage());
            global $err_msg;
            $err_msg[$key] = $e->getMessage();
        }
    }
}
//メンター情報を取得する関数
function getMantor($u_id,$m_id){//getProduct関数の応用
    debug('メンター情報を取得します');
    debug('ユーザーID:'.$u_id);
    debug('メンターID:'.$m_id);
    //例外処理
    try { 
        //DBへ接続
        $dbh = dbConnect();
        $sql = 'SELECT * FROM mantor WHERE u_id = :u_id AND mentorId = :m_id AND delete_flg = 0';
        $data = array(':u_id' => $u_id,':m_id' => $m_id);
        //クエリ実行
        $stmt = queryPost($dbh,$sql,$data);

        if($stmt){
            //クエリ結果のデータを１レコード返却
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }else{
            return false;
        }
    }catch(Exeption $e){
        error_log('エラー発生'.$e->getMessage());
    }
}
//メンター詳細取得用関数
function getMantorOne($m_id){
    debug('メンター詳細を取得します。');
    debug('メンターID:'.print_r($m_id,true));
    try {
        $dbh = dbConnect();
        $sql = 'SELECT m.mantorId,m.nickName,m.prof_pic,m.category_id,m.mantorTitle,m.mantorComment,m.price,m.pic1,m.pic2,m.pic3,m.u_id,c.categoryName AS category
        FROM mantor AS m LEFT JOIN category AS c ON m.category_id = c.categoryId WHERE m.mantorId = :m_id AND m.delete_flg = 0 AND c.delete_flg = 0';
        $data = array(':m_id' => $m_id);
        $stmt = queryPost($dbh,$sql,$data);

        if($stmt){
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }else{
            return false;
        }
    }catch(Exeption $e){
        error_log('エラー発生:'.$e->getMessage());
    }
}
//プロフィール情報の取得
function getProfData($u_id){
    try { 
        //DBへ接続
        $dbh = dbConnect();
        $sql = 'SELECT id,nickName,like_j,profComment,pic FROM users WHERE id = :u_id AND  delete_flg = 0';
        $data = array(':u_id' => $u_id);
        //クエリ実行
        $stmt = queryPost($dbh,$sql,$data);

        if($stmt){
            //クエリ結果のデータを１レコード返却
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }else{
            return false;
        }
    }catch(Exeption $e){
        error_log('エラー発生'.$e->getMessage());
    }
}

//カテゴリー情報を取得する関数
function getCategory(){
    debug('カテゴリー情報を取得します');
    //例外処理
    try{
        $dbh = dbConnect();
        $sql = 'SELECT * FROM category';
        $data = array();
        $stmt = queryPost($dbh,$sql,$data);

        if($stmt){
            return $stmt->fetchAll();
        }else{
            return false;
        }
    }catch(Exeption $e){
        error_log('エラー発生'.$e->getMessage());
    }
}
//掲示板用の関数
function getMsgsAndBord($id){
    debug('msg情報を取得します');
    debug('掲示板ID:'.$id);
    try {
        $dbh = dbConnect();
        $sql = 'SELECT ms.id AS ms_id,b.mantorId,bord_id,send_date,to_user,from_user,sale_user,buy_user,msg,b.create_date FROM b_message AS ms RIGHT JOIN bord AS b ON b.id = ms.bord_id WHERE b.id = :id ORDER BY send_date ASC';
        $data = array(':id'=> $id);
        $stmt = queryPost($dbh,$sql,$data);

        if($stmt){
            return $stmt->fetchAll();
        }else{
            return false;
        }
    }catch(Exeption $e){
        error_log('エラー発生:'.$e->getMessage());
    }
}
//掲示板の自分のメッセージ取得
function getMyMsgsAndBord($u_id) {
    debug('自分のmsg情報を取得します');
    try {
        $dbh = dbConnect();
        //まず掲示板レコード取得
        $sql = 'SELECT * FROM bord AS b WHERE b.sale_user = :id OR b.buy_user = :id AND b.delete_flg = 0';
        $data = array(':id'=> $u_id);
        $stmt = queryPost($dbh,$sql,$data);
        $rst = $stmt->fetchAll();
        if(!empty($rst)){
            foreach($rst as $key => $val){
                $sql = 'SELECT * FROM b_message WHERE bord_id = :id AND delete_flg = 0 ORDER BY send_date DESC';
                $data = array(':id' => $val['id']);
                $stmt = queryPost($dbh,$sql,$data);
                $rst[$key]['msg'] = $stmt->fetchAll();
            }
        }
        if($stmt){
            return $rst;
        }else{
            return false;
        }
    }catch(Exeption $e){
        error_log('エラー発生'.$e->getMessage());
    }
}
//自分のフォローしているパイセンを取得
function getMyfollow($u_id) {
    debug('自分のフォロー情報を取得します');
    debug('ユーザーID');
    try {
        $dbh = dbConnect();
        $sql = 'SELECT * FROM follow AS f LEFT JOIN mantor AS m ON f.mantor_id = m.mantorId WHERE f.user_id = :u_id';
        $data = array(':u_id' => $u_id);
        $stmt = queryPost($dbh,$sql,$data);
        if($stmt) {
            return $stmt->fetchAll();
        }else{
            return false;
        }
    }catch (Exeption $e){
        error_log('エラー発生'.$e->getMessage());
    }
}
//GETパラメータの付与
function appendGetParam($arr_del_key = array()) {
    if(!empty($_GET)) {
        $str = '?';
        foreach($_GET as $key => $val){
            if(!in_array($key,$arr_del_key,true)){
                $str .= $key.'='.$val.'&';
            }
        }
        $str = mb_substr($str,0,-1,"UTF-8");
        return $str;
    }
}
//画像表示用
function showImg($path){
    if(empty($path)){
        return 'img/no-img.png';
    }else{
        return $path;
    }
}
//ページング
//$currentPageNum : 現在のページ数
//$totalPageNum : 総ページ数
//$link : 検索用GETパラメータリンク
//$pageColNum : ページネーション表示数
function pagination($currentPageNum,$totalPageNum,$link = '', $pageColNum = 5){
    //現在のページが総ページ数と同じ、かつ、総ページ数が表示項目数異常なた、左にリンク４個出す
    if($currentPageNum == $totalPageNum && $totalPageNum > $pageColNum){
        $minPageNum = $currentPageNum - 4;
        $maxPageNum = $currentPageNum;
    //現在のページ数が、総ページ数の１ページ前なら左にリンク３個、右に１個出す
    }elseif($currentPageNum == ($totalPageNum - 1) && $totalPageNum > $pageColNum){
        $minPageNum = $currentPageNum - 3;
        $maxPageNum = $currentPageNum + 1;
    //現在のページが２なら左に１個、右に3個だす
    }elseif($currentPageNum == 2 && $totalPageNum > $pageColNum){
        $minPageNum = $currentPageNum  -2;
        $maxPageNum = $currentPageNum +3;
    //現在のページが1の場合は左に何も出さずに右に４個だす
    }elseif($currentPageNum == 1 && $totalPageNum > $pageColNum){
        $minPageNum = $currentPageNum;
        $maxPageNum = $currentPageNum + 4;
    //総ページ数が表示項目より少ない時はそうページ数をループのmax、ループのminを1に設定
    }elseif($totalPageNum < $pageColNum){
        $minPageNum = 1;
        $maxPageNum = $totalPageNum;
    //それ以外は左右に2個出す
    }else{
        $minPageNum = $currentPageNum -2;
        $maxPageNum = $currentPageNum +2;
    }
    echo '<div class="pagination">';
        echo '<ul class="pagination-list">';
        if($currentPageNum != 1){
            echo '<li class="list-item"><a href="?p=1'.$link.'">&lt;</a></li>';
        }
        for($i = $minPageNum; $i <= $maxPageNum; $i++){
            echo '<li class="list-item ';
            if($currentPageNum == $i){echo 'active';}
            echo '"><a href="?p='.$i.$link.'">'.$i.'</a></li>';
        }
        if($currentPageNum != $maxPageNum && $maxPageNum > 1){
            echo '<li class="list-item"><a href="?p='.$maxPageNum.$link.'">&gt;</a></li>';
        }
        echo '</ul>';
    echo '</div>';
}
//お気に入り用
function isLike($u_id,$m_id) {
    debug('フォロー情報があるか確認します');
    debug('ユーザーID:'.$u_id);
    debug('パイセンID:'.$m_id);
    try {
        $dbh = dbConnect();
        $sql = 'SELECT * FROM follow WHERE mantor_id = :m_id AND user_id = :u_id';
        $data = array(':m_id'=> $m_id, ':u_id' => $u_id);
        $stmt = queryPost($dbh,$sql,$data);

        if($stmt->rowCount()){
            debug('フォロー済みです');
            return true;
        }else{
            debug('未フォローです');
            return false;
        }
    }catch(Exeption $e){
        error_log('エラー発生:'.$e->getMessage());
    }
}
//フォロー数取得
function getFollow($u_id) {
    debug('フォロー数を取得します');
    debug('パイセンのユーザーID:'.$u_id);
    try {
        $dbh = dbConnect();
        $sql = 'SELECT user_id FROM follow WHERE user_id = :u_id';
        $data = array(':u_id' => $u_id);
        $stmt = queryPost($dbh,$sql,$data);
        $resultCount = $stmt->rowCount();
        debug('フォロー数:'.$resultCount);
        return $resultCount;
    }catch (Exeption $e){
        error_log('エラー発生'.$e->getMessage());
    }
}
//フォロワー数取得
function getFollower($m_id){
    debug('フォロワー数を取得します');
    debug('パイセンのメンターID:'.$m_id);
    try {
        $dbh = dbConnect();
        $sql = 'SELECT mantor_id FROM follow WHERE mantor_id = :m_id';
        $data = array(':m_id' => $m_id);
        $stmt = queryPost($dbh,$sql,$data);
        $resultCount = $stmt->rowCount();
        debug('フォロワー数:'.$resultCount);
        return $resultCount;
    }catch(Exeption $e){
        error_log('エラー発生:'.$e->getMessage());
    }
}
//ログイン認証
function isLogin(){
    //ログインしている場合
    if(!empty($_SESSION['login_date'])){
        debug('ログイン済みユーザーです');

        //現在日時が最終ログイン日時+有効期限を超えていた場合
        if(($_SESSION['login_date'] + $_SESSION['login_limit']) < time()){
            debug('ログイン有効期限オーバーです');

            //セッションを削除
            session_destroy();
            return false;
        }else{
            debug('ログイン有効期限以内です');
            return true;
        }
    }else{
        debug('未ログインユーザーです');
        return false;
    }
}
//フォローしているパイセンを取得
function getMyFollowMantor($u_id) {
    debug('フォローしているパイセンを取得します');
    try {
        $dbh = dbConnect();
        
        $sql = 'SELECT mantor_id FROM follow WHERE user_id = :u_id';
        $data = array(':u_id' => $u_id);
        $stmt = queryPost($dbh,$sql,$data);
        $rst = $stmt->fetchAll();

        if(!empty($rst)){
            foreach($rst as $key => $val){
            $sql = 'SELECT * FROM mantor WHERE mantorId = :m_id';
            $data = array(':m_id'=>$val['mantor_id']);
            $stmt = queryPost($dbh,$sql,$data);
            $rst[$key] = $stmt->fetch(PDO::FETCH_ASSOC);
            }
        }
        if($rst){
            return $rst;          
        }else{
            return false;
        }
    }catch(Exeption $e){
        error_log('エラー発生'.$e->getMessage());
    }
}
//掲示板の相手の名前取得
function getBordPartnerName($sale_user){
    debug('掲示板相手の名前を取得します');

    try {
        $dbh = dbConnect();
        $sql = 'SELECT nickName FROM users WHERE id = :u_id';
        $data = array(':u_id'=>$sale_user);
        $stmt = queryPost($dbh,$sql,$data);
        $rst= $stmt->fetchColumn();
        if($rst){
            return $rst;
        }else{
            return false;
        }
    }catch(Exeption $e){
        error_log('エラー発生'.$e->getMessage());
    }
}
//教えている後輩を取得
function getMyTeachUser($u_id){
    debug('教えている後輩を取得します');
    try {
        $dbh = dbConnect();
        $sql = 'SELECT buy_user FROM bord WHERE sale_user = :u_id';
        $data = array(':u_id'=> $u_id);
        $stmt = queryPost($dbh,$sql,$data);
        $rst = $stmt->fetchAll();
        if(!empty($rst)) {
            foreach($rst as $key => $val){
                $sql = 'SELECT * FROM users WHERE id = :u_id';
                $data = array(':u_id'=> $val['buy_user']);
                $stmt = queryPost($dbh,$sql,$data);
                $rst[$key] = $stmt->fetch(PDO::FETCH_ASSOC); 
            }
        }
        if($rst){
            return $rst;
        }else{
            return false;
        }
    }catch(Exeption $e){
        error_log('エラー発生'.$e->getMessage());
    }
}

?>
