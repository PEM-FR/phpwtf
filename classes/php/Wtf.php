<?php
/**
 * @copyright Pierre-Emmanuel Manteau @ 2012
 * @author Pierre-Emmanuel Manteau aka PEM
 * @license MIT
 */

class Phpwtf\Wtf
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
            throw new Exception('No file path specified !');
        }
        if (!empty($wtfArray['wtfs'])) {
            $this->_wtfs = $wtfArray['wtfs'];
        } else {
            $this->_wtfs = array();
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
        $xml = '<file name="' . $this->getFile() . '">';
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
            array(
                '${fileName}', '${wtfsNb}', '${phpwtf_path}', '${lastModified}'
            ),
            array(
                $this->getFile(), count($wtfs), __DIR__, date('Y-m-d H:i:s')
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