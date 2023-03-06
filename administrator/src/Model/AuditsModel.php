<?php
/**
 * @package    com_iseo
 * @author     Pavel Lange <pavel@ilange.ru>
 * @link       https://github.com/i-lange/com_iseo
 * @copyright  (C) 2023 Pavel Lange <https://ilange.ru>
 * @license    GNU General Public License version 2 or later
 */

namespace Ilange\Component\Iseo\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Utilities\ArrayHelper;
use Joomla\Database\ParameterType;

/**
 * Модель списка записей Audits
 * @since 1.0.0
 */
class AuditsModel extends ListModel
{
    /**
     * Конструктор
     * @param array $config Массив параметров, необязательно
     * @throws \Exception
     * @since 1.0.0
     * @see JController
     */
    public function __construct($config = [])
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] =[
                'id', 'a.id',
                'title', 'a.title',
                'state', 'a.state',
                'created_by', 'a.created_by',
                'uniqid', 'a.uniqid',
                'hits', 'a.hits',
                'catid', 'a.catid',                
            ];
        }

        parent::__construct($config);
    }

    /**
     * Метод для автоматического заполнения модели
     * Вызов getState в этом методе приведет к рекурсии
     * @param string $ordering Порядок элементов
     * @param string $direction Направление сортировки
     * @return void
     * @throws \Exception
     * @since 1.0.0
     */
    protected function populateState($ordering = 'a.id', $direction = 'asc')
    {
        $search = $this->getUserStateFromRequest(
            $this->context . '.filter.search', 
            'filter_search');
        $this->setState('filter.search', $search);

        $published = $this->getUserStateFromRequest(
            $this->context . '.filter.published', 
            'filter_published', '');
        $this->setState('filter.published', $published);

        parent::populateState($ordering, $direction);
    }

    /**
     * Метод для получения идентификатора на основе конфигурации модели 
     * Это необходимо, поскольку модель используется компонентом и различными модулями, 
     * которым могут понадобиться разные наборы данных или разный порядок сортировки
     * @param string $id Префикс
     * @return string Идентификатор
     * @since 1.0.0
     */
    protected function getStoreId($id = '')
    {
        $id .= ':' . $this->getState('filter.search');
        $id .= ':' . $this->getState('filter.published');
        $id .= ':' . serialize($this->getState('filter.created_by'));
        $id .= ':' . serialize($this->getState('filter.category_id'));

        return parent::getStoreId($id);
    }

    /**
     * Составляем запрос к базе данных, для выборки списка аудитов
     * @return \Joomla\Database\QueryInterface
     * @throws \Exception
     * @since 1.0.0
     */
    protected function getListQuery()
    {
        $db     = $this->getDatabase();
        $query  = $db->getQuery(true);
        $user   = Factory::getApplication()->getIdentity();

        $query
            ->select(
                $this->getState('list.select', 'a.*')
            )
            ->select(
                [
                    $db->quoteName('c.title', 'category_title'),
                    $db->quoteName('u_checked.name', 'checked_out'),
                    $db->quoteName('u_created.name', 'created_by'),
                    $db->quoteName('u_modified.name', 'modified_by'),
                ]
            )
            ->from($db->quoteName('#__iseo_results', 'a'))
            ->join(
                'LEFT',
                $db->quoteName('#__categories', 'c'),
                $db->quoteName('c.id') . ' = ' . $db->quoteName('a.catid')
            )
            ->join(
                'LEFT',
                $db->quoteName('#__users', 'u_checked'),
                $db->quoteName('u_checked.id') . ' = ' . $db->quoteName('a.checked_out')
            )
            ->join(
                'LEFT',
                $db->quoteName('#__users', 'u_created'),
                $db->quoteName('u_created.id') . ' = ' . $db->quoteName('a.created_by')
            )
            ->join(
                'LEFT',
                $db->quoteName('#__users', 'u_modified'),
                $db->quoteName('u_modified.id') . ' = ' . $db->quoteName('a.modified_by')
            );

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

        // Фильтрация по автору
        $created_by = $this->getState('filter.created_by');
        if (is_numeric($created_by)) {
            $created_by = (int) $created_by;
            $type = $this->getState('filter.created_by.include', true) ? ' = ' : ' <> ';
            $query->where($db->quoteName('a.created_by') . $type . ':created_by')
                ->bind(':created_by', $created_by, ParameterType::INTEGER);
        } elseif (is_array($created_by)) {
            // Проверяем есть ли by_me в массиве
            if (\in_array('by_me', $created_by)) {
                // Заменяем by_me на ID текущего пользователя
                $created_by['by_me'] = $user->id;
            }
            $created_by = ArrayHelper::toInteger($created_by);
            $query->whereIn($db->quoteName('a.created_by'), $created_by);
        }

        // Фильтрация на основе поискового запроса
        $search = $this->getState('filter.search');
        if (!empty($search)) {
            if (stripos($search, 'url:') === 0) {
                $search = '%' . substr($search, 4) . '%';
                $query->where('(' . $db->quoteName('a.url') . ' LIKE :search1 OR '
                    . $db->quoteName('a.result') . ' LIKE :search2)')
                    ->bind([':search1', ':search2'], $search);
            } else {
                $search = '%' . str_replace(' ', '%', trim($search)) . '%';
                $query->where(
                    '(' . $db->quoteName('a.url') . ' LIKE :search1 OR '
                    . $db->quoteName('a.result') . ' LIKE :search2 OR '
                    . $db->quoteName('a.title') . ' = :search3)')
                    ->bind([':search1', ':search2', ':search3'], $search);
            }
        }

        // Сортировка списка
        $orderCol = $this->state->get('list.ordering', 'a.id');
        $orderDirn = $this->state->get('list.direction', 'ASC');

        if ($orderCol && $orderDirn) {
            $query->order($db->escape($orderCol . ' ' . $orderDirn));
        }

        return $query;
    }

    /**
     * Метод для получения списка прогулок,
     * переопределяем для добавления проверки уровней доступа
     * @return mixed Массив элементов или false
     * @since 1.0.0
     */
    public function getItems()
    {
        return parent::getItems();
    }
}
