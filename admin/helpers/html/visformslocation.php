<?php
/**
 * @author       Aicha Vack
 * @package      Joomla.Administration
 * @subpackage   com_visforms
 * @link         http://www.vi-solutions.de
 * @license      GNU General Public License version 2 or later; see license.txt
 * @copyright    2017 vi-solutions
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

class JHtmlVisformslocation
{
	protected static $loaded = array();

	public static function createDbValue($setting = array()) {
		if (!is_array($setting)) {
			$setting = array();
		}
		return VisformsHelper::registryStringFromArray($setting);
	}

	public static function extractDbValue($string = '') {
		return VisformsHelper::registryArrayFromString($string);
	}

	public static function includeLocationSearchJs() {
		if (!empty(static::$loaded[__METHOD__])) {
			return true;
		}
		$doc = JFactory::getDocument();
		$script = '
			jQuery(document).ready(function () {
				jQuery(".placessearchbox").keypress(function (event) {
			        var key = event.keyCode;
			        if (key === 13) {
			            event.preventDefault();
			            //if we call geocodeSearchAddress now, this function will be called afterwards a second time by the onchange event handler of the field
			            //So we "trigger" change event by leaving the field by setting the focus to matching radius field
			            var id = event.target.getAttribute("id") + "_radius";
			            var next = document.getElementById(id);
			            next.focus(); 
			            return false;
			        }
			    });
			});
			function geocodeSearchAddress(element) {
				var id = jQuery(element).attr("id");
				if (element.value) {
					var geocoder = new google.maps.Geocoder();
					geocoder.geocode({"address": element.value}, function(results, status) {
			            if (status === "OK") {
			                var lat = results[0].geometry.location.lat();
			                var lng = results[0].geometry.location.lng();
			                var location = \'{"lat":"\' + results[0].geometry.location.lat() + \'", "lng":"\' + results[0].geometry.location.lng()+ \'"}\';
			                jQuery("#" + id + "_location").val(location);
			                element.form.submit();
			            } else {
			                alert("'. addslashes(JText::_( "COM_VISFORMS_GEOCODING_ERROR" )).' " + status);
			                jQuery("#" + id + "_location").val("");
			            }
			        });
				} else {
					jQuery("#" + id + "_location").val("");
					element.form.submit();
				}
			}
		';
		$doc->addScriptDeclaration($script);
		self::loadGoogleMapsApi();
		static::$loaded[__METHOD__] = true;
		return false;
	}

	public static function includeLocationFieldJs() {
		if (!empty(static::$loaded[__METHOD__])) {
			return true;
		}
		$doc = JFactory::getDocument();
		$script = '
			function onChangeCoord(event) {
				var lat = parseFloat(document.getElementById("field"+event.data.field+"_lat").value);
				var lng = parseFloat(document.getElementById("field"+event.data.field+"_lng").value);
				updateMap(lat, lng, event.data.field);
				clearSearchField(event.data.field);
				validateLatLngInputs(event.data.field);
			}
			function updateMap(lat, lng, fieldId) {
				removeVfMarker(fieldId);
				if (!isNaN(lat) && !isNaN(lng)) {
					setVfMarker(lat, lng, fieldId);
				}
			}
			function setUserPostion(event) {
				event.preventDefault();
				var fieldId = event.data.field;
				if (navigator.geolocation) {
					navigator.geolocation.getCurrentPosition(
						function(position) {
						if (event.data.displayMap) {
								updateMap(position.coords.latitude, position.coords.longitude, fieldId);
							}
							updateLocationInputFields(position.coords.latitude.toFixed(8), position.coords.longitude.toFixed(8), fieldId);
							clearSearchField(event.data.field);
						}, function (error) {
							var code = error.code;
							var msg = ["'. addslashes(JText::_( "COM_VISFORMS_UNKNOWN_ERROR" )).'", 
							"'. addslashes(JText::_( "COM_VISFORMS_NO_PERMISSION" )).'", 
							"'. addslashes(JText::_( "COM_VISFORMS_NO_POSITION" )).'", 
							"'. addslashes(JText::_( "COM_VISFORMS_GEOLOCATION_TIMEOUT" )).'"];
							alert("'. addslashes(JText::_( "COM_VISFORMS_GEOLOCATION_POSITION_ERROR" )).' "+ msg[error.code]);
						}
					);
				} else {
					alert("'. addslashes(JText::_( "COM_VISFORMS_GEOLOCATION_NOT_SUPPORTED" )).'");
				}
			}
			function updateLocationInputFields(lat, lng, id) {
				var latFieldId = "field" + id + "_lat";
				var lngFieldId = "field" + id + "_lng"
				jQuery("#"+latFieldId).val(lat);
				jQuery("#"+lngFieldId).val(lng);
				validateLatLngInputs(id);
			}
			function validateLatLngInputs(id){
				var latFieldId = "field" + id + "_lat";
				var lngFieldId = "field" + id + "_lng"
				var latError = false;
				var lngError = false;
				if (jQuery("#"+latFieldId).hasClass("error") || jQuery("#"+latFieldId).hasClass("valid")) {latError = true;}
				if (jQuery("#"+lngFieldId).hasClass("error") || jQuery("#"+lngFieldId).hasClass("valid")) {lngError = true;}
				if (latError) {jQuery("#"+latFieldId).valid();}
				if (lngError) {jQuery("#"+lngFieldId).valid();}
			}
			function removeVfMarker(fieldId) {
				var markersName = "vfMarkers" + fieldId;
				if (typeof window[markersName] != "undefined") {
					for (var i = 0; i < window[markersName].length; i++) {window[markersName][i].setMap(null);}
					window[markersName] = [];
				}
			}
			function setVfMarker(lat, lng, fieldId) {
				var markersName = "vfMarkers" + fieldId;
				var mapName = "vfMap" + fieldId;
				if (typeof window[mapName] != "undefined" && typeof window[markersName] != "undefined") {
					var pos = {lat: lat, lng: lng};
					var marker = new google.maps.Marker({position:  pos , map: window[mapName]});
					window[mapName].setCenter(pos);
					window[markersName].push(marker);
				}
			}
			function initLocation(fieldOptions) {
				fieldOptions = fieldOptions || {};
				if (!fieldOptions.fieldId) {return false;}
				this.fieldId = fieldOptions.fieldId;
				this.displayMap = fieldOptions.displayMap;
				this.defaultPos = fieldOptions.attPos;
				this.mapCenter = fieldOptions.mapCenter;
				this.latFieldId = "field" + this.fieldId + "_lat";
				this.lngFieldId = "field" + this.fieldId + "_lng"
				this.zoom = fieldOptions.zoom;
				this.searchBoxButtonId = "searchLocationfield" + this.fieldId;			
				this.geocoder = new google.maps.Geocoder();
				if (this.displayMap) {
					this.markersName = "vfMarkers" + this.fieldId;
					this.mapName = "vfMap" + this.fieldId;
					this.center = (this.defaultPos.lat && this.defaultPos.lng) ? this.defaultPos : this.mapCenter;
					this.showMarkers = (this.defaultPos.lat && this.defaultPos.lng) ? 1 : 0;
					this.mapOptions = {center: this.center, zoom: this.zoom, mapTypeId: \'roadmap\'}; 
					this.map = new google.maps.Map(document.getElementById(this.mapName), this.mapOptions);
					if (this.showMarkers) {
						this.marker = new google.maps.Marker({position:  this.center , map: this.map});
						if (typeof window[this.markersName] != "undefined") {window[this.markersName].push(this.marker);}
					}
					window[this.mapName] = this.map;
					jQuery("#"+this.latFieldId).on("change", {field: this.fieldId}, onChangeCoord);
					jQuery("#"+this.lngFieldId).on("change", {field: this.fieldId}, onChangeCoord);
					jQuery("#"+this.latFieldId).keypress({handler: "onChangeCoord", field: this.fieldId}, vfMapKeyEventHandler);
					jQuery("#"+this.lngFieldId).keypress({handler: "onChangeCoord", field: this.fieldId}, vfMapKeyEventHandler);
					jQuery(".conditional.field"+fieldId).on("reloadVfMap", {field: this.fieldId}, refreshVfMap);
					jQuery(".field"+fieldId).closest("form").on("reloadVfMap", {field: this.fieldId}, refreshVfMap);
				}
				jQuery("#getLocationfield"+this.fieldId).on("click", {field: this.fieldId, displayMap: this.displayMap}, setUserPostion);
				jQuery("#" + this.searchBoxButtonId).on("click", {field: this.fieldId, displayMap: this.displayMap, geocoder: this.geocoder }, geocodeAddress);
				jQuery("#searchLocationInputfield" + this.fieldId).keypress({handler: "geocodeAddress", field: this.fieldId, displayMap: this.displayMap, geocoder: this.geocoder }, vfMapKeyEventHandler);
			}
			function vfMapKeyEventHandler (event) {
				var key = event.keyCode;
		        if (key === 13) {
		            event.preventDefault();
		            if (typeof event.data.handler !== "undefined" && typeof window[event.data.handler] !== "undefined") {
		                window[event.data.handler](event);
		            }
		            return false;
		        }
			}
			function geocodeAddress(event) {
				event.preventDefault();
				var searchBox = "searchLocationInputfield" + event.data.field;
				var geocoder = event.data.geocoder;
				var value = jQuery("#" + searchBox).val();
				if (value) {
					geocoder.geocode({"address": value}, function(results, status) {
		            if (status === "OK") {
		                var lat = results[0].geometry.location.lat();
		                var lng = results[0].geometry.location.lng();
		                updateLocationInputFields(lat.toFixed(8), lng.toFixed(8), event.data.field);
		                if (event.data.displayMap) {
							updateMap(lat, lng, event.data.field);
						}
		            } else {
		                alert("'. addslashes(JText::_( "COM_VISFORMS_GEOCODING_ERROR" )).' " + status);
		            }
		        });
				}
			}
			
			function clearSearchField(fieldId) {
				jQuery("#searchLocationInputfield" + fieldId).val("");
			}
			function refreshVfMap(event) {
				var mapName = "vfMap" + event.data.field;
				if (typeof window[mapName] != "undefined") {
					var map = window[mapName];
					var center = map.getCenter();
					google.maps.event.trigger(map, "resize");
					map.setCenter(center);
					window[mapName] = map;
				}
			}
			function initDataMap(fieldOptions) {
				fieldOptions = fieldOptions || {};
				if (!fieldOptions.fieldId) {return false;}
				this.fieldId = fieldOptions.fieldId;
				this.defaultPos = fieldOptions.attPos;
				this.center = fieldOptions.attPos;
				this.zoom = fieldOptions.zoom;
				this.markersName = "vfMarkers" + this.fieldId;
				this.mapName = "vfMap" + this.fieldId;
				this.mapOptions = {center: this.center, zoom: this.zoom, mapTypeId: \'roadmap\'}; 
				this.map = new google.maps.Map(document.getElementById(this.mapName), this.mapOptions);
				this.marker = new google.maps.Marker({position:  this.center , map: this.map});
				if (typeof window[this.markersName] != "undefined") {window[this.markersName].push(this.marker);}
				window[this.mapName] = this.map;
			};
		';

		$doc->addScriptDeclaration($script);
		self::loadGoogleMapsApi();
		static::$loaded[__METHOD__] = true;
		return false;
	}

	protected static function loadGoogleMapsApi() {
		if (!empty(static::$loaded[__METHOD__])) {
			return true;
		}
		$url = self::getApiUrl();
		if (empty($url)) {
			return true;
		}
		$doc = JFactory::getDocument();
		$doc->addScript($url, array('relative' => false, 'detectBrowser' => false, 'detectDebug' => false), array('defer' =>true, 'async' => false));
		static::$loaded[__METHOD__] = true;
		return true;
	}

	public static function getMapOption($displayData) {
		$view  = $displayData['view'];
		$field = $displayData['field'];
		$data  = $displayData['data'];
		$zoom  = 8;
		$lat   = '';
		$lng   = '';
		if (isset($data['lat'])) {
			$lat = $data['lat'];
		}
		if (isset($data['lng'])) {
			$lng = $data['lng'];
		}
		if (($lat !== '' && $lng !== '')) {
			if ($view == 'list') {
				if (!empty($field->listMapZoom) && is_numeric($field->listMapZoom)) {
					$zoom = (int) $field->listMapZoom;
				}
			}
			if ($view == 'detail') {
				if (!empty($field->detailMapZoom) && is_numeric($field->detailMapZoom)) {
					$zoom = (int) $field->detailMapZoom;
				} else {
					$zoom = 13;
				}
			}
			// Map default values for script
			$mapId = '';
			if (!empty($displayData['makeUnique'])) {
				$mapId .= 'plugin';
			}
			$mapId        .= 'f' . $field->id . 'd' . $displayData['rowId'];
			$attPos       = '{lat: ' . $lat . ', lng: ' . $lng . '}';
			$fieldOptions = '{fieldId: "' . $mapId . '", attPos : ' . $attPos . ', zoom : ' . $zoom . '}';
			$mapOptions = new stdClass();
			$mapOptions->mapId = $mapId;
			$mapOptions->mapOptions = $fieldOptions;
			return $mapOptions;
		}
	}

	protected static function getApiKey() {
		return JComponentHelper::getParams('com_visforms')->get('googleMapApiKey');
	}

	protected static function getApiUrl () {
		$apiKey = self::getApiKey();
		if (empty($apiKey)) {
			return '';
		}
		return  'https://maps.googleapis.com/maps/api/js?key='.$apiKey;
	}
}