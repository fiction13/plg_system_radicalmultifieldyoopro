<?php
/*
 * @package   plg_system_radicalmultifieldyoopro
 * @version   1.0
 * @author    Dmitriy Vasyukov - https://fictionlabs.ru
 * @copyright Copyright (c) 2021 Fictionlabs. All rights reserved.
 * @license   GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link      https://fictionlabs.ru/
 */

use Joomla\CMS\Factory;

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

class plgSystemRadicalMultiFieldYooProInstallerScript
{	
	/**
	 * Runs right after any installation action.
	 *
	 * @param   string            $type    Type of PostFlight action. Possible values are:
	 * @param   InstallerAdapter  $parent  Parent object calling object.
	 *
	 * @throws  Exception
	 *
	 * @return  boolean True on success, False on failure.
	 *
	 * @since   1.0.0
	 */

	function postflight( $type, $parent )
	{
		echo $type;

		// Enable plugin
		if ($type == 'install') {

			// Prepare plugin object
			$plugin           = new stdClass();
			$plugin->type     = 'plugin';
			$plugin->element  = $parent->getElement();
			$plugin->folder   = (string) $parent->getParent()->manifest->attributes()['group'];
			$plugin->ordering = 1000;
			$plugin->enabled  = 1;

			// Update
			Factory::getDbo()->updateObject('#__extensions', $plugin, array('type', 'element', 'folder'));
		}
	}
}
