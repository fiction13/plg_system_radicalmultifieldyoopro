<?php
/*
 * @package   plg_system_radicalmultifieldyoopro
 * @version   1.0
 * @author    Dmitriy Vasyukov - https://fictionlabs.ru
 * @copyright Copyright (c) 2024 Fictionlabs. All rights reserved.
 * @license   GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link      https://fictionlabs.ru/
 */

namespace YOOtheme;

use Joomla\Plugin\System\RadicalMultifieldYooPro\Provider\SourceListener;

return [
	'events' => [
		'source.init' => [
			SourceListener::class => ['@handle', -20],
		]
	]
];