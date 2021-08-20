<?php
/**
 * @author       Aicha Vack
 * @package      Joomla.Site
 * @subpackage   com_visforms
 * @link         http://www.vi-solutions.de
 * @license      GNU General Public License version 2 or later; see license.txt
 * @copyright    2018 vi-solutions
 */

// no direct access
defined('_JEXEC') or die('Restricted access');
require_once(JPATH_ADMINISTRATOR . '/components/com_visforms/lib/jSignature/jSignature_Tools_Base30.php');

class JHtmlVisformssignature
{
	protected static $loaded = array();
	public static $sigImgPath = '/images/visforms/sigtmp';
	public static $sigImgBaseUrl = 'images/visforms/sigtmp/';

	//get img src as file name
	public static function createPngFile($base30Data, $canvasWidth = 280, $canvasHeight = 120) {
		if (empty($base30Data)) {
			return '';
		}
		$xyArray = self::convertBase30ToxyArray($base30Data);
		if (empty($xyArray)) {
			return '';
		}
		$im = self::getBinaryImg($xyArray, $canvasWidth, $canvasHeight);
		$path = JPATH_ROOT . self::$sigImgPath;
		JLoader::import('joomla.filesystem.file');
		JFolder::create(self::$path);
		$hash = self::createHash();
		$fileName = $hash . '.png';
		$success = imagepng($im, self::$path . '/' . $fileName);
		imagedestroy($im);
		if ($success) {
			return $fileName;
		}
		return $success;
	}

	//get img src as base46 encoded binary
	public static function createBinaryPng($base30Data, $canvasWidth = 280, $canvasHeight = 120) {
		if (empty($base30Data)) {
			return '';
		}
		$xyArray = self::convertBase30ToxyArray($base30Data);
		if (empty($xyArray)) {
			return '';
		}
		$im = self::getBinaryImg($xyArray, $canvasWidth, $canvasHeight);
		ob_start();
		$success = imagepng($im);
		$image_data = ob_get_contents();
		ob_end_clean();
		imagedestroy($im);
		if ($success) {
			$base64 = base64_encode($image_data);
			return ('data:image/png;base64,' . $base64);
		}
		return '';
	}

	//get image as svg xml
	protected static function getSvgXml($base30Data) {
		//works but does not scale properly
		if (empty($base30Data)) {
			return '';
		}
		$xyArray = self::convertBase30ToxyArray($base30Data);
		if (empty($xyArray)) {
			return '';
		}
		require_once(JPATH_ADMINISTRATOR . '/components/com_visforms/lib/jSignature/jSignature_Tools_SVG.php');
		$svgConverter = new jSignature_Tools_SVG();
		$img = $svgConverter->NativeToSVG($xyArray);
		return $img;
	}

	protected static function convertBase30ToxyArray($base30Data) {
		$data = str_replace('image/jsignature;base30,', '', $base30Data);
		if (empty($data)) {
			return array();
		}
		$signature = new jSignature_Tools_Base30();
		return $signature->Base64ToNative($data);
	}

	protected static function getBinaryImg($xyArray, $width, $height) {
		$im = imagecreatetruecolor($width, $height);
		imagesavealpha($im, true);
		$trans_colour = imagecolorallocatealpha($im, 0, 0, 0, 127);
		imagefill($im, 0, 0, $trans_colour);
		imagesetthickness($im, 5);
		$color = imagecolorallocate($im, 0, 0, 0);
		for ($i = 0; $i < count($xyArray); $i++)
		{
			for ($j = 0; $j < count($xyArray[$i]['x']); $j++)
			{
				if ( ! isset($xyArray[$i]['x'][$j]) or ! isset($xyArray[$i]['x'][$j+1])) break;
				imageline($im, $xyArray[$i]['x'][$j], $xyArray[$i]['y'][$j], $xyArray[$i]['x'][$j+1], $xyArray[$i]['y'][$j+1], $color);
			}
		}
		return $im;
	}

	protected static function createHash() {
		if (function_exists('openssl_random_pseudo_bytes')) {
			$rand = openssl_random_pseudo_bytes(16);
			if ($rand === false) {
				// Broken or old system
				$rand = mt_rand();
			}
		} else {
			$rand = mt_rand();
		}
		$hashThis = microtime() . $rand;
		if (function_exists('hash')) {
			$hash = hash('sha256', $hashThis);
		} else if (function_exists('sha1')) {
			$hash = sha1($hashThis);
		} else {
			$hash = md5($hashThis);
		}
		return $hash;
	}

	public static function loadSignatureJs () {
		if (!empty(static::$loaded[__METHOD__])) {
			return true;
		}
		$doc = JFactory::getDocument();
		//init function as var in order to be able to directly create functions inside
		$script = 'var initVfSignature = function (fieldOptions) {
				fieldOptions = fieldOptions || {} ;
				if (!fieldOptions.fieldId) {return false;}
				//used to prevent unintentional writing on mobiles on scroll
				this.disableSignature = function(data){
					jQuery("#field" + data.field + "_sig").jSignature("disable");
					jQuery("#field" + data.field + "_sigtools .vfSigReset").hide();
					jQuery("#field" + data.field + "_sigtools .vfLockC").hide();
					jQuery("#field" + data.field + "_sigtools .vfUnlockC").show();
				};
				this.enableSignature = function(data){
					jQuery("#field" + data.field + "_sig").jSignature("enable");
					jQuery("#field" + data.field + "_sigtools .vfSigReset").show();
					jQuery("#field" + data.field + "_sigtools .vfLockC").show();
					jQuery("#field" + data.field + "_sigtools .vfUnlockC").hide();
				};
				this.fieldId = fieldOptions.fieldId;
				jQuery("#field" + this.fieldId + "_sig").jSignature({cssclass:"visCanvas",width:fieldOptions.width, height:fieldOptions.height});
				jQuery(\'<input type="button" value="'.addslashes(JText::_('COM_VISFORMS_RESET_CANVAS')).'" class="vfSigReset">\').on("click", {field: this.fieldId}, function(e){
           			jQuery("#field" + e.data.field + "_sig").jSignature("reset");
           			jQuery("#field" + e.data.field).val("");
        		}).appendTo(jQuery("#field" + this.fieldId + "_sigtools"));
        		//prevent unintentional writing on mobiles on scroll
        		if (("ontouchstart" in window) || navigator.maxTouchPoints || (fieldOptions.lockCanvas && !(jQuery("#field" + this.fieldId + "_sig").hasClass("isForbidden")))) {
        		    jQuery(\'<input type="button" value="'.addslashes(JText::_('COM_VISFORMS_UNLOCK_CANVAS')).'" class="vfUnlockC">\').on("click", {field: this.fieldId}, function(e){
	                    enableSignature({field: e.data.field});
	                }).appendTo(jQuery("#field" + this.fieldId + "_sigtools"));
	                jQuery(\'<input type="button" value="'.addslashes(JText::_('COM_VISFORMS_LOCK_CANVAS')).'" class="vfLockC">\').on("click", {field: this.fieldId}, function(e){
	                    disableSignature({field: e.data.field});
	                }).appendTo(jQuery("#field" + this.fieldId + "_sigtools"));
	                this.disableSignature({field: this.fieldId});
        		}
        		jQuery("#field" + this.fieldId + "_sig").on("change", {field: this.fieldId}, function (e) {
        			var data = jQuery("#field" + e.data.field + "_sig").jSignature("getData", "base30");
        			if(jQuery.isArray(data) && (data.length === 2) && (data[1] !== "")){
                        jQuery("#field" + e.data.field).val(data.join(","))
                    } else {
                        jQuery("#field" + e.data.field).val("");
                    }
                    jQuery("#field" + e.data.field).valid();
        		});
        		jQuery("#field" + this.fieldId + "_sig").closest("form").on("reset", {field: this.fieldId}, function(e) {
        		    jQuery("#field" + e.data.field + "_sig").jSignature("reset");
                });
                if (jQuery("#field" + this.fieldId + "_sig").hasClass("isForbidden")) {
                    this.disableSignature({field: this.fieldId});
                }
               
            }
			function getVfSignatureImgFromCanvas(sigFieldOptions) {
				sigFieldOptions = sigFieldOptions || {} ;
				if (!sigFieldOptions.sigFieldId) {return false;}
				var supportsSvg = document.implementation.hasFeature("http://www.w3.org/TR/SVG11/feature#Shape", "1.0");
				var dataFormat = (supportsSvg) ? "image/svg+xml;base64" : "image";
				var imgFormat = (supportsSvg) ? "image/svg+xml;base64" : "image/png;base64";
				try{
					var data = jQuery(sigFieldOptions.sigFieldId).jSignature("getData", dataFormat);
					var src = "data:" + imgFormat + "," + data[1];
					return src;
				} catch (ex) {
					return "'.addslashes(JText::_('COM_VISFORMS_CANNOT_CREATE_IMAGE_FROM_SIGNATURE')).'";
				}
			}
		';
		$doc->addScriptDeclaration($script);
		$css ='.visCanvas {border:1px dotted gray !important;}';
		$doc->addStyleDeclaration($css);
		self::loadSignatureApi();
		static::$loaded[__METHOD__] = true;
		return false;//??
	}

	protected static function loadSignatureApi() {
		if (!empty(static::$loaded[__METHOD__])) {
			return true;
		}
		JHtml::_('jquery.framework');
		$doc = JFactory::getDocument();
		$doc->addScript(JUri::root(true). '/media/com_visforms/js/flashcanvas.js', array('version' => 'auto', 'relative' => false, 'detectBrowser' => false, 'detectDebug' => false, 'conditional' => 'lt IE 9'));
		$doc->addScript(JUri::root(true). '/media/com_visforms/js/jSignature.min.noconflict.js', array('version' => 'auto', 'relative' => false, 'detectBrowser' => false, 'detectDebug' => false));
		static::$loaded[__METHOD__] = true;
		return true;
	}
}