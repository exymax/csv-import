<?php

namespace AppBundle\Service\Helper\ConverterAggregator;

use AppBundle\Service\Helper\Headers;

class ProductConverterAggregator extends ConverterAggregator
{
    private $headers;

    public function __construct()
    {
        parent::__construct();
        $this->headers = Headers::get();
        $this
            ->addConverter('[discontinued]', $this->getDiscontinuedConverter())
             ->addConverter('[cost]', $this->getCostConverter())
             ->addConverter('[stock]', $this->getStockConverter());
    }

    /**
     * Returns converter, which transforms 'discountinued' field.
     *
     * @return \Closure
     */
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
     * Returns converter, which extracts a float number from the input 'cost' string field.
     *
     * @return \Closure
     */
    public function getCostConverter()
    {
        $converter = function ($input) {
            $matches = [];
            preg_match('#([0-9\.]+)#', $input, $matches);

            return (count($matches) > 0) ? floatval($matches[0]) : 0;
        };

        return $converter;
    }

    /**
     * Returns converter, which extracts an integer number from the input 'stock' string field.
     *
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
