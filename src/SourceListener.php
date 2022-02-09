<?php
/*
 * @package   plg_system_radicalmultifieldyoopro
 * @version   1.0.1
 * @author    Dmitriy Vasyukov - https://fictionlabs.ru
 * @copyright Copyright (c) 2021 Fictionlabs. All rights reserved.
 * @license   GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link      https://fictionlabs.ru/
 */

namespace YOOtheme\Builder\Joomla\RadicalMultiField;

use YOOtheme\Builder\Joomla\Fields\FieldsHelper;

class SourceListener
{
    /**
     * @param Source $source
     */
    public static function initSource($source)
    {
        if (!class_exists(\FieldsHelper::class))
		{
            return;
        }

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
