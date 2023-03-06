<?php
/**
 * @package    com_iseo
 * @author     Pavel Lange <pavel@ilange.ru>
 * @link       https://github.com/i-lange/com_iseo
 * @copyright  (C) 2023 Pavel Lange <https://ilange.ru>
 * @license    GNU General Public License version 2 or later
 */

namespace Ilange\Component\Iseo\Site\View\Category;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\CategoryView;
use Ilange\Component\Iseo\Site\Helper\RouteHelper;

/**
 * HTML представление одиночной категории
 * @since 1.0.0
 */
class HtmlView extends CategoryView
{
    /**
     * @var string Имя расширения для категории
     * @since 1.0.0
     */
    protected $extension = 'com_iseo';

    /**
     * @var string Заголовок по умолчанию, используемый для заголовка страницы
     * @since 1.0.0
     */
    protected $defaultPageTitle = 'COM_ISEO_CATEGORY_RESULTS';

    /**
     * @var string Имя представления, с которым будут связаны аудиты в списке
     * @since 1.0.0
     */
    protected $viewName = 'audit';

    /**
     * Выполнение и отображение шаблона
     * @param string $tpl Имя файла шаблона для | автоматический поиск
     * @return void
     * @throws \Exception
     * @since 1.0.0
     */
    public function display($tpl = null)
    {
        parent::commonCategoryDisplay();

        // Флаг указывает не добавлять limitstart=0 к URL адресу
        $this->pagination->hideEmptyLimitstart = true;

        parent::display($tpl);
    }

    /**
     * Подготовка документа к выводу
     * @return void
     * @throws \Exception
     * @since 1.0.0
     */
    protected function prepareDocument()
    {
        parent::prepareDocument();

        if ($this->menuItemMatchCategory) {
            // If the active menu item is linked directly to the category being displayed, no further process is needed
            return;
        }

        // Get ID of the category from active menu item
        $menu = $this->menu;

        if (
            $menu && $menu->component == 'com_iseo' && isset($menu->query['view'])
            && in_array($menu->query['view'], ['categories', 'category'])
        ) {
            $id = $menu->query['id'];
        } else {
            $id = 0;
        }

        $path = [['title' => $this->category->title, 'link' => '']];
        $category = $this->category->getParent();

        while ($category !== null && $category->id !== 'root' && $category->id != $id) {
            $path[] = [
                'title' => $category->title,
                'link' => RouteHelper::getCategoryRoute($category->id, $category->language)
            ];
            $category = $category->getParent();
        }

        $path = array_reverse($path);

        foreach ($path as $item) {
            $this->pathway->addItem($item['title'], $item['link']);
        }
    }
}
