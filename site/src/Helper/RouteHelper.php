<?php
/**
 * @package    com_iseo
 * @author     Pavel Lange <pavel@ilange.ru>
 * @link       https://github.com/i-lange/com_iseo
 * @copyright  (C) 2023 Pavel Lange <https://ilange.ru>
 * @license    GNU General Public License version 2 or later
 */

namespace Ilange\Component\Iseo\Site\Helper;

use Joomla\CMS\Language\Multilanguage;

defined('_JEXEC') or die;

/**
 * Helper роутера компонента com_iseo
 * @since 1.0.0
 */
abstract class RouteHelper
{
    /**
     * Получаем маршрут одиночного аудита
     * @param int $id Идентификатор аудита
     * @param int $catid Идентификатор категории
     * @param string|null $language Код языка
     * @param string|null $layout Макет
     * @return string Построенный маршрут аудита
     * @since 1.0.0
     */
    public static function getAuditRoute(
        int $id,
        int $catid = 0,
        string $language = null,
        string $layout = null
    ): string {
        $link = 'index.php?option=com_iseo&view=audit&id=' . $id;

        if ($catid > 1) {
            $link .= '&catid=' . $catid;
        }

        if (!empty($language) && $language !== '*' && Multilanguage::isEnabled()) {
            $link .= '&lang=' . $language;
        }

        if ($layout) {
            $link .= '&layout=' . $layout;
        }

        return $link;
    }

    /**
     * Получаем маршрут списка аудитов
     * @param int $catid Идентификатор категории
     * @param string|null $language Код языка
     * @return string Построенный маршрут списка аудитов
     * @since 1.0.0
     */
    public static function getAuditsRoute(
        int $catid = 0,
        string $language = null,
        string $layout = null
    ): string {
        $link = 'index.php?option=com_iseo&view=audits';

        if ($catid > 1) {
            $link .= '&catid=' . $catid;
        }

        if (!empty($language) && $language !== '*' && Multilanguage::isEnabled()) {
            $link .= '&lang=' . $language;
        }

        if ($layout) {
            $link .= '&layout=' . $layout;
        }

        return $link;
    }

    /**
     * Получаем маршрут категории
     * @param int $catid Идентификатор категории
     * @param string|null $language Код языка
     * @param string|null $layout Макет
     * @return string Построенный маршрут категории
     * @since 1.0.0
     */
    public static function getCategoryRoute(
        int $catid,
        string $language = null,
        string $layout = null
    ) {
        if ($catid < 1) {
            return '';
        }

        $link = 'index.php?option=com_iseo&view=category&id=' . $catid;

        if ($language && $language !== '*' && Multilanguage::isEnabled()) {
            $link .= '&lang=' . $language;
        }

        if ($layout) {
            $link .= '&layout=' . $layout;
        }

        return $link;
    }

    /**
     * Получаем маршрут для Online аудита
     * @param int $uniqid Идентификатор аудита (Не primary key)
     * @param string|null $language Код языка
     * @param string|null $layout Макет
     * @return string Построенный маршрут
     * @since 1.0.0
     */
    public static function getOnlineRoute(
        string $uniqid = null,
        string $language = null,
        string $layout = null
    ): string {
        $link = 'index.php?option=com_iseo&view=online';

        if ($uniqid) {
            $link .= '&uniqid=' . $uniqid;
        }

        if (!empty($language) && $language !== '*' && Multilanguage::isEnabled()) {
            $link .= '&lang=' . $language;
        }

        if ($layout) {
            $link .= '&layout=' . $layout;
        }

        return $link;
    }
}