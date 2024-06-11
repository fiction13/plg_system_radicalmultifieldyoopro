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

use YOOtheme\Builder\Joomla\Fields\FieldsHelper;
use YOOtheme\Builder\Source;

class SourceListener
{
    /**
     * @param Source $source
     */
    public static function handle($source)
    {
        $fields = array();
        $articleFields = FieldsHelper::getFields('com_content.article');

        foreach ($articleFields as $field)
		{
            if ($field->state == 1 && $field->type === 'radicalmultifield')
			{
                $fields[$field->name] = $field;
            }
        }

        if ($fields)
		{
        	static::configFields($source, 'Article', 'com_content.article', $fields);
        }
    }


	/**
	 * @param          $source
	 * @param          $type
	 * @param          $context
	 * @param   array  $fields
	 *
	 *
	 * @since 1.0.0
	 */
	protected static function configFields($source, $type, $context, array $fields)
    {
        // add field on type
        $source->objectType($type, $config = [
            'fields' => [
                'field' => [
                    'type' => $fieldType = "{$type}Fields",
                    'metadata' => [
                        'label' => 'Fields',
                    ],
                    'extensions' => [
                        'call' => Type\FieldsType::class . '::field',
                    ],
                ],
            ],
        ]);

        // configure field type
        $source->objectType($fieldType, Type\FieldsType::config($source, $type, $context, $fields));
    }
}
