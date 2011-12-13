<html>
	<head>

		<script src="../js/jquery.min.js"></script>
		<script src="../js/jquery.Jcrop.js"></script>
		<link rel="stylesheet" href="../css/jquery.Jcrop.css" type="text/css" />
		<link rel="stylesheet" href="demo_files/demos.css" type="text/css" />

		<script language="Javascript">
			jQuery(document).ready(function()
			{
				jQuery('#cropbox').Jcrop({
					onChange: showCoords,
					onSelect: showCoords
				});
			});
			function showCoords(c)
			{
				jQuery('#x').val(c.x);
				jQuery('#y').val(c.y);
				jQuery('#x2').val(c.x2);
				jQuery('#y2').val(c.y2);
				jQuery('#w').val(c.w);
				jQuery('#h').val(c.h);
			};
		</script>

	</head>
	<body>
	<div id="outer">
		<div class="jcExample">
			<div class="article">
				<img src="demo_files/flowers.jpg" id="cropbox" />
	
				<form action="crop.php" name="crop" method="POST">
					<label>X1 <input type="text" size="4" id="x" name="x" /></label>
					<label>Y1 <input type="text" size="4" id="y" name="y" /></label>
					<label>X2 <input type="text" size="4" id="x2" name="x2" /></label>
					<label>Y2 <input type="text" size="4" id="y2" name="y2" /></label>
					<label>W <input type="text" size="4" id="w" name="w" /></label>
					<label>H <input type="text" size="4" id="h" name="h" /></label>
					<input type="submit" value="salvar"> 
				</form>
			</div>
		</div>
	</div>
	</body>

</html>
