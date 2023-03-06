<?php
/**
 * @package    com_iseo
 * @author     Pavel Lange <pavel@ilange.ru>
 * @link       https://github.com/i-lange/com_iseo
 * @copyright  (C) 2023 Pavel Lange <https://ilange.ru>
 * @license    GNU General Public License version 2 or later
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Ilange\Component\Iseo\Site\Helper\RouteHelper;

$user = Factory::getApplication()->getIdentity();
$groups = $user->getAuthorisedViewLevels();


?>
<?php if ($this->children[$this->category->id] > 0) : ?>
<h3><?php echo Text::_('COM_ISEO_SUBCATEGORIES'); ?></h3>
<?php foreach ($this->children[$this->category->id] as $id => $child) : ?>
   
    <?php if (in_array($child->access, $groups)) : ?>
        <div class="mt-3 mb-3">
            <a href="<?php echo Route::_(RouteHelper::getCategoryRoute($child->id, $child->language)); ?>">
                <?php echo $this->escape($child->title); ?>
            </a>
            <?php if ($child->description) : ?>
                <p><?php echo HTMLHelper::_('content.prepare', $child->description, '', 'com_iseo.category'); ?></p>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    
<?php endforeach; ?>
<?php endif; ?>
