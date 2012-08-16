<?php
/**
 * @copyright Pierre-Emmanuel Manteau @ 2012
 * @author Pierre-Emmanuel Manteau aka PEM
 * @license MIT
 */

class Phpwtf\Wtfs
{
    /**
     * A list of Wtf objects
     * @var array Wtf(]
     */
    private $_wtfs;

    /**
     * Returns the list of Wtfs
     * @return array Wtf[]
     */
    public function getWtfs()
    {
        return $this->_wtfs;
    }

    /**
     * Add a Wtf object to the list of wtfs
     * @param Wtf $wtf
     */
    public function addWtf(Wtf $wtf)
    {
        $this->_wtfs[] = $wtf;
    }

    /**
     * Used to generate the output of the wtfs objects to an xml string
     * @return string XML
     */
    public function toXml()
    {
        $xml = '';
        $wtfs = $this->getWtfs();
        if (!empty($wtfs)) {
            foreach ($wtfs as $wtf) {
                $xml .= $wtf->toXml();
            }
        }
        return $xml;
    }
}