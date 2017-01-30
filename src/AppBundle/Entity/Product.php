<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Product
 *
 * @ORM\Table(name="tblProductData", uniqueConstraints={@ORM\UniqueConstraint(name="strProductCode", columns={"strProductCode"})})
 * @ORM\Entity
 */
class Product
{
    /**
     * @var string
     *
     * @ORM\Column(name="strProductName", type="string", length=255)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="strProductCode", type="string", length=255)
     */
    private $code;

    /**
     * @var int
     *
     * @ORM\Column(name="intStock", type="integer")
     */
    private $stock;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="dtmAdded", type="datetime")
     */
    private $added;

    /**
     * @var float
     *
     * @ORM\Column(name="dcCost", type="decimal", precision=10, scale=2)
     */
    private $cost;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="dtmDiscontinued", type="datetime")
     */
    private $discontinued;

    /**
     * @var string
     *
     * @ORM\Column(name="strProductDesc", type="string", length=512)
     */
    private $description;

    /**
     * @var int
     *
     * @ORM\Column(name="intProductDataId", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * Set name.
     *
     * @param string $name
     *
     * @return Product
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set code.
     *
     * @param string $code
     *
     * @return Product
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code.
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set stock.
     *
     * @param int $stock
     *
     * @return Product
     */
    public function setStock($stock)
    {
        $this->stock = $stock;

        return $this;
    }

    /**
     * Get stock.
     *
     * @return int
     */
    public function getStock()
    {
        return $this->stock;
    }

    /**
     * Set added.
     *
     * @param \DateTime $added
     *
     * @return Product
     */
    public function setAdded($added)
    {
        $this->added = $added;

        return $this;
    }

    /**
     * Get added.
     *
     * @return \DateTime
     */
    public function getAdded()
    {
        return $this->added;
    }

    /**
     * Set cost.
     *
     * @param float $cost
     *
     * @return Product
     */
    public function setCost($cost)
    {
        $this->cost = $cost;

        return $this;
    }

    /**
     * Get cost.
     *
     * @return float
     */
    public function getCost()
    {
        return $this->cost;
    }

    /**
     * Set discontinued.
     *
     * @param \DateTime $discontinued
     *
     * @return Product
     */
    public function setDiscontinued($discontinued)
    {
        $this->discontinued = $discontinued;

        return $this;
    }

    /**
     * Get discontinued.
     *
     * @return \DateTime
     */
    public function getDiscontinued()
    {
        return $this->discontinued;
    }

    /**
     * Set description.
     *
     * @param string $description
     *
     * @return Product
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
}
