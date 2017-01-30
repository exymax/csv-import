<?php

namespace AppBundle\Service\Helper\ConverterAggregator;

class ProductConverterAggregator extends ConverterAggregator
{
    public function __construct()
    {
        parent::__construct();

        $this
            ->addConverter('[discontinued]', $this->getDiscontinuedConverter())
             ->addConverter('[cost]', $this->getCostConverter());
             //->addConverter('[stock]', $this->getStockConverter());
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

    /**
     * Returns converter, which extracts a float number from the input 'cost' string field
     * @return \Closure
     */
    public function getCostConverter()
    {
        $converter = function ($input) {
            $cost = floatval(str_replace(',', '.', str_replace('.', '', $input)));
            return (is_null($cost) || is_string($cost)) ? null : $cost;
        };

        return $converter;
    }

    /**
     * Returns converter, which extracts an integer number from the input 'stock' string field
     * @return \Closure
     */
    public function getStockConverter()
    {
        $converter = function ($input) {
            return (strlen($input) > 0 && is_numeric($input)) ? intval($input) : null;
        };

        return $converter;
    }
}
