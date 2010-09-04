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
	var $templateFile = null;
	var $templatePart = null;
	
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

		return $this->pi_wrapInBaseClass($return_string);
	}

	/**
	 * Return if the overrideSetup isset
	 * 
	 */
	function isOverride()
	{
		return ($this->lConf['overrideSetup'] ? true : false);
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/buymeabeer/pi1/class.tx_buymeabeer_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/buymeabeer/pi1/class.tx_buymeabeer_pi1.php']);
}

?>