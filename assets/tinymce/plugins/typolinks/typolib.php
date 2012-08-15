<?php

/**
 * Contao Open Source CMS
 * 
 * Copyright (C) 2005-2012 Leo Feyer
 * 
 * @package Typolinks
 * @link    http://contao.org
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */


/**
 * Class typolib
 * 
 * Provide methods to render TinyMCE page and file drop down menus.
 * @copyright  Leo Feyer 2005-2012
 * @author     Leo Feyer <http://contao.org>
 */
class typolib extends Backend
{

	/**
	 * Initialize the controller
	 * 
	 * 1. Import user
	 * 2. Call parent constructor
	 * 3. Authenticate user
	 * DO NOT CHANGE THIS ORDER!
	 */
	public function __construct()
	{
		$this->import('BackendUser', 'User');
		parent::__construct();

		$this->User->authenticate();
	}


	/**
	 * Get all allowed images and return them as string
	 * @return string
	 */
	public function createImageList()
	{
		if ($this->User->isAdmin)
		{
			return $this->doCreateImageList($GLOBALS['TL_CONFIG']['uploadPath']);
		}

		$return = '';
		$processed = array();

		// Limit nodes to the filemounts of the user
		foreach ($this->eliminateNestedPaths($this->User->filemounts) as $path)
		{
			if (in_array($path, $processed))
			{
				continue;
			}

			$processed[] = $path;
			$return .= $this->doCreateImageList($path);
		}

		return $return;
	}


	/**
	 * Recursively get all allowed images and return them as string
	 * @param integer
	 * @param integer
	 * @return string
	 */
	public function doCreateImageList($strFolder=null, $level=-1)
	{
		$arrPages = scan(TL_ROOT . '/' . $strFolder);

		// Empty folder
		if (empty($arrPages))
		{
			return '';
		}

		// Protected folder
		if (array_search('.htaccess', $arrPages) !== false)
		{
			return '';
		}

		++$level;
		$strFolders = '';
		$strFiles = '';

		// Recursively list all images
		foreach ($arrPages as $strFile)
		{
			if (substr($strFile, 0, 1) == '.')
			{
				continue;
			}

			// Folders
			if (is_dir(TL_ROOT . '/' . $strFolder . '/' . $strFile))
			{
				$strFolders .= $this->doCreateImageList($strFolder . '/' . $strFile, $level);
			}

			// Files
			elseif (preg_match('/\.gif$|\.jpg$|\.jpeg$|\.png$/i', $strFile))
			{
				$strFiles .= sprintf('["%s", "%s"]', specialchars($strFolder . '/' . $strFile), $strFolder . '/' . $strFile) . ",\n";
			}
		}

		return $strFiles . $strFolders;
	}


	/**
	 * Get all tiny_ templates and return them as string
	 * @return string
	 */
	public function createTemplateList()
	{
		$dir = TL_ROOT . '/' . $GLOBALS['TL_CONFIG']['uploadPath'] . '/tiny_templates';

		if (!is_dir($dir))
		{
			return '';
		}

		$strFiles = '';
		$arrTemplates = scan($dir);

		foreach ($arrTemplates as $strFile)
		{
			if (strncmp('.', $strFile, 1) !== 0 && is_file($dir . '/' . $strFile))
			{
				$strFiles .= sprintf('["%s", "' . TL_PATH . '/' . $GLOBALS['TL_CONFIG']['uploadPath'] . '/tiny_templates/%s", "' . $GLOBALS['TL_CONFIG']['uploadPath'] . '/tiny_templates/%s"]', $strFile, $strFile, $strFile) . ",\n";
			}
		}

		return $strFiles;
	}
}