<?php
/**
 * @package    com_iseo
 * @author     Pavel Lange <pavel@ilange.ru>
 * @link       https://github.com/i-lange/com_iseo
 * @copyright  (C) 2023 Pavel Lange <https://ilange.ru>
 * @license    GNU General Public License version 2 or later
 */

namespace Ilange\Component\Iseo\Administrator\Extension;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Extension\BootableExtensionInterface;
use Joomla\CMS\Extension\MVCComponent;
use Joomla\CMS\HTML\HTMLRegistryAwareTrait;
use Joomla\CMS\Helper\ContentHelper;
use Psr\Container\ContainerInterface;

use Joomla\CMS\Categories\CategoryServiceInterface;
use Joomla\CMS\Categories\CategoryServiceTrait;

use Joomla\CMS\Component\Router\RouterServiceInterface;
use Joomla\CMS\Component\Router\RouterServiceTrait;

use Ilange\Component\Iseo\Administrator\Service\Html\AdministratorService;

/**
 * Класс компонента com_iseo
 * @since 1.0.0
 */
class IseoComponent extends MVCComponent implements 
    BootableExtensionInterface, 
    CategoryServiceInterface, 
    RouterServiceInterface
{    
    use HTMLRegistryAwareTrait;
    use RouterServiceTrait;
    use CategoryServiceTrait;

    /**
     * Загрузка расширения. Это функция для настройки среды расширения,
     * например, регистрация новых загрузчиков классов и т.д.
     * При необходимости, некоторые начальные настройки могут быть выполнены
     * из служб контейнера, например, регистрация служб HTML.
     * @param ContainerInterface $container Контейнер
     * @return void
     * @since 1.0.0
     */
    public function boot(ContainerInterface $container)
    {
        $this->getRegistry()->register('iseoadministrator', new AdministratorService());
    }

    /**
     * Подсчет количества элементов
     * @param \stdClass[] $items Объекты аудитов
     * @param string $section Имя текущего представления
     * @return void
     * @throws \Exception
     * @since 1.0.0
     */
    public function countItems(array $items, string $section)
    {
        $config = (object)[
            'related_tbl' => $this->getTableNameForSection($section),
            'state_col' => $this->getStateColumnForSection($section),
            'group_col' => 'catid',
            'relation_type' => 'category_or_group',
        ];

        ContentHelper::countRelations($items, $config);
    }

    /**
     * Возвращает таблицу для функций подсчета элементов данного раздела
     * @param string|null $section Секция
     * @return string|null Имя таблицы
     * @since 1.0.0
     */
    protected function getTableNameForSection(string $section = null): string
    {
        return ($section === 'category' ? 'categories' : 'iseo_results');
    }

    /**
     * Возвращает имя колонки состояния публикации для данного раздела
     * @param string|null $section Секция
     * @return string|null Имя таблицы
     * @since 1.0.0
     */
    protected function getStateColumnForSection(string $section = null)
    {
        return 'state';
    }
}
