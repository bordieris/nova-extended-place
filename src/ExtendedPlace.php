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


    /**
     * Create a new field.
     *
     * @param  string  $name
     * @param  string|null  $attribute
     * @param  mixed|null  $resolveCallback
     * @return void
     */
    public function __construct($name = null, $attribute = null, $resolveCallback = null) {
        parent::__construct($name, $attribute, $resolveCallback);

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