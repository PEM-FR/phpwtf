<?php
/**
 * Created by PhpStorm.
 * User: pem
 * Date: 23/07/15
 * Time: 16:51
 */

namespace Phpwtf;


class WtfSnippet {

    /**
     * @var string
     */
    private $identifier;

    /**
     * @var int
     */
    private $lineStart;

    /**
     * @var int
     */
    private $lineStop;

    /**
     * @var string
     */
    private $snippet;

    /**
     * @var string
     */
    private $severity;

    /**
     * @param string $identifier
     */
    function __construct($identifier)
    {
        $this->identifier = sha1($identifier);
    }

    /**
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * @param int $lineStart
     */
    public function setLineStart($lineStart)
    {
        $this->lineStart = $lineStart;
    }

    /**
     * @return int
     */
    public function getLineStart()
    {
        return $this->lineStart;
    }

    /**
     * @param int $lineStop
     */
    public function setLineStop($lineStop)
    {
        $this->lineStop = $lineStop;
    }

    /**
     * @return int
     */
    public function getLineStop()
    {
        return $this->lineStop;
    }

    /**
     * @param string $severity
     */
    public function setSeverity($severity)
    {
        $this->severity = $severity;
    }

    /**
     * @return string
     */
    public function getSeverity()
    {
        return $this->severity;
    }

    /**
     * @param string $snippet
     */
    public function setSnippet($snippet)
    {
        $this->snippet = $snippet;
    }

    /**
     * @return string
     */
    public function getSnippet()
    {
        return $this->snippet;
    }




} 