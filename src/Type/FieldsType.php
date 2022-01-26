<?php
/*
 * @package   plg_system_radicalmultifieldyoopro
 * @version   1.0
 * @author    Dmitriy Vasyukov - https://fictionlabs.ru
 * @copyright Copyright (c) 2021 Fictionlabs. All rights reserved.
 * @license   GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link      https://fictionlabs.ru/
 */

namespace YOOtheme\Builder\Joomla\RadicalMultiField\Type;

use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Component\Users\Administrator\Helper\UsersHelper;
use YOOtheme\Builder\Source;
use YOOtheme\Event;
use YOOtheme\Str;

class FieldsType
{
    /**
     * @var string
     */
    protected $context;

    /**
     * Constructor.
     *
     * @param string $context
     */
    public function __construct($context)
    {
        $this->context = $context;
    }

    /**
     * @param Source $source
     * @param string $type
     * @param string $context
     * @param array  $fields
     *
     * @return array
     */
    public static function config(Source $source, $type, $context, array $fields)
    {
    	return [
            'fields' => array_filter(array_reduce($fields, function ($fields, $field) use ($source, $type, $context) {

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

    protected static function configFields($field, array $config, Source $source, $context, $type)
    {
        $fields = [];

        $config               = static::configRadicalMultiField($field, $config, $source);
        $fields[$field->name] = Event::emit('source.com_fields.field|filter', $config, $field, $source, $context);

        return $fields;
    }

    protected static function configRadicalMultiField($field, array $config, Source $source)
    {
        $fields = [];

        foreach ((array) $field->fieldparams->get('listtype') as $key => $params) {

            $fields[$params->name] = [
                'type' => $params->type === 'media' ? 'MediaField' : 'String',
                'name' => Str::snakeCase($params->name),
                'metadata' => [
                    'label' => $params->title,
                    'filters' => !in_array($params->type, ['media', 'number']) ? ['limit'] : [],
                ],
            ];

        }

        if ($fields) {
            $name = Str::camelCase(['Field', $field->name], true);
            $source->objectType($name, compact('fields'));

            return ['type' => ['listOf' => $name]] + $config;
        }
    }

    public static function field($item, $args, $ctx, $info)
    {
        return $item;
    }

    public function resolve($item, $args, $ctx, $info)
    {
        if (!isset($item->id) || !$field = $this->getField($info->fieldName, $item, $this->context)) {
            return;
        }

        return $this->resolveRadicalMultiField($field);
    }

    public function resolveRadicalMultiField($field)
    {
	    $fields = [];
        foreach ($field->fieldparams->get('listtype', []) as $subField) {
            $fields[$subField->name] = $subField->type;
        }

        return array_map(function ($vals) use ($fields) {

            $values = [];

            foreach ($vals as $name => $value) {
                if ($fields[$name] === 'media') {
                    $values[Str::snakeCase($name)] = ['imagefile' => $value];
                } else {
                    $values[Str::snakeCase($name)] = $value;
                }
            }

            return $values;

        }, array_values(json_decode($field->rawvalue, true) ?: []));
    }

    public static function getField($name, $item, $context)
    {
        $fields = static::getFields($item, $context);

        return isset($fields[$name]) ? $fields[$name] : null;
    }

    protected static function getFields($item, $context)
    {
        if (!isset($item->_fields)) {

            PluginHelper::importPlugin('fields');

            $item->_fields = [];

            foreach (isset($item->jcfields) ? $item->jcfields : FieldsHelper::getFields($context, $item) as $field) {

                if (!isset($item->jcfields)) {
                    Factory::getApplication()->triggerEvent('onCustomFieldsBeforePrepareField', [$context, $item, &$field]);
                }

                $item->_fields[$field->name] = $field;
            }
        }

        return $item->_fields;
    }
}
