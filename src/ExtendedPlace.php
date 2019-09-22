<?php

namespace Bordieris\ExtendedPlace;

use App\City;
use Illuminate\Support\Facades\Config;
use Laravel\Nova\Fields\Field;
use Laravel\Nova\Http\Requests\NovaRequest;

class ExtendedPlace extends Field
{
    /**
     * The field's component.
     *
     * @var string
     */
    public $component = 'extended_place';

    private $cityField = '';
    private $cityIdField = '';
    private $latField = '';
    private $longField = '';

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

    public function city($city='city'){
        if(!is_array($city)){
            $city = [$city, $city.'_id'];
        }
        if(count($city)>1){
            $this->cityField = $city[0];
            $this->cityIdField = $city[1];
        }

        //return $this->withMeta([]);
        return $this->withMeta([__FUNCTION__ => $this->cityField]);
    }

    /**
     * Specify the field that contains the province.
     *
     * @param  string  $field
     * @return $this
     */
    public function province($field)
    {
        return $this->withMeta([__FUNCTION__ => $field]);
    }

    /**
     * Specify the field that contains the latitude.
     *
     * @param  string  $field
     * @return $this
     */
    public function latitude($field)
    {
        $this->latField=$field;
        return $this->withMeta([__FUNCTION__ => $field]);
    }

    /**
     * Specify the field that contains the longitude.
     *
     * @param  string  $field
     * @return $this
     */
    public function longitude($field)
    {
        $this->longField=$field;
        return $this->withMeta([__FUNCTION__ => $field]);
    }

    /**
     * Hydrate the given attribute on the model based on the incoming request.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param  string  $requestAttribute
     * @param  object  $model
     * @param  string  $attribute
     * @return void
     */
    protected function fillAttributeFromRequest(NovaRequest $request,
                                                $requestAttribute,
                                                $model,
                                                $attribute)
    {
        if ($request->exists($requestAttribute)) {
            if($request[$requestAttribute] && $address = json_decode($request[$requestAttribute], true)) {
                $model->{$attribute} = $address['address'];//address
                $city = City::getByName($address['city']);
                $model->{$this->latField} = $address['latlng']['lat'];
                $model->{$this->longField} = $address['latlng']['lng'];
                if (!$city) {
                    $city = City::create([
                        'name' => $address['city'],
                        'province' => $address['province'],
                        'external' => ($address['province'] != 'PC')
                    ]);
                }
                $model->{$this->cityIdField} = $city->id;


            }
        }

    }
}