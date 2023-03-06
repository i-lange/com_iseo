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
use Joomla\CMS\Router\Route;

if ($this->params->get('record_hits', 1)) {
    $this->getModel()->hit($this->item->id);
}

$wa = $this->document->getWebAssetManager();

if ($this->params->get('use_js')) {
    $wa->useScript('com_iseo.front.min');
}

if ($this->params->get('use_css')) {
    $wa->useStyle('com_iseo.front.min');
}
?>
<div class="card mb-5">
<?php if ($this->params->get('show_page_heading', 1)) : ?>
    <h1 class="card-header"><?php echo $this->params->get('page_heading'); ?></h1>
<?php endif; ?>
    <div class="com_iseo card-body">
        <form class=""
          id="i-seo-<?php echo $this->item->id; ?>"
          action="<?php echo Route::_('index.php', true); ?>"
          method="post"
          data-iseo-form>
        <fieldset class="mb-3">
            <label class="form-label"
                   for="url-<?php echo $this->item->id; ?>"><?php echo Text::_('COM_ISEO_AUTO_URL_LABEL'); ?></label>
            <input class="form-control"
                   id="url-<?php echo $this->item->id; ?>"
                   type="text"
                   name="url"
                   value="<?php echo $this->item->url; ?>"
                   placeholder="<?php echo Text::_('COM_ISEO_AUTO_URL_PLACEHOLDER'); ?>"
                   required
                   aria-describedby="url-help-<?php echo $this->item->id; ?>">
            <div class="invalid-feedback"><?php echo Text::_('COM_ISEO_AUTO_URL_INVALID'); ?></div>
            <div class="form-text"
                 id="url-help-<?php echo $this->item->id; ?>"><?php echo Text::_('COM_ISEO_AUTO_URL_HELP'); ?></div>
            <?php echo HTMLHelper::_('form.token'); ?>
        </fieldset>
        <button class="btn btn-primary mb-3"
                type="submit"><?php echo Text::_('COM_ISEO_AUTO_BTN'); ?></button>
        <div class="com_iseo_spinner">
            <div class="d-flex justify-content-center align-items-center h-100">
                <div class="spinner-border text-light" role="status">
                    <span class="visually-hidden"><?php echo Text::_('COM_ISEO_AUTO_LOADING'); ?></span>
                </div>
            </div>
        </div>
    </form>
    </div>
</div>
<div class="categories my-5">
    <div class="row justify-content-center text-center">
        <?php foreach ($this->item->result->categories as $category) : ?>
            <?php
            $scoreClass = 'danger';
            if ($category->score >= 0.5) $scoreClass = 'warning';
            if ($category->score >= 0.9) $scoreClass = 'success';
            ?>
            <div class="col-sm-6 col-lg-4 mb-3">
                <span class="d-inline-block h1 bg-light rounded-circle p-3 border border-5 border-<?php echo $scoreClass; ?> text-<?php echo $scoreClass; ?>">
                    <?php echo $category->score * 100; ?>
                </span><br/>
                <span class="fw-bold"><?php echo $category->title; ?></span>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<div class="head my-5">
    <table class="table">
        <tr>
            <th><?php echo Text::_('COM_ISEO_RESULTS_URL'); ?></th>
            <td><?php echo $this->item->url; ?></td>
        </tr>
        <tr>
            <th><?php echo Text::_('COM_ISEO_RESULTS_UNIQID'); ?></th>
            <td><?php echo $this->item->uniqid; ?></td>
        </tr>
        <tr>
            <th><?php echo Text::_('COM_ISEO_RESULTS_FORMFACTOR'); ?></th>
            <td><?php echo $this->item->result->formFactor; ?></td>
        </tr>
        <tr>
            <th><?php echo Text::_('COM_ISEO_RESULTS_VERSION'); ?></th>
            <td><?php echo $this->item->result->version; ?></td>
        </tr>
        <tr>
            <th><?php echo Text::_('COM_ISEO_RESULTS_FETCHTIME'); ?></th>
            <td><?php echo $this->item->result->fetchTime; ?></td>
        </tr>
        <tr>
            <th><?php echo Text::_('COM_ISEO_RESULTS_USERAGENT'); ?></th>
            <td><?php echo $this->item->result->userAgent; ?></td>
        </tr>
    </table>
</div>
<h2><?php echo Text::_('COM_ISEO_RESULTS_TITLE'); ?></h2>
<div class="mainAudits mb-5">
    <div class="row">
        <?php foreach ($this->item->result->mainAudits as $audit) : ?>
            <?php
            $scoreClass = 'danger';
            if ($audit->score >= 0.5) $scoreClass = 'warning';
            if ($audit->score >= 0.9) $scoreClass = 'success';
            ?>
            <div class="col-sm-6 mb-3">
                <span class="badge bg-<?php echo $scoreClass; ?> mr-1">&nbsp;</span>
                <span class="fw-bold"><?php echo $audit->title; ?></span><br/>
                <span class="h2 text-<?php echo $scoreClass; ?>"><?php echo $audit->displayValue; ?></span><br/>
                <?php echo $audit->description; ?>
            </div>
        <?php endforeach; ?>    
    </div>
</div>
<div class="otherAudits my-5">
    <table class="table">
        <?php foreach ($this->item->result->otherAudits as $audit) : ?>
            <?php 
            $scoreClass = 'danger';
            if ($audit->score >= 0.5) $scoreClass = 'warning';
            if ($audit->score >= 0.9) $scoreClass = 'success';
            ?>
            <tr>
                <td>
                    <span class="badge bg-<?php echo $scoreClass; ?> mr-1"><?php echo $audit->score * 100; ?></span>
                    <?php echo $audit->title; ?>
                    <?php if (isset($audit->displayValue)) : ?>
                        : <?php echo $audit->displayValue; ?>
                    <?php endif; ?>
                </td>
                <td><?php echo $audit->description; ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>
<h2><?php echo Text::_('COM_ISEO_RESULTS_INFO'); ?></h2>
<div class="infoAudits">
    <table class="table">
        <?php foreach ($this->item->result->infoAudits as $audit) : ?>
            <tr>
                <td>
                    <span class="badge bg-info mr-1">!</span>
                    <?php echo $audit->title; ?>
                    <?php if (isset($audit->displayValue)) : ?>
                        - <?php echo $audit->displayValue; ?>
                    <?php endif; ?>
                </td>
                <td><?php echo $audit->description; ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>