<?php 	
	include("../ImageEdit.class.php");
	
	/*Crop the Image*/
	function crop1($url)
	{
		$image = new ImageEdit($url);
		$image->crop( 
			0 /* x */
			, 0 /* y */
			, 200 /* width */
			, 200 /* height */
		);
		return $image->getOutputImage();
	}
	
	/*Crop the Image by Borders*/
	function crop2($url)
	{
		$image = new ImageEdit($url);
		$image->cropBorder( 
			40 /* left */
			, 40 /* top */
			, 200 /* right */
			, 100 /* bottom */
		);
		
		return $image->getOutputImage();
	}
	
	function crop3($url)
	{
		$image = new ImageEdit($url);
		if( $image->cropFace( true /* Optional: Preserve Aspect Ratio */)){
			return $image->getOutputImage();
		}
		return $image->getOutputImage();
	}

	
	function crop4($url)
	{
		$image = new ImageEdit($url);
		$image->autoCrop();
		return $image->getOutputImage();
	}
	
?>
<body style="background: yellow;">
	<h1>Imagedit Demo 2: Cropping</h1>
	<img style="border: 1px solid black;" src="data:image/png;base64,<?=base64_encode(crop1("test.png"))?>"></img>
	<img style="border: 1px solid black;" src="data:image/png;base64,<?=base64_encode(crop2("portrait.png"))?>"></img>
	<img style="border: 1px solid black;"src="data:image/png;base64,<?=base64_encode(crop4("test.png"))?>"></img>
	<img style="border: 1px solid black;"src="data:image/png;base64,<?=base64_encode(crop3("portrait.png"))?>"></img>
</body>