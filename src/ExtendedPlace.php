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

    private $lat;
    private $lng;


    public function __construct($user = null) {
        parent::__construct(null, null, null);

        $this->init();

        return $this->execute();
    }

    private function execute(){
        return $this->withMeta([
            'lat' => $this->lat,
            'lng' => $this->lng,
        ]);
    }

    private function init(){
        $this->lat = Config::get('extended-place.default_geopoint.latitude');
        $this->lng = Config::get('extended-place.default_geopoint.longitude');
    }

    public function initLocation($latitude, $longitude){
        $this->lat = $latitude;
        $this->lng = $longitude;
        return $this->execute();
    }

    public function zoom($zoom)
    {
        return $this->withMeta([
            'zoom' => $zoom
        ]);
    }
}