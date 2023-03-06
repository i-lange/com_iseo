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
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;

$wa = $this->document->getWebAssetManager();
$wa->useScript('keepalive')->useScript('form.validate');

?>
<form action="<?php echo Route::_('index.php?option=com_iseo&view=audit&layout=edit&id=' . (int) $this->item->id); ?>"
	method="post" name="adminForm" id="audit-form" class="form-validate">
	<?php echo LayoutHelper::render('joomla.edit.title_alias', $this); ?>
    <div class="main-card">
		<?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', array('active' => 'details')); ?>
		<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'details', Text::_('COM_ISEO_TAB_RESULT')); ?>
        <div class="row">
            <div class="col-lg-9">
                <fieldset class="adminform">
                    <legend><?php echo Text::_('COM_ISEO_FIELDSET_RESULT'); ?></legend>
                    <div><?php echo $this->form->getInput('result'); ?></div>
                </fieldset>
            </div>
            <div class="col-lg-3">
                <?php echo LayoutHelper::render('joomla.edit.global', $this); ?>
            </div>
        </div>
		<?php echo HTMLHelper::_('uitab.endTab'); ?>

		<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'options', Text::_('COM_ISEO_TAB_OPTIONS')); ?>
        <div class="row">
            <div class="col-12 col-lg-6">
                <fieldset id="fieldset-metadata" class="options-form">
                    <legend><?php echo Text::_('COM_ISEO_FIELDSET_DATA'); ?></legend>
                    <div>
                        <?php echo $this->form->renderField('url'); ?>
                        <?php echo $this->form->renderField('uniqid'); ?>
                    </div>
                </fieldset>
            </div>
            <div class="col-12 col-lg-6">
                <fieldset id="fieldset-publishingdata" class="options-form">
                    <legend><?php echo Text::_('COM_ISEO_FIELDSET_OPTIONS'); ?></legend>
                    <div>
                        <?php echo LayoutHelper::render('joomla.edit.publishingdata', $this); ?>
                    </div>
                </fieldset>
            </div>
        </div>
		<?php echo HTMLHelper::_('uitab.endTab'); ?>
		<?php echo HTMLHelper::_('uitab.endTabSet'); ?>
	</div>
	<input type="hidden" name="task" value="">
	<?php echo HTMLHelper::_('form.token'); ?>
</form>