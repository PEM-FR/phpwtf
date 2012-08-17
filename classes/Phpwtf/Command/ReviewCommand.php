<?php

namespace Phpwtf\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ReviewCommand extends Command
{
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
                null,
                InputOption::VALUE_REQUIRED,
                'Paths to scan for sources, ' .
                'ie: "/some/path/*.php,/some/other/path/*.js"' .
                "\n" . 'Default to "/"',
                '/'
            )
            ->addOption(
                'output-path',
                null,
                InputOption::VALUE_REQUIRED,
                'The directory where the reports should be written to.' .
                'Default to "./reports/"',
                './reports/'
            )
            ->addOption(
                'format',
                null,
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
                'bench',
                '-b',
                InputOption::VALUE_NONE,
                'If set, times will be displayed when task has been completed.'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // now we are ready to start working
        // let's have fun finding all the files to parse
        $files = array();
        $found = false;
        $errors = '';
        $this->_getFiles(
            $input->getOption('paths'), $files, $input->getOption('recursive')
        );

        $wtfs = new \Phpwtf\Wtfs();
        $params = array(
            'outputPath' => $input->getOption('output-path'),
            'format' => $input->getOption('format'),
        );
        $report = new \Phpwtf\WtfReport($params);

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
            $endFound = false;
            $errorMsg = '';

            $wtfsInFile = array();

            foreach ($lines as $lineNb => $line) {
                $wtfStart = stripos($line, '@wtf_start');
                if ($wtfStart !== false) {
                    $startFound = true;
                }

                $wtfEnd = stripos($line, '@wtf_stop');
                if ($wtfEnd !== false) {
                    $endFound = true;
                    $startFound = false;
                }

                if(($startFound && !$endFound) || (!$startFound && $endFound)){
                    $errorMsg .= $line;
                    $found = true;
                    if ($endFound) {
                        $wtfsInFile[$lineNb] = array(
                            'severity' => 'error', 'snippet' => $errorMsg
                        );
                        $endFound = false;
                        $errorMsg = '';
                    }
                }
            }
            if ($found) {
                $wtfs->addWtf(
                    new \Phpwtf\Wtf(
                        array('file' => $file, 'wtfs' => $wtfsInFile)
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
            if ($recursive) {
                $lookup = substr($curPath, strrpos($curPath, "/"));
                $dirPath = substr(
                    $curPath, 0, strrpos($curPath, "/") + 1
                ) . "*";
                $dirs = glob($dirPath, GLOB_ONLYDIR);
                foreach ($dirs as $dir) {
                    if ($dir . "/*" != $dirPath) {
                        $this->_getFiles($dir . $lookup, $files, $recursive);
                    }
                }
            }
            $result = glob($curPath);
            if ($result !== false) {
                $files = array_merge($files, $result);
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