<?php 	
	include("ImageEdit.class.php");
	function resize($url)
	{
		$image = new ImageEdit($url);
		$image->setHeight(
			300 // x
			, true // Optional: Preserve Aspect Ratio
		);
		$image->setWidth( 
			200 /* x */
			, false /* Optional: Preserve Aspect Ratio */
		);
		
		return $image->getPNG();
	}
?>
<body style="background: yellow;">
	<h1>Imagedit Demo 3: Resizing</h1>
	<img style="border: 1px solid black;" src="data:image/png;base64,<?=base64_encode(resize("portrait.png"))?>"></img>
</body>