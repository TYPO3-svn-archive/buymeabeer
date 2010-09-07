<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2010 Juergen Furrer <juergen.furrer@gmail.com>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 * Hint: use extdeveval to insert/update function index above.
 */

require_once(PATH_tslib.'class.tslib_pibase.php');

if (t3lib_extMgm::isLoaded('t3jquery')) {
	require_once(t3lib_extMgm::extPath('t3jquery').'class.tx_t3jquery.php');
}

/**
 * Plugin 'Donate' for the 'buymeabeer' extension.
 *
 * @author	Juergen Furrer <juergen.furrer@gmail.com>
 * @package	TYPO3
 * @subpackage	tx_buymeabeer
 */
class tx_buymeabeer_pi1 extends tslib_pibase
{
	var $prefixId      = 'tx_buymeabeer_pi1';		// Same as class name
	var $scriptRelPath = 'pi1/class.tx_buymeabeer_pi1.php';	// Path to this script relative to the extension dir.
	var $extKey        = 'buymeabeer';	// The extension key.
	var $pi_checkCHash = true;
	var $lConf = array();
	var $contentKey = null;
	var $jsFiles = array();
	var $js = array();
	var $cssFiles = array();
	var $css = array();
	var $images = array();
	var $hrefs = array();
	var $captions = array();
	var $imageDir = 'uploads/tx_buymeabeer/';
	var $type = 'normal';

	/**
	 * The main method of the PlugIn
	 *
	 * @param	string		$content: The PlugIn content
	 * @param	array		$conf: The PlugIn configuration
	 * @return	The content that is displayed on the website
	 */
	function main($content, $conf)
	{
		$this->conf = $conf;
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();

		// define the key of the element
		if ($this->getContentKey() == null) {
			$this->setContentKey();
		}

		$pageID = false;
		if ($this->cObj->data['list_type'] == $this->extKey.'_pi1') {
			$this->type = 'normal';
			// It's a content, all data from flexform
			// Set the Flexform information
			$this->pi_initPIflexForm();
			$piFlexForm = $this->cObj->data['pi_flexform'];
			foreach ($piFlexForm['data'] as $sheet => $data) {
				foreach ($data as $lang => $value) {
					foreach ($value as $key => $val) {
						if ($key == 'amounts') {
							// special for the amounts
							$this->lConf['amounts'] = array();
							if (is_array($val['el'])) {
								foreach ($val['el'] as $amount) {
									$this->lConf['amounts'][] = array(
										'value'   => $amount['data']['el']['value']['vDEF'],
										'label' => $amount['data']['el']['label']['vDEF'],
									);
								}
							}
						} else {
							$this->lConf[$key] = $this->pi_getFFvalue($piFlexForm, $key, $sheet);
						}
					}
				}
			}

			// define the key of the element
			$this->setContentKey('buymeabeer_c' . $this->cObj->data['uid']);

			// override the config if set in Plugin
			if ($this->lConf['business'] || $this->isOverride() === true) {
				$this->conf['business'] = $this->lConf['business'];
			}
			if ($this->lConf['currencyCode'] || $this->isOverride() === true) {
				$this->conf['currencyCode'] = $this->lConf['currencyCode'];
			}
			if ($this->lConf['amount'] || $this->isOverride() === true) {
				$this->conf['amount'] = $this->lConf['amount'];
			}
			if ($this->lConf['amounts'] || $this->isOverride() === true) {
				$this->conf['amounts.'] = $this->lConf['amounts'];
			}
			if ($this->lConf['returnUrl'] || $this->isOverride() === true) {
				$this->conf['returnUrl'] = $this->lConf['returnUrl'];
			}
			if ($this->lConf['itemName'] || $this->isOverride() === true) {
				$this->conf['itemName'] = $this->lConf['itemName'];
			}
			if ($this->lConf['donateText'] || $this->isOverride() === true) {
				$this->conf['donateText'] = nl2br($this->lConf['donateText']);
			}
		}

		return $this->pi_wrapInBaseClass($this->parseTemplate());
	}

	/**
	 * Return if the overrideSetup isset
	 * 
	 */
	function isOverride()
	{
		return ($this->lConf['overrideSetup'] ? true : false);
	}

	/**
	 * Set the contentKey
	 * @param string $contentKey
	 */
	public function setContentKey($contentKey=null)
	{
		$this->contentKey = ($contentKey == null ? $this->extKey : $contentKey);
	}

	/**
	 * Get the contentKey
	 * @return string
	 */
	public function getContentKey()
	{
		return $this->contentKey;
	}


	/**
	 * Parse all images into the template
	 * @param $data
	 * @return string
	 */
	function parseTemplate()
	{
		// define the jQuery mode and function
		if ($this->conf['jQueryNoConflict']) {
			$jQueryNoConflict = "jQuery.noConflict();";
		} else {
			$jQueryNoConflict = "";
		}

		// The template for JS
		if (! $this->templateFileJS = $this->cObj->fileResource($this->conf['templateFileJS'])) {
			$this->templateFileJS = $this->cObj->fileResource("EXT:buymeabeer/pi1/tx_buymeabeer_pi1.js");
		}
		// get the Template of the Javascript
		if (! $templateCode = trim($this->cObj->getSubpart($this->templateFileJS, "###TEMPLATE_JS###"))) {
			$templateCode = "alert('Template TEMPLATE_JS is missing')";
		}
		// set the key
		$markerArray = array();
		$markerArray["KEY"] = $this->getContentKey();
		$templateCode = $this->cObj->substituteMarkerArray($templateCode, $markerArray, '###|###', 0);

		$this->addJS($jQueryNoConflict . $templateCode);

		// Add the ressources
		$this->addResources();

		$GLOBALS['TSFE']->register['key']          = $this->getContentKey();
		$GLOBALS['TSFE']->register['donateUrl']    = $this->conf['donateUrl'];
		$GLOBALS['TSFE']->register['business']     = $this->conf['business'];
		$GLOBALS['TSFE']->register['currencyCode'] = $this->conf['currencyCode'];
		$GLOBALS['TSFE']->register['returnUrl']    = $this->conf['returnUrl'];
		$GLOBALS['TSFE']->register['itemName']     = $this->conf['itemName'];
		$GLOBALS['TSFE']->register['donateText']   = $this->conf['donateText'];

		$markerArray = array(
			'AMOUNTS' => null,
		);
		if (is_array($this->conf['amounts.'])) {
			foreach ($this->conf['amounts.'] as $amount) {
				$GLOBALS['TSFE']->register['amount'] = $amount['value'];
				$link     = $this->cObj->typolink($amount['label'], $this->conf['template.']['typolink.']);
				$amounts .= $this->cObj->stdWrap($link, $this->conf['template.']['amounts.']['itemWrap.']);
			}
			$markerArray['AMOUNTS'] = $this->cObj->stdWrap($amounts, $this->conf['template.']['amounts.']['stdWrap.']);
		}

		$GLOBALS['TSFE']->register['amount'] = $this->conf['amount'];

		$inner = $this->cObj->stdWrap("", $this->conf['template.']['innerStdWrap.']);
		$link = $this->cObj->typolink($inner, $this->conf['template.']['typolink.']);
		$content = $this->cObj->stdWrap($link, $this->conf['template.']['stdWrap.']);
		$return_string = $this->cObj->substituteMarkerArray($content, $markerArray, '###|###', 0);

		return $return_string;
	}

	/**
	 * Include all defined resources (JS / CSS)
	 *
	 * @return void
	 */
	function addResources()
	{
		// checks if t3jquery is loaded
		if (T3JQUERY === true) {
			tx_t3jquery::addJqJS();
		} else {
			$this->addJsFile($this->conf['jQueryLibrary'], true);
			$this->addJsFile($this->conf['jQueryEasing']);
		}
		// Fix moveJsFromHeaderToFooter (add all scripts to the footer)
		if ($GLOBALS['TSFE']->config['config']['moveJsFromHeaderToFooter']) {
			$allJsInFooter = true;
		} else {
			$allJsInFooter = false;
		}
		// add all defined JS files
		if (count($this->jsFiles) > 0) {
			foreach ($this->jsFiles as $jsToLoad) {
				if (T3JQUERY === true) {
					tx_t3jquery::addJS('', array('jsfile' => $jsToLoad));
				} else {
					// Add script only once
					$hash = md5($this->getPath($jsToLoad));
					if ($allJsInFooter) {
						$GLOBALS['TSFE']->additionalFooterData['jsFile_'.$this->extKey.'_'.$hash] = ($this->getPath($jsToLoad) ? '<script src="'.$this->getPath($jsToLoad).'" type="text/javascript"></script>'.chr(10) : '');
					} else {
						$GLOBALS['TSFE']->additionalHeaderData['jsFile_'.$this->extKey.'_'.$hash] = ($this->getPath($jsToLoad) ? '<script src="'.$this->getPath($jsToLoad).'" type="text/javascript"></script>'.chr(10) : '');
					}
				}
			}
		}
		// add all defined JS script
		if (count($this->js) > 0) {
			foreach ($this->js as $jsToPut) {
				$temp_js .= $jsToPut;
			}
			if ($this->conf['jsMinify']) {
				$temp_js = t3lib_div::minifyJavaScript($temp_js);
			}
			$conf = array();
			$conf['jsdata'] = $temp_js;
			if (T3JQUERY === true && t3lib_div::int_from_ver($this->getExtensionVersion('t3jquery')) >= 1002000) {
				$conf['tofooter'] = ($this->conf['jsInFooter']);
				tx_t3jquery::addJS('', $conf);
			} else {
				// Add script only once
				$hash = md5($temp_js);
				if ($this->conf['jsInFooter'] || $allJsInFooter) {
					$GLOBALS['TSFE']->additionalFooterData['js_'.$this->extKey.'_'.$hash] = t3lib_div::wrapJS($temp_js, true);
				} else {
					$GLOBALS['TSFE']->additionalHeaderData['js_'.$this->extKey.'_'.$hash] = t3lib_div::wrapJS($temp_js, true);
				}
			}
		}
		// add all defined CSS files
		if (count($this->cssFiles) > 0) {
			foreach ($this->cssFiles as $cssToLoad) {
				// Add script only once
				$hash = md5($this->getPath($cssToLoad));
				$GLOBALS['TSFE']->additionalHeaderData['cssFile_'.$this->extKey.'_'.$hash] = ($this->getPath($cssToLoad) ? '<link rel="stylesheet" href="'.$this->getPath($cssToLoad).'" type="text/css" />'.chr(10) :'');
			}
		}
		// add all defined CSS Script
		if (count($this->css) > 0) {
			foreach ($this->css as $cssToPut) {
				$temp_css .= $cssToPut;
			}
			$GLOBALS['TSFE']->additionalHeaderData['css_'.$this->extKey] .= '
<style type="text/css">
' . $temp_css . '
</style>';
		}
	}

	/**
	 * Return the webbased path
	 * 
	 * @param string $path
	 * return string
	 */
	function getPath($path="")
	{
		return $GLOBALS['TSFE']->tmpl->getFileName($path);
	}

	/**
	 * Add additional JS file
	 * 
	 * @param string $script
	 * @param boolean $first
	 * @return void
	 */
	function addJsFile($script="", $first=false)
	{
		$script = t3lib_div::fixWindowsFilePath($script);
		if ($this->getPath($script) && ! in_array($script, $this->jsFiles)) {
			if ($first === true) {
				$this->jsFiles = array_merge(array($script), $this->jsFiles);
			} else {
				$this->jsFiles[] = $script;
			}
		}
	}

	/**
	 * Add JS to header
	 * 
	 * @param string $script
	 * @return void
	 */
	function addJS($script="")
	{
		if (! in_array($script, $this->js)) {
			$this->js[] = $script;
		}
	}

	/**
	 * Add additional CSS file
	 * 
	 * @param string $script
	 * @return void
	 */
	function addCssFile($script="")
	{
		$script = t3lib_div::fixWindowsFilePath($script);
		if ($this->getPath($script) && ! in_array($script, $this->cssFiles)) {
			$this->cssFiles[] = $script;
		}
	}

	/**
	 * Add CSS to header
	 * 
	 * @param string $script
	 * @return void
	 */
	function addCSS($script="")
	{
		if (! in_array($script, $this->css)) {
			$this->css[] = $script;
		}
	}

	/**
	 * Returns the version of an extension (in 4.4 its possible to this with t3lib_extMgm::getExtensionVersion)
	 * @param string $key
	 * @return string
	 */
	function getExtensionVersion($key)
	{
		if (! t3lib_extMgm::isLoaded($key)) {
			return '';
		}
		$_EXTKEY = $key;
		include(t3lib_extMgm::extPath($key) . 'ext_emconf.php');
		return $EM_CONF[$key]['version'];
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/buymeabeer/pi1/class.tx_buymeabeer_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/buymeabeer/pi1/class.tx_buymeabeer_pi1.php']);
}

?>