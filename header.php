<header id="header" class="header">
        <h1><a href="index.php">マンガ部！！</a></h1>
        <form method="get" action="" class="search_container">
            <input type="text" placeholder="パイセンを探す">
            <input type="submit" value="&#xf002">
        </form>
        <nav id="top-nav" class="top-nav">
            <ul>
            <?php
            if(empty($_SESSION['user_id'])){
            ?>
            <li><a class="header-btn" href="signup.php">入部する</a></li>
            <li><a class="header-btn" href="login.php">ログイン</a></li>
            <?php
            }elseif(!empty($_SESSION['user_id'])){
                ?>
                <li><a class="header-btn" href="mypage.php">マイページ</a></li>
                <li><a class="header-btn" href="logout.php">ログアウト</a></li>
            <?php
            }
            ?>
            </ul>   
        </nav>
</header>