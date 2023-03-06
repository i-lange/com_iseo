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
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

$wa = $this->document->getWebAssetManager();
$wa->useScript('table.columns');

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
?>
<?php if ($this->params->get('show_page_heading', 1)) : ?>
    <h1><?php echo $this->params->get('page_heading'); ?></h1>
<?php endif; ?>
<form action="<?php echo htmlspecialchars(Uri::getInstance()->toString()); ?>"
      method="post"
      name="adminForm"
      id="adminForm">
    <div class="table-responsive">
        <table class="table table-striped" id="auditsList">
            <caption class="visually-hidden">
                <?php echo Text::_('COM_ISEO_TITLE_RESULTS'); ?>,
                <span id="orderedBy"><?php echo Text::_('JGLOBAL_SORTED_BY'); ?></span>,
                <span id="filteredBy"><?php echo Text::_('JGLOBAL_FILTERED_BY'); ?></span>
            </caption>
            <thead>
            <tr>
                <th scope="col" class="w-10 d-none d-md-table-cell text-center">
                    <?php echo HTMLHelper::_(
                            'grid.sort', 
                            'JGRID_HEADING_ID', 
                            'a.id', $listDirn, $listOrder); ?>
                </th>
                <th scope="col" class="w-10 d-none d-md-table-cell text-center">
                    <?php echo HTMLHelper::_(
                            'grid.sort', 
                            'JPUBLISHED', 
                            'a.state', $listDirn, $listOrder); ?>
                </th>
                <th scope="col" class="w-30 text-center">
                    <?php echo HTMLHelper::_(
                            'grid.sort', 
                            'JGLOBAL_TITLE', 
                            'a.title', $listDirn, $listOrder); ?>
                </th>
                <th scope="col" class="w-30 text-center">
                    <?php echo HTMLHelper::_(
                            'grid.sort',
                            'COM_ISEO_RESULTS_URL',
                            'a.url', $listDirn, $listOrder
                    ); ?>
                </th>
                <th scope="col" class="w-10 text-center">
                    <?php echo HTMLHelper::_(
                            'grid.sort',
                            'COM_ISEO_RESULTS_UNIQID',
                            'a.uniqid', $listDirn, $listOrder
                    ); ?>
                </th>
                <?php if ($this->hits) : ?>
                <th scope="col" class="w-10 d-none d-md-table-cell text-center">
                    <?php echo HTMLHelper::_(
                            'grid.sort',
                            'JGLOBAL_HITS',
                            'a.hits', $listDirn, $listOrder
                        ); ?>
                    </th>
                <?php endif; ?>
            </tr>
            </thead>
            <tbody><?php echo $this->loadTemplate('items'); ?></tbody>
        </table>
    </div>
    <div class="pagination">
        <?php echo $this->pagination->getPagesLinks(); ?>
    </div>
    <input type="hidden" name="task" value=""/>
    <input type="hidden" name="filter_order" value=""/>
    <input type="hidden" name="filter_order_Dir" value=""/>
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
