<?php

/**
 * Using modified code from:
 * Author: Arlo Carreon <http://arlocarreon.com>
 * Info: http://mexitek.github.io/phpColors/
 * License: http://arlo.mit-license.org/
 */

namespace craft

/**
 * Class ColorMixerTwigExtension
 */
class ColorMixerTwigExtension extends \Twig_Extension
{

    /**
     * Auto darkens/lightens by 10%.
     * Set this to FALSE to adjust automatic shade to be between given color
     * and black (for darken) or white (for lighten)
     */
    const DEFAULT_ADJUST = 10;

    /**
     * @return string
     */
    public function getName()
    {
        return 'Color Mixer';
    }

    /**
     * @return array
     */
        public function getFunctions()
    {
        return array(
            'hexToHsl' => new \Twig_SimpleFunction('hexToHsl', array(
                $this, 'hexToHsl',
                array('is_safe' => array('html')),
            )),
            'hexToRgb' => new \Twig_SimpleFunction('hexToRgb', array(
                $this, 'hexToRgb',
                array('is_safe' => array('html')),
            )),
            'darken' => new \Twig_SimpleFunction('darken', array(
                $this, 'darken',
                array('is_safe' => array('html')),
            )),
            'lighten' => new \Twig_SimpleFunction('lighten', array(
                $this, 'lighten',
                array('is_safe' => array('html')),
            )),
            'mix' => new \Twig_SimpleFunction('mix', array(
                $this, 'mix',
                array('is_safe' => array('html')),
            )),
            'isLight' => new \Twig_SimpleFunction('isLight', array(
                $this, 'isLight',
                array('is_safe' => array('html')),
            )),
            'isDark' => new \Twig_SimpleFunction('isDark', array(
                $this, 'isDark',
                array('is_safe' => array('html')),
            )),
            'complementary' => new \Twig_SimpleFunction('complementary', array(
                $this, 'complementary',
                array('is_safe' => array('html')),
            )),
            'gradientColors' => new \Twig_SimpleFunction('gradientColors', array(
                $this, 'gradientColors',
                array('is_safe' => array('html')),
            )),
            'gradient' => new \Twig_SimpleFunction('gradient', array(
                $this, 'gradient',
                array('is_safe' => array('html')),
            )),
        );
    }

    /////////////////////
    // Public          //
    /////////////////////

    /**
     * Given a HEX string returns a HSL array equivalent.
     * @param string  $color
     * @param boolean $returnAsArray
     * @return array HSL associative array
     */
    public function hexToHsl($color, $returnAsArray = false)
    {
        $color = self::_checkHex($color);

        // Convert HEX to DEC
        $r = hexdec($color[0].$color[1]);
        $g = hexdec($color[2].$color[3]);
        $b = hexdec($color[4].$color[5]);

        $hsl = array();
        $varR = ($r / 255);
        $varG = ($g / 255);
        $varB = ($b / 255);
        $varMin = min($varR, $varG, $varB);
        $varMax = max($varR, $varG, $varB);
        $delMax = $varMax - $varMin;
        $l = ($varMax + $varMin)/2;
        if ($delMax == 0) {
            $h = 0;
            $s = 0;
        } else {
            if ($l < 0.5) {
                $s = $delMax / ($varMax + $varMin);
            } else {
                $s = $delMax / ( 2 - $varMax - $varMin );
            }
            $delR = ((($varMax - $varR)/6) + ($delMax / 2)) / $delMax;
            $delG = ((($varMax - $varG)/6) + ($delMax / 2)) / $delMax;
            $delB = ((($varMax - $varB)/6) + ($delMax / 2)) / $delMax;
            $h = 0.5;
            if ($varR == $varMax) {
                $h = $delB - $delG;
            } elseif ($varG == $varMax) {
                $h = ( 1 / 3 ) + $delR - $delB;
            } elseif ($varB == $varMax) {
                $h = ( 2 / 3 ) + $delG - $delR;
            }
            if ($h < 0) {
                $h++;
            }
            if ($h > 1) {
                $h--;
            }
        }

        $hsl['H'] = ($h*360);
        $hsl['S'] = $s;
        $hsl['L'] = $l;

        return $returnAsArray ? $hsl : implode(",", $hsl);
    }

    /**
     * Given a HEX string returns a RGB array equivalent.
     * @param string  $color
     * @param boolean $returnAsArray
     * @return array RGB associative array
     */
    public function hexToRgb($color, $returnAsArray = false)
    {
        $color = self::_checkHex($color);

        // Convert HEX to DEC
        $r = hexdec($color[0].$color[1]);
        $g = hexdec($color[2].$color[3]);
        $b = hexdec($color[4].$color[5]);

        $rGB['R'] = $r;
        $rGB['G'] = $g;
        $rGB['B'] = $b;

        return $returnAsArray ? $rGB : implode(",", $rGB);
    }

    /**
     * Given a HEX value, returns a darker color. If no desired amount provided, then the color halfway between
     * given HEX and black will be returned.
     * @param string $color
     * @param int    $amount
     * @return string Darker HEX value
     */
    public function darken($color, $amount = self::DEFAULT_ADJUST)
    {
        $color = self::_checkHex($color);
        $color = $this->hexToHsl($color, true);
        // Darken
        $darkerHSL = $this->_darken($color, $amount);
        // Return as HEX
        return self::_hslToHex($darkerHSL);
    }

    /**
     * Given a HEX value, returns a lighter color. If no desired amount provided, then the color halfway between
     * given HEX and white will be returned.
     * @param string $color
     * @param int    $amount
     * @return string Lighter HEX value
     */
    public function lighten($color, $amount = self::DEFAULT_ADJUST)
    {
        $color = self::_checkHex($color);
        $color = $this->hexToHsl($color, true);
        // Lighten
        $lighterHSL = $this->_lighten($color, $amount);
        // Return as HEX
        return self::_hslToHex($lighterHSL);
    }

    /**
     * Given a HEX value, returns a mixed color. If no desired amount provided, then the color mixed by this ratio
     * @param string $color
     * @param string $hex2   Secondary HEX value to mix with
     * @param int    $amount = -100..0..+100
     * @return string mixed HEX value
     */
    public function mix($color, $hex2, $amount = 0)
    {
        $color = self::_checkHex($color);

        $color = self::hexToRgb($color, true);
        $rgb2 = self::hexToRgb($hex2, true);
        $mixed = $this->_mix($color, $rgb2, $amount);
        // Return as HEX
        return self::_rgbToHex($mixed);
    }

    /**
     * Returns whether or not given color is considered "light"
     * @param string $color
     * @param int    $threshold
     * @return bool
     * @throws Exception
     */
    public function isLight($color, $threshold = 130)
    {
        $color = self::_checkHex($color);
        // Get our color
        // Calculate straight from rbg
        $r = hexdec($color[0].$color[1]);
        $g = hexdec($color[2].$color[3]);
        $b = hexdec($color[4].$color[5]);

        return (( $r*299 + $g*587 + $b*114 )/1000 > $threshold);
    }

    /**
     * Returns whether or not a given color is considered "dark"
     * @param string $color
     * @param int    $threshold
     * @return bool
     * @throws Exception
     */
    public function isDark($color, $threshold = 130)
    {
        $color = self::_checkHex($color);
        // Get our color
        // Calculate straight from rbg
        $r = hexdec($color[0].$color[1]);
        $g = hexdec($color[2].$color[3]);
        $b = hexdec($color[4].$color[5]);

        return (( $r*299 + $g*587 + $b*114 )/1000 <= $threshold);
    }

    /**
     * Returns the complimentary color
     * @param string $color
     * @return string Complementary hex color
     */
    public function complementary($color)
    {
        $color = self::_checkHex($color);
        // Get our HSL
        $hsl = $this->hexToHsl($color, true);
        // Adjust Hue 180 degrees
        $hsl['H'] += ($hsl['H'] > 180) ? -180:180;

        // Return the new value in HEX
        return self::_hslToHex($hsl);
    }

    /**
     * Returns an array with the input color and a slightly darkened / lightened counterpart
     * @param string $color
     * @param int    $amount
     * @param int    $threshold
     * @return array
     */
    public function gradientColors($color, $amount = self::DEFAULT_ADJUST, $threshold = 130)
    {
        // Decide which color needs to be made
        if ($this->isLight($color, $threshold)) {
            $lightColor = $color;
            $darkColor = $this->darken($color, $amount);
        } else {
            $lightColor = $this->lighten($color, $amount);
            $darkColor = $color;
        }

        // Return our gradient array
        return array( "light" => $lightColor, "dark" => $darkColor );
    }

    /**
     * Returns a string containing CSS for a gradient background
     * @param mixed  $color
     * @param string $direction
     * @param int    $amount
     * @param int    $threshold
     * @return string
     */
    public function gradient($color, $direction = 'horizontal', $amount = self::DEFAULT_ADJUST, $threshold = 130)
    {
        if (is_string($amount)) {
            $color = $this->_checkHex($color);
            $amount = $this->_checkHex($amount);
            $g = ['light' => '#'.$color, 'dark' => '#'.$amount];
        } else {
            $g = $this->gradientColors($color, $amount, $threshold);
        }
        $css = "";

        $radial = false;

        switch ($direction) {
            case 'horizontal':
                $nonStandard = 'left';
                $standard = 'to right';
                $gType = 1;
                break;
            case 'vertical':
                $nonStandard = 'top';
                $standard = 'to bottom';
                $gType = 0;
                break;
            case 'diagonalDown':
                $nonStandard = '-45deg';
                $standard = '135deg';
                $gType = 1;
                break;
            case 'diagonalUp':
                $nonStandard = '45deg';
                $standard = '45deg';
                $gType = 1;
                break;
            case 'radial':
                $nonStandard = 'center, ellipse cover';
                $standard = 'ellipse at center';
                $gType = 1;
                $radial = true;
                break;
            default:
                $nonStandard = 'top';
                $standard = 'to bottom';
                $gType = 0;
                break;
        }

        /* fallback/image non-cover color */
        $css .= "background-color: #".$this->_checkHex($color).";";

        if ($radial) {
            /* IE Browsers */
            $css .= "filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='".$g['light']."', endColorstr='".$g['dark']."', GradientType={$gType});";

            /* Safari 5.1+, Mobile Safari, Chrome 10+ */
            $css .= "background: -webkit-radial-gradient({$nonStandard}, ".$g['light'].", ".$g['dark'].");";

            /* Firefox 3.6+ OLD */
            $css .= "background: -moz-radial-gradient({$nonStandard}, ".$g['light'].", ".$g['dark'].");";

            /* Opera 11.10+ OLD */
            $css .= "background: -o-radial-gradient({$nonStandard}, ".$g['light'].", ".$g['dark'].");";

            /* Unprefixed version (standards): FF 16+, IE10+, Chrome 26+, Safari 7+, Opera 12.1+ */
            $css .= "background: radial-gradient({$standard}, ".$g['light'].", ".$g['dark'].");";
        } else {
            /* IE Browsers */
            $css .= "filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='".$g['light']."', endColorstr='".$g['dark']."', GradientType={$gType});";

            /* Safari 5.1+, Mobile Safari, Chrome 10+ */
            $css .= "background-image: -webkit-linear-gradient({$nonStandard}, ".$g['light'].", ".$g['dark'].");";

            /* Firefox 3.6+ OLD */
            $css .= "background-image: -moz-linear-gradient({$nonStandard}, ".$g['light'].", ".$g['dark'].");";

            /* Opera 11.10+ OLD */
            $css .= "background-image: -o-linear-gradient({$nonStandard}, ".$g['light'].", ".$g['dark'].");";

            /* Unprefixed version (standards): FF 16+, IE10+, Chrome 26+, Safari 7+, Opera 12.1+ */
            $css .= "background-image: linear-gradient({$standard}, ".$g['light'].", ".$g['dark'].");";
        }

        // Return our CSS
        return $css;
    }

    /////////////////////
    // Private         //
    /////////////////////

    /**
     * Given a HSL associative array returns the equivalent HEX string
     * @param array $hsl
     * @return string HEX string
     * @throws \Exception "Bad HSL Array"
     */
    private static function _hslToHex($hsl = array())
    {
        // Make sure it's HSL
        if (empty($hsl) || !isset($hsl["H"]) || !isset($hsl["S"]) || !isset($hsl["L"])) {
            throw new \Exception("Param was not an HSL array");
        }
        list($h, $s, $l) = array($hsl['H']/360, $hsl['S'], $hsl['L']);
        if ($s == 0) {
            $r = $l * 255;
            $g = $l * 255;
            $b = $l * 255;
        } else {
            if ($l < 0.5) {
                $var2 = $l*(1+$s);
            } else {
                $var2 = ($l+$s) - ($s*$l);
            }
            $var1 = 2 * $l - $var2;
            $r = round(255 * self::_hueToRgb($var1, $var2, $h + (1/3)));
            $g = round(255 * self::_hueToRgb($var1, $var2, $h));
            $b = round(255 * self::_hueToRgb($var1, $var2, $h - (1/3)));
        }
        // Convert to hex
        $r = dechex($r);
        $g = dechex($g);
        $b = dechex($b);
        // Make sure we get 2 digits for decimals
        $r = (strlen("".$r) === 1) ? "0".$r:$r;
        $g = (strlen("".$g) === 1) ? "0".$g:$g;
        $b = (strlen("".$b) === 1) ? "0".$b:$b;

        return '#'.$r.$g.$b;
    }

    /**
     * Given an RGB associative array returns the equivalent HEX string
     * @param array $rgb
     * @return string RGB string
     * @throws \Exception "Bad RGB Array"
     */
    private static function _rgbToHex($rgb = array())
    {
        // Make sure it's RGB
        if (empty($rgb) || !isset($rgb["R"]) || !isset($rgb["G"]) || !isset($rgb["B"])) {
            throw new \Exception("Param was not an RGB array");
        }

        // Convert RGB to HEX
        $hex[0] = dechex($rgb['R']);
        $hex[1] = dechex($rgb['G']);
        $hex[2] = dechex($rgb['B']);

        return '#'.implode('', $hex);
    }

    /**
     * Darkens a given HSL array
     * @param array $hsl
     * @param int $amount
     * @return array $hsl
     */
    private function _darken($hsl, $amount = self::DEFAULT_ADJUST)
    {
        // Check if we were provided a number
        if ($amount) {
            $hsl['L'] = ($hsl['L'] * 100) - $amount;
            $hsl['L'] = ($hsl['L'] < 0) ? 0:$hsl['L']/100;
        } else {
            // We need to find out how much to darken
            $hsl['L'] = $hsl['L']/2 ;
        }

        return $hsl;
    }
    /**
     * Lightens a given HSL array
     * @param array $hsl
     * @param int $amount
     * @return array $hsl
     */
    private function _lighten($hsl, $amount = self::DEFAULT_ADJUST)
    {
        // Check if we were provided a number
        if ($amount) {
            $hsl['L'] = ($hsl['L'] * 100) + $amount;
            $hsl['L'] = ($hsl['L'] > 100) ? 1:$hsl['L']/100;
        } else {
            // We need to find out how much to lighten
            $hsl['L'] += (1-$hsl['L'])/2;
        }

        return $hsl;
    }

    /**
     * Mix 2 rgb colors and return an rgb color
     * @param array $rgb1
     * @param array $rgb2
     * @param int $amount ranged -100..0..+100
     * @return array $rgb
     *
     * 	ported from http://phpxref.pagelines.com/nav.html?includes/class.colors.php.source.html
     */
    private function _mix($rgb1, $rgb2, $amount = 0)
    {
        $r1 = ($amount + 100) / 100;
        $r2 = 2 - $r1;
        $rmix = (($rgb1['R'] * $r1) + ($rgb2['R'] * $r2)) / 2;
        $gmix = (($rgb1['G'] * $r1) + ($rgb2['G'] * $r2)) / 2;
        $bmix = (($rgb1['B'] * $r1) + ($rgb2['B'] * $r2)) / 2;

        return array('R' => $rmix, 'G' => $gmix, 'B' => $bmix);
    }

    /**
     * Given a Hue, returns corresponding RGB value
     * @param int $v1
     * @param int $v2
     * @param int $vH
     * @return int
     */
    private static function _hueToRgb( $v1,$v2,$vH )
    {
        if ($vH < 0) {
            $vH += 1;
        }
        if ($vH > 1) {
            $vH -= 1;
        }
        if ((6*$vH) < 1) {
            return ($v1 + ($v2 - $v1) * 6 * $vH);
        }
        if ((2*$vH) < 1) {
            return $v2;
        }
        if ((3*$vH) < 2) {
            return ($v1 + ($v2-$v1) * ( (2/3)-$vH ) * 6);
        }

        return $v1;
    }

    /**
     * You need to check if you were given a good hex string
     * @param string $hex
     * @return string Color
     * @throws \Exception "Bad color format"
     */
    private static function _checkHex($hex)
    {
        // Strip # sign is present
        $color = str_replace("#", "", $hex);
        // Make sure it's 6 digits
        if (strlen($color) == 3) {
            $color = $color[0].$color[0].$color[1].$color[1].$color[2].$color[2];
        } elseif (strlen($color) != 6) {
            throw new \Exception("HEX color needs to be 6 or 3 digits long, received: ".$color);
        }

        return $color;
    }
}
