<?php

namespace CaptchaCalc;

/**
 * Class CaptchaCalc
 * @package CaptchaCalc
 */

class CaptchaCalc
{
    /**
     * @var int
     */
    protected $maxLow;

    /**
     * @var int
     */
    protected $maxHigh;

    /**
     * @var int
     */
    protected $maxImgWidth;

    /**
     * @var int
     */
    protected $low;

    /**
     * @var int
     */
    protected $high;

    /**
     * @var string
     */
    protected $operator;

    /**
     * @var array
     */
    protected $availableOperators = [];

    /**
     * @var array
     */
    protected $allowedOperators = [
        '+',
        '-',
        '*',
        '/'
    ];

    /**
     * @var int
     */
    protected $fontSize = null;

    /**
     * @var int
     */
    protected $angle = 0;

    /**
     * @var string
     */
    protected $fontFile = '';

    /**
     * @var string
     */
    protected $calculation = '';

    /**
     * constant int
     */
    const TOO_HIGH_COLOR_FACTOR = 10;

    /**
     * CaptchaCalc constructor.
     * @param int    $maxLow
     * @param int    $maxHigh
     * @param array  $availableOperators
     * @param string $fontFile
     * @param int    $maxImgWidth
     * @throws \Exception
     */
    function __construct(int $maxLow = 1, int $maxHigh = 255, array $availableOperators = ['+', '-', '*', '/'], string $fontFile = '', int $maxImgWidth)
    {
        if (!extension_loaded('gd')) {
            throw new \Exception('gd-lib not loaded');
        }
        $this->maxLow = $maxLow;
        $this->maxHigh = $maxHigh;
        $this->maxImgWidth = $maxImgWidth;
        $opDiff = array_diff($availableOperators, $this->allowedOperators);
        if (sizeof($opDiff) > 0) {
            throw new \Exception('Operator(s): ' . implode(', ', $opDiff) . ' is/are not supported');
        }
        $this->availableOperators = $availableOperators;
        $this->setRandomCalc();
        if (strlen($fontFile) == 0) {
            $this->setFontFile();
        } else {
            $this->setFontFile($fontFile);
        }
        $this->setFontSize();
    }

    /**
     * @param int|null $fontSize
     * @throws \Exception
     */
    public function setFontSize(int $fontSize = null)
    {
        if (is_null($fontSize) || !is_null($this->fontSize)) {
            $this->fontSize = $this->setRandomFontSize();
        } else {
            $this->fontSize = $fontSize;
        }
    }

    public function setAngle(int $angle)
    {
        $this->angle = $angle;
    }

    public function getAngle()
    {
        return $this->angle;
    }

    /**
     * @param string $path
     */
    public function setFontFile(string $path = 'C:\Dev\Web\test\toesslab-CaptCha\captcha\fonts\Roboto-Black.ttf')
    {
        $this->fontFile = $path;
    }

    /**
     * @return string
     */
    protected function setRandomCalc()
    {
        try {
            $this->low = random_int($this->maxLow, $this->maxHigh);
        } catch (\Exception $e) {
            return $e->getMessage();
        }
        try {
            $this->high = random_int($this->maxLow, $this->maxHigh);
        } catch (\Exception $e) {
            return $e->getMessage();
        }
        $this->operator = $this->availableOperators[array_rand($this->availableOperators, 1)];
        $this->getCalculation();
        return '';
    }

    /**
     * @param $max
     * @return int|string
     */
    protected function setRandomAngle($max)
    {
        try {
            return random_int(0, $max - $max / 100 * 10);
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     *
     */
    protected function getCalculation()
    {
        if ($this->operator === '/') {
            if ($this->low < $this->high) {
                $this->setIntegerDivision($this->high, $this->low);
            } else {
                $this->setIntegerDivision($this->low, $this->high);
            }
            $this->calculation = $this->high . ' : ' . $this->low;
        } else if ($this->operator === '-') {
            if ($this->low < $this->high) {
                $this->calculation = $this->high . ' ' . $this->operator . ' ' . $this->low;
            } else {
                $this->calculation = $this->low . ' ' . $this->operator . ' ' . $this->high;
            }
        } else if ($this->operator === '*') {
            $this->calculation = $this->high . ' x ' . $this->low;
        } else {
            $this->calculation = $this->high . ' ' . $this->operator . ' ' . $this->low;
        }
    }

    /**
     * @return int
     */
    public function calculateResult()
    {
        $low = ($this->low > $this->high) ? $this->high : $this->low;
        $high = ($this->low < $this->high) ? $this->high : $this->low;
        $result = 0;
        switch ($this->operator) {
            case '+':
                $result = $this->low + $this->high;
                break;
            case '-':
                $result = $high - $low;
                break;
            case '/':
                $result = $high / $low;
                break;
            case '*':
                $result = $this->low * $this->high;
                break;
        }
        return (int)$result;
    }

    /**
     * @param int|null $backRed
     * @param int|null $backGreen
     * @param int|null $backBlue
     * @return string
     * @throws \Exception
     */
    public function generateImage(int $backRed = null, int $backGreen = null, int $backBlue = null)
    {
        $textLength = strlen($this->calculation) * $this->fontSize / 1.5;
        $width = intval($textLength / 2 + $textLength);
        $startX = intval(($width - $textLength) - $textLength / 100 * 5);
        if ($startX <= 0) {
            $startX = $width - $textLength;
        }
        $angle = $this->setRandomAngle($startX);
        $tan = intval(rad2deg(atan(deg2rad($angle))));
        if ($tan == 0) {
            $tan += 1;
        }
        $height = intval(($width + $tan) / 2 + $this->fontSize);
        $startY = intval(($height - $this->fontSize) / 2 + 4 * $tan);
        if (intval($startX + $textLength) > $width) {
            $startX += $startX + $textLength;
        }
        if ((int)$startY > $height) {
            $tmp = $this->checkTooHighStartY($height, $startY, $this->fontSize);
            $startY = $tmp[0];
            $this->fontSize = $tmp[1];
        }
        $image = imagecreatetruecolor($width, $height);
        $bgColor = $this->setRandomBackgroundColor($backRed, $backGreen, $backBlue);
        $txtColor = $this->setRandomTextColor($bgColor);
        $backGroundColor = imagecolorallocate($image, $bgColor['red'], $bgColor['green'], $bgColor['blue']);
        $textColor = imagecolorallocate($image, $txtColor[0], $txtColor[1], $txtColor[2]);
        imagefill($image, 0, 0, $backGroundColor);
        if (@imagettftext($image, $this->fontSize, $angle, $startX, $startY, $textColor, $this->fontFile, $this->calculation) !== false) {
            $r = '';
            if ($width > $this->maxImgWidth) {
                $image = imagescale($image, $this->maxImgWidth, -1);
                $r = 'resized_';
            }
            imagepng($image, 'C:\Dev\Web\test\toesslab-CaptCha\tests\images\\' . $r . $this->fontSize . '_startX_' . $startX . '_startY_' . $startY . '_height_' . $height . '_width_' . $width . '_length_' . (int)$textLength . '_widthX_' . (int)($startX + $textLength) . '_heightY_' . (int)($startY + $textLength) . '.png');
            ob_start();
            imagepng($image);
            $img = ob_get_clean();
            imagedestroy($image);
            return 'data:image/png;base64,' . base64_encode($img);
        }
        throw new \Exception('Invalid font filename');
    }

    /**
     * @param int|null $red
     * @param int|null $green
     * @param int|null $blue
     * @return array|string
     */
    protected function setRandomBackgroundColor(int $red = null, int $green = null, int $blue = null)
    {
        try {
            $r = ($red) ? $red : random_int(0, 255);
            $g = ($green) ? $green : random_int(0, 255);
            $b = ($blue) ? $blue : random_int(0, 255);
        } catch (\Exception $e) {
            return $e->getTraceAsString();
        }
        return [
            'red' => $r,
            'green' => $g,
            'blue' => $b
        ];
    }

    /**
     * Gets the opposite color of the given background color
     *
     * @param array $color
     * @return array
     */
    protected function setRandomTextColor(array $color = [0, 0, 0])
    {
        $oppositeColor = [];
        $ss = '';
        $list1 = ["0", "1", "2", "3", "4", "5", "6", "7", "8", "9", "A", "B", "C", "D", "E", "F"];
        $list2 = array_reverse($list1);
        foreach ($color as $c) {
            $int = strtoupper(dechex($c));
            if (strlen($int) < 2 && (int)$int < 10) {
                $tempColor = '0' . $int;
            } else {
                $tempColor = $int;
            }
            $ss .= $tempColor;
            $tC[] = substr($tempColor, 0, 1);
            $tC[] = substr($tempColor, 1, 1);
            $key1 = array_keys($list1, $tC[0]);
            $key2 = array_keys($list2, $tC[1]);
            $checkColor = hexdec($list1[$key2[0]]) . hexdec($list2[$key1[0]]);
            if ((int)$checkColor > 255) {
                $checkColor = $this->checkTooHighColorValue($checkColor);
            }
            $oppositeColor[] = $checkColor;
        }
        return $oppositeColor;
    }

    /**
     * @param int $min
     * @param int $max
     * @return int
     * @throws \Exception
     */
    protected function setRandomFontSize(int $min = 10, int $max = 50)
    {
        try {
            return random_int($min, $max);
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * Increments the divisor until the quotient is an integer, checks division by zero
     *
     * @param int $divisor
     * @param int $dividend
     * @return bool
     */
    protected function setIntegerDivision(int $divisor, int $dividend)
    {
        if ($dividend === 0) {
            $dividend += 1;
        }
        $result = $divisor / $dividend;
        if (is_int($result)) {
            $this->high = $divisor;
            $this->low = $dividend;
            return true;
        }
        $divisor += 1;
        return $this->setIntegerDivision($divisor, $dividend);
    }

    /**
     * @param string $color
     * @return int|string
     */
    protected function checkTooHighColorValue(string $color)
    {
        if ((int)$color > 255) {
            $color -= self::TOO_HIGH_COLOR_FACTOR;
            return $this->checkTooHighColorValue($color);
        }
        return $color;
    }

    /**
     * @param int $height
     * @param int $startY
     * @param int $fontSize
     * @return array
     */
    protected function checkTooHighStartY(int $height, int $startY, int $fontSize)
    {
        $tmp = [];
        if ((int)$startY > (int)$height) {
            //$fontSize -= 4;
            $startY -= 4;
            return $this->checkTooHighStartY($height, $startY, $fontSize);
        }
        $tmp[] = $startY;
        $tmp[] = $fontSize;
        return $tmp;
    }
}
