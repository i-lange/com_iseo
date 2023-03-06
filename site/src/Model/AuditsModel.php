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

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\ListModel;

/**
 * Эта модель поддерживает получение списка аудитов
 * @since 1.0.0
 */
class AuditsModel extends ListModel
{
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
        
        $params = $app->getParams();
        $this->setState('params', $params);
        
        parent::populateState($ordering, $direction);

        $value = $app->input->get('limit', $app->get('list_limit', 0), 'uint');
        $this->setState('list.limit', $value);

        $value = $app->input->get('limitstart', 0, 'uint');
        $this->setState('list.start', $value);

        
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
            ->from($db->quoteName('#__iseo_results','a'));        

        $orderCol  = $this->state->get('list.ordering', 'id');
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

    

    /**
     * Возвращает объект параметров
     * @return object Объект параметров
     * @since 1.0.0
     */
    public function getParams()
    {
        return $this->getState('params');
    }
}