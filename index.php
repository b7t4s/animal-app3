<?php 

require_once './dbc.php';
$files = getAllFile();

//データベースの接続情報
define('DB_HOST','localhost');
define('DB_USER','animal3');
define('DB_PASS','animal30219');
define('DB_NAME','animal-app3');

//タイムゾーン設定
date_default_timezone_set('Asia/Tokyo');

//変数の初期化
$current_date = null;
$message = array();
$message_array = array();
$error_message = array();
$pdo = null;
$stmt = null;
$res = null;
$option = null;

session_start();

//データベースに接続
try{

    $option = array(
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::MYSQL_ATTR_MULTI_STATEMENTS => false
    );
    $pdo = new PDO('mysql:charset=UTF8;dbname='.DB_NAME.';host='.DB_HOST,DB_USER,DB_PASS,$option);

} catch(PDOException $e) {

    //接続エラーの時エラー内容を取得する
    $error_message[] = $e->getMessage();
}

if(!empty($_POST['btn_submit'])) {

    //空白除去
    $view_name = preg_replace( '/\A[\p{C}\p{Z}]++|[\p{C}\p{Z}]++\z/u', '', $_POST['view_name']);
	$message = preg_replace( '/\A[\p{C}\p{Z}]++|[\p{C}\p{Z}]++\z/u', '', $_POST['message']);

    //名前の入力チェック
    if(empty($view_name)) {
        $error_message[] = '名前を入力してください。';
    }else{

        //セッションに表示名を保存
        $_SESSION['view_name'] = $view_name;
    }

    //メッセージの入力チェック
    if(empty($message)) {
        $error_message[] = 'メッセージを入力してください。';      
    }else{

        //文字数を確認
        if(100 < mb_strlen($message,'UTF-8')) {
            $error_message[] = 'メッセージは１００文字以内で入力してください。';
        }
    }

    if(empty($error_message)) {

        //書き込み日時を取得
        $current_date = date("Y-m-d H:i:s");

        //トランザクション開始
        $pdo->beginTransaction();

        try{

        //SQL作成
        $stmt = $pdo->prepare("INSERT INTO message_board(view_name,message,post_date)VALUES(:view_name,:message,:current_date)");

        //値のセット
        $stmt->bindParam(':view_name',$view_name,PDO::PARAM_STR);
        $stmt->bindParam(':message',$message,PDO::PARAM_STR);
        $stmt->bindParam(':current_date',$current_date,PDO::PARAM_STR);

        //SQLクエリの実行
        $res = $stmt->execute();

        //コミット
        $res = $pdo->commit();

        }catch(Exception $e) {

            //エラーが発生した時はロールバック
            $pdo->rollBack();
        }

        if($res) {
            $_SESSION['success_message'] = 'メッセージを書き込みました。';
        }else{
            $error_message[] = '書き込みに失敗しました。';
        }

        //プリペアドステートメントを削除
        $stmt = null;

        header('Location:./');
        exit;
    }
}

if(!empty($pdo)) {

    //メッセージのデータを取得する
    $sql = "SELECT view_name,message,post_date FROM message_board ORDER BY post_date DESC";
    $message_array = $pdo->query($sql);
}

//データベースの接続を閉じる
$pdo = null;




?>



<!DOCTYPE html>
<html lang="ja">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-giJF6kkoqNQ00vy+HMDP7azOuL0xtbfIcaT9wjKHr8RbDVddVHyTfAAsrekwKmP1" crossorigin="anonymous">

    <!-- オリジナルCSS -->
    <link rel="stylesheet" href="./css/style.css">

    <title>wan.chibi</title>
    
    <link href="images/favicon.ico" rel="icon" type="image/x-icon" />
</head>
<style>
        label {
            display: block;
            margin-bottom: 7px;
            font-size: 86%;
        }
        input[type="text"],
        textarea {
            margin-bottom: 20px;
            padding: 10px;
            font-size: 86%;
            border: 1px solid #ddd;
            border-radius: 3px;
            background: #fff;
        }
        input[type="text"] {
            width: 200px;
        }
        textarea {
            width: 50%;
            max-width: 50%;
            height: 70px;
        }
        input[type="submit"] {
            appearance: none;
            -webkit-appearance: none;
            padding: 10px 20px;
            color: #fff;
            font-size: 86%;
            line-height: 1.0em;
            cursor: pointer;
            border: none;
            border-radius: 5px;
            /* background-color: #37a1e5; */
            background-color: #bdb7ae;
        }
        input[type=submit]:hover,
        button:hover {
            background-color: #ab7474;
        }
        .success_message {
            margin-bottom: 20px;
            padding: 10px;
            color: #48b400;
            border-radius: 10px;
            border: 1px solid #4dc100;
        }
        .error_message {
            margin-bottom: 20px;
            padding: 10px;
            color: #ef072d;
            list-style-type: none;
            border-radius: 10px;
            border: 1px solid #ff5f79;
        }
        .success_message,
        .error_message li {
            font-size: 86%;
            line-height: 1.6em;
        }

        /*-----------------------------------
        掲示板エリア
        -----------------------------------*/
        article {
            margin-top: 20px;
            padding: 20px;
            border-radius: 10px;
            background: #fff;
        }
        article.reply {
            position: relative;
            margin-top: 15px;
            margin-left: 30px;
        }
        article.reply::before {
            position: absolute;
            top: -10px;
            left: 20px;
            display: block;
            content: "";
            border-top: none;
            border-left: 7px solid #f7f7f7;
            border-right: 7px solid #f7f7f7;
            border-bottom: 10px solid #fff;
        }
            .info {
                margin-bottom: 10px;
            }
            .info h2 {
                display: inline-block;
                margin-right: 10px;
                color: #222;
                line-height: 1.6em;
                font-size: 86%;
            }
            .info time {
                color: #999;
                line-height: 1.6em;
                font-size: 72%;
            }
            article p {
                color: #555;
                font-size: 86%;
                line-height: 1.6em;
            }
</style>
<body>
    <!-- ■ ヘッダーエリア -->
    <header>
        <nav class="navbar navbar-expand-lg navbar-light bg-light">
            <div class="container-fluid">
                <!--<a class="navbar-brand">--><img src="images/logo2.png" id="logo"><!--</a>-->
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                    aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                    <ul class="navbar-nav">
                        <li class="nav-item">
                            <a class="nav-link active" aria-current="page" href="#">ホーム</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="contact.php">お問い合わせ</a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <!-- ■ カルーセルエリア -->
    <div id="carouselExampleFade" class="carousel slide carousel-fade" data-bs-ride="carousel">
        <div class="carousel-inner">
            <div class="carousel-item carousel-item-ex active">
                <img src="images/title.png" class="d-block w-100 img-fluid" alt="写真">
            </div>
            <div class="carousel-item carousel-item-ex">
                <img src="images/concept1.png" class="d-block w-100 img-fluid" alt="写真">
            </div>
            <div class="carousel-item carousel-item-ex">
                <img src="images/concept2.png" class="d-block w-100 img-fluid" alt="写真">
            </div>
        </div>
        <a class="carousel-control-prev" href="#carouselExampleFade" role="button" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
        </a>
        <a class="carousel-control-next" href="#carouselExampleFade" role="button" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
        </a>
    </div>

        <?php if(empty($_POST['btn_submit'])&& !empty($_SESSION['success_message'])): ?>
            <p class="success_message"><?php echo htmlspecialchars($_SESSION['success_message'],ENT_QUOTES,'UTF-8'); ?></p>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>
        <?php if(!empty($error_message)): ?>
            <ul class="error_message">
                <?php foreach($error_message as $value): ?>
                    <li>・<?php echo $value; ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    <form method="post" enctype="multipart/form-data">
        <div>
            <label for="view_name">名前</label>
            <input id="view_name" type="text" name="view_name" value="<?php if(!empty($_SESSION['view_name'])){ echo htmlspecialchars($_SESSION['view_name'],ENT_QUOTES,'UTF-8'); } ?>">
        </div>
        <div>
            <label for="message">投稿</label>
            <textarea id="message" name="message"><?php if(!empty($message)) { echo htmlspecialchars($message,ENT_QUOTES,'UTF-8');} ?></textarea>
        </div>
        <div class="file-up">
            <input type="hidden" name="MAX_FILE_SIZE" value="2097152" />
            <input name="img" type="file" accept="image/*" />
        </div>
        <input type="submit" name="btn_submit" value="書き込む">
     </form>
     <hr>
     <section>
        <?php if(!empty($message_array)): ?>
        <?php foreach($message_array as $value): ?>
        <article>
            <div class="info">
                <h2><?php echo htmlspecialchars($value['view_name'],ENT_QUOTES,'UTF-8'); ?></h2>
                <time><?php echo date('Y年m月d日 H:i', strtotime($value['post_date'])); ?></time>
            </div>
            <p><?php echo nl2br(htmlspecialchars($value['message'],ENT_QUOTES,'UTF-8')); ?></p>
        </article>
        <?php endforeach; ?>
        <?php foreach ($files as $file): ?>
            <img src="<?php echo "{$file['file_path']}"; ?>" alt="">
            <p><?php echo h("{$file['caption']}"); ?></p>
        <?php endforeach;?>
        
        <?php endif; ?>
     </section>
     <!-- <div class="card-group">
        <div class="card">
            <img src="..." class="card-img-top" alt="...">
            <div class="card-body">
            <p class="card-text">This is a wider card with supporting text below as a natural lead-in to additional content. This content is a little bit longer.</p>
            <p class="card-text"><small class="text-muted">Last updated 3 mins ago</small></p>
            </div>
        </div>
    </div> -->
    <div style="position: relative; padding-bottom: 56.25%;">
        <iframe 
            style="position: absolute; top: 0; left: 0; width: 100%; height: 100%;" 
            src="https://www.youtube.com/embed/eJ36aZiAiwg" 
            frameborder="0" 
            allow="autoplay; 
            encrypted-media" 
            allowfullscreen>
        </iframe>
    </div>
      <!-- ■ フッターエリア -->
    <footer>
        <div class="container">
            <p class="text-center">© 2021 Copyright: wan.chibi</p>
        </div>
    </footer>

    <!-- Optional JavaScript; choose one of the two! -->

    <!-- Option 1: Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-ygbV9kiqUc6oa4msXn9868pTtWMgiQaeYH7/t7LECLbyPA2x65Kgf80OJFdroafW"
        crossorigin="anonymous"></script>

</body>
</html>
