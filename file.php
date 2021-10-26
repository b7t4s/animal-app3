<?php
//①ファイルの保存
//②DB接続
//③DBへの保存

require_once './dbc.php';
define('DB_HOST','localhost');
define('DB_USER','animal3');
define('DB_PASS','animal30219');
define('DB_NAME','animal-app3');


//ファイル関連の取得
// $file = $_FILE['img'];
$file = $_FILES['img'];
$filename = basename($file['name']); //1ファイルの保存
$tmp_path = $file['tmp_name'];
$file_err = $file['error'];
$filesize = $file['size'];
$upload_dir = 'images/'; //アップロード先のファイルパスを決める
$save_filename = date('YmdHis') . $filename; //ファイル名を変えてあげる
$err_msgs = array();
$save_path = $upload_dir . $save_filename;

// ===== デバッグコード =====
// echo '$file =>';
// echo $file;
// echo '<br>';

// echo '$filename =>';
// echo $filename;
// echo '<br>';

// echo '$tmp_path =>';
// echo $tmp_path;
// echo '<br>';

// echo '$file_err =>';
// echo $file_err;
// echo '<br>';

// echo '$filesize =>';
// echo $filesize;
// echo '<br>';

// echo '$upload_dir =>';
// echo $upload_dir;
// echo '<br>';

// echo '$save_filename =>';
// echo $save_filename;
// echo '<br>';

// echo '$save_path =>';
// echo $save_path;
// echo '<br>';
// ===== デバッグコード =====

//キャプションを取得
// $caption = filter_input(INPUT_POST, 'caption', FILTER_SANITIZE_SPECIAL_CHARS);

//キャプションのバリデーション
//未入力かどうか調べる
// if (empty($caption)) {
//     array_push($err_msgs, 'キャプションを入力してください。');
// }
//140字以内か
// if (strlen($caption) > 140) {
//     array_push($err_msgs, 'キャプションは140字以内で入力してください。');
// }

//ファイルのバリデーション
//ファイルサイズが2MB未満か
if ($filesize > 2097152 || $file_err == 2) {
    array_push($err_msgs, 'ファイルサイズは2MB未満にしてください。');
}

//拡張子は画像形式か
$arrow_ext = array('jpg', 'jpeg', 'png');
$file_ext = pathinfo($filename, PATHINFO_EXTENSION);

if (!in_array(strtolower($file_ext), $arrow_ext)) {
    array_push($err_msgs, '画像ファイルを添付してください。');
}

//もし$err_msgsの配列に何も値が入ってなかったら、0だった場合ファイルを保存する処理を使う
if (count($err_msgs) === 0) {
    //ファイルはあるかどうか
    if (is_uploaded_file($tmp_path)) {
        if (move_uploaded_file($tmp_path, $save_path)) {
            echo $filename . 'を' . $upload_dir . 'アップしました。';
            //DBに保存（ファイル名、ファイルパス、キャプション)
            $result = fileSave($filename, $save_path,$message);

            if ($result) {
                echo 'データベースに保存しました。';
                // リダイレクト
                header("Location: ./index.php");
            } else {
                echo 'データベースへの保存が失敗しました。';
            }
        } else {
            echo 'ファイルが保存できませんでした。';
        }
    } else {
        echo 'ファイルが選択されていません。';
        echo '<br>';
    }
} else {
    foreach ($err_msgs as $msg) {
        echo $msg;
        echo '<br>';
    }
}

?>

<a href="./animal.php">戻る</a>