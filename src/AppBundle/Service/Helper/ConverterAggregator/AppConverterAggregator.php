<?php

namespace AppBundle\Service\Helper\ConverterAggregator;

class AppConverterAggregator extends ConverterAggregator
{
    public function __construct()
    {
        parent::__construct();

        $this->addConverter('[discontinued]', $this->getDiscontinuedConverter())
             ->addConverter('[added]', $this->getAddedConverter())
             ->addConverter('[cost]', $this->getCostConverter())
             ->addConverter('[stock]', $this->getStockConverter());
    }

    public function getDiscontinuedConverter()
    {
        $converter = function ($input) {
            if ($input === 'yes') {
                return new \DateTime();
            } else {
                return null;
            }
        };

        return $converter;
    }

    /** Returns converter, which turns 'added' fields with null value to the current time, else turns to null
     * @return \Closure
     */
    public function getAddedConverter()
    {
        $converter = function ($input) {
            return (is_null($input)) ? new \DateTime() : null;
        };

        return $converter;
    }

    /** Returns converter, which extracts a float number from the input 'cost' string field
     * @return \Closure
     */
    public function getCostConverter()
    {
        $converter = function ($input) {
            $matches = [];
            preg_match('!\d+\.*\d*!', $input, $matches);
            $cost = floatval(trim($matches[0]));

            return is_null($cost) ? null : $cost;
        };

        return $converter;
    }

    /** Returns converter, which extracts an integer number from the input 'stock' string field
     * @return \Closure
     */
    public function getStockConverter()
    {
        $converter = function ($input) {
            return (strlen($input) > 0) ? intval($input, 10) : null;
        };

        return $converter;
    }
}
