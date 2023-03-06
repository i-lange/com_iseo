<?php
/**
 * @package    com_iseo
 * @author     Pavel Lange <pavel@ilange.ru>
 * @link       https://github.com/i-lange/com_iseo
 * @copyright  (C) 2023 Pavel Lange <https://ilange.ru>
 * @license    GNU General Public License version 2 or later
 */

namespace Ilange\Component\Iseo\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\AdminController;

/**
 * Класс контроллера списка аудитов
 * @since 1.0.0
 */
class AuditsController extends AdminController
{
    /**
     * Прокси метод для метода getModel
     * @param string $name Имя модели, необязательно
     * @param string $prefix Префикс класса, необязательно
     * @param array $config Массив параметров, необязательно
     * @return object Возвращает модель
     * @since 1.0.0
     */
    public function getModel($name = 'Audit', $prefix = 'Administrator', $config = ['ignore_request' => true]): object
    {
        return parent::getModel($name, $prefix, $config);
    }
}