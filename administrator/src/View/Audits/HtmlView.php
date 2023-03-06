<?php
/**
 * @package    com_iseo
 * @author     Pavel Lange <pavel@ilange.ru>
 * @link       https://github.com/i-lange/com_iseo
 * @copyright  (C) 2023 Pavel Lange <https://ilange.ru>
 * @license    GNU General Public License version 2 or later
 */

namespace Ilange\Component\Iseo\Administrator\View\Audits;

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;

/**
 * Класс отображения списка результатов
 * @since 1.0.0
 */
class HtmlView extends BaseHtmlView
{
    /**
     * Массив результатов
     * @var array
     * @since 1.0.0
     */
    protected $items;

    /**
     * Постраничная навигация
     * @var \Joomla\CMS\Pagination\Pagination
     * @since 1.0.0
     */
    protected $pagination;

    /**
     * Состояние модели
     * @var \Joomla\CMS\Object\CMSObject
     * @since 1.0.0
     */
    protected $state;

    /**
     * Объект формы для фильтров поиска
     * @var \Joomla\CMS\Form\Form
     * @since 1.0.0
     */
    public $filterForm;

    /**
     * Активные фильтры поиска
     * @var array
     * @since 1.0.0
     */
    public $activeFilters;

    /**
     * Является ли это представление пустым
     * @var bool
     * @since 1.0.0
     */
    public $isEmptyState = false;

    /**
     * Отображать ли просмотры
     * @var bool
     * @since 1.0.0
     */
    public $hits = false;

    /**
     * Отображение шаблона вывода
     * @param string $tpl Template name
     * @return void
     * @throws \Exception
     * @since 1.0.0
     */
    public function display($tpl = null)
    {
        $this->items = $this->get('Items');
        $this->pagination = $this->get('Pagination');
        $this->state = $this->get('State');
        $this->filterForm = $this->get('FilterForm');
        $this->activeFilters = $this->get('ActiveFilters');
        $this->hits = ComponentHelper::getParams('com_iseo')->get('record_hits', 1);

        if (!count($this->items) && $this->isEmptyState = $this->get('IsEmptyState')) {
            $this->setLayout('emptystate');
        }
        
        // Проверяем наличие ошибок
        if (count($errors = $this->get('Errors'))) {
            throw new GenericDataException(implode("\n", $errors), 500);
        }

        $this->addToolbar();

        parent::display($tpl);
    }

    /**
     * Добавляем заголовок страницы и панель инструментов
     * @return void
     * @since 1.0.0
     */
    protected function addToolbar()
    {
        $toolbar = Toolbar::getInstance();

        ToolbarHelper::title(Text::_('COM_ISEO_TITLE_RESULTS'));

        $canDo = ContentHelper::getActions('com_iseo');

        if ($canDo->get('core.create')) {
            $toolbar->addNew('audit.add');
        }

        if ($canDo->get('core.edit.state')) {
            $dropdown = $toolbar->dropdownButton('status-group')
                ->text('JTOOLBAR_CHANGE_STATUS')
                ->toggleSplit(false)
                ->icon('icon-ellipsis-h')
                ->buttonClass('btn btn-action')
                ->listCheck(true);

            $childBar = $dropdown->getChildToolbar();

            $childBar->publish('audits.publish')->listCheck(true);

            $childBar->unpublish('audits.unpublish')->listCheck(true);

            $childBar->archive('audits.archive')->listCheck(true);

            if ($this->state->get('filter.published') != -2) {
                $childBar->trash('audits.trash')->listCheck(true);
            }
        }

        if ($this->state->get('filter.published') == -2 && $canDo->get('core.delete')) {
            $toolbar->delete('audits.delete')
                ->text('JTOOLBAR_EMPTY_TRASH')
                ->message('JGLOBAL_CONFIRM_DELETE')
                ->listCheck(true);
        }

        if ($canDo->get('core.create')) {
            $toolbar->preferences('com_iseo');
        }
    }
}
