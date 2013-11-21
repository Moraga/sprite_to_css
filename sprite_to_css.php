<?php
/**
 * Generates CSS background-position from vertical image sprite
 * @author Alejandro Moraga <moraga86@gmail.com>
 * @param string $filename Path to the image
 * @return string 
 */
function sprite_to_css($filename) {
	// mime types available
	$mimes = array(
		'image/png'		=> 'png',
		'image/jpeg'	=> 'jpg',
		'image/gif'		=> 'gif',
	);
	
	if (isset($mimes[$mime = mime_content_type($filename)]))
		$fn = 'imagecreatefrom' . $mimes[$mime];
	else
		return '';
	
	$m = $fn($filename);
	$w = imagesx($m); // width of the image
	$h = imagesy($m); // height of the image
	
	$bgcolor = array(); // background color (starts undefined)
	$bgopts  = array(); // background color options
	$tmp     = array(); // lines of single color
	
	// transparent alias
	$transp  = array(
		array('red' => 255, 'green' => 255, 'blue' => 255, 'alpha' => 127),
		array('red' =>   0, 'green' =>   0, 'blue' =>   0, 'alpha' => 127),
	);
	
	// from top to bottom
	for ($y=0; $y < $h; $y++) {
		// line color
		$color = array();
		
		// from left to right
		for ($x=0; $x < $w; $x++) {
			// pixel color
			$pcolor = imagecolorsforindex($m, imagecolorat($m, $x, $y));
			
			// checks whether is a transparent alias
			if ($pcolor == $transp[1])
				$pcolor  = $transp[0];
			
			// assumes the color leftmost
			if ($x == 0) {
				$color = $pcolor;
			}
			// line with more than one color
			elseif ($pcolor != $color) {
				$color = false;
				break;
			}		
		}
		
		// ignores line with more than one color
		if (!$color)
			continue;
		
		// background color start undefined
		if (!$bgcolor) {
			// color array as string: r,g,b,a
			$rgba = implode(',', $color);
			
			// background color options and frequency
			if (isset($bgopts[$rgba]))
				$bgopts[$rgba]++;
			else
				$bgopts[$rgba] = 1;
			
			// stores the line position (from height)
			$tmp[$y] = $color;
			
			// number of background color options
			$count = count($bgopts);
			
			// sort by color repeated more times
			if ($count > 1)
				arsort($bgopts, SORT_NUMERIC);
			
			// tries to find the background color of the sprite
			if (pow(current($bgopts), 1 / $count) * $y / $h >= 1) {
				// founded
				// backs the color to php array
				$bgcolor = array_combine(array('red', 'green', 'blue', 'alpha'), explode(',', key($bgopts)));
				
				// remove lines with different color
				// assumes different colors are part of the image
				foreach ($tmp as $k => $v)
					if ($v != $bgcolor)
						unset($tmp[$k]);
				
				// redefine the array
				$tmp = array_keys($tmp);
			}
		}
		// with the background color defined
		// checks if the line color matches the background color
		elseif ($color == $bgcolor) {
			$tmp[] = $y;
		}
	}
	
	// here we have all spacings
	// but not all spacing is a spacing
	// a space may be part of an image
	
	$tmp[] = false; // to facilitate run to the end
	$b = array();
	$c = array();
	
	$x = current($tmp); // first array item
	$p = $x - 1;  // previous array item
	$u = array(); // container of positions
	$t = array(); // temporary container
	
	// join intervals
	foreach ($tmp as $v) {
		if ($v - $p != 1) {
			$d = $p - $x + 1;
			if (isset($b[$d])) {
				$b[$d]++;
				$u[$d] = array_merge($u[$d], $t);
			}
			else {
				$b[$d] = 1;
				$u[$d] = $t;
			}
			
			$x = $v;
			$t = array();
		}
		
		$p = $v;
		$t[] = $v;
	}
	
	$bk = array_keys($b); // heights
	$bv = $b; // copy
	
	// sort by height and frequency
	array_multisort($bv, SORT_DESC, $bk, SORT_DESC, $bv);
	
	// eliminates smaller spaces
	foreach ($u as $k => $v)
		if ($k < $bk[0])
			$tmp = array_diff($tmp, $v);
	
	// and the css
	$css = array();
	$p = -1;
	
	foreach ($tmp as $v) {
		if ($v - $p != 1)
			$css[] = '. {background-position:0 -'. ($p + 1) .'px;}';
		$p = $v;
	}
	
	return implode("\n", $css);
}

?>