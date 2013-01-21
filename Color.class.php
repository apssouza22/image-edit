<?php

/**
 * GD2 Imaging (part of Lotos Framework)
 *
 * Copyright (c) 2005-2011 Artur Graniszewski (aargoth@boo.pl)
 * All rights reserved.
 *
 * @category   Library
 * @package    Lotos
 * @subpackage Imaging
 * @copyright  Copyright (c) 2005-2011 Artur Graniszewski (aargoth@boo.pl)
 * @license    GNU LESSER GENERAL PUBLIC LICENSE Version 3, 29 June 2007
 * @version    1.0.0
 */

class Color
{
	const RED = 1;
	const GREEN = 2;
	const BLUE = 4;

	/**
	 * HSL color model.
	 */
	const HSL = 8;

	/**
	 * HSV color model.
	 */
	const HSV = 16;

	/**
	 * HSI color model.
	 */
	const HSI = 32;

	/**
	 * RGB value of the color.
	 *
	 * @var int
	 */
	public $rgb;

	/**
	 * Creates color instance.
	 *
	 * @param int $rgbOrRedValue Full RGB value or Red value (as int)
	 * @param mixed $greenValue Green value (if $rgbOrRedValue does not contain entire RGB value)
	 * @param mixed $blueValue Blue value (if $rgbOrRedValue does not contain entire RGB value)
	 * @return Color
	 */
	public function __construct($rgbOrRedValue, $greenValue = null, $blueValue = null) {
		if($greenValue === null && $blueValue === null) {
			$this->rgb = $rgbOrRedValue;
			return;
		}

		$this->rgb = ($rgbOrRedValue << 16) + ($greenValue << 8) + $blueValue;
	}

	/**
	 * Calculates a value of a red component of the RGB value.
	 *
	 * Note: should not be used for performance reasons (reason PHP < 5.4 functions overhead).
	 * @return int
	 */
	public function getRed() {
		return ($this->rgb >> 16) & 0xff;
	}

	/**
	 * Calculates a value of a green component of the RGB value.
	 *
	 * Note: should not be used for performance reasons (reason PHP < 5.4 functions overhead).
	 * @return int
	 */
	public function getGreen() {
		return ($this->rgb >> 8) & 0xff;
	}

	/**
	 * Calculates a value of a blue component of the RGB value.
	 *
	 * Note: should not be used for performance reasons (reason PHP < 5.4 functions overhead).
	 * @return int
	 */
	public function getBlue() {
		return $this->rgb & 0xff;
	}

	/**
	 * Returns color chroma.
	 *
	 * @return float
	 */
	public function getChroma() {
		$r = ($this->rgb >> 16) & 0xff;
		$g = ($this->rgb >> 8) & 0xff;
		$r = $this->rgb & 0xff;
		return (max($r, $g, $b) - min($r, $g, $b)) / 255;
	}
	/**
	 * Returns color hue.
	 *
	 * @return int Value in degrees (0 => 360).
	 */
	public function getHue() {
		$r = (($rgb >> 16) & 0xff) / 255;
		$g = (($rgb >> 8) & 0xff) / 255;
		$b = ($rgb & 0xff) / 255;
		$hue = rad2deg(atan2(1.7320508075688 /* = sqrt(3) */ * ($g - $b), 2 * $r - $g - $b));
		return $hue >= 0 ? $hue : 360 + $hue;
	}

	/**
	 * Returns color saturation.
	 *
	 * @param int $colorMode Color mode for saturation (use Color::HSV, Color::HSI or Color::HSL as the value), default is Color::HSL
	 * @return float
	 */
	public function getSaturation($colorMode = self::HSL) {
		$r = (($this->rgb >> 16) & 0xff) / 255;
		$g = (($this->rgb >> 8) & 0xff) / 255;
		$b = ($this->rgb & 0xff) / 255;
		$max = max($r, $g, $b);
		$min = min($r, $g, $b);
		if($max === 0) {
			return 0;
		}
		if($colorMode === self::HSL) {
			$diff = $max - $min;
			//$luminance = ($max + $min) / 2;
			if($diff < 0.5) {
				return $diff / ($max + $min);
			} else {
				return $diff / (2 - $max - $min);
			}
		} else if($colorMode === self::HSV) {
			return ($max - $min) / $max;
		} else if($colorMode === self::HSI) {
			if($max - $min === 0) {
				return 0;
			} else {
				return 1 - $min / (($r + $g + $b) / 3);
			}
		}

		throw new Exception('Unknown color mode');
	}

	/**
	 * Returns hexadecimal representation of the current color.
	 *
	 * @return string
	 */
	public function getHexValue() {
		return str_pad(dechex($this->rgb), 6, '0', STR_PAD_LEFT);
	}

	/**
	 * Returns color luminance.
	 *
	 * @param int $mode Luminance mode: 0 = fastest, 1 = Digital CCIR601, 2 = Digital ITU-R, 3 = HSP (best quality), Color::HSL = HSL (default), Color::HSV = HSV
	 * @return float
	 */
	public function getLuminance($mode = self::HSL) {
		$r = ($this->rgb >> 16) & 0xff;
		$g = ($this->rgb >> 8) & 0xff;
		$b = $this->rgb & 0xff;

		switch ($mode) {
			case 0:
				// fastest, but less accurate.
				return (($r + $r + $r + $b + $g + $g + $g + $g) >> 3) / 255;
				break;
			case 1:
				// Digital CCIR601
				return (int)(0.299 * $r + 0.587 * $g + 0.114 * $b) / 255;
				break;
			case 2:
				// Ditigal ITU-R
				return (int)(0.2126 * $r + 0.7152 * $g + 0.0722 * $b) / 255;
				break;
			case 3:
				// HSP algorithm
				return round(sqrt(0.299 * $r * $r + 0.587 * $g * $g + 0.114 * $b * $b)) / 255;
				break;
			case self::HSL:
				// HSL algorithm
				return (max($r, $g, $b) + min($r, $g, $b)) / (2 * 255);
				break;
			case self::HSV:
				// HSV algorithm
				return max($r, $g, $b) / 255;
				break;
			case self::HSI:
				// HSI algorithm
				return ($r + $g + $b) / (3 * 255);
				break;
			default:
				throw new Exception('Unknown color mode');
				break;
		}
	}
}
?>
