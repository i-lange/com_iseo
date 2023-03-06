<?php
/**
 * @package    com_iseo
 * @author     Pavel Lange <pavel@ilange.ru>
 * @link       https://github.com/i-lange/com_iseo
 * @copyright  (C) 2023 Pavel Lange <https://ilange.ru>
 * @license    GNU General Public License version 2 or later
 */

namespace Ilange\Component\Iseo\Site\Service;

use Joomla\CMS\Categories\Categories;

defined('_JEXEC') or die;

/**
 * Дерево категорий для аудитов
 * @since 1.0.0
 */
class Category extends Categories
{
    /**
     * Конструктор
     * @param array $options Массив параметров
     * @since 1.0.0
     */
    public function __construct($options = [])
    {
        $options['table'] = '#__iseo_results';
        $options['extension'] = 'com_iseo';

        parent::__construct($options);
    }
}
