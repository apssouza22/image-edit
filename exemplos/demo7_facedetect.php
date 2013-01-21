<?php
include("../FaceDetect.class.php");
include("../ImageEdit.class.php");

/*
$imageSource = imagecreatefromstring(file_get_contents("eu.jpg"));
$detector = new FaceDetect('detection.dat');
if ($detector->face_detect($imageSource)) {
	$face = $detector->getFace();
}
*/

//croping image
$image = new ImageEdit("eu.jpg");
$image->cropFace( true /* Optional: Preserve Aspect Ratio */);
$image->getOutputImage('cropFace.jpg');

$face  = $image->getFace();
?>

<html style="background: #<?= $bg ?>;">
	<head>

		<script src="js/jquery.min.js"></script>
		<script src="js/jquery.Jcrop.js"></script>
		<link rel="stylesheet" href="css/jquery.Jcrop.css" type="text/css" />
		<link rel="stylesheet" href="demo_files/demos.css" type="text/css" />
	</head>

	<body>
		<div id="outer">
			<div class="jcExample">
				<div class="article">
					<h3>Selected Face</h3>
					<!-- This is the image we're attaching Jcrop to -->
					<div style="position:relative;">
						<div style="height: <?= $face['w'] ?>px;left: <?= $face['x'] ?>px;    position: absolute;    top: <?= $face['y'] ?>px;    width: <?= $face['w'] ?>px;    z-index: 300; border:2px solid #000;"></div>
						<img src="eu.jpg" id="cropbox" />
					</div>

				</div>
			</div>
			
			<div class="jcExample">
				<div class="article">
					<h3>Face crop</h3>
						<img src="cropFace.jpg" id="cropbox" />
				</div>
			</div>
			
		</div>
	</body>

</html>
