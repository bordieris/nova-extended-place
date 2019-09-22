<template>
    <default-field :field="field">
        <template slot="field">
            <div class="google-map" :id="mapName"></div><br>
            <input :id="field.name" type="text"
                   class="w-full form-control form-input form-input-bordered"
                   :class="errorClasses"
                   :placeholder="field.name"
                   v-model="value"
            />
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
                mapName: this.name + "-map"
            }
        },
        mounted: function () {
            var map;
            const element = document.getElementById(this.mapName);
            var latitudeFieldName = this.field.latitude;
            var longitudeFieldName = this.field.longitude;
            var cityFieldName = this.field.city;
            var provinceFieldName = this.field.province;
            this.address = {};
            if (this.value.length>0 && this.isValidJSON(this.value)){
                this.address = JSON.parse(this.value);
            } else {
                this.address.address = this.value;
                let tmpLat = this.getElementValue(this.$parent, latitudeFieldName)
                let tmpLng = this.getElementValue(this.$parent, longitudeFieldName)
                if(tmpLat && tmpLng) {
                    this.address.latlng = {"lat": tmpLat, "lng": tmpLng}
                }
                this.address.city = this.getElementValue(this.$parent, cityFieldName)
                this.address.province = this.getElementValue(this.$parent, provinceFieldName)
                this.address.formatted_address = this.address.address + ' ' +this.address.city
            }
            console.log(this.address);
            var lat = 45.0430593;
            var lng = 9.6725524;


            if (this.field.lat && this.field.lng){
                lat = this.field.lat;
                lng = this.field.lng;
            }
            if (this.address.latlng && this.address.latlng.lat && this.address.latlng.lng){
                lat = this.address.latlng.lat;
                lng = this.address.latlng.lng;
                if (previousMarker) {
                    previousMarker.setMap(null);
                }
            }
            const options = {
                zoom: this.field.zoom || 4,
                center: new google.maps.LatLng(lat, lng)
            };
            map = new google.maps.Map(element, options);
            var previousMarker;

            var extendedPlaceField = this; //address.formatted_address ? address.formatted_address : '' ;
            extendedPlaceField.value = this.address.formatted_address ? this.address.formatted_address : '';
            previousMarker = new google.maps.Marker({
                position: new google.maps.LatLng(lat, lng),
                map: map
            });
            google.maps.event.addListener(map, 'click', function(event) {
                if (previousMarker) {
                    previousMarker.setMap(null);
                }
                previousMarker = new google.maps.Marker({
                    position: event.latLng,
                    map: map
                });
                var geocoder = new google.maps.Geocoder;
                geocoder.geocode({'location': event.latLng}, function(results, status) {
                    if (status === 'OK') {
                        if (results[0]) {
                            //address = results[0].formatted_address+'|'+event.latLng.lat().toFixed(6)+','+event.latLng.lng().toFixed(6);
                            this.address = {
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
                                    if(this.address.address===null){
                                        this.address.address = value.short_name
                                    } else {
                                        this.address.address = value.short_name + ', ' + this.address.address
                                    }
                                    if(this.address.address=='Unnamed Road') this.address.address = ''
                                } else if (value.types.includes('street_number')){
                                    if(this.address.address===null){
                                        this.address.address = value.short_name
                                    } else {
                                        this.address.address =  this.address.address + ', ' + value.short_name
                                    }
                                } else if (value.types.includes('administrative_area_level_2')){
                                    this.address.province = value.short_name
                                }  else if (value.types.includes('administrative_area_level_3')){
                                    cityname.city = value.short_name
                                }  else if (value.types.includes('locality') ){
                                    cityname.locality = value.short_name
                                }
                                this.address.city = cityname.locality
                                if(null==address.city) this.address.city = cityname.city
                            }

                            extendedPlaceField.value = this.address.formatted_address ? this.address.formatted_address : ''
                            Nova.$emit(latitudeFieldName + '-value', this.address.latlng.lat)
                            Nova.$emit(longitudeFieldName + '-value', this.address.latlng.lng)
                            Nova.$emit(cityFieldName + '-value', this.address.city)
                            Nova.$emit(provinceFieldName + '-value', this.address.province)
                            extendedPlaceField.address = this.address;
                        } else {
                            window.alert('No results found');
                        }
                    } else {
                        window.alert('Geocoder failed due to: ' + status);
                        error = false;
                    }
                });
            });
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
                        console.log(component)
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
            }
        },
    }
</script>