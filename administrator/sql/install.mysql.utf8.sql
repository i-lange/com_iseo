/*
 * @package    com_iseo
 * @author     Pavel Lange <pavel@ilange.ru>
 * @link       https://github.com/i-lange/com_iseo
 * @copyright  (C) 2023 Pavel Lange <https://ilange.ru>
 * @license    GNU General Public License version 2 or later
 */

--
-- Структура таблицы `#__iseo_results`
--

CREATE TABLE IF NOT EXISTS `#__iseo_results`
(
    `id`               int          UNSIGNED    NOT NULL    AUTO_INCREMENT,
    `catid`            int          UNSIGNED    NOT NULL    DEFAULT 0,
    `title`            varchar(100)             NOT NULL    DEFAULT '',
    `alias`            varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '',
    `state`            tinyint                  NOT NULL    DEFAULT 1,
    `created`          datetime                 NOT NULL,
    `created_by`       int          UNSIGNED    NOT NULL    DEFAULT 0,
    `modified`         datetime                 NOT NULL,
    `modified_by`      int          UNSIGNED    NOT NULL    DEFAULT 0,
    `checked_out`      int          UNSIGNED                DEFAULT NULL,
    `checked_out_time` datetime                             DEFAULT NULL,
    `hits`             int          UNSIGNED    NOT NULL    DEFAULT 0       COMMENT 'Количество просмотров', 
    `url`              varchar(255)             NOT NULL                    COMMENT 'URL проверяемой страницы',
    `result`           text                     NOT NULL                    COMMENT 'Результат проверки',
    `uniqid`           varchar(20)              NOT NULL                    COMMENT 'Уникальный Id для доступа к результатам с фронтенда',
    PRIMARY KEY (`id`),
    KEY `idx_catid` (`catid`),
    KEY `idx_checkout` (`checked_out`),
    KEY `idx_state` (`state`),
    KEY `idx_createdby` (`created_by`),
    KEY `idx_alias` (`alias`),
    KEY `idx_uniqid` (`uniqid`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;