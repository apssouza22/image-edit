<?php 	
	include("../ImageEdit.php");
	
		$image = new ImageEdit("mask.png");
		$image->setHeight(
			null // 
			, true // Optional: Preserve Aspect Ratio
		);
		
		$image->setWidth(
			200 // x
			, true // Optional: Preserve Aspect Ratio
		);
		
		//dimensionando pela a maior escala
		$image->setDimensions(200,300);
		
		switch ($image->extension) {
			case 1:
				header('Content-type: image/gif');
				break;
			case 2:
				header('Content-type: image/jpeg');
				break;

			default:
				header('Content-type: image/png');
				break;
		}
		
		echo $image->getOutputImage();
?>

