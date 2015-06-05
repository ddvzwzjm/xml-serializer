<?php
/**
 * Created by PhpStorm.
 * User: alexerm
 * Date: 4/11/15
 * Time: 12:38
 */

namespace XmlSerializer\Config;


/**
 * Class Configuration
 * @package XmlSerializer\Config
 */
class Configuration {
    /**
     * @var bool
     */
    private $useGeneratedNamespaceAliases = true;

    /**
     * @var string
     */
    protected $defaultNs = ''; // "urn:iso:std:iso:20022:tech:xsd:pain.002.002.03"

    /**
     * @var array
     */
    protected $nsMap = [
//        "urn:iso:std:iso:20022:tech:xsd:pain.002.002.03" => "ns2",
    ];

    /**
     * @var array
     */
    protected $classNsMap = [
//        "de.xcom.sdd.management.payment.001"             => "Crosslend\\ProviderBundle\\Entity\\Sepa\\Payment",
    ];

    /**
     * @var array
     */
    private $xsdMap = [
//        "de.xcom.sdd.management.payment.001" => "../../xcom.management.payment.xsd"
    ];


    /**
     * @param $defaultXmlNamespace
     */
    public function setDefaultNamespace($defaultXmlNamespace)
    {
        $this->defaultNs = $defaultXmlNamespace;
        if ($this->useGeneratedNamespaceAliases) {
            $this->_regenerateNamespaceAliases();
        }
    }

    /**
     * @param $xmlNamespace
     * @param $classNamespace
     */
    public function addNamespace($xmlNamespace, $classNamespace)
    {
        $this->classNsMap[$xmlNamespace] = $classNamespace;
        if (!isset($this->nsMap[$xmlNamespace])) {
            $this->nsMap[$xmlNamespace] = "ns" . (count($this->nsMap) + 1);
        }
        if ($this->useGeneratedNamespaceAliases) {
            $this->_regenerateNamespaceAliases();
        }
    }

    /**
     * @param $xmlNamespace
     * @param $alias
     */
    public function addXmlNamespaceAlias($xmlNamespace, $alias)
    {
        $this->nsMap[$xmlNamespace] = $alias;
        $this->useGeneratedNamespaceAliases = false;
    }

    /**
     * @param $xmlNamespace
     *
     * @return bool
     */
    public function getClassNamespace($xmlNamespace)
    {
        return isset($this->classNsMap[$xmlNamespace]) ? $this->classNsMap[$xmlNamespace] : false;
    }

    /**
     * @param $classNamespace
     *
     * @return bool|int|string
     */
    public function getXmlNamespace($classNamespace)
    {
        foreach ($this->classNsMap as $xmlNs => $ns) {
            if (strpos($classNamespace, $ns) === 0) {
                return $xmlNs;
            }
        }

        return false;
    }

    /**
     * @param $xmlNamespace
     *
     * @return string
     */
    public function getShortXmlNamespace($xmlNamespace)
    {
        return isset($this->nsMap[$xmlNamespace]) ? $this->nsMap[$xmlNamespace] : "";
    }

    /**
     * @return array
     */
    public function getAllShortXmlNamespaces()
    {
        return $this->nsMap;
    }

    /**
     * @return string
     */
    public function getDefaultXmlNamespace()
    {
        return $this->defaultNs;
    }

    private function _regenerateNamespaceAliases()
    {
        $index = 1;
        foreach ($this->nsMap as $ns => $alias) {
            if ($ns == $this->defaultNs) {
                $this->nsMap[$ns] = '';
                continue;
            }
            $this->nsMap[$ns] = 'ns'.$index;
            $index++;
        }
    }

//    private $xmlDestinationPath = '';
//
//    public function addXsdScheme($xmlNamespace, $xsdPath)
//    {
//        $this->xsdMap[$xmlNamespace] = $xsdPath;
//    }
//
//    public function getXsdSchemePath($xmlNamespace)
//    {
//        return $this->xsdMap[$xmlNamespace];
//    }




}