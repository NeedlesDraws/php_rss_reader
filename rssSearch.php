<?php
require_once("Pager/Pager.php");
require_once("Smarty/libs/Smarty.class.php");

//Smartyクラスのインスタンス生成
$smarty = new Smarty;

//Form:inputのデフォルト値をCOOKIE値に設定
//POST値があれば後でそれで上書きする
foreach( $_COOKIE as $key => $val ){
  $key = mysql_escape_string( $key );
  $val = urldecode(mysql_escape_string( $val ));
  $smarty->assign('c_' . $key, $val);
}

//POST値があれば検索またはページングを行った場合
//であるとみなしてデータ取得を実行する
if (count($_POST) > 0)
{
  // MySQLへの接続設定
  $server = "localhost";
  $dbname = "kd036";
  $user = "kd036";
  $pass = "gQGtv48v";

  // MySQLと接続する
  $conn = mysql_connect($server, $user, $pass) or die("DB接続エラー");
  mysql_select_db($dbname) or die("DB接続エラー");
  
  //エントリー番号のチェックボックス値のチェック
  setcookie('entry_no_chk', '');
  $smarty->assign('c_entry_no_chk', '');
  if (isset($_POST['entry_no_chk']))
  {
    $entry_no_chk = 1;
    setcookie('entry_no_chk', '1');
    $smarty->assign('c_entry_no_chk', '1');
  }
  //ページIDのチェック
  $pageID = 1;
  if (isset($_POST['pageID']))
  {
    $pageID = $_POST['pageID'];
  }
  
  //検索条件作成
  foreach( $_POST as $key => $val ){
    //key値とvalue値の取得
    //value値はPagerによってurlencodeされた値をデコードする
    $key = mysql_escape_string( $key );
    $val = urldecode(mysql_escape_string( $val ));

    //ページ番号・エントリー番号チェックボックス値は
    //既に処理済みのため処理対象から外す
    if ($key == 'pageID' or 
        $key == 'entry_no_chk')
    {
      continue;
    }

    //COOKIEの保存
    setcookie($key, $val);
    
    //Pager:extraVars
    //Pagerのリンクに含めるPOST値を取得する
    $posts[$key] = $val;
    
    //Form:inputのデフォルト値をPOST値で上書き
    $smarty->assign('c_' . $key, $val);    
    
    //検索条件の作成
    if (!empty($val))
    {
      if ($key == 'date_time_from')
      {
        $sql_and[$key] = " `date_time` >= '{$val}' ";
      }
      else if ($key == 'date_time_to')
      {
        $sql_and[$key] = " `date_time` <= '{$val}' ";
      }
      else if ($key == 'entry_no' and $entry_no_chk == 1)
      {
        $sql_and[$key] = " `{$key}` >= '{$val}' ";
      }      
      else
      {
        $sql_and[$key] = " `{$key}` = '{$val}' ";
      }
    }
  }
  
  if (count($sql_and) > 0)
  {
    $sqlWhere = "where " . join( " AND " , $sql_and );
  }

  //作成した検索条件での総データ件数を事前に取得する
  $sql = "select count(*) as cnt from rss_table " . $sqlWhere . ";";
  $res = mysql_query($sql, $conn) or die("DATA抽出エラー");
  $row = mysql_fetch_row($res);
  $rss_count = $row[0];
  
  //総データ件数と現在のページ番号から、
  //取得するデータの範囲(開始位置、件数）を決定する
  $perPage = 10;
  $start = ($pageID - 1) * $perPage;
  $totalItems = "（" . $rss_count . "件）";
  $currentPage = $pageID . "ページを表示";

  // SELECTを実行
  $sql = "select * from rss_table ".
         $sqlWhere .
         " order by date_time desc ".
         "limit $start, $perPage;";
  $res = mysql_query($sql, $conn) or die("DATA抽出エラー");
  
  // データを配列に取得
  while ($row = mysql_fetch_array($res, MYSQL_ASSOC))
  {
    $res_data[] = array(
      "server_no" => $row["server_no"],
      "entry_no" => $row["entry_no"],
      "user_name" => $row["user_name"],
      "url" => $row["url"],
      "date_time" => $row["date_time"],
      "title" => $row["title"],
      "description" => $row["description"]
    );
  }
  
  // MySQLの接続を解除する
  mysql_close($conn);
  
  //Pagerのパラメータを指定
  $params = array(
    "totalItems" => $rss_count,
    "perPage" => $perPage,
    "delta" => 10,
    "mode" =>"Jumping",
    "httpMethod" =>"POST",
    "formID" =>"rssSearchFrom",
    'importQuery'=> FALSE,
    'extraVars' => $posts
  );
  
  //Pager初期化
  $pager =& Pager::factory($params);
  //ページリンクを取得する
  $link = $pager -> getLinks();
  
  //テンプレートへ変数を割り当て
  $smarty->assign('data', $res_data);
  $smarty->assign('pageNavi', $link['all']);
  $smarty->assign('currentPage', $currentPage);
  $smarty->assign("totalItems", $totalItems);
  
}

//各ディレクトリを指定
$smarty->template_dir = './templates/';
$smarty->compile_dir = './templates_c/';
$smarty->config_dir = './configs/';
$smarty->cache_dir = './cache/';

//テンプレートに表示
$smarty->display('rssSearch.tpl');
?>
