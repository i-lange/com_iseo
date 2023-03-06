<?php
/**
 * @package    com_iseo
 * @author     Pavel Lange <pavel@ilange.ru>
 * @link       https://github.com/i-lange/com_iseo
 * @copyright  (C) 2023 Pavel Lange <https://ilange.ru>
 * @license    GNU General Public License version 2 or later
 */

namespace Ilange\Component\Iseo\Administrator\View\Audit;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Ilange\Component\Iseo\Administrator\Helper\IseoHelper;

/**
 * Класс представления для редактирования аудита
 * @since 1.0.0
 */
class HtmlView extends BaseHtmlView
{
    /**
     * Объект формы
     * @var \Joomla\CMS\Form\Form
     * @since 1.0.0
     */
    protected $form;

    /**
     * Текущий аудит
     * @var object
     * @since 1.0.0
     */
    protected $item;

    /**
     * Состояние модели
     * @var object
     * @since 1.0.0
     */
    protected $state;

    /**
     * Действия, которые может выполнить пользователь
     * @var \Joomla\CMS\Object\CMSObject
     * @since 1.0.0
     */
    protected $canDo;

    /**
     * Выполнение и отображение шаблона
     * @param string $tpl Имя файла шаблона
     * @throws \Exception
     * @since 1.0.0
     */
    public function display($tpl = null)
    {
        $this->form = $this->get('Form');
        $this->item = $this->get('Item');
        $this->state = $this->get('State');

        if (count($errors = $this->get('Errors'))) {
            throw new GenericDataException(implode("\n", $errors), 500);
        }

        $this->addToolbar();

        parent::display($tpl);
    }

    /**
     * Добавляем заголовок и панель инструментов
     * @return void
     * @throws \Exception
     * @since 1.0.0
     */
    protected function addToolbar()
    {
        Factory::getApplication()->input->set('hidemainmenu', true);

        $user       = Factory::getApplication()->getIdentity();
        $userId     = $user->id;
        $isNew      = ($this->item->id == 0);
        $checkedOut = !(is_null($this->item->checked_out) || $this->item->checked_out == $userId);
        $canDo      = IseoHelper::getActions();
        $editOwn    = $canDo->{'core.edit.own'} && ($this->item->created_by == $userId);

        ToolbarHelper::title(
            Text::_('COM_ISEO_' .
                ($checkedOut ? 'VIEW' : ($isNew ? 'ADD' : 'EDIT')) .
                '_RESULT'),
            'pencil-alt article-add'
        );

        $toolbarButtons = [];

        // Build the actions for new and existing records.
        if ($isNew && ($canDo->{'core.create'})) {
            ToolbarHelper::apply('audit.apply');

            $toolbarButtons[] = ['save', 'audit.save'];
            $toolbarButtons[] = ['save2new', 'audit.save2new'];

            ToolbarHelper::saveGroup($toolbarButtons);
            ToolbarHelper::cancel('audit.cancel');

        } else {
            if ($canDo->{'core.edit'} || $editOwn) {
                ToolbarHelper::apply('audit.apply');
                $toolbarButtons[] = ['save', 'audit.save'];

                if ($canDo->{'core.create'}) {
                    $toolbarButtons[] = ['save2new', 'audit.save2new'];
                }
            }

            if ($canDo->{'core.create'}) {
                $toolbarButtons[] = ['save2copy', 'audit.save2copy'];
            }

            ToolbarHelper::saveGroup($toolbarButtons);
            ToolbarHelper::cancel('audit.cancel', 'JTOOLBAR_CLOSE');
        }

        ToolbarHelper::help('Articles:_Edit');
    }
}
