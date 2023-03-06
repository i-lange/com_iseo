<?php
/**
 * @package    com_iseo
 * @author     Pavel Lange <pavel@ilange.ru>
 * @link       https://github.com/i-lange/com_iseo
 * @copyright  (C) 2023 Pavel Lange <https://ilange.ru>
 * @license    GNU General Public License version 2 or later
 */

namespace Ilange\Component\Iseo\Site\Model;

defined('_JEXEC') or die;

use Joomla\CMS\Categories\Categories;
use Joomla\CMS\Categories\CategoryNode;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Registry\Registry;

/**
 * This models supports retrieving lists of article categories.
 * @since 1.0.0
 */
class CategoriesModel extends ListModel
{
    /**
     * Model context string.
     * @var string
     * @since 1.0.0
     */
    public $_context = 'com_iseo.categories';

    /**
     * The category context (allows other extensions to derive from this model).
     * @var string
     * @since 1.0.0
     */
    protected $_extension = 'com_iseo';

    /**
     * Parent category of the current one
     * @var CategoryNode|null
     * @since 1.0.0
     */
    private $_parent = null;

    /**
     * Method to auto-populate the model state.
     * Note. Calling getState in this method will result in recursion.
     * @param string $ordering The field to order on.
     * @param string $direction The direction to order on.
     * @return void
     * @throws \Exception
     * @since 1.0.0
     */
    protected function populateState($ordering = null, $direction = null)
    {
        $app = Factory::getApplication();
        $this->setState('filter.extension', $this->_extension);

        // Get the parent id if defined.
        $parentId = $app->input->getInt('id');
        $this->setState('filter.parentId', $parentId);

        $params = $app->getParams();
        $this->setState('params', $params);

        $this->setState('filter.published', 1);
        $this->setState('filter.access', true);
    }

    /**
     * Method to get a store id based on model configuration state.
     * This is necessary because the model is used by the component and
     * different modules that might need different sets of data or different
     * ordering requirements.
     * @param string $id A prefix for the store id.
     * @return string A store id.
     * @since 1.0.0
     */
    protected function getStoreId($id = '')
    {
        // Compile the store id.
        $id .= ':' . $this->getState('filter.extension');
        $id .= ':' . $this->getState('filter.published');
        $id .= ':' . $this->getState('filter.access');
        $id .= ':' . $this->getState('filter.parentId');

        return parent::getStoreId($id);
    }

    /**
     * Redefine the function and add some properties to make the styling easier
     * @param bool $recursive True if you want to return children recursively.
     * @return mixed An array of data items on success, false on failure.
     * @throws \Exception
     * @since 1.0.0
     */
    public function getItems(bool $recursive = false)
    {
        $store = $this->getStoreId();

        if (!isset($this->cache[$store])) {
            $app = Factory::getApplication();
            $menu = $app->getMenu();
            $active = $menu->getActive();

            if ($active) {
                $params = $active->getParams();
            } else {
                $params = new Registry();
            }

            $options = [];
            $options['countItems'] = $params->get('show_cat_num_audits', 1) || !$params->get(
                    'show_empty_categories',
                    0
                );
            //$categories = Categories::getInstance('Ideo', $options);
            $categories = Factory::getApplication()->bootComponent('com_iseo')->getCategory();
            $this->_parent = $categories->get($this->getState('filter.parentId', 'root'));

            if (is_object($this->_parent)) {
                $this->cache[$store] = $this->_parent->getChildren($recursive);
            } else {
                $this->cache[$store] = false;
            }
        }

        return $this->cache[$store];
    }

    /**
     * Get the parent
     * @return object An array of data items on success, false on failure.
     * @throws \Exception
     * @since 1.0.0
     */
    public function getParent()
    {
        if (!is_object($this->_parent)) {
            $this->getItems();
        }

        return $this->_parent;
    }
}
