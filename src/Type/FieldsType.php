<?php
/*
 * @package   plg_system_radicalmultifieldyoopro
 * @version   1.0.1
 * @author    Dmitriy Vasyukov - https://fictionlabs.ru
 * @copyright Copyright (c) 2021 Fictionlabs. All rights reserved.
 * @license   GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link      https://fictionlabs.ru/
 */

namespace YOOtheme\Builder\Joomla\RadicalMultiField\Type;

use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use YOOtheme\Builder\Joomla\Fields\FieldsHelper;
use YOOtheme\Builder\Source;
use YOOtheme\Event;
use YOOtheme\Str;


class FieldsType
{

	/**
	 * @var string
	 * @since 1.0.0
	 */
	protected $context;


	/**
	 * @param $context
	 * @since 1.0.0
	 */
	public function __construct($context)
    {
        $this->context = $context;
    }


	/**
	 * @param   Source  $source
	 * @param           $type
	 * @param           $context
	 * @param   array   $fields
	 *
	 * @return array
	 *
	 * @since 1.0.0
	 */
	public static function config(Source $source, $type, $context, array $fields)
    {
		$type = 'Article.RadicalMultifield';

    	return [
            'fields' => array_filter(array_reduce($fields, function ($fields, $field) use ($source, $type, $context)
            {
                return $fields + static::configFields($field, [
                    'type' => 'String',
                    'name' => strtr($field->name, '-', '_'),
                    'metadata' => [
                        'label' => $field->title,
                        'group' => $field->group_title,
                    ],
                    'extensions' => [
                        'call' => "{$type}.fields@resolve",
                    ],
                ], $source, $context, $type);
            }, [])),

            'extensions' => [
                'bind' => [

                    "{$type}.fields" => [
                        'class' => __CLASS__,
                        'args' => ['$context' => $context],
                    ],
                ],
            ],
        ];
    }


	/**
	 * @param           $field
	 * @param   array   $config
	 * @param   Source  $source
	 * @param           $context
	 * @param           $type
	 *
	 * @return array
	 *
	 * @since 1.0.0
	 */
	protected static function configFields($field, array $config, Source $source, $context, $type)
    {
        $fields = [];

        $config               = static::configRadicalMultiField($field, $config, $source);
        $fields[$field->name] = Event::emit('source.com_fields.field|filter', $config, $field, $source, $context);

        return $fields;
    }


	/**
	 * @param           $field
	 * @param   array   $config
	 * @param   Source  $source
	 *
	 * @return array|array[]|void
	 *
	 * @since 1.0.0
	 */
	protected static function configRadicalMultiField($field, array $config, Source $source)
    {
        $fields = [];

        foreach ((array) $field->fieldparams->get('listtype') as $key => $params)
		{
            $fields[$params->name] = [
                'type' => $params->type === 'media' ? 'MediaField' : 'String',
                'name' => Str::snakeCase($params->name),
                'metadata' => [
                    'label' => $params->title,
                    'filters' => !in_array($params->type, ['media', 'number']) ? ['limit'] : [],
                ],
            ];
        }

        if ($fields)
		{
            $name = Str::camelCase(['Field', $field->name], true);
            $source->objectType($name, compact('fields'));

            return ['type' => ['listOf' => $name]] + $config;
        }
    }


	/**
	 * @param $item
	 * @param $args
	 * @param $ctx
	 * @param $info
	 *
	 * @return mixed
	 *
	 * @since 1.0.0
	 */
	public static function field($item, $args, $ctx, $info)
    {
        return $item;
    }


	/**
	 * Resolve after render
	 *
	 * @param $item
	 * @param $args
	 * @param $ctx
	 * @param $info
	 *
	 * @return array[]|void
	 *
	 * @since 1.0.0
	 */
	public function resolve($item, $args, $ctx, $info)
	{
		$name = str_replace('String', '', strtr($info->fieldName, '_', '-'));

        if (!isset($item->id) || !$field = $this->getField($name, $item, $this->context))
		{
            return;
        }

        return $this->resolveRadicalMultiField($field);
    }


	/**
	 * @param $field
	 *
	 * @return array[]
	 *
	 * @since 1.0.0
	 */
	public function resolveRadicalMultiField($field)
    {
	    $fields = [];
        foreach ($field->fieldparams->get('listtype', []) as $subField)
		{
            $fields[$subField->name] = $subField->type;
        }

        return array_map(function ($vals) use ($fields)
        {
            $values = [];

            foreach ($vals as $name => $value)
			{
                if ($fields[$name] === 'media')
				{
                    $values[Str::snakeCase($name)] = ['imagefile' => $value];
                } else {
                    $values[Str::snakeCase($name)] = $value;
                }
            }

            return $values;

        }, array_values(json_decode($field->rawvalue, true) ?: []));
    }


	/**
	 * Check current field in the field list of article
	 *
	 * @param $name
	 * @param $item
	 * @param $context
	 *
	 * @return mixed|null
	 *
	 * @since 1.0.0
	 */
	public static function getField($name, $item, $context)
    {
        $fields = static::getFields($item, $context);

        return isset($fields[$name]) ? $fields[$name] : null;
    }


	/**
	 * Get article fields
	 *
	 * @param $item
	 * @param $context
	 *
	 * @return array
	 *
	 * @throws \Exception
	 * @since 1.0.0
	 */
	protected static function getFields($item, $context)
    {
        if (!isset($item->_fields))
		{

            PluginHelper::importPlugin('fields');

            $item->_fields = [];

            foreach (isset($item->jcfields) ? $item->jcfields : FieldsHelper::getFields($context, $item) as $field)
			{
                if (!isset($item->jcfields))
				{
                    Factory::getApplication()->triggerEvent('onCustomFieldsBeforePrepareField', [$context, $item, &$field]);
                }

                $item->_fields[$field->name] = $field;
            }
        }

        return $item->_fields;
    }
}
