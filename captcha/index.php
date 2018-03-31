<?php
require_once 'CaptChaCalc/CaptchaCalc.php';
$captcha = new \CaptchaCalc\CaptchaCalc(3, 15, ['+', '-', '*', '/'], '', 150);
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
        <li><code>int $maxLow </code>:<br>The lowest randomly possible number</li>
        <li><code>int $maxHigh </code>:<br>The highest randomly possible number</li>
        <li><code>array $availableOperators </code>:<br>Addition, Subtraction, Multiplication & Division are supported</li>
        <li><code>string $fontFile </code>:<br>Absolute path to a .ttf font file</li>
</ul>
<h3>Must haves</h3>
<ul>
    <li>An image must be created with the calculation.</li>
    <li>A check for necessary PHP libraries must be made</li>
</ul>
<h3>Optional</h3>
<ul>
    <li>Random colors</li>
    <li>Random position of the calculation inside the image</li>
</ul>
<pre>
    $captcha = new \CaptchaCalc\CaptchaCalc(3, 15, ['+', '-', '*', '/'], $_SERVER['DOCUMENT_ROOT'] . '/captcha/fonts/sedgwick/SedgwickAveDisplay-Regular.ttf');
</pre>
</body>
</html>
