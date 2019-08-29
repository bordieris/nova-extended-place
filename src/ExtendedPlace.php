<?php

namespace Bordieris\ExtendedPlace;

use Illuminate\Support\Facades\Config;
use Laravel\Nova\Fields\Field;

class ExtendedPlace extends Field
{
    /**
     * The field's component.
     *
     * @var string
     */
    public $component = 'extended_place';

    public function __construct($user = null) {
        parent::__construct(null, null, null);

        $this->initLocation(Config::get('extended-place.default_geopoint.latitude'), Config::get('extended-place.default_geopoint.longitude'))
            ->zoom(Config::get('extended-place.default_zoom'));
    }


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