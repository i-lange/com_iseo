<?php
/**
 * @package    com_iseo
 * @author     Pavel Lange <pavel@ilange.ru>
 * @link       https://github.com/i-lange/com_iseo
 * @copyright  (C) 2023 Pavel Lange <https://ilange.ru>
 * @license    GNU General Public License version 2 or later
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\Registry;
use Joomla\CMS\Extension\Service\Provider\ComponentDispatcherFactory;
use Joomla\CMS\Dispatcher\ComponentDispatcherFactoryInterface;
use Joomla\CMS\Extension\ComponentInterface;
use Joomla\CMS\Extension\Service\Provider\MVCFactory;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;

use Joomla\CMS\Extension\Service\Provider\CategoryFactory;
use Joomla\CMS\Categories\CategoryFactoryInterface;

use Joomla\CMS\Extension\Service\Provider\RouterFactory;
use Joomla\CMS\Component\Router\RouterFactoryInterface;

use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;

use Ilange\Component\Iseo\Administrator\Extension\IseoComponent;

/**
 * Класс service provider компонента
 * @since 1.0.0
 */
return new class implements ServiceProviderInterface {
    /**
     * Регистрация поставщика с помощью DI контейнера
     * @param Container $container DI контейнер
     * @return void
     * @since 1.0.0
     */
    public function register(Container $container)
    {
        $container->registerServiceProvider(new CategoryFactory('\\Ilange\\Component\\Iseo'));
        $container->registerServiceProvider(new MVCFactory('\\Ilange\\Component\\Iseo'));
        $container->registerServiceProvider(new ComponentDispatcherFactory('\\Ilange\\Component\\Iseo'));
        $container->registerServiceProvider(new RouterFactory('\\Ilange\\Component\\Iseo'));
        $container->set(
            ComponentInterface::class,
            function (Container $container) {
                $component = new IseoComponent($container->get(ComponentDispatcherFactoryInterface::class));

                $component->setRegistry($container->get(Registry::class));
                $component->setMVCFactory($container->get(MVCFactoryInterface::class));
                $component->setCategoryFactory($container->get(CategoryFactoryInterface::class));
                $component->setRouterFactory($container->get(RouterFactoryInterface::class));

                return $component;
            }
        );
    }
};