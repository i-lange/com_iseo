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
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\ItemModel;
use Joomla\Utilities\ArrayHelper;

/**
 * Модель одиночного аудита
 * @since 1.0.0
 */
class AuditModel extends ItemModel
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

        // Состояние публикации
        $this->setState('filter.published', 1);
        $this->setState('filter.archived', 2);

        // Устанавливаем параметры из запроса
        $pk = $app->input->getInt('id');
        $this->setState('audit.id', $pk);

        // Загружаем параметры компонента
        $params = $app->getParams();
        $this->setState('params', $params);
    }

    /**
     * Метод получения данных аудита
     * @param int $pk Идентификатор аудита
     * @return object|bool Объект данных аудита при успехе, иначе false
     * @throws \Exception
     * @since 1.0.0
     */
    public function getItem($pk = null)
    {
        $pk = (int) ($pk ?: $this->getState('audit.id'));
        $item = false;

        $table = $this->getTable();
        if ($table->load($pk)) {
            if ($table->state !== $this->getState('filter.published') &&
                $table->state !== $this->getState('filter.archived')) {

                throw new \Exception(Text::_('COM_ISEO_ERROR_AUDIT_NOT_FOUND'), 403);
            }

            $item = ArrayHelper::toObject($table->getProperties(1));
        }

        return $item;
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

            $table = $this->getTable();
            $table->hit($pk);
        }

        return true;
    }
    
}
