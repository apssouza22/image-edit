<?php 	
	include("../ImageEdit.class.php");
	function set($url)
	{
		$image = new ImageEdit($url);
		$image
			->mask('test.png');
		
		return $image->getPNG();
	}
	
	
	function original($url){
		$image = new ImageEdit($url);
		$image
			->setHeight(200,true);
		
		return $image->getPNG();
	}
?>
<body style="background: yellow;">
	<h1>Imagedit Demo 5: Set Transparent Color</h1>
	<img style="border: 1px solid black;" src="data:image/jpeg;base64,<?=base64_encode(original("mask.png"))?>"></img>Original
	<br><img style="border: 1px solid black;" src="data:image/jpeg;base64,<?=base64_encode(set("portrait.png"))?>"></img>Normal Transparency
</body>