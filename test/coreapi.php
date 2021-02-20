<?php
require("../src/CoreAPI.php");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>ID Analyzer Core API Test</title>
</head>
<body>
<h1>ID Analyzer Core API Test</h1>
<form enctype="multipart/form-data" method="post">
    Document Image (Front)*: <input type="file" name="DocumentFront"><br/>
    Document Image (Back):<input type="file" name="DocumentBack"><br/>
    Face Photo: <input type="file" name="FacePhoto">
</form>
<div>
    <h2>Result</h2>
    <?php
    if($_FILES['DocumentFront']['tmp_name']!=""){
        $coreapi = new \IDAnalyzer\CoreAPI();
        $coreapi->init("Your API Key", "US");
        $coreapi->setAccuracy(2);
        $result = $coreapi->scan($_FILES['DocumentFront']['tmp_name'],$_FILES['DocumentBack']['tmp_name'],$_FILES['FacePhoto']['tmp_name']);
        print_r($result);
    }

    ?>
</div>

</body>
</html>
