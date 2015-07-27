<?php

namespace Phpwtf\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Phpwtf\WtfSnippet as Snippet;
use Phpwtf\Wtfs as Wtfs;
use Phpwtf\Wtf as Wtf;
use Phpwtf\WtfReport as Report;

class ReviewCommand extends Command
{
    private $_rootPath;

    protected function configure()
    {
        $this
            ->setName('review')
            ->setDescription(
                'This command is used to parse files, detect wtfs and ' .
                'report them in a given format.'
            )
            ->addOption(
                'paths',
                '-p',
                InputOption::VALUE_REQUIRED,
                'Paths to scan for sources, ' .
                'ie: "/some/path/*.php,/some/other/path/*.js"' .
                "\n" . 'Default to "/". Becareful, relative path are resolved ' .
                'from either vendor folder or root folder if you ' .
                'have not installed phpwtf with composer.',
                '/'
            )
            ->addOption(
                'output-path',
                '-o',
                InputOption::VALUE_REQUIRED,
                'The directory where the reports should be written to.' .
                'Default to "./reports/"',
                './reports/'
            )
            ->addOption(
                'format',
                '-f',
                InputOption::VALUE_REQUIRED,
                'The format of the reports, ie: xml, html, stats, xml+stats.' .
                'Note that html already includes the stats. Default to xml',
                'xml'
            )
            ->addOption(
                'recursive',
                '-r',
                InputOption::VALUE_NONE,
                'If set, the paths will be scanned recursively.'
            )
            ->addOption(
                'skip-error',
                '-s',
                InputOption::VALUE_NONE,
                'If set, errors will not stop the execution of the script.'
            )
            ->addOption(
                'bench',
                '-b',
                InputOption::VALUE_NONE,
                'If set, times will be displayed when task has been completed.'
            )
        ;

        $this->_rootPath = __DIR__ . '/../../../';
        if (stripos($this->_rootPath, 'phpwtf/phpwtf') !== false) {
            $this->_rootPath .= '../../';
        }

    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // now we are ready to start working
        // let's have fun finding all the files to parse
        $files = array();
        $found = false;
        $this->_getFiles(
            $input->getOption('paths'), $files, $input->getOption('recursive')
        );

        $wtfs = new Wtfs();
        $params = array(
            'outputPath' => $input->getOption('output-path'),
            'format' => $input->getOption('format'),
        );
        $report = new Report($params);

        // foreach file we should check if it is an amd one, if so,
        // get the dependency list, and check the code.
        $start = microtime(true);
        foreach ($files as $file) {
            // Get a file into an array.
            // In this example we'll go through HTTP to get
            // the HTML source of a URL.
            $lines = file($file); //, FILE_IGNORE_NEW_LINES
            // Loop through our array, show HTML source as HTML source;
            // and line numbers too.
            $startFound = false;
            $endFound   = false;
            $errorMsg   = '';
            $lineStart  = 0;
            $wtfsInFile = array();

            foreach ($lines as $lineNb => $line) {
                $wtfStart = stripos($line, '@wtf_start');
                if ($wtfStart !== false && !$startFound) {
                    $lineStart  = $lineNb;
                    $startFound = true;
                } elseif($wtfStart !== false && !!$startFound) {
                    // we found a @wtf_start inside another @wtf_start snippet
                    // we do not support nested wtfs, come on...
                    $snippet = new Snippet($lineStart . '-noEnd');
                    $snippet->setLineStart($lineStart);
                    $snippet->setSeverity('error');
                    $snippet->setSnippet(
                        'A @wtf_start has been found without a matching @wtf_stop in ' .
                        $file . ' at line ' . $lineStart
                    );
                    $wtfsInFile[$lineStart] = $snippet;
                    $found = true;

                    // proceed to the next snippet directly
                    $lineStart  = $lineNb;
                    $startFound = true;
                }

                $wtfEnd = stripos($line, '@wtf_stop');
                if ($wtfEnd !== false) {
                    $endFound   = true;
                    $startFound = false;
                }

                if(($startFound && !$endFound) || (!$startFound && $endFound)){
                    $errorMsg .= $line;
                    $found = true;
                    if ($endFound) {
                        $snippet = new Snippet($lineStart . '-' . $lineNb);
                        $snippet->setLineStart($lineStart);
                        $snippet->setLineStop($lineNb);
                        $snippet->setSeverity('error');
                        $snippet->setSnippet($errorMsg);
                        $wtfsInFile[$lineNb] = $snippet;
                        $endFound = false;
                        $errorMsg = '';
                    }
                }
            }
            if ($startFound) {
                // we have a problem here, it means that someone has put a
                // wtf_start without a wtf_stop.
                // Rather than reporting the whole file, we will trigger an
                // exception or just report the line where the wtf_start was if skip-error option was set.
                $message = 'A @wtf_start has been found without a matching @wtf_stop in ' .
                    $file . ' at line ' . $lineStart;
                if (!$input->getOption('skip-error')) {
                    throw new \Exception($message);
                } else {
                    $snippet = new Snippet($lineStart . '-noEnd');
                    $snippet->setLineStart($lineStart);
                    $snippet->setSeverity('error');
                    $snippet->setSnippet(
                        'A @wtf_start has been found without a matching @wtf_stop in ' .
                        $file . ' at line ' . $lineStart
                    );
                    $wtfsInFile[$lineStart] = $snippet;
                }
            }
            if ($found) {
                $wtfs->addWtf(
                    new Wtf(
                        array('file' => $file, 'wtfsnippets' => $wtfsInFile)
                    )
                );
                $found = false;
            }
        }
        $middle = microtime(true);
        $report->generateReport($wtfs);
        $end = microtime(true);

        $output->writeln('<info>Work Done!</info>');

        if ($input->getOption('bench')) {
            $this->_displayBench(count($files), $start, $middle, $end, $output);
        }

    }

    private function _getFiles($path, &$files, $recursive = false)
    {
        // we check if several paths have been given
        $paths = explode(",", $path);

        foreach ($paths as $curPath){
            $rootPath = substr($curPath, 0, 3);
            // is the path given relative?
            if ('../' == $rootPath
                || '/..' == $rootPath
                || './.' == $rootPath) {
                // the user has input a relative path, we start from vendor
                $curPath = $this->_rootPath . $curPath;
            }
            if ($recursive) {
                $lookup = substr($curPath, strrpos($curPath, "/"));
                $dirPath = substr(
                    $curPath, 0, strrpos($curPath, "/") + 1
                ) . "*";
                $dirs = glob($dirPath, GLOB_ONLYDIR|GLOB_ERR);
                if ($dirs === false) {
                    throw new \Exception(
                        'Error wrong path : ' . $dirPath . ' --- ' .
                        'please fix the path(s) and try again.'
                    );
                }
                foreach ($dirs as $dir) {
                    if ($dir . "/*" != $dirPath) {
                        $this->_getFiles($dir . $lookup, $files, $recursive);
                    }
                }
            }
            $result = glob($curPath, GLOB_ERR);
            if ($result !== false) {
                $files = array_merge($files, $result);
            } else {
                throw new \Exception(
                    'Error wrong path : ' . $curPath . ' --- please fix the path(s) and try again.'
                );
            }
        }
    }

    private function _displayBench(
        $nbFiles, $start, $middle, $end, OutputInterface $output
    )
    {
        $elapsedParser = number_format(($middle - $start), 5);
        $elapsedReporter = number_format(($end - $middle), 5);
        $elapsedTotal = number_format(($end - $start), 5);

        $output->writeln(
            'Parsed ' . $nbFiles . ' files in ' . $elapsedTotal . ' s'
        );

        $output->writeln(
            '<info>----------------------------------------------------</info>'
        );

        $output->writeln('Parsing time : ' . $elapsedParser . ' s');
        $output->writeln('Report writing time : ' . $elapsedReporter . ' s');
        $output->writeln('Total time : ' . $elapsedTotal);

        // green text
        $output->writeln(
            '<info>----------------------------------------------------</info>'
        );
    }
}