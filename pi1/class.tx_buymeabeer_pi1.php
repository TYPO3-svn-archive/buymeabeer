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
require_once(t3lib_extMgm::extPath('buymeabeer').'lib/class.tx_buymeabeer_pagerenderer.php');

/**
 * Plugin 'Donate' for the 'buymeabeer' extension.
 *
 * @author	Juergen Furrer <juergen.furrer@gmail.com>
 * @package	TYPO3
 * @subpackage	tx_buymeabeer
 */
class tx_buymeabeer_pi1 extends tslib_pibase
{
	public $prefixId      = 'tx_buymeabeer_pi1';
	public $scriptRelPath = 'pi1/class.tx_buymeabeer_pi1.php';
	public $extKey        = 'buymeabeer';
	public $pi_checkCHash = true;
	protected $lConf = array();
	protected $contentKey = null;
	protected $jsFiles = array();
	protected $js = array();
	protected $cssFiles = array();
	protected $css = array();
	protected $images = array();
	protected $hrefs = array();
	protected $captions = array();
	protected $piFlexForm = array();
	protected $imageDir = 'uploads/tx_buymeabeer/';
	protected $type = 'normal';

	/**
	 * The main method of the PlugIn
	 *
	 * @param	string		$content: The PlugIn content
	 * @param	array		$conf: The PlugIn configuration
	 * @return	The content that is displayed on the website
	 */
	public function main($content, $conf)
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

			$this->lConf['overrideSetup'] = $this->getFlexformData('general', 'overrideSetup');
			$this->lConf['business']      = $this->getFlexformData('general', 'business');
			$this->lConf['currencyCode']  = $this->getFlexformData('general', 'currencyCode');
			$this->lConf['amount']        = $this->getFlexformData('general', 'amount');
			$this->lConf['returnUrl']     = $this->getFlexformData('general', 'returnUrl');
			$this->lConf['itemName']      = $this->getFlexformData('general', 'itemName');
			$this->lConf['donateText']    = $this->getFlexformData('general', 'donateText');

			$amounts = $this->getFlexformData('general', 'amounts');
			$this->lConf['amounts'] = array();
			if (isset($amounts['el']) && count($amounts['el']) > 0) {
				foreach ($amounts['el'] as $elKey => $el) {
					if (is_numeric($elKey)) {
						$this->lConf['amounts'][] = array(
							'value' => $el['data']['el']['value']['vDEF'],
							'label' => $el['data']['el']['label']['vDEF'],
						);
					}
				}
			}

			$this->lConf['transition']         = $this->getFlexformData('animation', 'transition');
			$this->lConf['transitionDir']      = $this->getFlexformData('animation', 'transitionDir');
			$this->lConf['transitionDuration'] = $this->getFlexformData('animation', 'transitionDuration');

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
			// Animation
			if ($this->lConf['transition']) {
				$this->conf['transition'] = $this->lConf['transition'];
			}
			if ($this->lConf['transitionDir']) {
				$this->conf['transitionDir'] = $this->lConf['transitionDir'];
			}
			if ($this->lConf['transitionDuration']) {
				$this->conf['transitionDuration'] = $this->lConf['transitionDuration'];
			}
		}

		return $this->pi_wrapInBaseClass($this->parseTemplate());
	}

	/**
	 * Return true if the overrideSetup isset
	 * 
	 * @return boolean
	 */
	public function isOverride()
	{
		return ($this->lConf['overrideSetup'] ? true : false);
	}

	/**
	 * Set the contentKey
	 * 
	 * @param string $contentKey
	 */
	public function setContentKey($contentKey=null)
	{
		$this->contentKey = ($contentKey == null ? $this->extKey : $contentKey);
	}

	/**
	 * Get the contentKey
	 * 
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
	public function parseTemplate()
	{
		$this->pagerenderer = t3lib_div::makeInstance('tx_buymeabeer_pagerenderer');
		$this->pagerenderer->setConf($this->conf);

		// define the jQuery mode and function
		if ($this->conf['jQueryNoConflict']) {
			$jQueryNoConflict = "jQuery.noConflict();";
		} else {
			$jQueryNoConflict = "";
		}

		// The template for JS
		if (! $this->templateFileJS = $this->cObj->fileResource($this->conf['templateFileJS'])) {
			$this->templateFileJS = $this->cObj->fileResource("EXT:buymeabeer/res/tx_buymeabeer_pi1.js");
		}
		// get the Template of the Javascript
		if (! $templateCode = trim($this->cObj->getSubpart($this->templateFileJS, "###TEMPLATE_JS###"))) {
			$templateCode = "alert('Template TEMPLATE_JS is missing')";
		}

		// set the key
		$markerArray = array();
		$markerArray["KEY"] = $this->getContentKey();
		// Define the transition
		if (in_array($this->conf['transition'], array('linear', 'swing'))) {
			$markerArray['EASING'] = $this->conf['transition'];
		} elseif ($this->conf['transitionDir'] && $this->conf['transition']) {
			$markerArray['EASING'] = "ease{$this->conf['transitionDir']}{$this->conf['transition']}";
		}
		// Set the duration
		$markerArray['SPEED'] = $this->conf['transitionDuration']>0 ? $this->conf['transitionDuration'] : '0';

		$templateCode = $this->cObj->substituteMarkerArray($templateCode, $markerArray, '###|###', 0);

		$this->pagerenderer->addJS($jQueryNoConflict . $templateCode);

		// checks if t3jquery is loaded
		if (T3JQUERY === true) {
			tx_t3jquery::addJqJS();
		} else {
			$this->pagerenderer->addJsFile($this->conf['jQueryLibrary'], true);
			$this->pagerenderer->addJsFile($this->conf['jQueryEasing']);
		}

		// Add the ressources
		$this->pagerenderer->addResources();

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
				$GLOBALS['TSFE']->register['amount'] = $this->cObj->stdWrap($amount['value'], $amount['value.']);
				$label    = $this->cObj->stdWrap($amount['label'], $amount['label.']);
				$link     = $this->cObj->typolink($label, $this->conf['template.']['typolink.']);
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
	* Set the piFlexform data
	*
	* @return void
	*/
	protected function setFlexFormData()
	{
		if (! count($this->piFlexForm)) {
			$this->pi_initPIflexForm();
			$this->piFlexForm = $this->cObj->data['pi_flexform'];
		}
	}

	/**
	 * Extract the requested information from flexform
	 * @param string $sheet
	 * @param string $name
	 * @param boolean $devlog
	 * @return string
	 */
	protected function getFlexformData($sheet='', $name='', $devlog=true)
	{
		$this->setFlexFormData();
		if (! isset($this->piFlexForm['data'])) {
			if ($devlog === true) {
				t3lib_div::devLog("Flexform Data not set", $this->extKey, 1);
			}
			return null;
		}
		if (! isset($this->piFlexForm['data'][$sheet])) {
			if ($devlog === true) {
				t3lib_div::devLog("Flexform sheet '{$sheet}' not defined", $this->extKey, 1);
			}
			return null;
		}
		if (! isset($this->piFlexForm['data'][$sheet]['lDEF'][$name])) {
			if ($devlog === true) {
				t3lib_div::devLog("Flexform Data [{$sheet}][{$name}] does not exist", $this->extKey, 1);
			}
			return null;
		}
		if (isset($this->piFlexForm['data'][$sheet]['lDEF'][$name]['vDEF'])) {
			return $this->pi_getFFvalue($this->piFlexForm, $name, $sheet);
		} else {
			return $this->piFlexForm['data'][$sheet]['lDEF'][$name];
		}
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/buymeabeer/pi1/class.tx_buymeabeer_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/buymeabeer/pi1/class.tx_buymeabeer_pi1.php']);
}

?>