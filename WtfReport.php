<?php
/**
 * @copyright Pierre-Emmanuel Manteau @ 2012
 * @author Pierre-Emmanuel Manteau aka PEM
 * @license MIT
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
     * The html template to use for the html files
     * @var string
     */
    private $_htmlTemplate;

    /**
     * Constructor with parameter injection
     * @param array $params
     */
    public function __construct($params)
    {
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

        // TODO: make it overridable later
        $this->_htmlTemplate = './resources/templates/html/wtfFile.html';
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
                throw new Exception('No Wtf list to use !');
            }
        } else {
            $this->_wtfs = $wtfs;
        }

        switch ($this->_format) {
            case 'html' :
                $this->_createHtmlReport();
                // html also generates the stats
            case 'stats' :
                $output = $this->_createStats();
                // do something with the output
                break;
            default : //xml
                $output = $this->_toXml();
                $this->_createFile(
                    $this->_outputPath . time() . '.xml',
                    $output
                );
        }

        // TODO: handle file generation, folder permissions etc

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
     * Used to generate the svg charts
     * @return void
     */
    private function _createStats()
    {
        // TODO: generate svg charts
    }

    /**
     * Used to create and generate the whole html report
     */
    private function _createHtmlReport()
    {
        $wtfs = $this->_wtfs;
        if (empty($wtfs)) {
            throw new Exception('No wtfs provided!');
        }
        $wtfsList = $wtfs->getWtfs();
        $indexOfFiles = array();

        if (!file_exists($this->_htmlTemplate)) {
            throw new Exception('No html template file found!');
        }

        // we copy, if necessary, the resources to report folder
        if (!is_dir($this->_outputPath . 'resources')) {
            mkdir($this->_outputPath . 'resources', 0755);
        }
        if (!is_file($this->_outputPath . 'about.html')) {
            copy(
                './resources/about.html', $this->_outputPath . 'about.html'
            );
        }
        // syncing dirs
        $this->_rsyncDirs(
            __DIR__ . '/resources/images',
            $this->_outputPath . 'resources/images'
        );

        $this->_rsyncDirs(
            __DIR__ . '/resources/css',
            $this->_outputPath . 'resources/css'
        );

        $htmlTemplate = file_get_contents($this->_htmlTemplate);

        foreach ($wtfsList as $wtf) {
            $file     = $this->_getSplInfo($wtf->getFile());

            // if you are php >= 5.3.6 you can uncomment this lines
            // $fileName   = $file->getBasename('.' . $file->getExtension());
            // otherwise we use the old way
            $fileName = $file->getFilename();
            $fileName = substr($fileName, 0, strrpos($fileName, '.'));
            $fileName .= '.html';
            $this->_createFile(
                $this->_outputPath . $fileName, $wtf->toHtml($htmlTemplate)
            );
            $indexOfFiles[$this->_outputPath . $fileName] = count(
                $wtf->getWtfs()
            );
        }

        // do something with the output
        // TODO: generate index.html
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
            throw new Exception('Could not write into file ' . $outputFile);
        }
    }

    /**
     * This function returns an SplInfo object for a given file
     * @param string $filePath
     * @return SplFileInfo
     */
    private function _getSplInfo($filePath)
    {
        return new SplFileInfo($filePath);
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

        $iterator = new DirectoryIterator($src);
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