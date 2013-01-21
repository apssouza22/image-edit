<?php 	
	include("../ImageEdit.class.php");
	function getImage($url)
	{
		$image = new ImageEdit($url);
		$angle = rand( -180 , 180 );
		$image->rotate( $angle );
		
		return $image->getPNG();
	}
?>
<body style="background: yellow;">
	<h1>Imagedit Demo 1: Rotation</h1>
	<img src="data:image/png;base64,<?=base64_encode(getImage("test.png"))?>"></img>
</body>