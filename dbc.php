<?php
//①DBから全データを取得
//-PDO::query(sql)を使おう
//②foreachで表示
//-出力はエスケープする

define('DB_HOST','localhost');
define('DB_USER','animal3');
define('DB_PASS','animal30219');
define('DB_NAME','animal-app3');

    //データベースに接続
try{

    $option = array(
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::MYSQL_ATTR_MULTI_STATEMENTS => false
    );
    $pdo = new PDO('mysql:charset=UTF8;dbname='.DB_NAME.';host='.DB_HOST,DB_USER,DB_PASS,$option);

}catch(PDOException $e) {

    //接続エラーの時エラー内容を取得する
    $error_message[] = $e->getMessage();
}

// function dbc()
// {
    

//     $dns = "mysql:host=$host;dbname=$dbname;charset=utf8";

//     try {
//         $pdo = new PDO($dns, $user, $pass,
//             [
//                 PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
//                 PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
//             ]);
//         return $pdo;
//     } catch (PDOException $e) {
//         exit($e->getMessage());
//     }

// }

/**
 * ファイルデータを保存
 * @param string $filename ファイル名
 * @param string $save_path 保存先のパス
 * @return bool $result
 */
function fileSave($filename, $save_path)
{
    $result = false;

    $sql = "INSERT INTO file_table(file_name,file_path)VALUES(?,?)";

    try {
        $stmt = dbc()->prepare($sql); //SQLの準備

        $stmt->bindValue(1, $filename); //？に三つ入れる
        $stmt->bindValue(2, $save_path);

        $result = $stmt->execute(); //SQL文を実行
        return $result;

    } catch (\Exception $e) {
        echo $e->getMessage();
        return $result;
    }
}

/**
 * ファイルデータを保存
 * @return array $fileData
 */

function getAllFile()
{
    $sql = "SELECT * FROM file_table order by id desc";

    $fileData = dbc()->query($sql);

    return $fileData;
}

function h($s)
{
    return htmlspecialchars($s, ENT_QUOTES, "UTF-8");
}