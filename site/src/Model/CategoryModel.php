<?php
/**
 * @package    com_iseo
 * @author     Pavel Lange <pavel@ilange.ru>
 * @link       https://github.com/i-lange/com_iseo
 * @copyright  (C) 2023 Pavel Lange <https://ilange.ru>
 * @license    GNU General Public License version 2 or later
 */

namespace Ilange\Component\Iseo\Site\Model;

defined('_JEXEC') or die;

use Joomla\CMS\Categories\CategoryNode;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Database\ParameterType;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;

/**
 * Модель поддерживает получение категории, аудитов, связанных с этой категорией, 
 * дочерних и родительских категорий
 * @since 1.0.0
 */
class CategoryModel extends ListModel
{
    /**
     * Данные категории
     * @var array
     * @since 1.0.0
     */
    protected $_item = null;

    /**
     * Категория слева и справа от этой
     * @var CategoryNode[]|null
     * @since 1.0.0
     */
    protected $_siblings = null;

    /**
     * Массив дочерних категорий
     * @var CategoryNode[]|null
     * @since 1.0.0
     */
    protected $_children = null;

    /**
     * Родительская категория для текущей
     * @var CategoryNode|null
     * @since 1.0.0
     */
    protected $_parent = null;

    /**
     * Контекст модели
     * @var string
     * @since 1.0.0
     */
    protected $_context = 'com_iseo.category';

    /**
     * Категория
     * @var object
     * @since 1.0.0
     */
    protected $_category = null;

    /**
     * Список категорий
     * @var array
     * @since 1.0.0
     */
    protected $_categories = null;

    /**
     * Конструктор
     * @param array $config Ассоциативный массив параметров конфигурации, необязательно
     * @throws \Exception
     * @since 1.0.0
     */
    public function __construct($config = [])
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = [
                'id', 'a.id',
                'state', 'a.state',
                'title', 'a.title',
                'url', 'a.url',
                'uniqid', 'a.uniqid',
                'hits', 'a.hits',
            ];
        }

        parent::__construct($config);
    }

    /**
     * Метод для автоматического заполнения модели
     * Этот метод должен вызываться только один раз
     * и предназначен для первого вызове метода getState(),
     * если не установлен флаг для игнорирования запроса
     * Вызов getState в этом методе приведет к рекурсии
     * @param string $ordering Поле для сортировки
     * @param string $direction Направление сортировки
     * @return void
     * @throws \Exception
     * @since 1.0.0
     */
    protected function populateState($ordering = 'id', $direction = 'ASC')
    {
        $app = Factory::getApplication();

        // Состояние публикации
        $this->setState('filter.published', 1);

        // Устанавливаем параметры из запроса
        $pk = $app->input->getInt('id');        
        $this->setState('category.id', $pk);
        $this->setState('filter.category_id', $pk);

        // Загружаем параметры компонента.
        // Объединим глобальные параметры и параметры пункта меню
        $params = $app->getParams();

        if ($menu = $app->getMenu()->getActive()) {
            $menuParams = $menu->getParams();
        } else {
            $menuParams = new Registry();
        }

        $mergedParams = clone $menuParams;
        $mergedParams->merge($params);
        $this->setState('params', $mergedParams);

        parent::populateState($ordering, $direction);

        $value = $app->input->get('limit', $app->get('list_limit', 0), 'uint');
        $this->setState('list.limit', $value);

        $value = $app->input->get('limitstart', 0, 'uint');
        $this->setState('list.start', $value);
    }

     /**
     * Метод получения данных текущей категории
     * @return object
     * @throws \Exception
     * @since 1.0.0
     */
    public function getCategory()
    {
        if (!is_object($this->_item)) {

            //$categories = Categories::getInstance('Iseo', $options);
            $categories = Factory::getApplication()->bootComponent('com_iseo')->getCategory();
            $this->_item = $categories->get($this->getState('category.id', 'root'));

            if (is_object($this->_item)) {
                $this->_children = $this->_item->getChildren();                
                $this->_parent = false;

                if ($this->_item->getParent()) {
                    $this->_parent = $this->_item->getParent();
                }

                $this->_rightsibling = $this->_item->getSibling();
                $this->_leftsibling = $this->_item->getSibling(false);
            } else {
                $this->_children = false;
                $this->_parent = false;
            }
        }

        return $this->_item;
    }

    /**
     * Получаем дочерние категории
     * @return array|CategoryNode[]|null Массив категорий или false, если произошла ошибка
     * @throws \Exception
     * @since 1.0.0
     */
    public function &getChildren()
    {
        if (!is_object($this->_item)) {
            $this->getCategory();
        }

        return $this->_children;
    }

    /**
     * Получаем родительскую категорию
     * @return object Массив категорий или false, если произошла ошибка
     * @throws \Exception
     * @since 1.0.0
     */
    public function getParent()
    {
        if (!is_object($this->_item)) {
            $this->getCategory();
        }

        return $this->_parent;
    }

    /**
     * Основной запрос для получения списка аудитов на основе состояния модели
     * @return \Joomla\Database\QueryInterface
     * @throws \Exception
     * @since 1.0.0
     */
    protected function getListQuery()
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true);

        $query
            ->select($this->getState('list.select','a.*'))
            ->select($this->getSlugColumn($query, 'a.id', 'a.uniqid') . ' AS slug')
            ->select($this->getSlugColumn($query, 'c.id', 'c.alias') . ' AS catslug')
            ->from($db->quoteName('#__iseo_results','a'))
            ->leftJoin($db->quoteName('#__categories', 'c') . ' ON c.id = a.catid');

        // Фильтрация по категориям
        $categoryId = $this->getState('filter.category_id', []);
        if (!is_array($categoryId)) {
            $categoryId = $categoryId ? [$categoryId] : [];
        }
        if (count($categoryId)) {
            $categoryId = ArrayHelper::toInteger($categoryId);
            $categoryTable = Factory::getApplication()
                ->bootComponent('com_categories')
                ->getMVCFactory()->createTable('Category', 'Administrator', ['dbo' => $db]);
            $subCatItemsWhere = [];

            foreach ($categoryId as $filter_catid) {
                $categoryTable->load($filter_catid);

                // Поскольку значения в $query->bind() передаются по ссылке,
                // используем $query->bindArray() для предотвращения перезаписи.
                $valuesToBind = [$categoryTable->lft, $categoryTable->rgt];
                $bounded = $query->bindArray($valuesToBind);
                $categoryWhere = $db->quoteName('c.lft') . ' >= ' . $bounded[0] . ' AND ' . $db->quoteName('c.rgt') . ' <= ' . $bounded[1];
                $subCatItemsWhere[] = '(' . $categoryWhere . ')';
            }

            $query->where('(' . implode(' OR ', $subCatItemsWhere) . ')');
        }

        // Фильтрация по состоянию публикации
        $published = $this->getState('filter.published');
        if (is_numeric($published)) {
            $published = (int) $published;
            $query->where($db->quoteName('a.state') . ' = :published')
                ->bind(':published', $published, ParameterType::INTEGER);
        } elseif (empty($published)) {
            $published = [0, 1];
            $query->whereIn($db->quoteName('a.state'), $published);
        } elseif (is_array($published)) {
            $published = ArrayHelper::toInteger($published);
            $query->whereIn($db->quoteName('a.state'), $published);
        }

        $orderCol  = $this->state->get('list.ordering', 'id');
        $orderDirn = $this->state->get('list.direction', 'ASC');

        if ($orderCol && $orderDirn) {
            $query->order($db->escape($orderCol . ' ' . $orderDirn));
        }

        return $query;
    }

    /**
     * Выражение для получения строк slug или catslug.
     * @param object $query Текущий запрос
     * @param string $id Значение идентификатора
     * @param string $alias Значение псевдонима
     * @return string
     * @since 1.0.0
     */
    private function getSlugColumn(object $query, string $id, string $alias)
    {
        return 'CASE WHEN '
            . $query->charLength($alias, '!=', '0')
            . ' THEN '
            . $query->concatenate([$query->castAs('CHAR', $id), $alias], ':')
            . ' ELSE '
            . $query->castAs('CHAR', $id) . ' END';
    }
    
    /**
     * Увеличивает счетчик просмотров категории
     * @param int $pk Идентификатор категории, необязательно
     * @return bool True если успешно
     * @throws \Exception
     * @since 1.0.0
     */
    public function hit(int $pk = 0)
    {
        $input = Factory::getApplication()->input;
        $hitcount = $input->getInt('hitcount', 1);

        if ($hitcount) {
            $pk = (int) ($pk ?: $this->getState('category.id'));

            //$table = Table::getInstance('Category');
            $table = Factory::getApplication()
                ->bootComponent('com_categories')
                ->getMVCFactory()->createTable('Category', 'Administrator');
            $table->hit($pk);
        }

        return true;
    }
}
