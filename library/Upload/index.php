<?php
/**
 * Image resize while uploading
 * @author Resalat Haque
 * @link http://www.w3bees.com/2013/03/resize-image-while-upload-using-php.html
 */

include( 'function.php');
// settings
$max_file_size = 5000*5000; // 200kb
$valid_exts = array('jpeg', 'jpg', 'png', 'gif');
// thumbnail sizes
$sizes = array(1024 => 1024);

if ($_SERVER['REQUEST_METHOD'] == 'POST' AND isset($_FILES['image'])) {
	if( $_FILES['image']['size'] < $max_file_size ){
		// get file extension
		$ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
		if (in_array($ext, $valid_exts)) {
			/* resize image */
			foreach ($sizes as $w => $h) {
				$files[] = resize($w, $h);
			}

		} else {
			$msg = 'Unsupported file';
		}
	} else{
		$msg = 'Please upload image smaller than 20000KB';
	}
}
?>
<!doctype html>
<html>
<head>
	<meta charset="UTF-8" />
	<title>Image resize while uploading</title>
	<link rel="stylesheet" href="style.css">
</head>
<body>
	<div class="wrap">
		<?php if(isset($msg)): ?>
			<p class='alert'><?php echo $msg ?></p>
		<?php endif ?>
		
		<!-- file uploading form -->
		<form action="" method="post" enctype="multipart/form-data">
			<label>
				<span>Choose image</span>
				<input type="file" name="image" accept="image/*" />
			</label>
			<input type="submit" value="Upload" />
		</form>
		
		<?php
		// show image thumbnails
		/*if(isset($files)){
			foreach ($files as $image) {
				echo "<img class='img' src='{$image}' /><br /><br />";
			}
		}*/
		?>
	</div>
</body>
</html>