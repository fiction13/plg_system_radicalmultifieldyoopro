<?php
/*
 * @package   plg_system_radicalmultifieldyoopro
 * @version   1.0
 * @author    Dmitriy Vasyukov - https://fictionlabs.ru
 * @copyright Copyright (c) 2024 Fictionlabs. All rights reserved.
 * @license   GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link      https://fictionlabs.ru/
 */

namespace Joomla\Plugin\System\RadicalMultifieldYooPro\Provider;

use YOOtheme\Builder\Source;

class SourceListener
{
	/**
	 * @param   array|null  $config
	 * @param   object      $field
	 * @param   Source      $source
	 * @param   string      $context
	 *
	 * @return array|null
	 *
	 * @since 1.0.0
	 */
	public static function config($config, $field, Source $source, string $context)
	{
		if (!$config || $field->type !== 'radicalmultifield')
		{
			return $config;
		}

		return Type\FieldsType::config($config, $field, $source, $context);
	}
}
