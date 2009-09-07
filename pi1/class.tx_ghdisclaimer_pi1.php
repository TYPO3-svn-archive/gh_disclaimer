<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2007 Gregor Hermens <gregor.hermens@a-mazing.de>
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
 *
 *
 *   49: class tx_ghdisclaimer_pi1 extends tslib_pibase
 *   66:     function main($content,$conf)
 *  143:     function displayForm()
 *  173:     function renderRteContent($content='')
 *
 * TOTAL FUNCTIONS: 3
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */

require_once(PATH_tslib.'class.tslib_pibase.php');


/**
 * Plugin 'Disclaimer' for the 'gh_disclaimer' extension.
 *
 * @author	Gregor Hermens <gregor.hermens@a-mazing.de>
 * @package	TYPO3
 * @subpackage	tx_ghdisclaimer
 */
class tx_ghdisclaimer_pi1 extends tslib_pibase {
	var $prefixId      = 'tx_ghdisclaimer_pi1';		// Same as class name
	var $scriptRelPath = 'pi1/class.tx_ghdisclaimer_pi1.php';	// Path to this script relative to the extension dir.
	var $extKey        = 'gh_disclaimer';	// The extension key.
	var $pi_checkCHash = true;

	var $acceptTarget = 0;
	var $denyTarget = 0;
	var $disclaimerPid = 0;

	/**
	 * The main method of the PlugIn
	 *
	 * @param	string		$content: The PlugIn content
	 * @param	array		$conf: The PlugIn configuration
	 * @return	The		content that is displayed on the website
	 */
	function main($content,$conf)	{
		$this->conf=$conf;
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();
		$this->pi_initPIflexForm();
		$this->pi_USER_INT_obj=1;	// Configuring so caching is not expected. This value means that no cHash params are ever set. We do this, because it's a USER_INT object!

		$this->acceptTarget = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'inputDefaultAcceptTarget');
		if(empty($this->acceptTarget)) {
			$this->acceptTarget = $this->conf['defaultAcceptTarget'];
		}
		if(!empty($this->piVars['redirect'])) {
			$this->acceptTarget = (int) $this->piVars['redirect'];
		}

		$this->denyTarget = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'inputDenyTarget');
		if(empty($this->denyTarget)) {
			$this->denyTarget = $this->conf['denyTarget'];
		}

		$this->disclaimerPid = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'inputDisclaimerPid');
		if(empty($this->disclaimerPid)) {
			$this->disclaimerPid = $this->conf['disclaimerPid'];
		}

		// check for protected page:
		if(!empty($this->disclaimerPid) and $GLOBALS['TSFE']->id != $this->disclaimerPid ) {
			$disclaimerPid = (int) $this->conf['disclaimerPid'];
		// check if disclaimer has been accepted:
			if(!$GLOBALS['TSFE']->fe_user->getKey('ses', 'tx_ghdisclaimer_'.$disclaimerPid)) {
		// redirect to disclaimer page:

				$target = $this->pi_getPageLink($disclaimerPid,'',array($this->prefixId.'[redirect]' => $GLOBALS['TSFE']->id));
				if(substr($target,0,1) == '/') {
					$target = substr($target,1);
				}
				header('Location: '.t3lib_div::getIndpEnv('TYPO3_SITE_URL').$target);
				die;
			}
			return '';
		}

		if(!empty($this->piVars['reject'])) {
		// Disclaimer has been rejected. Store status in session and redirect to denyTarget:
			$GLOBALS['TSFE']->fe_user->setKey('ses','tx_ghdisclaimer_'.$GLOBALS['TSFE']->id, false);
			$GLOBALS['TSFE']->fe_user->storeSessionData();
			$target = $this->pi_getPageLink($this->denyTarget);
				if('/' == $target) {
					$target = '';
				}
			header('Location: '.t3lib_div::getIndpEnv('TYPO3_SITE_URL').$target);
			die;
		}

		if(!empty($this->piVars['accept'])) {
		// Disclaimer has been accepted. Store status in session and redirect to acceptTarget:
			$GLOBALS['TSFE']->fe_user->setKey('ses','tx_ghdisclaimer_'.$GLOBALS['TSFE']->id, true);
			$GLOBALS['TSFE']->fe_user->storeSessionData();
			$target = $this->pi_getPageLink($this->acceptTarget);
				if('/' == $target) {
					$target = '';
				}
			header('Location: '.t3lib_div::getIndpEnv('TYPO3_SITE_URL').$target);
			die;
		}

		// This is the disclaimer page. Show form:
		$content = $this->displayForm();

		return $this->pi_wrapInBaseClass($content);
	}

	/**
	 * Display the form
	 *
	 * @return	string		the rendered form
	 */
	function displayForm() {
		$content = '
			<form action="'.$this->pi_getPageLink($GLOBALS['TSFE']->id).'" method="post">
				<div style="display: none;">
					<input type="hidden" name="no_cache" value="1" />
					<input type="hidden" name="'.$this->prefixId.'[redirect]" value="'.$this->acceptTarget.'" />
				</div>
				<fieldset>
					';
		if(!empty($this->cObj->data['bodytext'])) {
			$content .= $this->renderRteContent($this->cObj->data['bodytext']);
		}
		$content .= '
					<div class="tx-ghdisclaimer-pi1-buttons">
						<input type="submit" name="'.$this->prefixId.'[accept]" value="'.htmlspecialchars($this->pi_getLL('accept_button_label')).'" />
						<input type="submit" name="'.$this->prefixId.'[reject]" value="'.htmlspecialchars($this->pi_getLL('reject_button_label')).'" />
					</div>
				</fieldset>
			</form>
		';

		return $content;
	}

	/**
	 * Wrap all non-block elements in p tags
	 *
	 * @param	string		the content
	 * @return	string		the rendered content
	 */
	function renderRteContent($content='') {

		require_once(PATH_t3lib.'class.t3lib_parsehtml.php');
		$parseObj = t3lib_div::makeInstance('t3lib_parsehtml');

		$content_array = $parseObj->splitIntoBlock('p,ul,ol,div,table,h1,h2,h3,h4,h5,address', $content);

		foreach($content_array as $k=>$v) {
			if(floor($k/2) == $k/2 and !empty($content_array[$k])) {
				$content_array[$k] = preg_replace('|<p>\s*</p>|', '', '<p>'.str_replace("\n", '</p><p>',$content_array[$k]).'</p>');
			}
		}

		return implode("\n", $content_array);
	}

}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/gh_disclaimer/pi1/class.tx_ghdisclaimer_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/gh_disclaimer/pi1/class.tx_ghdisclaimer_pi1.php']);
}
?>