<?php 	
	include("../ImageEdit.class.php");
	function crop1($url)
	{
		$image = new ImageEdit($url);
		$image->crop( 
			40 /* x */
			, 80 /* y */
			, 400 /* width */
			, 100 /* height */
		);
		
		return $image->getOutputImage();
	}
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
		return false;
		if( $image->cropFace( true /* Optional: Preserve Aspect Ratio */)){
			return $image->getOutputImage();
		}
	}

	function crop5($url)
	{
		$image = new ImageEdit($url);

		if( $image->cropFace( true /* Optional: Preserve Aspect Ratio */)){
			return $image->getOutputImage('rosto.png');
		}
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
	<?php crop5("portrait.png")?>
	<img style="border: 1px solid black;" src="data:image/png;base64,<?=base64_encode(crop1("test.png"))?>"></img>
	<img style="border: 1px solid black;"src="data:image/png;base64,<?=base64_encode(crop2("portrait.png"))?>"></img>
	<img style="border: 1px solid black;"src="data:image/jpg;base64,<?=base64_encode(crop3("319931_246181618763609_100001153185823_649052_1065711367_n.jpg"))?>"></img>
	<img style="border: 1px solid black;"src="rosto.png"></img>
	<img style="border: 1px solid black;"src="data:image/jpg;base64,<?=base64_encode(crop4("test.png"))?>"></img>
</body>