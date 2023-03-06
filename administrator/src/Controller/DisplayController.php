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

use Joomla\CMS\MVC\Controller\BaseController;

/**
 * Контроллер отображения компонента com_iseo
 * @since 1.0.0
 */
class DisplayController extends BaseController
{
    /**
     * Шаблон вывода по умолчанию
     * @var string
     * @since 1.0.0
     */
    protected $default_view = 'audits';

    /**
     * Метод отображения шаблона вывода
     * @param bool $cachable Если true, вывод будет кэшироваться
     * @param array $urlparams Массив параметров URL и их типов, см. {@link InputFilter::clean()}.
     * @return DisplayController
     * @throws \Exception
     * @since 1.0.0
     */
    public function display($cachable = false, $urlparams = []): DisplayController
    {
        return parent::display($cachable, $urlparams);
    }
}
