<html>
<head>
<title>FC2 新着ブログ</title>
</head>
<body>
<h4>FC2 新着ブログ</h4>
  <form id="rssSearchFrom" method="post" action="rssSearch.php">
    <dl>
      <dt>日付</dt>
      <dd>
        <input type="text" name="date_time_from" value="{$c_date_time_from}"/>から
      </dd>
      <dd>
        <input type="text" name="date_time_to" value="{$c_date_time_to}"/>まで
      </dd>
      <dt>URL</dt>
      <dd>
        <input type="text" name="url" value="{$c_url}"/>
      </dd>
      <dt>ユーザー名</dt>
      <dd>
        <input type="text" name="user_name" value="{$c_user_name}"/>
      </dd>
      <dt>サーバー番号</dt>
      <dd>
        <input type="text" name="server_no" value="{$c_server_no}"/>
      </dd>
      <dt>エントリーNo</dt>
      <dd>
        <input type="text" name="entry_no" value="{$c_entry_no}"/>
        <input type="checkbox" name="entry_no_chk" value="1" {if $c_entry_no_chk == 1}checked{/if}>指定した番号以上を表示
      </dd>
    </dl>
    <input type="submit" value="検索" />
    <p>{$pageNavi}{$totalItems}</p>
    <p>{$currentPage}</p>    
    <table border="1">
      <tr>
        <th>日付</th>
        <th>URL</th>
        <th>タイトル</th>
        <th>内容</th>
      </tr>
      {foreach from=$data item="data"}
      <tr>
        <td>{$data.date_time}</td><td>{$data.url}</td><td>{$data.title}</td><td>{$data.description}</td>
      </tr>
      {/foreach}
    </table>
  </form>
</body>
</html>
