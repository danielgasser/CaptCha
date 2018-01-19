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
     * CaptchaCalc constructor.
     * @param int $maxLow
     * @param int $maxHigh
     * @param array $availableOperators
     * @param string $fontFile
     * @throws \Exception
     */
    function __construct(int $maxLow = 1, int $maxHigh = 255, array $availableOperators = ['+', '-', '*', '/'], string $fontFile = '')
    {
        if(!extension_loaded('gd')) {
            throw new \Exception('lib not loaded');
        }
        $this->maxLow = $maxLow;
        $this->maxHigh = $maxHigh;
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
     * @param int $fontSize
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
     * @param string $path must be an absolute path
     */
    public function setFontFile(string $path = '/www/daniel-gasser.com/captcha/fonts/roboto/Roboto-Black.ttf')
    {
        $this->fontFile = $path;
    }

    /**
     *
     */
    protected function setRandomCalc()
    {
        $this->low = random_int($this->maxLow, $this->maxHigh);
        $this->high = random_int($this->maxLow, $this->maxHigh);
        $this->operator = $this->availableOperators[array_rand($this->availableOperators, 1)];
        $this->getCalculation();
    }

    /**
     * Sets a random angle between 0° and 270° because of Pythagoras's Theorem which dictates 1 fix angle of 90°: 360° - 90° = 270°
     *
     * @param $max
     * @return int
     */
    protected function setRandomAngle($max)
    {
        return random_int(0, $max - $max / 100 * 10);
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
     * @param int $backRed
     * @param int $backGreen
     * @param int $backBlue
     * @return string
     */
    public function generateImage(int $backRed = null, int $backGreen = null, int $backBlue = null)
    {
        $textLength = strlen($this->calculation) * $this->fontSize / 1.5;
        $width = intval($textLength / 2 + $textLength);
        $startX = intval(($width - $textLength) / 2);
        $angle = $this->setRandomAngle($startX);
        $tan = intval(rad2deg(atan(deg2rad($angle))));
        if ($tan == 0) {
            $tan += 1;
        }
        $height = intval(($width + $tan) / 2);
        $startY = intval(($height - $this->fontSize) / 2 + 3 * $tan);
        //echo "($height - $this->fontSize) / 2 + 3 * $tan = $startY";
        $image = imagecreatetruecolor($width, $height);
        $bgColor = $this->setRandomBackgroundColor($backRed, $backGreen, $backBlue);
        $txtColor = $this->setRandomTextColor($bgColor);
        $backGroundColor = imagecolorallocate($image, $bgColor['red'], $bgColor['green'], $bgColor['blue']);
        $textColor = imagecolorallocate($image, $txtColor[0], $txtColor[1], $txtColor[2]);
        imagefill($image, 0, 0, $backGroundColor);
        imagettftext($image, $this->fontSize, $angle, $startX, $startY, $textColor, $this->fontFile , $this->calculation);
        ob_start();
        imagepng($image);
        $img = ob_get_clean();
        imagedestroy($image);
        return 'data:image/png;base64,' . base64_encode($img);
    }

    /**
     * @param int|null $red
     * @param int|null $green
     * @param int|null $blue
     * @return array
     */
    protected function setRandomBackgroundColor(int $red = null, int $green = null, int $blue = null)
    {
        $r = ($red) ? $red : random_int(0, 255);
        $g = ($green) ? $green : random_int(0, 255);
        $b = ($blue) ? $blue : random_int(0, 255);
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
        $list1 = '0 1 2 3 4 5 6 7 8 9 A B C D E F';
        $list2 = 'F E D C B A 9 8 7 6 5 4 3 2 1 0';
        $arrList1 = explode(' ', $list1);
        $arrList2 = explode(' ', $list2);
        foreach ($color as $c) {
            $int = strtoupper(dechex($c));
            if (strlen($int) < 2 && (int) $int < 10) {
                $tempColor = '0' . $int;
            } else {
                $tempColor = $int;
            }
            $ss .= $tempColor;
            $tC[] = substr($tempColor, 0, 1);
            $tC[] = substr($tempColor, 1, 1);
            $key1 = array_keys($arrList1, $tC[0]);
            $key2 = array_keys($arrList2, $tC[1]);
            $oppositeColor[] = hexdec($arrList1[$key2[0]]) . hexdec($arrList2[$key1[0]]);
        }
        return $oppositeColor;
    }

    /**
     * @param int $min
     * @param int $max
     * @return int
     */
    protected function setRandomFontSize(int $min = 10, int $max = 50)
    {
        return random_int($min, $max);
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

}
