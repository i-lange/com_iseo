<?php
/**
 * @package    com_iseo
 * @author     Pavel Lange <pavel@ilange.ru>
 * @link       https://github.com/i-lange/com_iseo
 * @copyright  (C) 2023 Pavel Lange <https://ilange.ru>
 * @license    GNU General Public License version 2 or later
 */

namespace Ilange\Component\Iseo\Site\View\Audits;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Router\Route;
use Ilange\Component\Iseo\Site\Helper\RouteHelper;

/**
 * Класс представления для списка аудитов
 * @since 1.0.0
 */
class HtmlView extends BaseHtmlView
{
    /**
     * Состояние модели элемента
     * @var \Joomla\Registry\Registry
     * @since 1.0.0
     */
    protected $state;

    /**
     * Список аудитов
     * @var object
     * @since 1.0.0
     */
    protected $items;    

    /**
     * Параметры
     * @var \Joomla\Registry\Registry|null
     * @since 1.0.0
     */
    protected $params = null;

    /**
     * Объект постраничной навигации
     * @var object
     * @since 1.0.0
     */
    protected $pagination;

    /**
     * Выполнение и отображение шаблона
     * @param string $tpl Имя файла шаблона для | автоматический поиск
     * @return void
     * @throws \Exception
     * @since 1.0.0
     */
    public function display($tpl = null)
    {
        $this->state = $this->get('State');
        $this->items = $this->get('Items');
        $this->params = $this->get('Params');
        $this->pagination = $this->get('Pagination');
        $this->hits = $this->params->get('record_hits', 1);

        // Флаг указывает не добавлять limitstart=0 к URL адресу
        $this->pagination->hideEmptyLimitstart = true;

        // Проверяем, есть ли ошибки
        if (count($errors = $this->get('Errors'))) {
            throw new \Exception(implode("\n", $errors));
        }

        $this->_prepareDocument();

        parent::display($tpl);
    }

    /**
     * Подготовка документа к выводу
     * @return void
     * @throws \Exception
     * @since 1.0.0
     */
    protected function _prepareDocument()
    {
        $app = Factory::getApplication();
        $active = $app->getMenu()->getActive();        

        if ($active->component !== 'com_iseo') {
            $pathway = $app->getPathway();

            $breadcrumbTitle = Text::_('COM_ISEO_BREADCRUMB_RESULTS');
            if(!in_array($breadcrumbTitle, $pathway->getPathwayNames())) {
                $pathway->addItem($breadcrumbTitle, Route::_(RouteHelper::getAuditsRoute()));
            }

            $title = Text::_('COM_ISEO_BREADCRUMB_RESULTS');
            $this->params->set('show_page_heading', 1);
        } else {
            $title = $this->params->get('page_title', '');
        }

        $this->params->def('page_heading', Text::_('COM_ISEO_BREADCRUMB_RESULTS'));        

        if (empty($title)) {
            $title = $app->get('sitename');
        } elseif ($app->get('sitename_pagetitles', 0) == 1) {
            $title = Text::sprintf('JPAGETITLE', $app->get('sitename'), $title);
        } elseif ($app->get('sitename_pagetitles', 0) == 2) {
            $title = Text::sprintf('JPAGETITLE', $title, $app->get('sitename'));
        }

        $this->document->setTitle($title);

        if ($this->params->get('menu-meta_description')) {
            $this->document->setDescription($this->params->get('menu-meta_description'));
        }

        if ($this->params->get('robots')) {
            $this->document->setMetadata('robots', $this->params->get('robots'));
        }
    }
}
