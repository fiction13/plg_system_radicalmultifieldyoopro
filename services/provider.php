<?php
/*
 * @package   plg_system_radicalmultifieldyoopro
 * @version   1.0
 * @author    Dmitriy Vasyukov - https://fictionlabs.ru
 * @copyright Copyright (c) 2024 Fictionlabs. All rights reserved.
 * @license   GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link      https://fictionlabs.ru/
 */

\defined('_JEXEC') or die;

use Joomla\CMS\Extension\PluginInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Event\DispatcherInterface;
use Joomla\Plugin\System\RadicalMultifieldYooPro\Extension\MultiField;

return new class implements ServiceProviderInterface {

    /**
     * Registers the service provider with a DI container.
     *
     * @param   Container  $container  The DI container.
     *
     * @since   1.0.0
     */
    public function register(Container $container)
    {
        $container->set(PluginInterface::class,
            function (Container $container) {
                $plugin  = PluginHelper::getPlugin('system', 'radicalmultifieldyoopro');
                $subject = $container->get(DispatcherInterface::class);

                $plugin = new MultiField($subject, (array) $plugin);
                $plugin->setApplication(Factory::getApplication());

                return $plugin;
            }
        );
    }
};