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
use Joomla\CMS\Form\Form;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Language\Text;

/**
 * Модель одиночного аудита
 * @since 1.0.0
 */
class AuditModel extends AdminModel
{
    /**
     * Псевдоним типа для данного типа контента
     * @var string
     * @since 1.0.0
     */
    public $typeAlias = 'com_iseo.audit';
    
    /**
     * Префикс для языковых констант
     * @var string
     * @since 1.0.0
     */
    protected $text_prefix = 'COM_ISEO';

    /**
     * Возвращает ссылку на объект Table, всегда создавая его
     * @param string $name Тип таблицы
     * @param string $prefix Префикс для имени класса таблицы, необязательно
     * @param array $options Массив параметров для модели, необязательно
     * @return Table A database object
     * @throws \Exception
     * @since 1.0.0
     */
    public function getTable($name = 'Audit', $prefix = 'Table', $options = [])
    {
        return parent::getTable($name, $prefix, $options);
    }

    /**
     * Метод получения формы редактирования аудита
     * @param array $data Данные для формы
     * @param bool $loadData True, если форма должна загружать свои собственные данные (по умолчанию), false - если нет
     * @return Form|bool Объект формы при успехе, false при неудаче
     * @throws \Exception
     * @since 1.0.0
     */
    public function getForm($data = [], $loadData = true)
    {
        // Получаем форму аудита
        $form = $this->loadForm(
            'com_iseo.audit', 
            'audit', 
            ['control' => 'jform', 'load_data' => $loadData]);

        if (empty($form)) {
            return false;
        }

        return $form;
    }

    /**
     * Метод для получения данных, которые должны быть введены в форму
     * @return mixed Данные для формы
     * @throws \Exception
     * @since 1.0.0
     */
    protected function loadFormData()
    {
        // Проверяем сессию на наличие ранее введенных данных формы.
        $data = Factory::getApplication()->getUserState('com_iseo.edit.audit.data', []);

        if (empty($data)) {
            $data = $this->getItem();            
        }

        $this->preprocessData('com_iseo.audit', $data);

        return $data;
    }

    /**
     * Метод получения одной записи
     * @param int $pk Идентификатор
     * @return object|bool Object при успехе, false при неудаче
     * @throws \Exception
     * @since 1.0.0
     */
    public function getItem($pk = null)
    {
        if ($item = parent::getItem($pk)) {
            if ($item->id === null) {
                $uniqId = $this->getTable()->getUniqAlias();
                
                $item->title = Text::_('COM_ISEO_FIELDSET_RESULT') . ' #' . $uniqId;
                $item->alias = $item->uniqid = $uniqId;
            }
        }

        return $item;
    }
}