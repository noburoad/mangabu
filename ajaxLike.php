<?php
require('function.php');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「「「AlaxLike');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//==========================================
//Ajax処理
//==========================================

//postがありユーザーIDがあり、ログインしている場合
if(isset($_POST['mantorid']) && isset($_SESSION['user_id']) && isLogin()){
    debug('POSt送信があります');
    $m_id = $_POST['mantorid'];
    debug('パイセンID:'.$m_id);

    try {
        $dbh = dbConnect();
        //レコードがあるか検索
        $sql = 'SELECT * FROM follow WHERE mantor_id = :m_id AND user_id = :u_id ';
        $data = array(':m_id' => $m_id ,':u_id' => $_SESSION['user_id']);
        $stmt = queryPost($dbh,$sql,$data);

        $resultCount = $stmt->rowCount();
        debug($resultCount);
        //レコードが1件でもある場合
        if(!empty($resultCount)){
            //レコードの削除
            $sql = 'DELETE FROM follow WHERE mantor_id = :m_id AND user_id = :u_id';
            $data = array(':m_id' => $m_id,':u_id' => $_SESSION['user_id']);
            $stmt = queryPost($dbh,$sql,$data);
        }else{
            //レコードを挿入する
            $sql = 'INSERT INTO follow(mantor_id,user_id,create_date) VALUES (:m_id,:u_id,:c_date)';
            $data = array(':m_id' => $m_id, ':u_id' => $_SESSION['user_id'], ':c_date' => date('Y-m-d H:i:s'));
            $stmt = queryPost($dbh,$sql,$data);
        }
    }catch(Exeption $e) {
        error_log('エラー発生:'.$e->getMessage());
    }
}
debug('Ajax処理の終了');

?>