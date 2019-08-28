<?php

namespace Bordieris\ExtendedPlace;

use Laravel\Nova\Fields\Field;

class ExtendedPlace extends Field
{
    /**
     * The field's component.
     *
     * @var string
     */
    public $component = 'extended_address';

    public function initLocation($latitude, $longitude){
        return $this->withMeta([
            'lat' => $latitude,
            'lng' => $longitude,
        ]);
    }

    public function zoom($zoom)
    {
        return $this->withMeta([
            'zoom' => $zoom
        ]);
    }
}