<?php 	
	include("../ImageEdit.class.php");
	
	/*Crop the Image*/
	function crop1($url=null)
	{
		$image = new ImageEdit($url);
		$image->createEmptyimage(500, 500,'ccc');
		return $image->getOutputImage();
	}
	
?>
<body style="background: yellow;">
	<h1>Imagedit Demo 8: Create empty Image</h1>
	<img style="border: 1px solid black;" src="data:image/png;base64,<?=base64_encode(crop1())?>"></img>
</body>