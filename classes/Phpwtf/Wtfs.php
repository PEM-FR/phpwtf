<?php
/**
 * @copyright Pierre-Emmanuel Manteau @ 2012
 * @author Pierre-Emmanuel Manteau aka PEM
 * @license MIT
 */

namespace Phpwtf;

/**
 * This is a collection class
 * // TODO: check if ArrayCollection from Doctrine Common could work
 */
class Wtfs
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
        if (null == $this->_wtfs) {
            $this->_wtfs = array();
        }
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
     * @throws \Exception
     * @return string XML
     */
    public function toXml()
    {
        $xml = '';
        $wtfs = $this->getWtfs();
        if (!empty($wtfs)) {
            foreach ($wtfs as $wtf) {
                if ($wtf instanceof Wtf) {
                    $xml .= $wtf->toXml();
                } else {
                    throw new \Exception('Cannot convert to xml. Object is not an instance of Wtf');
                }
            }
        }
        return $xml;
    }
}