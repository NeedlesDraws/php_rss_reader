<?php
libxml_use_internal_errors(true);

//RSS読み込み・解析
$rss = simplexml_load_file('http://blog.fc2.com/newentry.rdf', 'SimpleXMLElement', LIBXML_NOCDATA);

//RSS解析エラー処理
$xml = explode("\n", 'http://blog.fc2.com/newentry.rdf');
if ($rss == FALSE)
{
    $errors = libxml_get_errors();

    foreach ($errors as $error) {
        echo display_xml_error($error, $xml);
    }

    libxml_clear_errors();
    die("解析できないXML形式がふくまれていました。");
}

//DB接続
$url = "localhost";
$user = "kd036";
$pass = "gQGtv48v";
$db = "kd036";

// MySQLへ接続する
$link = mysql_connect($url,$user,$pass) or die("MySQLへの接続に失敗しました。");
//mysql_set_charset("utf8");
// データベースを選択する
$sdb = mysql_select_db($db,$link) or die("データベースの選択に失敗しました。");

foreach ($rss->item as $item) {
  $dc = $item->children('http://purl.org/dc/elements/1.1/');
  $url = $item->link;

  //ユーザ名、サーバ番号、エントリー番号切り出し
  $urlar = split('[/.-]', $url);
  $user = $urlar[2];
  $server = str_replace('blog', '', $urlar[3]);
  $entry = $urlar[8];

  $title = $item->title;
  $date = $dc->date;
  $desc =$item->description;

  echo $url . ":" . $user .":" . $server . ":" . $entry . "\n";
  echo $title . ":" . $date .":" . $desc . "\n";

  //insert実行
  $sql = "REPLACE INTO rss_table VALUES('$server', '$entry', '$user', '$url', 
                                        '$date', '$title', '$desc')";
  $result = mysql_query($sql);  
}
//delete実行
$sql = "delete from rss_table where date_time < '" .
        date("Y-m-d H:i:s",strtotime("-2 week")) . "';";
$result = mysql_query($sql);
mysql_close($link);

//エラー表示用関数
function display_xml_error($error, $xml)
{
    $return  = $xml[$error->line - 1] . "\n";
    $return .= str_repeat('-', $error->column) . "^\n";

    switch ($error->level) {
        case LIBXML_ERR_WARNING:
            $return .= "Warning $error->code: ";
            break;
         case LIBXML_ERR_ERROR:
            $return .= "Error $error->code: ";
            break;
        case LIBXML_ERR_FATAL:
            $return .= "Fatal Error $error->code: ";
            break;
    }

    $return .= trim($error->message) .
               "\n  Line: $error->line" .
               "\n  Column: $error->column";

    if ($error->file) {
        $return .= "\n  File: $error->file";
    }

    return "$return\n\n--------------------------------------------\n\n";
}
?>
