<?php
/**
 * upload.php
 */
require_once 'config.inc.php';
/**
 * Read and parse plist data
 */
function readPlist($plistdata) {
    require_once(dirname(__FILE__) . '/lib/CFPropertyList/CFPropertyList.php');
    $plist = new CFPropertyList();
    $plist->parse($plistdata);
    return $plist->toArray();
}

/**
 * insert MySQL
 */
function insertApplication($key, $name, $version, $identifier, $size, $minos, $pass = null) {
    $conn = getConnection();
    if (empty($pass)) {
        return $conn->query('INSERT INTO `application` VALUES (null , "' . $key . '", "' . $conn->quote($name)
                        . '", "' . $conn->quote($version) . '", "' . $conn->quote($identifier) . '", null, ' . $size . ', "' . $conn->quote($minos) . '", now())');
    } else {
        return $conn->query('INSERT INTO `application` VALUES (null , "' . $key . '", "' . $conn->quote($name)
                        . '", "' . $conn->quote($version) . '", "' . $conn->quote($identifier) . '", "' . sha1($pass) . '", ' . $size . ', "' . $conn->quote($minos) . '", now())');
    }
}

/**
 * main
 */
if (isset($_POST['submit'])) {
    require_once(dirname(__FILE__) . '/lib/class.upload.php');

    $key = md5(microtime(true));
    $handle = new Upload($_FILES['upfile']);
    if ($handle->uploaded) {
        $handle->file_new_name_body = $key;
        $handle->file_new_name_ext = "ipa";
        $handle->Process("binary/");
        // set binary file
        if ($handle->processed) {
            require_once("Archive/Zip.php"); // extract
            $zip = new Archive_Zip($handle->file_dst_pathname);
            $files = $zip->extract( array( 'extract_as_string' => true ) ) ;
            //var_dump($files);exit;
            $plistArr = null;
            // search Info.plist file
            foreach ($files as $file) {
                if (strpos($file["filename"], 'Info.plist') > 0) {
                    $plistArr = readPlist($file['content']); // parse plist
                    break;
                }
            }
            if ($plistArr) {
                //some value contains single quote
                $appname = str_replace("'", "", $plistArr["CFBundleDisplayName"]);
                $identifier = str_replace("'", "", $plistArr["CFBundleIdentifier"]);
                $version = str_replace("'", "", $plistArr["CFBundleVersion"]);
                $minos = str_replace("'", "", $plistArr["MinimumOSVersion"]);
                $size = filesize($handle->file_dst_pathname);

                // generate plist file for ota install
                $template = file_get_contents(dirname(__FILE__) . "/lib/template.plist");
                $template = str_replace('{$hosturl}', HOST_URL, $template);
                $template = str_replace('{$ipaURL}', $key . '.ipa', $template);
                $template = str_replace('{$identifier}', $identifier, $template);
                $template = str_replace('{$version}', $version, $template);
                $template = str_replace('{$appName}', $appname, $template);
                if (empty($_POST['pass'])) {
                    file_put_contents('plist/' . $key . '.plist', $template);
                } else {
                    file_put_contents('plist/' . $key . md5($_POST['pass']) . '.plist', $template);
                }
                insertApplication($key, $appname, $version, $identifier, $size, $minos, $_POST['pass']);
                header("Location: index.php?ipa=" . $key);
            }  else {
                echo "Cannot parse plst file";
            }
        } else {
            // check directory permission.
            echo '  Error: ' . $handle->error . '';
        }
        $handle->Clean();
    } else {
        // Tips: some hosting service limit upload size up to 2MB.
        // change php.ini or put .htaccess and set upload_max_filesize
        // and post_max_size.
        echo $handle->error;
    }
}
// start html with bootstrap template http://twitter.github.com/bootstrap/
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>SimplePHPAdhocServer</title>
    <meta name="description" content="">
    <meta name="author" content="">
    <!-- Le HTML5 shim, for IE6-8 support of HTML elements -->
    <!--[if lt IE 9]>
      <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->
    <!-- Le styles -->
    <link href="http://twitter.github.com/bootstrap/1.4.0/bootstrap.css" rel="stylesheet">
    <style type="text/css">
    html,body{background-color:#eee}
    body{padding-top:40px}
    .container > footer p{text-align:center}
    .container{width:820px}
    .content{background-color:#fff;-webkit-border-radius:0 0 6px 6px;-moz-border-radius:0 0 6px 6px;border-radius:0 0 6px 6px;-webkit-box-shadow:0 1px 2px rgba(0,0,0,.15);-moz-box-shadow:0 1px 2px rgba(0,0,0,.15);box-shadow:0 1px 2px rgba(0,0,0,.15);margin:0 -20px;padding:20px}
    .page-header{background-color:#f5f5f5;margin:-20px -20px 20px;padding:20px 20px 10px}
    .content .span10,.content .span4{min-height:500px}
    .content .span4{margin-left:0;padding-left:19px;border-left:1px solid #eee}
    </style>
  </head>
  <body>
    <div class="topbar">
      <div class="fill">
        <div class="container">
          <a class="brand" href="#">SimplePHPAdhocServer</a>
        </div>
      </div>
    </div>

    <div class="container">

      <div class="content">
        <div class="row">
          <div class="span10">
            <h2>Upload .ipa file</h2>
            <form action="upload.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="MAX_FILE_SIZE" value="52428800" /><!-- 50MB -->
              <div class="clearfix">
                <label for="upfile">ipa file: </label>
                <div class="input">
                  <input class="input-file" type="file" name="upfile" id="upfile" size="30" />
                </div>
              </div><!-- /clearfix -->
              <div class="clearfix">
                <label for="upfile">Password: </label>
                <div class="input">
                  <input class="xlarge" type="password" name="pass" size="30" />(optional)
                </div>
              </div><!-- /clearfix -->
              <div class="actions span7">
                <input type="submit" class="btn primary" name="submit" value="Upload file">
              </div>
            </form>
          </div>
          <div class="span4">
            <h3>使い方</h3>
            <p>@TODO</p>
          </div>
        </div>
      </div>

      <footer>
        <p>&copy; SimplePHPAdhocServer by <a href="http://twitter.com/ku_suke">ku_suke</a></p>
      </footer>

    </div> <!-- /container -->

  </body>
</html>