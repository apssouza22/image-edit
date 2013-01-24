<?php

/*
 * 	Image Library to edit and modify Images
 * 	Copyright (C) 2011  Jakob Riedle <ijake@palmato.de>.
 * 	
 * 	This program is free software: you can redistribute it and/or modify
 * 	it under the terms of the GNU General Public License as published by
 * 	the Free Software Foundation, either version 3 of the License, or
 * 	(at your option) any later version.
 *
 * 	This program is distributed in the hope that it will be useful,
 * 	but WITHOUT ANY WARRANTY; without even the implied warranty of
 * 	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * 	GNU General Public License for more details.
 *
 * 	You should have received a copy of the GNU General Public License
 * 	along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * 
 * 	@Jakob Riedle <ijake@palmato.de> 
 * 	@version 1.0 
 * 	@since 1.0 
 * 	@access public 
 * 	@copyright iJake@Palmato 
 * 
 */

require_once("FaceDetect.class.php");
require_once("Color.class.php");

class ImageEdit
{

	/**
	 * Image Resource to be edited
	 * 
	 * @var image Resource 
	 * @access private 
	 * @see loadFile() 
	 */
	private $image;

	/**
	 * Image Height
	 *
	 * @access private 
	 */
	private $height;

	/**
	 * Image Width
	 *
	 * @access private 
	 */
	private $width;
	// Only check for a face once because very slow
	private $face;
	public $extension;
	private $pathImage;

	/**
	 * Constructor
	 * 
	 * @param String $url File to load
	 */
	public function __construct($url = null)
	{
		$this->image = null;

		// Set The Image (if one was passed)
		$this->setImage($url);

		// Reset Face data
		$this->face = null;
		$this->prefs = Array();
	}

	/**
	 * Set The Image
	 *
	 * @param String $url File to load
	 */
	public function setImage($url)
	{
		// To Be sure
		unset($this->image);
		$this->image = null;

		list($w, $h, $ext) = getimagesize($url);
		$this->extension = $ext;
		$this->pathImage = $url;

		// Now you can pass a GD IMage or Imagedit Class to this function!
		$image = $this->extractImage($url);

		// Valid Image?
		if ($image != false) {
			// Valid!
			$this->image = $image;
			$this->preserveAlpha();
			$this->setSize();
		}
	}

	/**
	 * Check if an Image is loaded
	 *
	 * @return Boolean Is an Image Loaded?
	 */
	public function isLoaded()
	{
		return ( $this->image != null );
	}

	/**
	 * Makes the image preserve it's Alpha Channel
	 *
	 * @access private 
	 */
	private function preserveAlpha($image = null)
	{
		if (!$this->isLoaded())
			return false;

		if ($image != null) {
			// Preserve Alpha-Channel
			imagealphablending($image, true);
			imagesavealpha($image, true);
		} else {
			// Preserve Alpha-Channel
			imagealphablending($this->image, true);
			imagesavealpha($this->image, true);
		}
	}

	/**
	 * Save the Image-dimensions to $height and $width
	 * 
	 * @see $height
	 * @see $width
	 * @access private 
	 */
	private function setSize()
	{
		if (!$this->isLoaded())
			return false;

		$this->width = imagesx($this->image);
		$this->height = imagesy($this->image);
	}

	/**
	 * Check if the image that is loaded has a Face on it
	 * 
	 * @return Boolean
	 */
	public function hasFace()
	{
		if (!$this->isLoaded())
			return false;

		if ($this->face != null)
			return ( $this->face != false );

		$detector = new FaceDetect('detection.dat');
		if ($detector->face_detect($this->image)) {
			//echo "hallo";
			$this->face = $detector->getFace();
			return true;
		} else {
			$this->face = false;
			return false;
		}
	}

	/**
	 * Rotates the image by a specific angle
	 * 
	 * @param Float $angle The Angle the Image is rotated by
	 */
	public function rotate($angle, $crop = false)
	{
		if (!$this->isLoaded())
			return false;

		$this->image = imagerotate($this->image, $angle, -1);
		$this->setSize();
		$this->preserveAlpha();

		if ($crop) {
			$h = $this->height;
			$w = $this->width;
			$dimens = $this->rverdrehung($w, $h, $angle);
			$y_crop = ( $h - $dimens['height'] ) / 2;
			$x_crop = ( $w - $dimens['width'] ) / 2;
			$this->crop($x_crop, $y_crop, $dimens['width'], $dimens['height']);
		}

		return $this;
	}

	/**
	 * Rotation of a Rectangle inside another
	 * 
	 * @param Integer $width Width of the original Rect
	 * @param Integer $height Height of the original Rect
	 * @param Integer $angle Angle to rotate the rect
	 * @copyright Jakob Riedle
	 */
	/* private function rverdrehung( $width , $height , $angle ){
	  $angle = deg2rad( $angle%90 );
	  $n = $height;
	  $m = $width;
	  $n0 = $n - $n / ( tan( $angle ) * ( $n / $m ) + 1 );
	  $m0 = $n0 * $ratio;
	  $n_new = sqrt( $m0*$m0 + ( $n - $n0 )*( $n - $n0 ) );
	  return array( "width" => intval( $n_new * ( $m / $n ) ) , "height" => intval( $n_new ) );
	  } */
	private function rverdrehung($width, $height, $angle)
	{
		$angle = deg2rad($angle % 90);
		$equi0 = $height / tan($angle + atan($height / $width));
		$r = ( $equi0 * $equi0 + $height * $height ) / ( $width * $width + $height * $height );
		return array("width" => intval($width * $r), "height" => intval($height * $r));
	}

	/**
	 * Convert the Image to grayscale
	 */
	public function grayscale()
	{
		if (!$this->isLoaded())
			return false;

		imagefilter($this->image, IMG_FILTER_GRAYSCALE);

		return $this;
	}

	/**
	 * Extract Either the Image out of Imagedit-Class or return an Image
	 */
	private function extractImage($image)
	{
		// Imagedit Class
		if (is_object($image) && get_class($image) == "ImageEdit")
			return $image->getImage();
		// GD Image
		elseif (@imagesx($image) > 0)
			return $image;
		// Filename
		elseif (is_file($image)) {
			//var_dump($image);
			return @imagecreatefromstring(file_get_contents($image));
		}
		// URL
		elseif (preg_match('|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', $image)) {
			if (!in_array('curl', get_loaded_extensions()))
				throw new Exeption("cUrl Library has to be installed to include cross-domain Images!");
			$curl = curl_init();
			curl_setopt($curl, CURLOPT_URL, $image);
			ob_start();
			curl_exec($curl);
			$data = ob_get_contents();
			ob_end_clean();
			return @imagecreatefromstring($data);
		}
		else {
			return false;
		}
	}

	/**
	 * Multiply Brightness of two pictures
	 *
	 * @param $image Gray (128,128,128) effects nothing White makes bright, Black makes dark
	 */
	public function multiply($image, $multiplyAlpha = false)
	{
		$image = $this->extractImage($image);

		if (!$this->isLoaded())
			return false;

		if (!@imagesx($image))
			throw new Exception("No valid image!");

		imageAlphaBlending($this->image, false);
		$noAlpha = !$multiplyAlpha;

		for ($x = 0; $x < $this->width; $x++) {
			for ($y = 0; $y < $this->height; $y++) {
				$color1 = imagecolorsforindex($this->image, imagecolorat($this->image, $x, $y));
				$color2 = imagecolorsforindex($image, imagecolorat($image, $x, $y));
				$alpha = ( $color1["alpha"] + $color2["alpha"] / 255) * $multiplyAlpha + $color1["alpha"] * $noAlpha;
				$color1 = imagecolorallocatealpha($this->image, $color1["red"] * ($color2["red"] / 255), $color1["green"] * ($color2["green"] / 255), $color1["blue"] * ($color2["blue"] / 255), $alpha);
				imagesetpixel($this->image, $x, $y, $color1);
			}
		}
		imageAlphaBlending($this->image, true);

		return $this;
	}

	/**
	 * Mask The Picture width B/W
	 *
	 * @param $image The Mask to be applied ( Black means full opaque, White means full transparent )
	 */
	public function mask($image)
	{
		$image = $this->extractImage($image);

		if (!$this->isLoaded())
			return false;

		if (!@imagesx($image))
			throw new Exception("No valid image!");

		imageAlphaBlending($this->image, false);

		for ($x = 0; $x < $this->width; $x++) {
			for ($y = 0; $y < $this->height; $y++) {
				$color1 = imagecolorsforindex($this->image, imagecolorat($this->image, $x, $y));
				$color2 = imagecolorsforindex($image, imagecolorat($image, $x, $y));
				$alpha = $color1["alpha"] + ( $color2["red"] + $color2["green"] + $color2["blue"] - 3 ) / 6;
				$alpha = intval($alpha);
				if ($alpha > 127)
					$alpha = 127;
				$color = imagecolorallocatealpha($this->image, $color1["red"], $color1["green"], $color1["blue"], $alpha);
				imagesetpixel($this->image, $x, $y, $color);
			}
		}

		imageAlphaBlending($this->image, true);

		return $this;
	}

	/**
	 * Flip the Image Horizontally
	 */
	public function flipH()
	{
		if (!$this->isLoaded())
			return false;

		// Create the new Image	
		$new_image = imagecreatetruecolor($this->width, $this->height);
		imageAlphaBlending($new_image, false);

		for ($x = 0; $x < $this->width; $x++) {
			for ($y = 0; $y < $this->height; $y++) {
				$color = imagecolorsforindex($this->image, imagecolorat($this->image, $x, $y));
				//var_dump(imagecolorat( $this->image , $x , $y ));
				imagesetpixel($new_image, $this->width - $x - 1, $y, imagecolorallocatealpha($new_image, $color["red"], $color["green"], $color["blue"], $color["alpha"]));
			}
		}
		unset($this->image);

		$this->image = $new_image;
		$this->preserveAlpha();

		return $this;
	}

	/**
	 * Flip the Image Vertically
	 */
	public function flipV()
	{
		if (!$this->isLoaded())
			return false;

		// Create the new Image	
		$new_image = imagecreatetruecolor($this->width, $this->height);
		imageAlphaBlending($new_image, false);

		for ($x = 0; $x < $this->width; $x++) {
			for ($y = 0; $y < $this->height; $y++) {
				$color = imagecolorsforindex($this->image, imagecolorat($this->image, $x, $y));
				imagesetpixel($new_image, $x, $this->height - $y - 1, imagecolorallocatealpha($new_image, $color["red"], $color["green"], $color["blue"], $color["alpha"]));
			}
		}
		unset($this->image);

		$this->image = $new_image;
		$this->preserveAlpha();

		return $this;
	}

	/**
	 * Negate the Image
	 */
	public function negate()
	{
		if (!$this->isLoaded())
			return false;

		imagefilter($this->image, IMG_FILTER_NEGATE);

		return $this;
	}

	/**
	 * Set the Opacity (linear/proportional)
	 *
	 * @param Integer $opacity Opacity to set (-100(opaque) To 100(transparent))
	 * @param Boolean $linear True for simply adding the opacity
	 */
	public function opacity($opacity, $linear = false)
	{
		if (!$this->isLoaded())
			return false;

		$opacity = min(100, max(-100, $opacity));

		// For Proportional Multiplication
		if ($linear) {
			if ($opacity < 0)
				$opacity = (-$opacity) / ( 100 / 3 ) + 1;
			elseif ($opacity > 0)
				$opacity = (100 - $opacity) / 132 + 0.25;
			else
				$opacity = 1;

			// Create the new Image	
			$new_image = imagecreatetruecolor($this->width, $this->height);
			imageAlphaBlending($new_image, false);

			for ($x = 0; $x < $this->width; $x++) {
				for ($y = 0; $y < $this->height; $y++) {
					$color = imagecolorsforindex($this->image, imagecolorat($this->image, $x, $y));
					$alpha = max(0, min(127, 127 - ( 127 - $color["alpha"] ) * $opacity));
					imagesetpixel($new_image, $x, $y, imagecolorallocatealpha($new_image, $color["red"], $color["green"], $color["blue"], $alpha));
				}
			}
		}

		// For Linear Addition
		else {
			if ($opacity < 0)
				$opacity = (-$opacity) / 100 * 127;
			elseif ($opacity > 0)
				$opacity = ($opacity) / 100 * 127;

			// Create the new Image	
			$new_image = imagecreatetruecolor($this->width, $this->height);
			imageAlphaBlending($new_image, false);

			for ($x = 0; $x < $this->width; $x++) {
				for ($y = 0; $y < $this->height; $y++) {
					$color = imagecolorsforindex($this->image, imagecolorat($this->image, $x, $y));
					$alpha = max(0, min(127, $color["alpha"] + $opacity));
					imagesetpixel($new_image, $x, $y, imagecolorallocatealpha($new_image, $color["red"], $color["green"], $color["blue"], $alpha));
				}
			}
		}
		unset($this->image);

		$this->image = $new_image;
		$this->preserveAlpha();

		return $this;
	}

	/**
	 * Put some Overlay/Image over this Image
	 *
	 * @param Image Resource $image The Image as a Resource or an Imagedit Class
	 * @param Integer $x X-Position to put the Overlay (Optional, defaults 0)
	 * @param Integer $y Y-Position to put the Overlay (Optional, defaults 0)
	 * @param String $align Alignment of the Overlay (Optional, defaults "topleft")
	 */
	public function overlay($image, $x = 0, $y = 0, $align = "topleft")
	{
		$image = $this->extractImage($image);

		if (!$this->isLoaded())
			return false;

		if (!@imagesx($image))
			throw new Exception("No valid image!");

		$width = imagesx($image);
		$height = imagesy($image);
		$myWidth = $this->width;
		$myHeight = $this->height;
		$x = ( $align == "topleft" || $align == "lefttop" || $align == "bottomleft" || $align == "leftbottom" ) ? $x : $myWidth - $width - $x;
		$y = ( $align == "topleft" || $align == "lefttop" || $align == "topright" || $align == "righttop" ) ? $y : $myHeight - $height - $y;

		imagecopy(
				$this->image /* Destination */
				, $image /* Source */
				, $x /* Dest X */
				, $y /* Dest Y */
				, 0 /* Source X */
				, 0 /* Source Y */
				, $width /* Source Width */
				, $height /* Source Height */
		);

		return $this;
	}

	/**
	 * Set A Specific Color(-Range) To be Transparent
	 *
	 * @param Array $color The Main Color of Transparency
	 * @param Integer $tolerance Tolerance of Main Color (Optional, defaults 0 )
	 * @param String $fix Whether the Transparency should be fixed ( See examples )
	 */
	public function setTransparent($color, $tolerance = 0, $fix = false)
	{
		if (!$this->isLoaded())
			return false;

		$tolerance += 1;
		$r = $color[0];
		$g = $color[1];
		$b = $color[2];

		imageAlphaBlending($this->image, false);

		for ($x = 0; $x < $this->width; $x++) {
			for ($y = 0; $y < $this->height; $y++) {
				$color2 = imagecolorsforindex($this->image, imagecolorat($this->image, $x, $y));
				$r2 = $color2["red"];
				$g2 = $color2["green"];
				$b2 = $color2["blue"];
				if (!$fix) {
					if ($b < ( $b2 + $tolerance ) && $g < ( $g2 + $tolerance ) && $r < ( $r2 + $tolerance ) && $b > ( $b2 - $tolerance ) && $g > ( $g2 - $tolerance ) && $r > ( $r2 - $tolerance ))
						imagesetpixel($this->image, $x, $y, IMG_COLOR_TRANSPARENT);
				}
				else {
					// Color-distance to Alpha
					$alpha = intval(127 - sqrt(abs(($r2 - $r) * ($g2 - $g) * ($b2 - $b))));
					$alpha += $tolerance;
					imagesetpixel($this->image, $x, $y, imagecolorallocatealpha($this->image, $r2, $g2, $b2, min(127, max(0, $alpha))));
				}
			}
		}
		imageAlphaBlending($this->image, true);

		return $this;
	}

	/**
	 * Set the Image Brightness
	 *
	 * @param $brighness Brightness to apply ( -100 To 100 )
	 */
	public function brightness($brightness)
	{
		if (!$this->isLoaded())
			return false;

		$brightness = min(100, max(-100, $brightness));
		$brightness = $brightness / 100 * 255;
		imagefilter($this->image, IMG_FILTER_BRIGHTNESS, $brightness);

		return $this;
	}

	/**
	 * Set the Image Contrast
	 *
	 * @param $bcontrast Contrast to apply ( -100 To 100 )
	 */
	public function contrast($contrast)
	{
		if (!$this->isLoaded())
			return false;

		$contrast = min(100, max(-100, $contrast));
		imagefilter($this->image, IMG_FILTER_CONTRAST, $contrast);

		return $this;
	}

	/**
	 * Add some Border
	 *
	 * @param Integer $left Left-Border-Width
	 * @param Integer $top Top-Border-Width
	 * @param Integer $right Right-Border-Width
	 * @param Integer $bottom Bottom-Border-Width
	 * @param Array $color Color of the Border
	 */
	public function border($left, $top, $right, $bottom, $color = Array(0, 0, 0, 0))
	{
		if (!$this->isLoaded())
			return false;

		// Create a new Image
		$new_image = imagecreatetruecolor($this->width + $left + $right, $this->height + $top + $bottom);
		imageAlphaBlending($new_image, false);

		// Fill the New Image!
		imagefill($new_image, 0, 0, imagecolorallocatealpha($new_image, $color[0], $color[1], $color[2], $color[3]));

		imagecopy(
				$new_image /* Destination */
				, $this->image /* Source */
				, $left /* Dest X */
				, $top /* Dest Y */
				, 0 /* Source X */
				, 0 /* Source Y */
				, $this->width /* Source/Dest Width */
				, $this->height /* Source/Dest Height */
		);
		unset($this->image);

		$this->image = $new_image;
		$this->setSize();
		$this->preserveAlpha();
	}

	/**
	 * Crop the Image by Position-Dimension-Pair
	 * 
	 * @param Integer $x X-Position of Left-Upper Corner
	 * @param Integer $y Y-Position of Left-Upper Corner
	 * @param Integer $width Width of the cropped Image
	 * @param Integer $height Height of the cropped Image
	 */
	public function crop($x, $y, $width, $height)
	{
		if (!$this->isLoaded())
			return false;

		$new_image = imagecreatetruecolor($width, $height);
		imageAlphaBlending($new_image, false);

		imagecopy(
				$new_image /* Destination */
				, $this->image /* Source */
				, 0 /* Dest X */
				, 0 /* Dest Y */
				, $x /* Source X */
				, $y /* Source Y */
				, $width /* Source/Dest Width */
				, $height /* Source/Dest Height */
		);
		unset($this->image);

		$this->image = $new_image;
		$this->setSize();
		$this->preserveAlpha();

		return $this;
	}

	/**
	 * Cut Off none-important Border
	 * 
	 * @param Integer $tolerance Tolerance
	 * @param Boolean $ignoreAlpha Whether to ignore alpha-channel (optional, defaults to true)
	 */
	public function autoCrop($tolerance = 0, $ignoreAlpha = true)
	{
		if (!$this->isLoaded())
			return false;

		$color = imagecolorsforindex($this->image, imagecolorat($this->image, 0, 0));
		$r = $color["red"];
		$g = $color["green"];
		$b = $color["blue"];
		$a = $color["alpha"];

		$minx = -1;
		$tolerance += 1;

		for ($x = 0; $x < $this->width; $x++) {
			for ($y = 0; $y < $this->height; $y++) {
				$color2 = imagecolorsforindex($this->image, imagecolorat($this->image, $x, $y));
				$r2 = $color2["red"];
				$g2 = $color2["green"];
				$b2 = $color2["blue"];
				$a2 = $color2["alpha"];
				if ($ignoreAlpha && $b < ( $b2 + $tolerance ) && $g < ( $g2 + $tolerance ) && $r < ( $r2 + $tolerance ) && $b > ( $b2 - $tolerance ) && $g > ( $g2 - $tolerance ) && $r > ( $r2 - $tolerance )) {
					
				} elseif (!$ignoreAlpha && $b < ( $b2 + $tolerance ) && $g < ( $g2 + $tolerance ) && $r < ( $r2 + $tolerance ) && $a < ( $a2 + $tolerance ) && $b > ( $b2 - $tolerance ) && $g > ( $g2 - $tolerance ) && $r > ( $r2 - $tolerance ) && $a > ( $a2 - $tolerance )) {
					
				} else {
					$minx = $x;
					break 2;
				}
			}
		}
		//The image is filled with $color
		if ($minx == -1) {
			return $this;
		}
		for ($y = 0; $y < $this->height; $y++) {
			for ($x = $minx; $x < $this->width; $x++) {
				$color2 = imagecolorsforindex($this->image, imagecolorat($this->image, $x, $y));
				$r2 = $color2["red"];
				$g2 = $color2["green"];
				$b2 = $color2["blue"];
				$a2 = $color2["alpha"];
				if ($ignoreAlpha && $b < ( $b2 + $tolerance ) && $g < ( $g2 + $tolerance ) && $r < ( $r2 + $tolerance ) && $b > ( $b2 - $tolerance ) && $g > ( $g2 - $tolerance ) && $r > ( $r2 - $tolerance )) {
					
				} elseif (!$ignoreAlpha && $b < ( $b2 + $tolerance ) && $g < ( $g2 + $tolerance ) && $r < ( $r2 + $tolerance ) && $a < ( $a2 + $tolerance ) && $b > ( $b2 - $tolerance ) && $g > ( $g2 - $tolerance ) && $r > ( $r2 - $tolerance ) && $a > ( $a2 - $tolerance )) {
					
				} else {
					$miny = $y;
					break 2;
				}
			}
		}

		$color = imagecolorsforindex($this->image, imagecolorat($this->image, $this->width - 1, $this->height - 1));
		$r = $color["red"];
		$g = $color["green"];
		$b = $color["blue"];
		$a = $color["alpha"];

		for ($x = $this->width - 1; $x > $minx; $x--) {
			for ($y = 0; $y < $this->height; $y++) {
				$color2 = imagecolorsforindex($this->image, imagecolorat($this->image, $x, $y));
				$r2 = $color2["red"];
				$g2 = $color2["green"];
				$b2 = $color2["blue"];
				$a2 = $color2["alpha"];
				if ($ignoreAlpha && $b < ( $b2 + $tolerance ) && $g < ( $g2 + $tolerance ) && $r < ( $r2 + $tolerance ) && $b > ( $b2 - $tolerance ) && $g > ( $g2 - $tolerance ) && $r > ( $r2 - $tolerance )) {
					
				} elseif (!$ignoreAlpha && $b < ( $b2 + $tolerance ) && $g < ( $g2 + $tolerance ) && $r < ( $r2 + $tolerance ) && $a < ( $a2 + $tolerance ) && $b > ( $b2 - $tolerance ) && $g > ( $g2 - $tolerance ) && $r > ( $r2 - $tolerance ) && $a > ( $a2 - $tolerance )) {
					
				} else {
					$maxx = $x + 1;
					break 2;
				}
			}
		}

		for ($y = $this->height - 1; $y > $miny; $y--) {
			for ($x = $minx; $x < $maxx; $x++) {
				$color2 = imagecolorsforindex($this->image, imagecolorat($this->image, $x, $y));
				$r2 = $color2["red"];
				$g2 = $color2["green"];
				$b2 = $color2["blue"];
				$a2 = $color2["alpha"];
				if ($ignoreAlpha && $b < ( $b2 + $tolerance ) && $g < ( $g2 + $tolerance ) && $r < ( $r2 + $tolerance ) && $b > ( $b2 - $tolerance ) && $g > ( $g2 - $tolerance ) && $r > ( $r2 - $tolerance )) {
					
				} elseif (!$ignoreAlpha && $b < ( $b2 + $tolerance ) && $g < ( $g2 + $tolerance ) && $r < ( $r2 + $tolerance ) && $a < ( $a2 + $tolerance ) && $b > ( $b2 - $tolerance ) && $g > ( $g2 - $tolerance ) && $r > ( $r2 - $tolerance ) && $a > ( $a2 - $tolerance )) {
					
				} else {
					$maxy = $y + 1;
					break 2;
				}
			}
		}
		$new_image = imagecreatetruecolor($maxx - $minx, $maxy - $miny);
		imageAlphaBlending($new_image, false);

		imagecopy(
				$new_image /* Destination */
				, $this->image /* Source */
				, 0 /* Dest X */
				, 0 /* Dest Y */
				, $minx /* Source X */
				, $miny /* Source Y */
				, $maxx - $minx /* Source/Dest Width */
				, $maxy - $miny /* Source/Dest Height */
		);

		unset($this->image);

		$this->image = $new_image;
		$this->setSize();
		$this->preserveAlpha();

		return $this;
	}

	/**
	 * Crop the Image by Borders
	 * 
	 * @param Integer $left Left-Crop
	 * @param Integer $top Top-Crop
	 * @param Integer $right Right-Crop
	 * @param Integer $bottom Bottom-Crop
	 */
	public function cropBorder($left, $top, $right, $bottom)
	{
		if (!$this->isLoaded())
			return false;

		$new_image = imagecreatetruecolor($this->width - $left - $right, $this->height - $top - $bottom);
		imageAlphaBlending($new_image, false);

		imagecopy(
				$new_image /* Destination */
				, $this->image /* Source */
				, 0 /* Dest X */
				, 0 /* Dest Y */
				, $left /* Source X */
				, $top /* Source Y */
				, $this->width - $left - $right /* Source/Dest Width */
				, $this->height - $top - $bottom /* Source/Dest Height */
		);
		unset($this->image);

		$this->image = $new_image;
		$this->setSize();
		$this->preserveAlpha();

		return $this;
	}

	/**
	 * Crop image by area selected, used in conjunction with javascript
	 *
	 * @param array $arrayAreaSelected a array with position x,y and w,h;
	 * @param int $wmax width max at moment of crop
	 * @param int $hmax height max  at moment of crop
	 */
	public function cropSelectedArea($arrayAreaSelected, $wmax = null, $hmax = null)
	{

		if ($this->width > $wmax || $this->height > $hmax) {
			$arrayAreaSelected = $this->dimensionsBack($arrayAreaSelected, $wmax, $hmax);
		}

		//AREA SELECTED
		$posX = $arrayAreaSelected['x'];
		$posY = $arrayAreaSelected['y'];
		$widthSelected = $arrayAreaSelected['w'];
		$heightSelected = $arrayAreaSelected['h'];

		$newImage = imagecreatetruecolor($widthSelected, $heightSelected);
		imageAlphaBlending($newImage, false);

		imagecopyresampled(
				$newImage /* Destination */
				, $this->image /* Source */
				, 0 /* Dest X */
				, 0 /* Dest Y */
				, $posX /* Source X */
				, $posY /* Source Y */
				, $widthSelected /* Dest Width */
				, $heightSelected /* Dest Height */
				, $widthSelected /* Source Width */
				, $heightSelected /* Source Height */
		);
		unset($this->image);

		$this->image = $newImage;
		$this->setSize();
		$this->preserveAlpha();

		return $this;
	}

	/**
	 * Back dimensions original of image
	 * used for fit imagen in screen at moment of crop
	 * @param array $arrayAreaSelected a array with position x,y and w,h;
	 * @param int $wmax width max at moment of crop
	 * @param int $hmax height max  at moment of crop
	 */
	private function dimensionsBack($arrayAreaSelected, $wmax = null, $hmax = null)
	{
		if ($wmax || $hmax) {
			$wmax = $wmax ? $wmax : $arrayAreaSelected['w'];
			$hmax = $hmax ? $hmax : $arrayAreaSelected['h'];
			$scale = min($this->width / $wmax, $this->height / $hmax);

			if ($scale > 1) {
				$arrayAreaSelected['x'] = $arrayAreaSelected['x'] * $scale;
				$arrayAreaSelected['y'] = $arrayAreaSelected['y'] * $scale;
				$arrayAreaSelected['w'] = $arrayAreaSelected['w'] * $scale;
				$arrayAreaSelected['h'] = $arrayAreaSelected['h'] * $scale;
			}
		}
		return $arrayAreaSelected;
	}

	public function getFace()
	{
		if ($this->hasFace()) {
			return $this->face;
		}
		return false;
	}

	/**
	 * Crop the Image to a Face
	 * 
	 * @param Boolean $preserveRatio Should the Output-Image be same-dimensioned as Input?
	 */
	public function cropFace($preserveRatio = false)
	{
		if (!$this->isLoaded())
			return false;

		if ($this->hasFace()) {
			$faceData = $this->face;
			$Fx = intval($faceData['x']);
			$Fy = intval($faceData['y']);
			$FWidth = intval($faceData['w']);
			$FHeight = intval($faceData['w']);
			$width = $FWidth;
			$height = $FHeight;
			$x = $Fx;
			$y = $Fy;
			if ($preserveRatio) {
				$ratio = $this->width / $this->height;
				if ($ratio > 1) { // wider than high
					$width = min($this->width, $FHeight * $ratio);
					$height = $width / $ratio;
				} elseif ($ratio < 1) { // higher than wide
					$height = min($this->height, $FWidth * $ratio);
					$width = $height * $ratio;
				}

				// When Size changes: Adjust X and Y Position to center face
				$y = $Fy + ( $FHeight - $height ) / 2;
				$x = $Fx + ( $FWidth - $width ) / 2;

				if ($x < 0)
					$x = 0;
				if ($y < 0)
					$y = 0;
				if ($x + $width > $this->width)
					$x = $this->width - $width;
				if ($y + $height > $this->height)
					$y = $this->height - $height;
			}
			// Convert them all to Int
			$width = intval($width);
			$height = intval($height);
			$x = intval($x);
			$y = intval($y);

			// Create the new Image	
			$new_image = imagecreatetruecolor($width, $height);
			imageAlphaBlending($new_image, false);

			imagecopy(
					$new_image /* Destination */
					, $this->image /* Source */
					, 0 /* Dest X */
					, 0 /* Dest Y */
					, $x /* Source X */
					, $y /* Source Y */
					, $width /* Source/Dest Width */
					, $height /* Source/Dest Height */
			);
			unset($this->image);

			$this->image = $new_image;
			$this->setSize();
			$this->preserveAlpha();

			return $this;
		}
		else
			return false;
	}

	/**
	 * Return the Image
	 *
	 * @return Image Resource
	 */
	public function getImage()
	{
		if (!$this->isLoaded())
			return false;

		return $this->image;
	}

	/**
	 * Adjusting the image to the size reported, if the image is smaller, performs stretching
	 * @param int $width new width to image
	 * @param int $height new height to image
	 */
	public function stretch($width, $height)
	{
		$this->setHeight($height, false, true)
				->setWidth($width, false, true);
		return $this;
	}

	/**
	 * Resize the Image by setting it's Height
	 * 
	 * @param Integer $height The Height of the Output-Image
	 * @param Boolean $preserveRatio Should the Image Preserve its Ratio?
	 * @param Boolean $stretch Should the Image stretch?
	 */
	public function setHeight($height, $preserveRatio = false, $stretch = false)
	{
		if (!$height) {
			return $this;
		}

		if (!$stretch) {
			if ($height > $this->height) {
				return $this;
			}
		}

		if (!$this->isLoaded())
			return false;

		if ($preserveRatio)
			$width = $this->width / $this->height * $height;
		else
			$width = $this->width;

		$new_image = imagecreatetruecolor($width, $height);
		imageAlphaBlending($new_image, false);

		imagecopyresampled(
				$new_image /* Destination */
				, $this->image /* Source */
				, 0 /* Dest X */
				, 0 /* Dest Y */
				, 0 /* Source X */
				, 0 /* Source Y */
				, $width /* Dest Width */
				, $height /* Dest Height */
				, $this->width /* Source Width */
				, $this->height /* Source Height */
		);
		unset($this->image);

		$this->image = $new_image;
		$this->setSize();
		$this->preserveAlpha();

		return $this;
	}

	/**
	 * Resize the Image by setting it's Width and Height preseve ratio e 
	 * choosing the largest dimension
	 * 
	 * @param Integer $width The Width of the Output-Image
	 * @param Integer $height The Height of the Output-Image
	 * @param Boolean $minorScale choosing the lowest
	 */
	public function setDimensions($width, $height, $minorScale = false)
	{
		$scaleW = $this->width / $width;
		$scaleH = $this->height / $height;

		if ($minorScale) {
			if ($scaleH > $scaleW) {
				$this->setHeight($height, true);
			} else {
				$this->setWidth($width, true);
			}
			return $this;
		}

		if ($scaleH < $scaleW) {
			$this->setHeight($height, true);
		} else {
			$this->setWidth($width, true);
		}
		return $this;
	}

	public function resizeAndCrop($wmax, $hmax)
	{
		$this->setDimensions($wmax, $hmax);
		$x = $y = 0;

		if ($this->width > $this->height) {
			$x = floor($this->width / 2);
			$halfW = floor($wmax / 2);
			$x = $x - $halfW;
		}

		if ($this->width < $this->height) {
			$y = floor($this->height / 2);
			$halfH = floor($hmax / 2);
			$y = $y - $halfH;
		}

		$this->crop($x, $y, $wmax, $hmax);
		return $this;
	}

	/**
	 * Resize the Image by setting it's Width
	 * 
	 * @param Integer $width The Width of the Output-Image
	 * @param Boolean $preserveRatio Should the Image Preserve its Ratio?
	 * @param Boolean $stretch Should the Image stretch?
	 */
	public function setWidth($width, $preserveRatio = false, $stretch = false)
	{
		if (!$width) {
			return $this;
		}

		if (!$stretch) {
			if ($width > $this->width) {
				return $this;
			}
		}

		if (!$this->isLoaded())
			return false;

		if ($preserveRatio)
			$height = $this->height / $this->width * $width;
		else
			$height = $this->height;

		$new_image = imagecreatetruecolor($width, $height);
		imageAlphaBlending($new_image, false);

		imagecopyresampled(
				$new_image /* Destination */
				, $this->image /* Source */
				, 0 /* Dest X */
				, 0 /* Dest Y */
				, 0 /* Source X */
				, 0 /* Source Y */
				, $width /* Dest Width */
				, $height /* Dest Height */
				, $this->width /* Source Width */
				, $this->height /* Source Height */
		);
		unset($this->image);

		$this->image = $new_image;
		$this->setSize();
		$this->preserveAlpha();

		return $this;
	}

	/**
	 * Output the image
	 */
	public function getPNG($new_img = null)
	{
		if (!$this->isLoaded())
			return false;

		ob_start();

		if ($new_img)
			imagepng($this->image, $new_img);
		else
			imagepng($this->image);

		$data = ob_get_contents();
		ob_end_clean();
		return $data;
	}

	/*
	 * * Output the Image
	 */

	public function getJPG($new_img = null)
	{
		if (!$this->isLoaded())
			return false;

		ob_start();
		if ($new_img)
			imagejpeg($this->image, $new_img, 100);
		else
			imagejpeg($this->image, null, 100);
		$data = ob_get_contents();
		ob_end_clean();
		return $data;
	}

	/**
	 * * Output the Image Gif
	 */
	public function getGIF($new_img = null)
	{
		if (!$this->isLoaded())
			return false;

		ob_start();
		if ($new_img)
			imagegif($this->image, $new_img);
		else
			imagegif($this->image);
		$data = ob_get_contents();
		ob_end_clean();
		return $data;
	}

	/**
	 * Output image or save
	 * @param string $new_image path para salvar a imagem editada
	 */
	public function getOutputImage($new_img = null)
	{
		switch ($this->extension) {
			case 1:
				return $this->getGIF($new_img);
				break;

			case 2:
				return $this->getJPG($new_img);
				break;

			default:
				return $this->getPNG($new_img);
				break;
		}
	}

	/**
	 * Detects the main color of this image.
	 *
	 * @param int $numberOfColors Number of colors of  return.
	 * @param bool $turboMode Set true to use faster alghoritm (slightly less accurate).
	 * @param bool $tolerance tolerance to colors return
	 * @return Color $objColor
	 */
	public function getMainColor($numberOfColors = 1, $turboMode = false, $tolerance = 0)
	{
		$colors = array();

		if ($turboMode && ($this->width > 256 || $this->height > 256)) {
			// create a thumbnail for the computations
			if ($this->width > $this->height) {
				$ratio = 256 / $this->width;
				$width = 256;
				$height = (int) ($ratio * $this->width);
			} else {
				$ratio = 256 / $this->height;
				$height = 256;
				$width = (int) ($ratio * $this->width);
			}
			$image = imagecreatetruecolor($width, $height);
			imagecopyresized($image, $this->image, 0, 0, 0, 0, $width, $height, $this->width, $this->height);
		} else {
			$image = $this->image;
			$width = $this->width;
			$height = $this->height;
		}

		// this is a quick test only, may be inaccurate
		// two image samples per one iteration (better performance)
		for ($x = 0; $x < $width; ++$x) {
			$rgb = imagecolorat($image, $x, 0);
			if (isset($colors[$rgb])) {
				++$colors[$rgb];
			} else {
				$colors[$rgb] = 1;
			}

			$rgb = imagecolorat($image, $x, $height - 1);
			if (isset($colors[$rgb])) {
				++$colors[$rgb];
			} else {
				$colors[$rgb] = 1;
			}
		}

		// two image samples per one iteration (better performance)
		for ($y = 0; $y < $height; ++$y) {
			$rgb = imagecolorat($image, 0, $y);

			if (isset($colors[$rgb])) {
				++$colors[$rgb];
			} else {
				$colors[$rgb] = 1;
			}
			$rgb = imagecolorat($image, $width - 1, $y);
			if (isset($colors[$rgb])) {
				++$colors[$rgb];
			} else {
				$colors[$rgb] = 1;
			}
		}

		arsort($colors, SORT_NUMERIC);


		if ($numberOfColors === 1) {
			reset($colors);
			return new Color(key($colors));
		} else {
			$objColors = array();
			$i = 1;
			$indice = 0;
			foreach ($colors as $value) {
				$indice++;
				if ($i < $numberOfColors) {
					if ($indice > 1) {
						$colorPrev = imagecolorsforindex($this->image, $objColors[$indice]);
						$color = imagecolorsforindex($this->image, key($colors));

						$r = $color["red"];
						$g = $color["green"];
						$b = $color["blue"];

						$rPrev = $colorPrev["red"];
						$gPrev = $colorPrev["green"];
						$bPrev = $colorPrev["blue"];

						if (($b > ( $bPrev + $tolerance ) || $b < ( $bPrev + $tolerance ) ) &&
								($g > ( $gPrev + $tolerance ) || $g > ( $gPrev + $tolerance ) ) &&
								($r > ( $rPrev + $tolerance ) || $r > ( $rPrev + $tolerance ))) {
							$i++;
							$objColors[] = new Color(key($colors));
						}
					} else {
						$objColors[] = new Color(key($colors));
					}
					next($colors);
				} else {
					break;
				}
			}
			return $objColors;
		}
	}

}

?>