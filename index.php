<!DOCTYPE html>
<html lang="en">
<head>
<title>CSS background-position generator</title>
</head>
<body>
<form action="." method="post" enctype="multipart/form-data">
	<div>
		<input type="file" name="file" accept="image/*"/>
		<button>Generate</button>
	</div>
	<?php
	
	if (isset($_FILES['file']) && !$_FILES['file']['error']) {
		require 'sprite_to_css.php';
		
		echo
			'<textarea style="width:60%; height:600px" onclick="this.select()">'.
				sprite_to_css($_FILES['file']['tmp_name']) .
			'</textarea>';
	}
	
	?>
</form>
</body>
</html>