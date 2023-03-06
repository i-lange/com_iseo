<?php
/**
 * @package    com_iseo
 * @author     Pavel Lange <pavel@ilange.ru>
 * @link       https://github.com/i-lange/com_iseo
 * @copyright  (C) 2023 Pavel Lange <https://ilange.ru>
 * @license    GNU General Public License version 2 or later
 */

defined('_JEXEC') or die;

use Joomla\CMS\Router\Route;
use Ilange\Component\Iseo\Site\Helper\RouteHelper;
?>
<?php foreach ($this->items as $item) : ?>
    <?php $isPublish = ($item->state === 1) || $item->state === 2; ?>
    <tr>
        <td class="d-none d-md-table-cell text-center"><?php echo $item->id; ?></td>
        <td class="d-none d-md-table-cell text-center">
            <?php if ($isPublish): ?>
                <i class="icon-publish"></i>
            <?php else: ?>
                <i class="icon-unpublish"></i>
            <?php endif; ?>
        </td>
        <td>
            <?php if ($isPublish): ?>
                <a href="<?php echo Route::_(RouteHelper::getAuditRoute((int)$item->id, (int)$item->catid)); ?>">
                    <?php echo $this->escape($item->title); ?>
                </a>
            <?php else: ?>
                <?php echo $item->title; ?>
            <?php endif; ?>
        </td>
        <td class="small"><?php echo $item->url; ?></td>
        <td class="small text-center"><?php echo $item->uniqid; ?></td>
        <?php if ($this->hits) : ?>
        <td class="d-none d-md-table-cell text-center">
            <span class="badge bg-info">
                <?php echo (int) $item->hits; ?>
            </span>
        </td>
        <?php endif; ?>
    </tr>
<?php endforeach; ?>