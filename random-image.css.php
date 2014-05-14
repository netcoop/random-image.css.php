<?php
/*
 Generates dynamic CSS stylesheet for a random image
 Usage:
	<link rel="stylesheet" type="text/css" href="/typo3conf/ext/yourextension/Resources/Public/Css/random-image.css.php?path=fileadminsubdir&css=%2ErandomImage" media="all">
	Generates a stylesheet containing:
	.randomImage { background-image:url("/fileadmin/fileadminsubdir/random/one-of-the-images.jpg") }
*/

class cssRandom {

	// Path to directory containing the images, from site-root.
	// '#' will be replaced by value of path GET-variable
	var $imageDirectoryScheme = '/fileadmin/#/random/';

	// Relative path from this script to siteroot
	var $relativePathPrepend = '../../../../../..';

	// Template for CSS-output
	var $css = '%s{background-image:url("%s")}';

	var $debug = FALSE;

	function randomImageFromDir($relPath) {
		$files = array();
		$dir = dir($relPath);
		// Read jpg, png and gif files in the directory $path into an array
		while (false !== ($entry = $dir->read())) {
			$ext = strtolower(substr($entry, -3));
			if ($entry != '.' && $entry != '..' && ($ext == 'jpg' || $ext == 'png' || $ext == 'gif')) {
				$files[] = $entry;
			}
		}
		// Select one random index from the array
		srand((float) microtime() * 10000000);
		$random = array_rand($files, 1);
		return $files[$random];
	}

	function getCssTarget() {
		$cssTarget = urldecode($_GET['css']);
		if (!preg_match('/[^A-Za-z0-9.,~=()#:_<>\+\-\[\]]/', $cssTarget)) {
			return $cssTarget;
		}
		return FALSE;
	}

	function getPath() {
		$path = urldecode($_GET['path']);
		if (!preg_match('/[^A-Za-z0-9._\+\-\ ]/', $path)) {
			$relPath = str_replace('#', $path, $this->imageDirectoryScheme);
			if ($this->debug) echo "No illegal chars in Path: $relPath<br>";
			if (is_dir($this->relativePathPrepend . $relPath)) {
				if ($this->debug) echo 'Path exists! ' . $this->relativePathPrepend . $relPath . '<br>';
				return $relPath;
			} else {
				if ($this->debug) echo 'Path does not exist! ' . $this->relativePathPrepend . $relPath . '<br>';
			}
		}
		return FALSE;
	}

	function echoRandomImageCss() {
		if (!$path = $this->getPath()) {
			if ($this->debug) echo "Invalid parameter: path";
			return;
		}

		if (!$cssTarget = $this->getCssTarget()) {
			if ($this->debug) echo "Invalid parameter: css";
			return;
		}

		if ($this->debug) echo "Parameters OK! <br>cssTarget = [" . htmlspecialchars($cssTarget) . "]<br>path = [$path]<br><br>";

		if (!$imageFile = $this->randomImageFromDir($this->relativePathPrepend . $path)) {
			if ($this->debug) echo "No image found in dir " . $this->relativePathPrepend . $path . '<br>';
			return;
		}

		$cssOutput = sprintf($this->css, $cssTarget, $path . $imageFile);

		if (!$this->debug) {
			// Just pretend you're a css file
			header('Content-type: text/css');
		} else {
			$cssOutput = htmlspecialchars($cssOutput);
		}

		// Output the prepared css with random image url
		echo $cssOutput;

	}

}

$randomImageObject = new cssRandom();
$randomImageObject->echoRandomImageCss();
