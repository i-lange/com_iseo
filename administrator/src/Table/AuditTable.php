<?php
/**
 * @package    com_iseo
 * @author     Pavel Lange <pavel@ilange.ru>
 * @link       https://github.com/i-lange/com_iseo
 * @copyright  (C) 2023 Pavel Lange <https://ilange.ru>
 * @license    GNU General Public License version 2 or later
 */

namespace Ilange\Component\Iseo\Administrator\Table;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseDriver;
use Joomla\CMS\Language\Text;
use Joomla\Utilities\ArrayHelper;

/**
 * Таблица с результатами аудитов
 * @since 1.0.0
 */
class AuditTable extends Table
{
	/**
	 * Конструктор
	 * @param DatabaseDriver $db
	 * @since 1.0.0
	 */
	public function __construct(DatabaseDriver $db)
	{
        $this->typeAlias = 'com_iseo.audit';
		parent::__construct('#__iseo_results', 'id', $db);
        
        // Установим псевдоним, так как колонка называется state
        $this->setColumnAlias('published', 'state');
	}

    /**
     * Метод привязки ассоциативного массива или объекта к экземпляру Table. 
     * Этот метод привязывает только public-свойства.
     * @param array|object $src Ассоциативный массив или объект
     * @param array|string $ignore Массив свойств, которые следует игнорировать, необязательно
     * @return bool True on success.
     * @since 1.0.0
     * @throws \InvalidArgumentException
     */
    public function bind($src, $ignore = [])
    {
        return parent::bind($src, $ignore);
    }

    /**
     * Переопределяем метод проверки данных
     * @return bool True при успехе, false при неудаче
     * @see Table::check()
     * @since 1.0.0
     */
    public function check()
    {
        parent::check();

        // Проверяем заголовок аудита
        if (trim($this->title) == '') {            
            return false;
        }

        // Проверяем псевдоним аудита
        if (trim($this->alias) == '') {
            $this->alias = $this->title;
        }

        $this->alias = ApplicationHelper::stringURLSafe($this->alias);

        if (trim(str_replace('-', '', $this->alias)) == '') {
            $this->alias = Factory::getDate()->format('Y-m-d-H-i-s');
        }

        // Проверяем идентификатор категории
        if (!$this->catid) {
            return false;
        }
        
        // Устанавливаем некоторые поля по умолчанию, если создается новый аудит
        if (!$this->id) {
            $this->hits = 0;
        }

        return true;
    }

    /**
     * Переопределяем метод сохранения данных
     * @param bool $updateNulls True для обновления полей, даже если они равны null.
     * @return bool True при успешном выполнении
     * @see Table::store()
     * @throws \Exception
     * @since 1.0.0
     */
    public function store($updateNulls = true)
    {
        $date = Factory::getDate()->toSql();
        $user = Factory::getApplication()->getIdentity();

        // Устанавливаем дату создания, если не установлена
        if (!(int) $this->created) {
            $this->created = $date;
        }

        if ($this->id) {
            // Устанавливаем автора изменений и дату редактирования
            $this->modified_by = $user->get('id');
            $this->modified    = $date;
        } else {
            // Поле автор может быть установлено пользователем, поэтому не трогаем его, если оно установлено
            if (empty($this->created_by)) {
                $this->created_by = $user->get('id');
            }

            // Установить дату изменения равной дате создания, если она не установлена
            if (!(int) $this->modified) {
                $this->modified = $this->created;
            }

            // Установим поле автора изменений равным создателю, если оно не установлено
            if (empty($this->modified_by)) {
                $this->modified_by = $this->created_by;
            }
        }

        // Проверяем, что псевдоним уникален, если нет - выводим сообщение
        if (!$this->isUniqAlias()) {
            Factory::getApplication()->enqueueMessage(
                Text::_('COM_ISEO_ERROR_UNIQUE_ALIAS'),
                'danger'
            );

            return false;
        }

        return parent::store($updateNulls);
    }

    /**
     * Возвращает уникальную строку для псевдонима
     * @return bool Уникален ли псевдоним записи
     * @throws \Exception
     * @since 1.0.0
     */
    public function isUniqAlias(string $alias = ''):bool
    {
        $alias = ($alias === '') ? $this->alias : $alias;
        $table = Factory::getApplication()
            ->bootComponent('com_iseo')
            ->getMVCFactory()
            ->createTable('Audit', 'Administrator', ['dbo' => $this->getDbo()]);

        // Проверяем среди всех записей, кроме элемента с тем же Id, если он существует
        if ($table->load(['alias' => $alias]) && 
            ((int)$table->id !== (int)$this->id || (int)$this->id === 0)) {

            return false;
        }

        return true;
    }
    
    /**
     * Возвращает уникальную строку для псевдонима
     * @return string
     * @throws \Exception
     * @since 1.0.0
     */
    public function getUniqAlias(): string
    {
        $id =
            bin2hex(random_bytes(2)) .
            uniqid() .
            bin2hex(random_bytes(2));

        $alias = ApplicationHelper::stringURLSafe(substr(md5($id), 1, 8));        

        if (!$this->isUniqAlias($alias)) {
            return $this->getUniqAlias();
        }

        return $alias;
    }
}
