<?php
/**
 * @package    com_iseo
 * @author     Pavel Lange <pavel@ilange.ru>
 * @link       https://github.com/i-lange/com_iseo
 * @copyright  (C) 2023 Pavel Lange <https://ilange.ru>
 * @license    GNU General Public License version 2 or later
 */

use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Installer\InstallerAdapter;
use Joomla\CMS\Installer\InstallerScript;
use Joomla\CMS\Language\Text;

defined('_JEXEC') or die;

class Com_IseoInstallerScript extends InstallerScript
{
    /**
     * Название устанавливаемого расширения Joomla!
     * @var string
     * @since 1.0.0
     */
    protected $extension = 'com_iseo';
    
    /**
     * Минимальная версия PHP, необходимая для установки модуля
     * @var string
     * @since 1.0.0
     */
    protected $minimumPhp = '7.2';

    /**
     * Минимальная версия Joomla, необходимая для установки модуля
     * @var string
     * @since 1.0.0
     */
    protected $minimumJoomla = '4.2.0';

    /**
     * Список файлов, которые необходимо удалить
     * @var array
     * @since 1.0.0
     */
    protected $deleteFiles = [];

    /**
     * Список папок, которые необходимо удалить
     * @var array
     * @since 1.0.0
     */
    protected $deleteFolders = [];

    /**
     * Объект приложения
     * @var object
     * @since 1.0.0
     */
    protected $app = null;

    /**
     * DBO
     * @var object
     * @since 1.0.0
     */
    protected $db = null;

    /**
     * Конструктор
     * @throws Exception
     * @since 1.0.0
     */
    public function __construct()
    {
        // Получаем объект приложения
        $this->app = Factory::getApplication();
        
        // Получаем DBO
        $this->db = Factory::getContainer()->get('DatabaseDriver');
    }

    /**
     * Метод запускается непосредственно перед установкой/обновлением/удалением модуля
     * @param string $type Тип действия, которое выполняется (install|uninstall|discover_install|update)
     * @param InstallerAdapter $parent Класс, вызывающий этот метод.
     * @return bool Возвращает True для продолжения, False для отмены установки/обновления/удаления
     * @throws Exception
     * @since 1.0.0
     */
    public function preflight($type, $parent): bool
    {
        if (!parent::preflight($type, $parent)) {
            return false;
        }        

        // Проверяем, установлен ли модуль mod_iseo
        if ($type !== 'uninstall') {
            $text = ($type !== 'update') 
                ? 'Для отображения формы на сайте, необходимо установить модуль mod_iseo' 
                : Text::_('COM_ISEO_XML_NO_MOD_AUDIT');
            
            if (!file_exists(JPATH_SITE . '/modules/mod_iseo/')) {
                $this->app->enqueueMessage(
                    $text, 
                    'warning'
                );
            }
        }        

        return true;
    }

    /**
     * Метод запускается непосредственно после установки/обновления/удаления модуля
     * @param string $type Тип действия, которое выполняется (install|uninstall|discover_install|update)
     * @param InstallerAdapter $parent Класс, вызывающий этот метод.
     * @return bool True при успешном выполнении
     * @throws Exception
     * @since 1.0.0
     */
    public function postflight(string $type, InstallerAdapter $parent): bool
    {
        // Удаляем файлы и папки, в которых больше нет необходимости
        $this->removeFiles();

        if ($type === 'install' || $type === 'discover_install') {
            if (!$this->categoryCreate()) {
                echo Text::_('COM_ISEO_CATEGORY_ADD_ERROR');
            }
        } elseif ($type === 'update') {
            // Получаем данные из xml файла компонента
            $xml = $parent->getManifest();

            // Пишем сообщение со ссылками на сайт автора и на репозиторий
            $message[] = '<p class="fs-2 mb-2">' . Text::_('COM_ISEO') . ' [' . $xml->name . ']</p>';
            $message[] = '<ul>';
            $message[] = '<li>' . Text::_('COM_ISEO_VERSION') . ': ' . $xml->version . '</li>';
            $message[] = '<li>' . Text::_('COM_ISEO_AUTHOR') . ': ' . $xml->author . '</li>';
            $message[] = "<li><a href='https://ilange.ru' target='_blank'>https://ilange.ru</a></li>";
            $message[] = "<li><a href='https://github.com/i-lange/" . $xml->name . "' target='_blank'>GitHub</a></li>";
            $message[] = '</ul>';
            $message[] = '<p class="mb-2">' . Text::_('COM_ISEO_DONATE') . ': </p>';
            $message[] = "<a href='" . 
                Text::_('COM_ISEO_DONATE_URL') . "' target='_blank' class='btn btn-primary'>" . 
                Text::_('COM_ISEO_DONATE_BTN') . "</a>";

            // Объединяем все в строку
            $msgStr = implode($message);

            // Показываем сообщение
            echo $msgStr;
        } else {
            $this->app->enqueueMessage(
                Text::_('COM_ISEO_XML_UNINSTALL_OK'), 
                'warning'
            );
        }

        return true;
    }

    /**
     * Создает корневую категорию для списка результатов компонента
     * @return bool True при успешном выполнении
     * @throws Exception
     * @since 1.0.0
     */
    private function categoryCreate(): bool
    {
        $lang = $this->app->getLanguage()->getTag();
        $title = 'NoCategory';
        $alias = ApplicationHelper::stringURLSafe($title, $lang);
        $user_id = $this->getAdminId();

        // Инициализация полей новой категории
        $category = $this->app
            ->bootComponent('com_categories')
            ->getMVCFactory()
            ->createTable('Category', 'Administrator', ['dbo' => $this->db]);
        $data = [
            'extension' => $this->extension,
            'title' => $title,
            'alias' => $alias,
            'description' => '',
            'published' => 1,
            'access' => 1,
            'params' => '{"target":"","image":""}',
            'metadesc' => '',
            'metakey' => '',
            'metadata' => '{"author":"","robots":""}',
            'created_time' => Factory::getDate()->toSql(),
            'created_user_id' => $user_id,
            'language' => '*',
            'rules' => [],
            'parent_id' => 1,
        ];

        try {
            // Установка местоположения узла
            $category->setLocation(1, 'last-child');
            
            // Устанавливаем данные в таблицу
            $category->bind($data);
            
            // Проверяем, что данные корректны
            $category->check();

            // Сохраняем категорию            
             $category->store(true);
            
            if (!$category->id) {
                return false;
            }

            // Построение пути для категории
            $category->rebuildPath($category->id);
            
        } catch (Exception $e) {
            $this->app->enqueueMessage(
                Text::_('COM_ISEO_XML_CATEGORY_ADD_ERROR') . ' Error: ' . $e->getMessage(),
                'danger'
            );

            return false;
        }

        return true;
    }

    /**
     * Возвращает Id супер администратора
     * @return int Id пользователя при успешном выполнении
     * @since 1.0.0
     */
    private function getAdminId(): int
    {
        $db = $this->db;
        $query = $db->getQuery(true);

        // Выбираем все Id пользователей с правами администратора
        $query
            ->select($db->quoteName('user.id'))
            ->from($db->quoteName('#__users', 'user'))
            ->join(
                'LEFT',
                $db->quoteName('#__user_usergroup_map', 'map'),
                $db->quoteName('map.user_id') . ' = ' . $db->quoteName('user.id')
            )
            ->join(
                'LEFT',
                $db->quoteName('#__usergroups', 'grp'),
                $db->quoteName('map.group_id') . ' = ' . $db->quoteName('grp.id')
            )
            ->where(
                $db->quoteName('grp.title') . ' = ' . $db->quote('Super Users')
            );

        $db->setQuery($query);

        // Берем первый из найденных
        $id = $db->loadResult();        

        if (!$id || $id instanceof Exception) {
            return 0;
        }        

        return $id;
    }
}