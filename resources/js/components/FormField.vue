<template>
    <default-field :field="field">
        <template slot="field">
            <input
                    :id="'algoliaSearch_'+field.attribute"
                    type="text"
                    v-model="address.algoliaSearch"
                    class="w-full form-control form-input form-input-bordered"
                    :class="errorClasses"
                    placeholder="Inserisci l'indirizzo da cercare"
                    :disabled="isReadonly"
            />
            <div class="google-map" :id="mapName"></div><br>
            <!--input :id="field.name" type="text"
                   class="w-full form-control form-input form-input-bordered"
                   :class="errorClasses"
                   :placeholder="field.name"
                   v-model="value"
                   disabled
            /-->
            <p v-if="hasError" class="my-2 text-danger">
                {{ firstError }}
            </p>
            <input v-if="false" type="text" class="w-full form-control form-input form-input-bordered"
                   :value="JSON.stringify(address)" />
        </template>
    </default-field>
</template>
<style scoped>
    .google-map {
        width: 720px;
        height: 300px;
        margin: 0 auto;
        background: gray;
        border:solid 1px #ccc;
    }
</style>
<script>
    import { FormField, HandlesValidationErrors } from 'laravel-nova'

    export default {
        name: 'google-map',
        mixins: [FormField, HandlesValidationErrors],
        props: ['resourceName', 'resourceId', 'field'],
        data: function () {
            return {
                mapName: this.name + "-map",
                latitudeFieldName: this.field.latitude,
                longitudeFieldName: this.field.longitude,
                cityFieldName: this.field.city,
                provinceFieldName: this.field.province,
                address: {"algoliaSearch": ''},
                map: null,
                previousMarker: null
            }
        },
        mounted: function () {
            const element = document.getElementById(this.mapName);
            var latitudeFieldName = this.latitudeFieldName;
            var longitudeFieldName = this.longitudeFieldName;
            var cityFieldName = this.cityFieldName;
            var provinceFieldName = this.field.province;
            //this.address = {};
            if (this.value.length>0 && this.isValidJSON(this.value)){
                this.address = JSON.parse(this.value);
                if(!this.address.algoliaSearch) this.address.algoliaSearch = '';
            } else {
                this.address.address = this.value;
                let tmpLat = this.getElementValue(this.$parent, latitudeFieldName)
                let tmpLng = this.getElementValue(this.$parent, longitudeFieldName)
                if(tmpLat && tmpLng) {
                    this.address.latlng = {"lat": tmpLat, "lng": tmpLng}
                }
                //this.address.city = this.getElementValue(this.$parent, cityFieldName)
                this.address.city = this.field.cityName
                this.address.province = this.getElementValue(this.$parent, provinceFieldName)
                this.address.formatted_address = this.address.address + ' ' +this.address.city
            }
            this.address.algoliaSearch = this.address.formatted_address ? this.address.formatted_address : this.address

            var lat = 45.0430593;
            var lng = 9.6725524;


            if (this.field.lat && this.field.lng){
                lat = this.field.lat;
                lng = this.field.lng;
            }
            if (this.address.latlng && this.address.latlng.lat && this.address.latlng.lng){
                lat = this.address.latlng.lat;
                lng = this.address.latlng.lng;
                if (this.previousMarker) {
                    this.previousMarker.setMap(null);
                }
            }
            const options = {
                zoom: this.field.zoom || 4,
                center: new google.maps.LatLng(lat, lng)
            };
            this.map = new google.maps.Map(element, options);

            var extendedPlaceField = this; //address.formatted_address ? address.formatted_address : '' ;
            extendedPlaceField.value = this.address.formatted_address ? this.address.formatted_address : '';
            extendedPlaceField.previousMarker = new google.maps.Marker({
                position: new google.maps.LatLng(lat, lng),
                map: extendedPlaceField.map
            });
            google.maps.event.addListener(extendedPlaceField.map, 'click', function(event) {
                if (extendedPlaceField.previousMarker) {
                    extendedPlaceField.previousMarker.setMap(null);
                }
                extendedPlaceField.previousMarker = new google.maps.Marker({
                    position: event.latLng,
                    map: extendedPlaceField.map
                });
                var geocoder = new google.maps.Geocoder;
                geocoder.geocode({'location': event.latLng}, function(results, status) {
                    if (status === 'OK') {
                        if (results[0]) {
                            //address = results[0].formatted_address+'|'+event.latLng.lat().toFixed(6)+','+event.latLng.lng().toFixed(6);
                            extendedPlaceField.address = {
                                "formatted_address" : results[0].formatted_address,
                                "latlng": {
                                    "lat": event.latLng.lat().toFixed(6),
                                    "lng": event.latLng.lng().toFixed(6)
                                },
                                "address": null
                            };
                            let pieces = results[0].address_components
                            let cityname = {'city':null, 'locality':null}
                            for(let i=0; i<pieces.length;i++){
                                let value = pieces[i]
                                if(value.types.includes('route')){
                                    if(extendedPlaceField.address.address===null){
                                        extendedPlaceField.address.address = value.short_name
                                    } else {
                                        extendedPlaceField.address.address = value.short_name + ', ' + extendedPlaceField.address.address
                                    }
                                    if(extendedPlaceField.address.address=='Unnamed Road') extendedPlaceField.address.address = ''
                                } else if (value.types.includes('street_number')){
                                    if(extendedPlaceField.address.address===null){
                                        extendedPlaceField.address.address = value.short_name
                                    } else {
                                        extendedPlaceField.address.address =  extendedPlaceField.address.address + ', ' + value.short_name
                                    }
                                } else if (value.types.includes('administrative_area_level_2')){
                                    extendedPlaceField.address.province = value.short_name
                                }  else if (value.types.includes('administrative_area_level_3')){
                                    cityname.city = value.short_name
                                }  else if (value.types.includes('locality') ){
                                    cityname.locality = value.short_name
                                }
                            }

                            extendedPlaceField.address.city = cityname.locality
                            if(null==extendedPlaceField.address.city) extendedPlaceField.address.city = cityname.city

                            extendedPlaceField.value = extendedPlaceField.address.formatted_address ? extendedPlaceField.address.formatted_address : ''
                            extendedPlaceField.address.algoliaSearch = extendedPlaceField.value

                            extendedPlaceField.updateFormFields()
                        } else {
                            window.alert('No results found');
                        }
                    } else {
                        window.alert('Geocoder failed due to: ' + status);
                        error = false;
                    }
                });
            });
            this.initializePlaces();

        },
        methods: {
            /*
             * Set the initial, internal value for the field.
             */
            setInitialValue() {
                const places = require('places.js')
                this.value = this.field.value || ''
            },
            /**
             * Fill the given FormData object with the field's internal value.
             */
            /*fill(formData) {
                formData.append(this.field.attribute, this.value || '')
            },*/
            /**
             * Fill the given FormData object with the field's internal value.
             */
            fill(formData) {
                //formData.append(this.field.attribute, this.value || '')
                formData.append(this.field.attribute, JSON.stringify(this.address) || '')
            },
            /**
             * Update the field's internal value.
             */
            handleChange(value) {
                this.value = value
            },
            isValidJSON(stringifiedJSON){
                return (/^[\],:{}\s]*$/.test(stringifiedJSON.replace(/\\["\\\/bfnrtu]/g, '@').
                replace(/"[^"\\\n\r]*"|true|false|null|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?/g, ']').
                replace(/(?:^|:|,)(?:\s*\[)+/g, '')))
            },
            getElementValue(root, elemName){
                let value = null
                root.$children.forEach(component => {
                    if (component.field !== undefined && component.field.attribute == elemName) {
                        value = component.field.value
                    }
                })
                return value
            },
            getElement(root, elemName){
                let theComponent = null
                root.$children.forEach(component => {
                    if (component.field !== undefined && component.field.attribute == elemName) {
                        theComponent = component;
                    }
                })
                return theComponent
            },
            /**
             * Initialize Algolia places library.
             */
            initializePlaces() {
                const places = require('places.js')

                const placeType = this.field.placeType

                const config = {
                    appId: Nova.config.algoliaAppId,
                    apiKey: Nova.config.algoliaApiKey,
                    container: document.querySelector('#algoliaSearch_' + this.field.attribute),
                    type: this.field.placeType ? this.field.placeType : 'address',
                    templates: {
                        value(suggestion) {
                            return suggestion.name
                        },
                    },
                }

                const placesAutocomplete = places(config)

                placesAutocomplete.on('change', e => {
                    this.$nextTick(() => {
                        //console.log(e.suggestion)
                        this.address.algoliaSearch = e.suggestion.name
                        this.address.address = e.suggestion.name
                        this.address.formatted_address = e.suggestion.name +' '+e.suggestion.city
                        this.address.city = e.suggestion.city
                        this.address.latlng = e.suggestion.latlng
                        this.address.province = e.suggestion.hit.county[1]

                        this.updateFormFields()
                    })
                })

                placesAutocomplete.on('clear', () => {
                    this.$nextTick(() => {
                        this.address.algoliaSearch = ''
                        this.address.address = ''
                        this.address.formatted_address = ''
                        this.address.city = ''
                        this.address.province = ''
                        this.address.latlng = {"lat":'', "lng":''}

                        this.updateFormFields()
                    })
                })
            },
            updateFormFields() {
                //Nova.$emit('algoliaSearch'+this.field.attribute + '-value', this.address.algoliaSearch)
                Nova.$emit(this.latitudeFieldName + '-value', this.address.latlng.lat)
                Nova.$emit(this.longitudeFieldName + '-value', this.address.latlng.lng)
                Nova.$emit(this.cityFieldName + '-value', this.address.city)
                Nova.$emit(this.provinceFieldName + '-value', this.address.province)
                var map = this.map
                if (this.previousMarker) {
                    this.previousMarker.setMap(null);
                };
                this.previousMarker = new google.maps.Marker({
                    position: new google.maps.LatLng(this.address.latlng.lat, this.address.latlng.lng),
                    map: map
                });
                map.panTo(this.previousMarker.getPosition())

            }
        },
    }
</script>