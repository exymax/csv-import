<?php

namespace AppBundle\Service\Helper;

class Headers
{
    public static function get()
    {
        return [
            'code', 'name', 'description', 'stock', 'cost', 'discontinued',
        ];
    }
}
