<?php
/*
 * @package   plg_system_radicalmultifieldyoopro
 * @version   1.0.0
 * @author    Dmitriy Vasyukov - https://fictionlabs.ru
 * @copyright Copyright (c) 2021 Fictionlabs. All rights reserved.
 * @license   GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link      https://fictionlabs.ru/
 */

use Joomla\CMS\Plugin\CMSPlugin;
use YOOtheme\Application;

class plgSystemRadicalMultiFieldYooPro extends CMSPlugin
{
	public function onAfterInitialise()
	{
		// check if YOOtheme Pro is loaded
        if (!class_exists(Application::class, false))
		{
            return;
        }

		// register plugin
		JLoader::registerNamespace('YOOtheme\\Builder\\Joomla\\RadicalMultiField\\', __DIR__ . '/src', false, false, 'psr4');

        // load a single module from the same directory
		$app = Application::getInstance();
        $app->load(__DIR__ . '/bootstrap.php');
	}
}
