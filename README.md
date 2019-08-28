## Nova Extended Place Field

A Nova field to place a marker on map to get coordinates then it reverse geocoding the coordinates to get a street address
## Installation

You can install the package in to a Laravel app that uses Nova via composer:

```bash
composer require bordieris/nova_extended_place
```

## Configuration
Publish the package config file:
```bash
php artisan vendor:publish --provider="Bordieris\ExtendedPlace\FieldServiceProvider"
```

This is the contents of the file which will be published at [config/extended-place.php](config/extended-place.php).

Add the following keys to your `.env`:

```
MAP_ADDRESS_API_KEY=

Optional: Set map and address language
MAP_ADDRESS_LANGUAGE=it
```

_If you need a Google Maps API key, you can create an app and enable Places API and create credentials to get your API key https://console.developers.google.com._

## Usage:
Add the below to Nova/User.php resource:

```php

ExtendedPlace::make('address'),

//You can set the initial map location. By default (United States)
 ExtendedPlace::make('address')
    ->initLocation(40.730610,-98.935242),

//You can also set the map zoom level. By default (4)
 ExtendedPlace::make('address')
    ->initLocation(40.730610,-98.935242)
    ->zoom(12),

```

![Package screenshot](https://pbs.twimg.com/media/DlyEKmaWsAIiUdZ.jpg)

![Package screenshot](https://pbs.twimg.com/media/DlyEL0AW0AU0UQL.jpg)

## Support:
bordieris@artimedia.net

## Inspired by
[naifalshaye/nova-map-address](https://github.com/naifalshaye/nova-map-address)


## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
