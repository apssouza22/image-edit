<?php 	
	include("../ImageEdit.class.php");
	function brighten($url)
	{
		$image = new ImageEdit($url);
		$image->brightness( 
			25 /* Area: -100 to 100(brightest) */
		);
		
		return $image->getJPG();
	}
	function contrast($url)
	{
		$image = new ImageEdit($url);
		$image->contrast( 
			-90 /* Area: -100 to 100(no Contrast) */
		);
		
		return $image->getJPG();
	}
	function grayscale($url)
	{
		$image = new ImageEdit($url);
		$image->grayscale();
		
		return $image->getJPG();
	}
?>
<body style="background: yellow;">
	<h1>Imagedit Demo 4: Brightness, Grayscale, Contrast</h1>
	<img style="border: 1px solid black;" src="data:image/jpeg;base64,<?=base64_encode(brighten("portrait.png"))?>"></img>
	<img style="border: 1px solid black;" src="data:image/jpeg;base64,<?=base64_encode(contrast("portrait.png"))?>"></img>
	<img style="border: 1px solid black;" src="data:image/jpeg;base64,<?=base64_encode(grayscale("portrait.png"))?>"></img>
</body>