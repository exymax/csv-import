<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Product
 *
 * @ORM\Table(name="tblProductData")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ProductRepository")
 */
class Product
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="intProductDataId", type="integer", unique=true)
     */
    private $intProductDataId;

    /**
     * @var string
     *
     * @ORM\Column(name="strProductName", type="string", length=255)
     */
    private $strProductName;

    /**
     * @var string
     *
     * @ORM\Column(name="strProductDesc", type="string", length=512)
     */
    private $strProductDesc;

    /**
     * @var string
     *
     * @ORM\Column(name="strProductCode", type="string", length=255, unique=true)
     */
    private $strProductCode;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="dtmAdded", type="datetime")
     */
    private $dtmAdded;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="dtmDiscounted", type="datetime")
     */
    private $dtmDiscontinued;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="stmTimestamp", type="time")
     */
    private $stmTimestamp;

    /**
     * @var int
     *
     * @ORM\Column(name="intStockLevel", type="integer")
     */
    private $intStockLevel;

    /**
     * @var float
     *
     * @ORM\Column(name="floatPrice", type="float")
     */
    private $floatPrice;


    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set intProductDataId
     *
     * @param integer $intProductDataId
     * @return Product
     */
    public function setIntProductDataId($intProductDataId)
    {
        $this->intProductDataId = $intProductDataId;

        return $this;
    }

    /**
     * Get intProductDataId
     *
     * @return integer
     */
    public function getIntProductDataId()
    {
        return $this->intProductDataId;
    }

    /**
     * Set strProductName
     *
     * @param string $strProductName
     * @return Product
     */
    public function setStrProductName($strProductName)
    {
        $this->strProductName = $strProductName;

        return $this;
    }

    /**
     * Get strProductName
     *
     * @return string
     */
    public function getStrProductName()
    {
        return $this->strProductName;
    }

    /**
     * Set strProductDesc
     *
     * @param string $strProductDesc
     * @return Product
     */
    public function setStrProductDesc($strProductDesc)
    {
        $this->strProductDesc = $strProductDesc;

        return $this;
    }

    /**
     * Get strProductDesc
     *
     * @return string
     */
    public function getStrProductDesc()
    {
        return $this->strProductDesc;
    }

    /**
     * Set strProductCode
     *
     * @param string $strProductCode
     * @return Product
     */
    public function setStrProductCode($strProductCode)
    {
        $this->strProductCode = $strProductCode;

        return $this;
    }

    /**
     * Get strProductCode
     *
     * @return string
     */
    public function getStrProductCode()
    {
        return $this->strProductCode;
    }

    /**
     * Set dtmAdded
     *
     * @param \DateTime $dtmAdded
     * @return Product
     */
    public function setDtmAdded($dtmAdded)
    {
        $this->dtmAdded = $dtmAdded;

        return $this;
    }

    /**
     * Get dtmAdded
     *
     * @return \DateTime
     */
    public function getDtmAdded()
    {
        return $this->dtmAdded;
    }

    /**
     * Set dtmDiscounted
     *
     * @param \DateTime $dtmDiscontinued
     * @return Product
     */
    public function setDtmDiscontinued($dtmDiscontinued)
    {
        $this->dtmDiscontinued = $dtmDiscontinued;

        return $this;
    }

    /**
     * Get dtmDiscontinued
     *
     * @return \DateTime
     */
    public function getDtmDiscontinued()
    {
        return $this->dtmDiscontinued;
    }

    /**
     * Set stmTimestamp
     *
     * @param \DateTime $stmTimestamp
     * @return Product
     */
    public function setStmTimestamp($stmTimestamp)
    {
        $this->stmTimestamp = $stmTimestamp;

        return $this;
    }

    /**
     * Get stmTimestamp
     *
     * @return \DateTime
     */
    public function getStmTimestamp()
    {
        return $this->stmTimestamp;
    }

    /**
     * Set intStockLevel
     *
     * @param integer $intStockLevel
     * @return Product
     */
    public function setIntStockLevel($intStockLevel)
    {
        $this->intStockLevel = $intStockLevel;

        return $this;
    }

    /**
     * Get intStockLevel
     *
     * @return integer
     */
    public function getIntStockLevel()
    {
        return $this->intStockLevel;
    }

    /**
     * Set floatPrice
     *
     * @param float $floatPrice
     * @return Product
     */
    public function setFloatPrice($floatPrice)
    {
        $this->floatPrice = $floatPrice;

        return $this;
    }

    /**
     * Get floatPrice
     *
     * @return float
     */
    public function getFloatPrice()
    {
        return $this->floatPrice;
    }
}
