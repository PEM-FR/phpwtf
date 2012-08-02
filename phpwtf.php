<?php
/**
 * @author Pierre-Emmanuel Manteau aka PEM
 */

// Args should be written like that :
// php phpwtf.php --path="some/path/*.php,some/path/*.js" --recursive > result.xml
// --path parameters given to a path function
// --recursive if not set then false

$args = $_SERVER["argv"];

$commands = checkInput($args);

if (null != $commands) {
    // now we are ready to start working
    // let's have fun finding all the files to parse
    $files = array();
	$found = false;
	$errors = '';
    getFiles($commands['--path'], $files, $commands['--recursive']);

    // foreach file we should check if it is an amd one, if so,
    // get the dependency list, and check the code.
	$xml = '<?xml version="1.0" encoding="UTF-8"?><phpwtf version="0.1">';
    foreach ($files as $file) {
		// Get a file into an array.  In this example we'll go through HTTP to get
		// the HTML source of a URL.
		$lines = file($file, FILE_IGNORE_NEW_LINES);
		// Loop through our array, show HTML source as HTML source; and line numbers too.
		$startFound = false;
		$endFound = false;
		$errorMsg = '';
		foreach ($lines as $lineNb => $line) {
			$wtfStart = stripos($line, '@wtf_start');
			if ($wtfStart !== false) {
				$startFound = true;
			}
			
			$wtfEnd = stripos($line, '@wtf_end');
			if ($wtfEnd !== false) {
				$endFound = true;
				$startFound = false;
			}

			if(($startFound && !$endFound) || (!$startFound && $endFound)){
				$pbm = str_replace('"', '\"', $line);
				$errorMsg .= $pbm;
				$found = true;
				if ($endFound) {
					$errors = '<error line="' . $lineNb . '" severity="error" message="' . $errorMsg . '"/>';
					$endFound = false;
					$errorMsg = '';
				}
			}
		}
		if ($found) {
			$xml .= '<file name="' . $file . '">' . $errors . '</file>';
			$found = false;
			$errors = '';
		}
    }
    echo $xml;
}

function checkInput($args)
{
    $needHelp = array_search("help", $args);

    if ($needHelp !== false) {
        $cmd = (isset($args[($needHelp - 1)]))
            ? $args[($needHelp - 1)]
            : $args[0];
        $pathHelp = "\t\033[1;32m" . '--path=' . "\033[1;36m" . 'string' .
            "\033[0;37m\t" .
            'the relative path where to look for files, ex: /path/to/*.php' .
            "\n";
        $recHelp = "\t\033[1;32m" . '--recursive' . "\033[1;36m" .
            "\033[0;37m\t" .
            'do you want the parser to loop in recursive directories' . "\n";
        switch ($cmd) {
            case 'path':
                echo $pathHelp;
                break;
            case 'recursive':
                echo $recHelp;
                break;
            default :
                echo 'phpwtf Command list :' . "\n";
                echo $pathHelp . $recHelp . "\033[0;37m\n";
        }
        return null;
    }

    $firstVal = array_shift($args);
    $commands = array();

    foreach($args as $command) {
        $keyVal = explode('=', $command);
        // if the =value isn't set, it means it's a boolean and that we want it
        // hence true
        $commands[$keyVal[0]] = ((isset($keyVal[1])) ? $keyVal[1] : true);
    }

    if (!isset($commands["--path"])) {
        // by default we use current directory and warn the user
        $commands["--path"] = "/";
    }
	$commands["--recursive"] = isset($commands["--recursive"]);

    return $commands;
}

function getFiles($path, &$files, $recursive = false)
{
    if ($recursive) {
        $lookup = substr($path, strrpos($path, "/"));
        $dirPath = substr($path, 0, strrpos($path, "/") + 1) . "*";
        $dirs = glob($dirPath, GLOB_ONLYDIR);
        foreach ($dirs as $dir) {
            if ($dir . "/*" != $dirPath) {
                getFiles($dir . $lookup, $files, $recursive);
            }
        }
    }
    $result = glob($path);
    if ($result !== false) {
        $files = array_merge($files, $result);
    }
}
?>