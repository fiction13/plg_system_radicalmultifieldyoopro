<?php
/*
 * @package   plg_system_radicalmultifieldyoopro
 * @version   1.0
 * @author    Dmitriy Vasyukov - https://fictionlabs.ru
 * @copyright Copyright (c) 2024 Fictionlabs. All rights reserved.
 * @license   GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link      https://fictionlabs.ru/
 */

namespace Joomla\Plugin\System\RadicalMultifieldYooPro\Provider\Type;

use YOOtheme\Builder\Joomla\Fields\Type\FieldsType as JoomlaFieldsType;
use YOOtheme\Builder\Source;
use YOOtheme\Str;

class FieldsType
{
	protected const MEDIA_FIELD_TYPE = 'RadicalMultifieldMediaField';

	/**
	 * @param   array   $config
	 * @param   object  $field
	 * @param   Source  $source
	 * @param   string  $context
	 *
	 * @return array
	 *
	 * @since 1.0.0
	 */
	public static function config(array $config, object $field, Source $source, string $context): array
	{
		$fields = [];
		$hasMedia = false;

		foreach ((array) $field->fieldparams->get('listtype', []) as $params)
		{
			$params = (object) $params;

			if (empty($params->name))
			{
				continue;
			}

			$name          = Str::snakeCase($params->name);
			$type          = $params->type ?? 'text';
			$hasMedia      = $hasMedia || $type === 'media';
			$fields[$name] = [
				'type'     => $type === 'media' ? self::MEDIA_FIELD_TYPE : 'String',
				'name'     => $name,
				'metadata' => [
					'label'   => $params->title ?? $params->name,
					'filters' => !in_array($type, ['media', 'number'], true) ? ['limit'] : [],
					'group'   => $field->group_title,
				],
			];
		}

		if (!$fields)
		{
			return $config;
		}

		if ($hasMedia)
		{
			static::configMediaField($source);
		}

		$name = Str::camelCase(['Field', $field->name], true);
		$source->objectType($name, compact('fields'));

		$config['type']               = ['listOf' => $name];
		$config['extensions']['call'] = [
			'func' => [static::class, 'resolve'],
			'args' => [
				'context' => $context,
				'name'    => $field->name,
			],
		];

		return $config;
	}

	/**
	 * @param   Source  $source
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	protected static function configMediaField(Source $source): void
	{
		$source->objectType(self::MEDIA_FIELD_TYPE, [
			'fields' => [
				'imagefile' => [
					'type'       => 'String',
					'metadata'   => [
						'label' => '',
					],
					'extensions' => [
						'call' => [
							'func' => [static::class, 'imagefile'],
						],
					],
				],
			],
		]);
	}

	/**
	 * @param   object|null  $item
	 * @param   array       $args
	 *
	 * @return array|null
	 *
	 * @since 1.0.0
	 */
	public static function resolve($item, array $args): ?array
	{
		if (!isset($item->id))
		{
			return null;
		}

		$field = JoomlaFieldsType::getField($args['name'], $item, $args['context']);

		return $field ? static::resolveRadicalMultiField($field) : null;
	}

	/**
	 * @param   object  $field
	 *
	 * @return array
	 *
	 * @since 1.0.0
	 */
	protected static function resolveRadicalMultiField(object $field): array
	{
		$types = [];

		foreach ((array) $field->fieldparams->get('listtype', []) as $subField)
		{
			$subField = (object) $subField;

			if (!empty($subField->name))
			{
				$type = $subField->type ?? 'text';

				$types[$subField->name] = $type;
				$types[Str::snakeCase($subField->name)] = $type;
			}
		}

		return array_values(array_map(
			fn($values) => static::resolveRow((array) $values, $types),
			static::normalizeRows($field->rawvalue, $types)
		));
	}

	/**
	 * @param   mixed  $value
	 * @param   array  $types
	 *
	 * @return array
	 *
	 * @since 1.0.0
	 */
	protected static function normalizeRows($value, array $types): array
	{
		if (is_string($value))
		{
			$value = json_decode($value, true);
		}

		if (!is_array($value))
		{
			return [];
		}

		if (array_intersect(array_keys($value), array_keys($types)))
		{
			return [$value];
		}

		return array_filter($value, 'is_array');
	}

	/**
	 * @param   array  $values
	 * @param   array  $types
	 *
	 * @return array
	 *
	 * @since 1.0.0
	 */
	protected static function resolveRow(array $values, array $types): array
	{
		$result = [];

		foreach ($values as $name => $value)
		{
			$key = Str::snakeCase($name);

			if (($types[$name] ?? null) === 'media')
			{
				$result[$key] = static::resolveMedia($value);
				continue;
			}

			$result[$key] = $value;
		}

		return $result;
	}

	/**
	 * @param   mixed  $value
	 *
	 * @return array|null
	 *
	 * @since 1.0.0
	 */
	protected static function resolveMedia($value): ?array
	{
		if (is_array($value))
		{
			return $value;
		}

		if (!is_string($value) || $value === '')
		{
			return null;
		}

		if (str_starts_with($value, '{'))
		{
			return json_decode($value, true);
		}

		return ['imagefile' => $value];
	}

	/**
	 * @param   array  $image
	 *
	 * @return string|null
	 *
	 * @since 1.0.0
	 */
	public static function imagefile(array $image): ?string
	{
		return rawurldecode($image['imagefile'] ?? '') ?: null;
	}
}
