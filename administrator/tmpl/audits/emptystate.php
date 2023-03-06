<?php
/**
 * @package    com_iseo
 * @author     Pavel Lange <pavel@ilange.ru>
 * @link       https://github.com/i-lange/com_iseo
 * @copyright  (C) 2023 Pavel Lange <https://ilange.ru>
 * @license    GNU General Public License version 2 or later
 */

use Joomla\CMS\Factory;
use Joomla\CMS\Layout\LayoutHelper;

defined('_JEXEC') or die;

$displayData = [
    'textPrefix' => 'COM_ISEO',
    'formURL' => 'index.php?option=com_iseo',
    'helpURL' => 'https://docs.joomla.org/Special:MyLanguage/Adding_a_new_article',
    'icon' => 'icon-copy',
];

$user = Factory::getApplication()->getIdentity();

if ($user->authorise('core.create', 'com_iseo')
    || count($user->getAuthorisedCategories('com_iseo', 'core.create')) > 0) {

    $displayData['createURL'] = 'index.php?option=com_iseo&task=audit.add';
}

echo LayoutHelper::render('joomla.content.emptystate', $displayData);