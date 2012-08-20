<?php
/**
 * @copyright Pierre-Emmanuel Manteau @ 2012
 * @author Pierre-Emmanuel Manteau aka PEM
 * @license MIT
 */

namespace Phpwtf;

/**
 * This is the class in charge of creating reports
 */
class WtfReport
{
    /**
     * The format to output (xml, html, stats)
     * @var string
     */
    private $_format;

    /**
     * The Wtf list to output
     * @var Wtfs
     */
    private $_wtfs;

    /**
     * The ouput path for reports
     * @var string
     */
    private $_outputPath;

    /**
     * The resources to use for the html files
     * @var string
     */
    private $_resources;

    /**
     * The root path can be either vendor if installed with composer
     * or the root folder of phpwtf if installed manually
     * @var string
     */
    private $_rootPath;

    /**
     * This is the path to the root of the phpwtf folder
     * @var string
     */
    private $_wtfRoot;

    /**
     * This array will be used to store stats so that we don't have to loop
     * once again through all Wtfs, except if empty.
     * @var array
     */
    private $_stats;

    /**
     * Constructor with parameter injection
     * @param array $params
     */
    public function __construct($params)
    {
        $this->_stats = array('total' => 0, 'wtfs' => array());

        // TODO: make it overridable later
        $this->_wtfRoot = __DIR__ . '/../../';
        $this->_resources = $this->_wtfRoot . 'resources/';
        $this->_rootPath = $this->_wtfRoot;
        if (stripos($this->_rootPath, 'phpwtf/phpwtf') !== false) {
            $this->_rootPath .= '../../';
        }

        if (!empty($params['outputPath'])) {
            $this->setOuputPath($params['outputPath']);
        } else {
            $this->setOuputPath('./reports/');
        }
        if (!empty($params['format'])) {
            $this->_format = $params['format'];
        } else {
            $this->_format = 'xml';
        }
        if (!empty($params['wtfs'])) {
            $this->_wtfs = $params['wtfs'];
        } else {
            $this->_wtfs = null;
        }

    }

    /**
     * This function apply some checks on the outputPath to normalize it
     * @param string $outputPath
     */
    private function setOuputPath($outputPath)
    {
        // we make sure the path ends with a /
        if (strrpos($outputPath, '/') != (strlen($outputPath) - 1 )) {
            $outputPath .= '/';
        }

        $rootPath = substr($outputPath, 0, 3);
        // is the path given relative?
        if ('../' == $rootPath
            || '/..' == $rootPath
            || './.' == $rootPath) {
            // the user has input a relative path, we start from vendor
            $outputPath = $this->_rootPath . $outputPath;
        } else {
            $outputPath = $this->_wtfRoot . $outputPath;
        }

        if (!is_dir($outputPath)) {
            mkdir($outputPath, 0755);
        }

        $this->_outputPath = $outputPath;
    }

    /**
     * Used to generate the report(s)
     * @param Wtfs $wtfs OPTIONAL
     * @throws Exception
     */
    public function generateReport(Wtfs $wtfs = null)
    {
        if (null == $wtfs) {
            if (null != $this->_wtfs) {
                $wtfs = $this->_wtfs;
            } else {
                throw new \Exception('No Wtf list to use !');
            }
        } else {
            $this->_wtfs = $wtfs;
        }

        // in case of output format combination like xml+stats
        $formats = explode('+', $this->_format);

        foreach ($formats as $format) {
            if ('html' == $format) {
                $this->_createHtmlReport();
                // html also generates the stats
                $this->_createStats();
            } elseif ('stats' == $format) {
                $this->_createStats();
            } else {
                $output = $this->_toXml();
                $this->_createFile(
                    $this->_outputPath . time() . '.xml',
                    $output
                );
            }
        }
    }

    /**
     * Used to generate the output of the wtfs objects to an xml string
     * @return string XML
     */
    private function _toXml()
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?><phpwtf version="0.1">';
        $wtfs = $this->_wtfs;
        if (!empty($wtfs)) {
            $xml .= $wtfs->toXml();
        }
        return $xml;
    }

    /**
     * Used to generate the charts data in json
     * @return void
     */
    private function _createStats()
    {
        if (!empty($this->_stats)) {
            $this->_writeStats();
            // we had stats, we wrote them, no need to go any further.
            return;
        }
        // there were no stats, this can happen, only if "stats" was the
        // only output format in the command line
        $wtfs = $this->_wtfs;
        if (empty($wtfs)) {
            throw new \Exception('No wtfs provided!');
        }
        $wtfsList = $wtfs->getWtfs();
        $totalWtfs = 0;
        foreach ($wtfsList as $wtf) {
            $file = $this->_getSplInfo($wtf->getFile());
            $nbWtfs = count($wtf->getWtfs());
            $this->_stats['wtfs'][$file->getRealPath()] = $nbWtfs;
            $totalWtfs += $nbWtfs;
        }
        $this->_stats['total'] = $totalWtfs;
        // now we write the stats
        $this->_writeStats();
    }

    /**
     * This function handle the file writing of stats
     * @throws \Exception
     */
    private function _writeStats()
    {
        // Djson stands for dataJson
        $djson = array();
        $djsonFile = $this->_outputPath . 'djson';
        if (file_exists($djsonFile)) {
            $data = file_get_contents($djsonFile);
            if (false !== $data) {
                // then we decode it as an associative array
                $djson = json_decode($data, true);
            }
        }
        $this->_stats['time'] = time();
        $djson[] = $this->_stats;
        $json = json_encode($djson);
        if (false === $json) {
            throw new \Exception(
                'An error occured during json encoding of data : <pre>' .
                print_r($djson, true) . '</pre>'
            );
        }
        $result = file_put_contents($djsonFile, $json);
        if (false === $result) {
            throw new \Exception(
                'Could not write json ' . "\n" .
                '<pre>' . print_r($json, true) . '</pre> ' . "\n" .
                'to file ' . $djsonFile
            );
        }
    }

    /**
     * Used to create and generate the whole html report
     */
    private function _createHtmlReport()
    {
        $wtfs = $this->_wtfs;
        if (empty($wtfs)) {
            throw new \Exception('No wtfs provided!');
        }

        $wtfsList = $wtfs->getWtfs();
        $indexOfFiles = array();

        // check for dir existence
        if (!file_exists($this->_resources . 'templates/html/')) {
            throw new \Exception(
                'No html template folder found in path ' .
                $this->_resources . 'templates/html/' . '!' .
                "\n" . 'Current path : ' . getcwd()
            );
        }

        // we copy, if necessary, the resources to report folder
        if (!is_dir($this->_outputPath . 'resources')) {
            mkdir($this->_outputPath . 'resources', 0755);
        }
        if (!is_file($this->_outputPath . 'about.html')) {
            copy(
                $this->_resources . 'about.html',
                $this->_outputPath . 'about.html'
            );
        }
        // syncing dirs
        $this->_rsyncDirs(
            $this->_resources . 'images',
            $this->_outputPath . 'resources/images'
        );

        $this->_rsyncDirs(
            $this->_resources . 'css', $this->_outputPath . 'resources/css'
        );

        $this->_rsyncDirs(
            $this->_resources . 'js', $this->_outputPath . 'resources/js'
        );

        $wtfFileTemplate = file_get_contents(
            $this->_resources . 'templates/html/wtfFile.html'
        );

        $indexTemplate = file_get_contents(
            $this->_resources . 'templates/html/index.html'
        );

        $totalWtfs = 0;
        foreach ($wtfsList as $wtf) {
            $file = $this->_getSplInfo($wtf->getFile());

            // we keep the extension in case of the user having several files
            // with the same name : example.php, example.js, example.java
            // we also need to have a unique id in case the user having files
            // with identical names but in different folders
            $fileName = substr(
                sha1(microtime() . '_' . $file->getFilename()), 0, 9
            );
            $fileName = $fileName . '_' . $file->getFilename() . '.html';
            $this->_createFile(
                $this->_outputPath . $fileName, $wtf->toHtml($wtfFileTemplate)
            );
            $nbWtfs = count($wtf->getWtfs());
            $indexOfFiles[$file->getRealPath()] = array(
                'reportFile' => './' . $fileName,
                'wtfsNb' => $nbWtfs
            );
            $this->_stats['wtfs'][$file->getRealPath()] = $nbWtfs;

            $totalWtfs += $nbWtfs;
        }

        $this->_stats['total'] = $totalWtfs;

        $indexTemplate = str_replace(
            array('${lastModified}', '${wtfsNb}'),
            array(date('Y-m-d H:i:s'), $totalWtfs),
            $indexTemplate
        );
        $list = '<table class="wtfFileList"><thead><tr>' .
            '<th>File</th><th>Nb. Wtfs</th>' .
            '</tr></thead><tbody>';
        foreach ($indexOfFiles as $realPath => $wtf_report) {
            $list .= '<tr><td>' .
                '<a href="' . $wtf_report['reportFile'] . '" ' .
                'title="Click for more details">' . $realPath . '</a></td>' .
                '<td>nb. wtfs : ' . $wtf_report['wtfsNb'] . '</td></tr>';
        }
        $list .= '</tbody><tfoot></tfoot></table>';

        $indexTemplate = str_replace('${wtf_reports}', $list, $indexTemplate);
        $this->_createFile($this->_outputPath . 'index.html', $indexTemplate);
    }

    /**
     * Used to generate a file or append into it, a given content.
     * @param string $outputFile Path to the output file
     * @param string $output Content to be written to the file
     * @param bool $append Should the content be appeneded or the file replaced
     * @throws Exception
     */
    private function _createFile($outputFile, $output, $append = false)
    {
        if (file_exists($outputFile) && !$append) {
            unlink($outputFile);
        }

        $result = file_put_contents($outputFile , $output , FILE_APPEND);

        if (false === $result) {
            throw new \Exception('Could not write into file ' . $outputFile);
        }
    }

    /**
     * This function returns an SplInfo object for a given file
     * @param string $filePath
     * @return SplFileInfo
     */
    private function _getSplInfo($filePath)
    {
        return new \SplFileInfo($filePath);
    }

    /**
     * Sync dirs - non recursive as of now
     * @param string $src
     * @param string $dest
     */
    private function _rsyncDirs($src, $dest)
    {
        if (!is_dir($dest)) {
            mkdir($dest, 0755);
        }

        $iterator = new \DirectoryIterator($src);
        foreach ($iterator as $fileinfo) {
            if ($fileinfo->isFile()) {
                // only copy if file does not exist
                if (!is_file($dest . '/' . $fileinfo->getFilename())) {
                    copy(
                        $fileinfo->getRealPath(),
                        $dest . '/' . $fileinfo->getFilename()
                    );
                }
            }
        }

    }

}