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
use Joomla\CMS\MVC\Model\ItemModel;
use Joomla\Utilities\ArrayHelper;
use Ilange\Component\Iseo\Administrator\Helper\IseoHelper;

/**
 * Модель одиночного аудита
 * @since 1.0.0
 */
class OnlineModel extends ItemModel
{
    /**
     * Строка контекста модели
     * @var string
     * @since 1.0.0
     */
    protected $_context = 'com_iseo.online';

    /**
     * Метод для автоматического заполнения модели
     * Вызов getState в этом методе приведет к рекурсии
     * @return void
     * @throws \Exception
     * @since 1.0.0
     */
    protected function populateState()
    {
        $app = Factory::getApplication();

        // Устанавливаем параметры из запроса
        $uniqid = $app->input->getString('uniqid');
        $this->setState('audit.uniqid', $uniqid);
        $online = $app->input->getString('url');
        $this->setState('audit.url', $online);

        // Загружаем параметры компонента
        $params = $app->getParams();
        $this->setState('params', $params);
    }

    /**
     * Метод получения данных аудита
     * @param string $pk Идентификатор аудита
     * @return object|bool Объект данных аудита при успехе, иначе false
     * @throws \Exception
     * @since 1.0.0
     */
    public function getItem($pk = null)
    {
        $pk = ($pk ?: $this->getState('audit.uniqid'));
        $table = $this->getTable('Audit');
        
        if (!$table->load(['uniqid' => $pk])) {
            return false;
        }
        
        return ArrayHelper::toObject($table->getProperties(1));
    }

    /**
     * Получение и сохранение данных с сервиса PageSpeed
     * @param string|null $url URL адрес для проверки
     * @return string uniqid аудита при успехе, иначе false
     * @throws \Exception
     * @since 1.0.0
     */
    public function getNewItem(string $url = null) : string
    {
        $url = ($url ?: $this->getState('audit.url'));

        if (!$url) {
            return false;            
        }

        $params = $this->getState('params');
        $params['newUrl'] = $url;

        return IseoHelper::getNewAudit($params);
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
    
    /**
     * Увеличивает счетчик просмотра результата аудита
     * @param int|null $pk Идентификатор аудита, необязательно
     * @return bool True если успешно
     * @throws \Exception
     * @since 1.0.0
     */    
    public function hit(int $pk = null)
    {
        $input = Factory::getApplication()->input;
        $hitcount = $input->getInt('hitcount', 1);

        if ($hitcount) {
            $pk = (int) ($pk ?: $this->getState('audit.id'));

            $table = $this->getTable('Audit');
            $table->hit($pk);
        }

        return true;
    }
    
}
