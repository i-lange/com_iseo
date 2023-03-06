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
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;

$wa = $this->document->getWebAssetManager();
$wa ->useStyle('com_iseo.admin')
    ->useScript('com_iseo.admin')
    ->useScript('table.columns')
    ->useScript('multiselect');

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));

$user = Factory::getApplication()->getIdentity();
$userId = $user->get('id');
?>
<form action="<?php echo Route::_('index.php?option=com_iseo&view=audits'); ?>" 
      method="post" 
      name="adminForm" 
      id="adminForm">
<div class="row">
<div class="col-md-12">
<div id="j-main-container" class="j-main-container">
<?php echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>
<?php if (empty($this->items)) : ?>
<div class="alert alert-info">
    <span class="fa fa-info-circle" aria-hidden="true"></span>
    <span class="sr-only"><?php echo Text::_('INFO'); ?></span>
    <?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
</div>
<?php else : ?>
<table class="table" id="auditsList">
    <caption class="visually-hidden">
        <?php echo Text::_('COM_ISEO_TITLE_RESULTS'); ?>,
        <span id="orderedBy"><?php echo Text::_('JGLOBAL_SORTED_BY'); ?></span>,
        <span id="filteredBy"><?php echo Text::_('JGLOBAL_FILTERED_BY'); ?></span>
    </caption>
    <thead>
    <tr>
        <td class="w-1 text-center">
            <?php echo HTMLHelper::_('grid.checkall'); ?>
        </td>
        <th scope="col" class="w-10 text-center">
            <?php echo HTMLHelper::_(
                    'searchtools.sort',
                    'JSTATUS',
                    'a.state', $listDirn, $listOrder
            ); ?>
        </th>
        <th scope="col" class="w-30 text-center">
            <?php echo HTMLHelper::_(
                    'searchtools.sort', 
                    'JGLOBAL_TITLE', 
                    'a.title', $listDirn, $listOrder); ?>
        </th>
        <th scope="col" class="w-15 d-none d-md-table-cell text-center">
            <?php echo HTMLHelper::_(
                    'searchtools.sort',
                    'JAUTHOR',
                    'a.created_by', $listDirn, $listOrder
            ); ?>
        </th>
        <th scope="col" class="w-25 text-center">
            <?php echo HTMLHelper::_(
                    'searchtools.sort',
                    'COM_ISEO_FIELD_UNIQID',
                    'a.uniqid', $listDirn, $listOrder
            ); ?>
        </th>
        <?php if ($this->hits) : ?>
        <th scope="col" class="w-10 d-none d-lg-table-cell text-center">
            <?php echo HTMLHelper::_(
                    'searchtools.sort', 
                    'JGLOBAL_HITS', 
                    'a.hits', $listDirn, $listOrder
            ); ?>
        </th>
        <?php endif; ?>
        <th scope="col" class="w-10 d-none d-lg-table-cell text-center">
            <?php echo HTMLHelper::_(
                    'searchtools.sort',
                    'JGRID_HEADING_ID',
                    'a.id', $listDirn, $listOrder
            ); ?>
        </th>
    </tr>
    </thead>
    <tbody>
    <?php
    $n = count($this->items);
    foreach ($this->items as $i => $item) :
        $canEdit = $user->authorise('core.edit', 'com_iseo');
        $canEditOwn = $user->authorise('core.edit.own', 'com_iseo') && $item->created_by == $userId;
        $canCheckin = $user->authorise('core.manage', 'com_iseo');
        $canChange = $user->authorise('core.edit.state', 'com_iseo');
        ?>
        <tr>
            <td class="text-center">
                <?php echo HTMLHelper::_('grid.id', $i, $item->id); ?>
            </td>
            <td class="text-center">
                <?php echo HTMLHelper::_(
                    'jgrid.published',
                    $item->state,
                    $i, 'audits.', $canChange, 'cb'
                ); ?>
            </td>
            <td>
                <?php if (isset($item->checked_out) && $item->checked_out && ($canEdit || $canChange)) : ?>
                    <?php echo HTMLHelper::_(
                        'jgrid.checkedout',
                        $i,
                        $item->created_by, $item->checked_out_time,
                        'audits.', $canCheckin
                    ); ?>
                <?php endif; ?>
                <?php if ($canEdit || $canEditOwn) : ?>
                    <a href="<?php echo Route::_('index.php?option=com_iseo&task=audit.edit&id=' . (int)$item->id); ?>" 
                       title="<?php echo Text::_('JACTION_EDIT'); ?> <?php echo $this->escape($item->title); ?>">
                        <?php echo $this->escape($item->title); ?>
                    </a>
                <?php else : ?>
                    <span><?php echo $this->escape($item->title); ?></span>
                <?php endif; ?>
                <div class="small break-word">
                    <?php echo Text::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($item->alias)); ?>
                </div>
                <?php echo $this->escape($item->url); ?>
                <div class="small">
                    <?php echo Text::_('JCATEGORY') . ': ' . $this->escape($item->category_title); ?>
                </div>
            </td>
            <td class="d-none d-md-table-cell small text-center">
                <?php if ($item->created_by !== '') : ?>
                    <?php echo $this->escape($item->created_by); ?>
                <?php else : ?>
                    <?php echo Text::_('JNONE'); ?>
                <?php endif; ?>
            </td>
            <td class="d-none d-lg-table-cell small text-center">
                <?php echo $item->uniqid; ?>
            </td>
            <?php if ($this->hits) : ?>
            <td class="d-none d-lg-table-cell text-center">
                <span class="badge bg-info">
                    <?php echo (int) $item->hits; ?>
                </span>
            </td>
            <?php endif; ?>
            <td class="d-none d-md-table-cell text-center">
                <?php echo $item->id; ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>						
</table>
<?php echo $this->pagination->getListFooter(); ?>
<?php endif; ?>
<input type="hidden" name="task" value="">
<input type="hidden" name="boxchecked" value="0">
<?php echo HTMLHelper::_('form.token'); ?>
</div>
</div>
</div>
</form>