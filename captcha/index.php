<?php
require_once 'CaptChaCalc/CaptchaCalc.php';

$captcha = new \CaptchaCalc\CaptchaCalc(3, 15, ['+', '-', '*', '/'], $_SERVER['DOCUMENT_ROOT'] . '/captcha/fonts/sedgwick/SedgwickAveDisplay-Regular.ttf');
$img = $captcha->generateImage();
?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title></title>
</head>
<body>
<h1>Hit F5 to generate a new calculation</h1>
<?php
echo "<img src='$img'>";
echo '<hr><h3>Result (Don\'t show in production environment):<br>';
echo $captcha->calculateResult();
echo '</h3>';
?>
<h3>Possible Options:</h3>
<ul>
        <li><b>int $maxLow </b>:<br>The lowest randomly possible number</li>
        <li><b>int $maxHigh </b>:<br>The lowest randomly possible number</li>
        <li><b>array $availableOperators </b>:<br>Addition, Subtraction, Multiplication & Division are supported</li>
        <li><b>string $fontFile </b>:<br>Absolute path to a .ttf font file</li>
</ul>
<pre>
    $captcha = new \CaptchaCalc\CaptchaCalc(3, 15, ['+', '-', '*', '/'], $_SERVER['DOCUMENT_ROOT'] . '/captcha/fonts/sedgwick/SedgwickAveDisplay-Regular.ttf');
</pre>
</body>
</html>
