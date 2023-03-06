<?php
/**
 * @package    com_iseo
 * @author     Pavel Lange <pavel@ilange.ru>
 * @link       https://github.com/i-lange/com_iseo
 * @copyright  (C) 2023 Pavel Lange <https://ilange.ru>
 * @license    GNU General Public License version 2 or later
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
use Ilange\Component\Iseo\Site\Helper\RouteHelper;

if (count($this->items[$this->parent->id]) > 0) : ?>
    <?php foreach ($this->items[$this->parent->id] as $id => $item) : ?>
        <div class="mt-3 mb-3">
            <a href="<?php echo Route::_(RouteHelper::getCategoryRoute($item->id, $item->language)); ?>">
                <?php echo $this->escape($item->title); ?>
            </a>
            <?php if ($item->getParams()->get('image')) : ?>
                <?php echo HTMLHelper::_(
                    'image',
                    $item->getParams()->get('image'),
                    $item->getParams()->get('image_alt')
                ); ?>
            <?php endif; ?>

            <?php if ($item->description) : ?>
                <p>
                    <?php echo HTMLHelper::_(
                        'content.prepare',
                        $item->description,
                        '',
                        'com_content.categories'
                    ); ?>
                </p>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
<?php endif; ?>