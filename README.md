# toesslab - CaptCha
Creates an image with a randomly created simple arithmetic operation as Captcha.

Requires PHP version 7.x

## Possible options:
- `int $maxLow`: The lowest randomly possible number
- `int $maxHigh`: The lowest randomly possible number
- `array $availableOperators`: Addition, Subtraction, Multiplication & Division are supported
- `string $fontFile`: Absolute path to a .ttf font file

## Usage:
    $captcha = new \CaptchaCalc\CaptchaCalc(3, 15, ['+', '-', '*', '/'], 'absolute/path/to/fontfile.ttf');
    $img = $captcha->generateImage();
    echo '<img src="$img">';
    // Result (Don't show in production environment):
    $captcha->calculateResult();
