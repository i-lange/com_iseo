<?php
/**
 * @package    com_iseo
 * @author     Pavel Lange <pavel@ilange.ru>
 * @link       https://github.com/i-lange/com_iseo
 * @copyright  (C) 2023 Pavel Lange <https://ilange.ru>
 * @license    GNU General Public License version 2 or later
 */

namespace Ilange\Component\Iseo\Site\Service;

defined('_JEXEC') or die;

use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Categories\CategoryFactoryInterface;
use Joomla\CMS\Categories\CategoryInterface;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Component\Router\RouterView;
use Joomla\CMS\Component\Router\RouterViewConfiguration;
use Joomla\CMS\Component\Router\Rules\MenuRules;
use Joomla\CMS\Component\Router\Rules\NomenuRules;
use Joomla\CMS\Component\Router\Rules\StandardRules;
use Joomla\CMS\Menu\AbstractMenu;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;

/**
 * Класс роутер для com_iseo
 * @since 1.0.0
 */
class Router extends RouterView
{
    /**
     * Флаг удаления ID
     * @var bool
     * @since 1.0.0
     */
    protected $noIDs = false;

    /**
     * Фабрика категорий
     * @var CategoryFactoryInterface
     * @since 1.0.0
     */
    private $categoryFactory;

    /**
     * Интерфейс базы данных
     * @var DatabaseInterface
     * @since 1.0.0
     */
    private $db;

    /**
     * Конструктор роутера для компонента com_iseo
     * @param SiteApplication $app The application object
     * @param AbstractMenu $menu The menu object to work with
     * @param CategoryFactoryInterface $categoryFactory The category object
     * @param DatabaseInterface $db The database object
     * @since 1.0.0
     */
    public function __construct(
        SiteApplication $app,
        AbstractMenu $menu,
        CategoryFactoryInterface $categoryFactory,
        DatabaseInterface $db
    ) {
        $this->categoryFactory = $categoryFactory;
        $this->db = $db;

        $params = ComponentHelper::getParams('com_iseo');
        $this->noIDs = (bool)$params->get('sef_ids');

        $categories = new RouterViewConfiguration('categories');
        $categories->setKey('id');
        $this->registerView($categories);

        $category = new RouterViewConfiguration('category');
        $category->setKey('id')->setParent($categories, 'catid')->setNestable();
        $this->registerView($category);

        $audits = new RouterViewConfiguration('audits');
        $this->registerView($audits);

        $audit = new RouterViewConfiguration('audit');
        $audit->setKey('id')->setParent($audits);
        $this->registerView($audit);

        $online = new RouterViewConfiguration('online');
        $this->registerView($online);

        parent::__construct($app, $menu);

        $this->attachRule(new MenuRules($this));
        $this->attachRule(new StandardRules($this));
        $this->attachRule(new NomenuRules($this));
        $this->attachRule(new IseoRules($this));
    }

    /**
     * Общий метод для предварительной обработки URL
     * @param array $query Ассоциативный массив аргументов URL
     * @return array Аргументы, которые следует использовать для сборки URL
     * @since 1.0.0
     */
    public function preprocess($query)
    {
        return parent::preprocess($query);
    }

    /**
     * Метод получения сегмента (сегментов) для аудита
     * @param string $id ID аудита для получения сегментов
     * @param array $query Запрос, который собирается в данный момент
     * @return array Массив сегментов одиночного аудита
     * @since 1.0.0
     */
    public function getAuditSegment(string $id, array $query)
    {
        if (!strpos($id, ':')) {
            $id = (int)$id;
            $dbquery = $this->db->getQuery(true);
            $dbquery->select($this->db->quoteName('uniqid'))
                ->from($this->db->quoteName('#__iseo_results'))
                ->where($this->db->quoteName('id') . ' = :id')
                ->bind(':id', $id, ParameterType::INTEGER);
            $this->db->setQuery($dbquery);

            $id .= ':' . $this->db->loadResult();
        }

        if ($this->noIDs) {
            list($void, $segment) = explode(':', $id, 2);

            return [$void => $segment];
        }

        return [(int)$id => $id];
    }

    /**
     * Метод получения идентификатора для аудита
     * @param string $segment Сегмент аудита для получения идентификатора
     * @param array $query Запрос, который разбирается в данный момент
     * @return int Идентификатор найденного аудита или нуль
     * @since 1.0.0
     */
    public function getAuditId(string $segment, array $query)
    {
        if ($this->noIDs) {
            $dbquery = $this->db->getQuery(true);
            $dbquery
                ->select($this->db->quoteName('id'))
                ->from($this->db->quoteName('#__iseo_results'))
                ->where($this->db->quoteName('uniqid') . ' = :uniqid')
                ->bind(':uniqid', $segment);
            $this->db->setQuery($dbquery);

            return (int)$this->db->loadResult();
        }

        return (int)$segment;
    }

    /**
     * Метод получения сегмента (сегментов) для списка категорий
     * @param string $id ID списка категорий для получения сегментов
     * @param array $query Запрос, который собирается в данный момент
     * @return array Массив сегментов списка категорий
     * @since 1.0.0
     */
    public function getCategoriesSegment(string $id, array $query)
    {
        return $this->getCategorySegment($id, $query);
    }

    /**
     * Метод получения идентификатора для списка категорий
     * @param string $segment Сегмент аудита для получения идентификатора
     * @param array $query Запрос, который разбирается в данный момент
     * @return int Идентификатор найденного аудита или нуль
     * @since 1.0.0
     */
    public function getCategoriesId(string $segment, array $query)
    {
        return $this->getCategoryId($segment, $query);
    }

    /**
     * Метод получения сегмента (сегментов) для категории
     * @param string $id ID категории для получения сегментов
     * @param array $query Запрос, который собирается в данный момент
     * @return array Массив сегментов категории
     * @since 1.0.0
     */
    public function getCategorySegment(string $id, array $query)
    {
        $category = $this->getCategories(['access' => true])->get($id);

        if ($category) {
            $path = array_reverse($category->getPath(), true);
            $path[0] = '1:root';

            if ($this->noIDs) {
                foreach ($path as &$segment) {
                    list($id, $segment) = explode(':', $segment, 2);
                }
            }

            return $path;
        }

        return [];
    }

    /**
     * Метод получения идентификатора для категории
     * @param string $segment Сегмент аудита для получения идентификатора
     * @param array $query Запрос, который разбирается в данный момент
     * @return int Идентификатор найденного аудита или нуль
     * @since 1.0.0
     */
    public function getCategoryId(string $segment, array $query)
    {
        if (isset($query['id'])) {
            $category = $this->getCategories(['access' => false])->get($query['id']);

            if ($category) {
                foreach ($category->getChildren() as $child) {
                    if ($this->noIDs) {
                        if ($child->alias == $segment) {
                            return $child->id;
                        }
                    } elseif ($child->id == (int)$segment) {
                        return $child->id;
                    }
                }
            }
        }

        return 0;
    }

    /**
     * Метод получения категорий из кэша
     * @param array $options Параметры получения категорий
     * @return CategoryInterface Объект, содержащий категории
     * @since 1.0.0
     */
    private function getCategories(array $options = []): CategoryInterface
    {
        $key = serialize($options);

        if (!isset($this->categoryCache[$key])) {
            $this->categoryCache[$key] = $this->categoryFactory->createCategory($options);
        }

        return $this->categoryCache[$key];
    }
}
