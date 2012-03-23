<?php
if(isset($_GET['mode'])&&$_GET['mode']=='done'){
        require_once '../config.inc.php';
        $conn = getConnection();
        $sql = "DROP TABLE IF EXISTS `application`";
        $conn->query($sql);
        $sql = "CREATE TABLE `application` (`id` INT(8) NOT NULL AUTO_INCREMENT PRIMARY KEY, `key` VARCHAR(64) NOT NULL, `name` VARCHAR(255) NOT NULL, `version` VARCHAR(16) NOT NULL, `identifier` VARCHAR(255) NOT NULL, `pass` VARCHAR(255) NULL, `size` INT(16) NOT NULL, `minimumOS` VARCHAR(32) NOT NULL, `timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, UNIQUE (`key`)) ENGINE = InnoDB;";
        $conn->query($sql);
        rename(__FILE__, ".renamed_".__FILE__);
        header("Location: ../upload.php");
}
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
      /* Override some defaults */
      html, body {
        background-color: #eee;
      }
      .container > footer p {
        text-align: center; /* center align it with the container */
      }
      .container {
        width: 820px; /* downsize our container to make the content feel a bit tighter and more cohesive. NOTE: this removes two full columns from the grid, meaning you only go to 14 columns and not 16. */
      }

      /* The white background content wrapper */
      .content {
        background-color: #fff;
        padding: 20px;
        margin: 0 -20px; /* negative indent the amount of the padding to maintain the grid system */
        -webkit-border-radius: 0 0 6px 6px;
           -moz-border-radius: 0 0 6px 6px;
                border-radius: 0 0 6px 6px;
        -webkit-box-shadow: 0 1px 2px rgba(0,0,0,.15);
           -moz-box-shadow: 0 1px 2px rgba(0,0,0,.15);
                box-shadow: 0 1px 2px rgba(0,0,0,.15);
      }

      /* Styles you shouldn't keep as they are for displaying this base example only */
      .content .span10,
      .content .span4 {
        min-height: 500px;
      }
      /* Give a quick and non-cross-browser friendly divider */
      .content .span4 {
        margin-left: 0;
        padding-left: 19px;
        border-left: 1px solid #eee;
      }

    </style>
  </head>

  <body>
    <div class="container">

      <div class="content">
      <div class="page-header">
          <h1>SimplePHPAdhocServer</h1>
        </div>
        <div class="row">
          <div class="span16">
            <h2>Install</h2>
            <table class="bordered-table span6">
<?php
/*
 * simple install program.
 */
$check_ok = false;
// check permission
$base_dir = dirname(dirname(__FILE__));
if(is_writable($base_dir."/binary")){
    echo '<tr><td><span class="label success">OK</span></td><td>/binary is writable</td></tr>';
    $check_ok = true;
}else{
    echo '<tr><td><span class="label important">NG</span></td><td>/binary is NOT writable</td></tr>';
}

if($check_ok){
    require_once '../config.inc.php';
    $conn = getConnection();
    if(!$conn){
        echo '<tr><td><span class="label important">NG</span></td><td>Could not connect DB</td></tr>';
        $check_ok = false;
    }else{
        echo '<tr><td><span class="label success">OK</span></td><td>DB Connected</td></tr>';
        $check_ok = true;
    }
}

if($check_ok){
        echo '<tr><td colspan="2"><a href="?mode=done" class="btn">I N S T A L L (All data will cleared)</a></td></tr>';
}else{
        echo '<tr><td colspan="2">Please check permission or DB settings at config.inc.php</td></tr>';
}
?>
            </table>
          </div>
        </div>
      </div>

      <footer>
        <p>&copy; Company 2011</p>
      </footer>

    </div> <!-- /container -->

  </body>
</html>