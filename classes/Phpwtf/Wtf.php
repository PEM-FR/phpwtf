<?php
/**
 * @copyright Pierre-Emmanuel Manteau @ 2012
 * @author Pierre-Emmanuel Manteau aka PEM
 * @license MIT
 */

namespace Phpwtf;

/**
 * This class is in charge of handling Wtfs found in code
 */
class Wtf
{
    /**
     * The path of the file
     * @var string
     */
    private $_file;

    /**
     * An associative array where wtf have been encountered in the file
     *     line number => array(
     *         'severity' => severity, 'snippet' => code snippet
     *     )
     * @var array
     */
    private $_wtfs;

    /**
     * The root path can be either vendor if installed with composer
     * or the root folder of phpwtf if installed manually
     */
    private $_rootPath;

    /**
     * Constructor with data injection
     * @param $wtfArray An array of data usable by the object
     *         array(
     *             'file' => filepath,
     *             'wtfs' => array(
     *                 lineNb => array(
     *                     'severity' => severity,
     *                     'snippet' => snippet
     *                 ),
     *             )
     *         )
     * @throws Exception
     */
    public function __construct($wtfArray)
    {
        if (!empty($wtfArray['file'])) {
            $this->_file = $wtfArray['file'];
        } else {
            throw new \Exception('No file path specified !');
        }
        if (!empty($wtfArray['wtfs'])) {
            $this->_wtfs = $wtfArray['wtfs'];
        } else {
            $this->_wtfs = array();
        }

        $this->_rootPath = __DIR__ . '/../../';
        if (stripos($this->_rootPath, 'phpwtf/phpwtf') !== false) {
            $this->_rootPath .= '../../';
        }
    }

    /**
     * Returns the file path
     * @return string
     */
    public function getFile()
    {
        return $this->_file;
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
     * Returns the file path without the phpwtf folder path
     * @return string
     */
    public function getReadableFileName()
    {
        if (null != $this->_rootPath) {
            return $this->_getSplInfo($this->getFile())->getRealPath();
        }
        return $this->_file;
    }

    /**
     * Returns the array of wtf encountered
     * @return array
     */
    public function getWtfs()
    {
        return $this->_wtfs;
    }

    /**
     * Add a wtf to the wtf list
     * @param int $line The line number where the wtf has been found
     * @param string $snippet The code snippet involved
     * @param string $severity OPTIONAL 'error' by default
     */
    public function addWtf($line, $snippet, $severity = 'error')
    {
        $wtfs = $this->getWtfs();
        $this->_wtfs[$line] = array(
            'severity' => $severity, 'snippet' => $snippet
        );
    }

    /**
     * Used to output the current wtf object to an xml format
     * @return string XML
     */
    public function toXml()
    {
        $wtfs = $this->getWtfs();
        $xml = '<file name="' . $this->getReadableFileName() . '">';
        if (!empty($wtfs)){
            foreach ($wtfs as $line => $wtf) {
                $xml .= '<error line="' . $line . '" ' .
                    'severity="' . $wtf['severity'] . '" ' .
                    'message="' . htmlentities($wtf['snippet']) . '"/>';
            }
        }
        $xml .= '</file>';
        return $xml;
    }

    /**
     * Used to create a html report for the current file
     * @params string $template
     * @returns string Html
     */
    public function toHtml($template)
    {
        $html = $template;
        $wtfs = $this->getWtfs();
        // replace vars by values, then return new updated html string
        $html = str_replace(
            array('${fileName}', '${wtfsNb}', '${lastModified}'),
            array(
                $this->getReadableFileName(), count($wtfs), date('Y-m-d H:i:s')
            ),
            $html
        );

        // now we make the snippet list
        $snippets = '';
        foreach ($wtfs as $line => $wtf) {
            $snippets .= '<div>' .
                '<div class="line"><span class="label">Line : </span>' .
                $line . '</div><div class="snippet">' .
                '<code>' . nl2br($wtf['snippet']) . '</code>' .
                '</div></div>';
        }

        $html = str_replace('${snippets}', $snippets, $html);

        return $html;
    }
}