<?php
/**
 * @package    com_iseo
 * @author     Pavel Lange <pavel@ilange.ru>
 * @link       https://github.com/i-lange/com_iseo
 * @copyright  (C) 2023 Pavel Lange <https://ilange.ru>
 * @license    GNU General Public License version 2 or later
 */

namespace Ilange\Component\Iseo\Site\View\Categories;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\CategoriesView;

/**
 * Content categories view.
 * @since 1.0.0
 */
class HtmlView extends CategoriesView
{
    /**
     * Language key for default page heading
     * @var string
     * @since 1.0.0
     */
    protected $pageHeading = 'JGLOBAL_ARTICLES';

    /**
     * @var string The name of the extension for the category
     * @since 1.0.0
     */
    protected $extension = 'com_iseo';
}