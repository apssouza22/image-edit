<?php

/**
 * Jcrop image cropping plugin for jQuery
 * Example cropping script
 * @copyright 2008 Kelly Hallman
 * More info: http://deepliquid.com/content/Jcrop_Implementation_Theory.html
 */

include("../ImageEdit.class.php");
$img = 'demo_files/flowers.jpg';//'rosto.png';//
$image = new ImageEdit($img);
$bg =  $image->getMainColor()->getHexValue();
$colors = $image->getMainColor(10, false, 10);


// If not a POST request, display page below:

?>
<html style="background: #<?=$bg1?>;">
	<head>

	</head>

	<body>
		<?php
			foreach ($colors as $value)
			{
				echo "<div style='background:#".$value->getHexValue()."'>";
				echo "<p>".$value->getHexValue()."</p>";
				echo "</div>";
			}
		?>
		<!-- This is the image we're attaching Jcrop to -->
		<img src="<?php echo  $img ?>" id="cropbox" />
	</body>

</html>
