<?php
/*
 * index.php
 */
require_once 'config.inc.php';
if (!isset($_REQUEST['ipa'])) { // POST または GET
    echo "パラメータが不適切です。";
    exit;
}
/**
 * find application
 */
function find_application($ipa) {
    $conn = getConnection();
    $res = $conn->query('SELECT * from `application` where `key` = ' . $conn->quote($ipa));
    return ($res == null) ? null : $res->fetch(PDO::FETCH_ASSOC);
}

function h($str) {
    return htmlspecialchars($str, ENT_QUOTES);
}

$isDownload = (isset($_POST['submit'])) ? true : false;
// アプリケーション情報を取得
$row = find_application($_REQUEST['ipa']);
if ($row) {
    $hasPass = false;
    $password = null;
    // パスワードセットの有無
    $key = $row['key'];
    if (!empty($row['password'])) {
        $hasPass = true;
        $password = $row['password'];
    }
    // ダウンロード実施の場合
    if ($isDownload) {
        if (!isset($_POST['rawpass'])) {
            echo "パラメータが不適切です";
            exit;
        }
        // 一致すればダウンロード
        if ($password == sha1($_POST['rawpass'])) {
            header("Location: itms-services://?action=download-manifest&url=" . HOST_URL . "/plist/" . $key . md5($_REQUEST['rawpass']) . '.plist');
        } else {
            echo "パスワードが一致しません";
        }
        exit;
    } else {
        // 表示モードの場合
        $name = $row['name'];
        $version = $row['version'];
        $identifier = $row['identifier'];
        $size = $row['size'];
        $minos = $row['minimumOS'];
        // Useragentをチェック
        if (preg_match("/iPhone OS ([0-9_]+)/", $_SERVER['HTTP_USER_AGENT'], $matches)) {
            $device = $matches[0];
        } else {
            $device = "不明";
        }
    }
} else {
    echo "キーが見つかりません ：" . htmlspecialchars($_GET['ipa'], ENT_QUOTES) . "";
    exit;
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>iOS App Install</title>
        <link rel="stylesheet" href="http://code.jquery.com/mobile/1.0/jquery.mobile-1.0.min.css" />
        <script src="http://code.jquery.com/jquery-1.6.4.min.js"></script>
        <script type="text/javascript">
            $(document).bind("mobileinit", function(){
                $.extend(  $.mobile , {
                    ajaxEnabled : false
                });
            });
        </script>
        <script src="http://code.jquery.com/mobile/1.0/jquery.mobile-1.0.min.js"></script>
    </head>
    <body>
        <div data-role="page">
            <div data-role="header">
                <h1>インストール</h1>
            </div>
            <div data-role="content">
                <h3><?php echo h($name); ?>をインストール</h3>
                <p>インストールはプロファイルにUDID登録したデバイスのみ可能です。</p>
                <ul data-role="listview" data-theme="g" class="ui-corner-top">
                    <li><h4>バージョン：<?php echo h($version); ?></h4></li>
                    <li><h4>サイズ：<?php echo h((round($size / 256) / 4) . " KB"); ?></h4></li>
                    <li><h4>必須OSバージョン：<?php echo h($minos); ?></h4>
                        <p>お使いの端末バージョン：<?php echo h($device); ?></p>
                    </li>
                    <li><h4>Bundle ID:</h4><p style="font-size:70%;"><?php echo h($identifier); ?></p></li>
                </ul>
                <p>
                    <?php
                    if ($hasPass) {
                        echo '<form action="index.php" method="POST">';
                        echo '<input type="hidden" name="ipa" value="' . h($key) . '" />';
                        echo 'パスワード：<input type="password" name="rawpass" size="30" /><br />
                                    <input type="submit" name="submit" value="インストール"></form>';
                    } else {
                        echo '<a href="itms-services://?action=download-manifest&url=' . HOST_URL . '/plist/' . h($key) . '.plist" data-role="button" rel="external">';
                        echo h($name) . 'をインストール</a>';
                    }
                    ?>
                </p>
                <p>このURLをコピー</p>
                <textarea><?php echo HOST_URL; ?>index.php?ipa=<?php echo h($key); ?></textarea>
            </div>
            <div data-role="footer">
                <h4>(C) SimplePHPAdhocServer</h4>
            </div>
        </div>
    </body>
</html>