<?php
/**
 * @package    com_iseo
 * @author     Pavel Lange <pavel@ilange.ru>
 * @link       https://github.com/i-lange/com_iseo
 * @copyright  (C) 2023 Pavel Lange <https://ilange.ru>
 * @license    GNU General Public License version 2 or later
 */

namespace Ilange\Component\Iseo\Administrator\Helper;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Response\JsonResponse;

/**
 * Класс helper компонента
 * @since 1.0.0
 */
class IseoHelper
{
    /**
     * Адрес сервиса Page Speed
     * @var string
     * @since 1.0.0
     */
    protected static $pageSpeedOnlineUrl = 'https://www.googleapis.com/pagespeedonline/v5/runPagespeed';   
    
    /**
     * Список проверок сервиса Page Speed
     * @var string
     * @since 1.0.0
     */
    protected static $pageSpeedAudits = [
        'performance', 
        'accessibility', 
        'best_practices', 
        'seo', 
        'pwa', 
    ];

    /**
     * Паттерн для обработки ссылок размеченных с помощью Markdown
     * @var string
     * @since 1.0.0
     */
    protected static $pattern = '/\[(.*?)\]\((\S+:\/\/[^\s]+)\)/';
    
    /**
     * Возвращает список действий, которые могут быть выполнены
     * @return \stdClass
     * @throws \Exception
     * @since 1.0.0
     */
    public static function getActions(): \stdClass
    {
        $user = Factory::getApplication()->getIdentity();
        $result = new \stdClass();

        $actions = [
            'core.admin',
            'core.options',
            'core.manage',
            'core.create',
            'core.delete',
            'core.edit',
            'core.edit.state',
            'core.edit.own',
            'core.edit.value',
        ];

        foreach ($actions as $action) {
            $result->$action = $user->authorise($action, 'com_iseo');
        }

        return $result;
    }

    /**
     * Возвращает Id супер администратора
     * @return int Id пользователя при успешном выполнении
     * @since 1.0.0
     */
    private static function getAdminId(): int
    {
        $db = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true);

        // Выбираем все Id пользователей с правами администратора
        $query
            ->select($db->quoteName('user.id'))
            ->from($db->quoteName('#__users', 'user'))
            ->join(
                'LEFT',
                $db->quoteName('#__user_usergroup_map', 'map'),
                $db->quoteName('map.user_id') . ' = ' . $db->quoteName('user.id')
            )
            ->join(
                'LEFT',
                $db->quoteName('#__usergroups', 'grp'),
                $db->quoteName('map.group_id') . ' = ' . $db->quoteName('grp.id')
            )
            ->where(
                $db->quoteName('grp.title') . ' = ' . $db->quote('Super Users')
            );

        $db->setQuery($query);

        // Берем первый из найденных
        $id = $db->loadResult();

        if (!$id || $id instanceof \Exception) {
            return 0;
        }

        return $id;
    }

    /**
     * Возвращает id вновь созданного аудита
     * @param object|null $params Объект параметров
     * @return string Идентификатор uniqId нового аудита в базе данных
     * @throws \Exception
     * @since 1.0.0
     */
    public static function getNewAudit(object $params = null): string 
    {
        // Устанавливаем URL сервиса PageSpeed
        if ($tmp = $params->get('pagespeed', 0)) {
            $query = $tmp;
        } else {
            $query = self::$pageSpeedOnlineUrl;
        }

        // Устанавливаем URL страницы, которую будем тестировать
        if ($tmp = $params->get('newUrl', 0)) {
            $query .= '?url=' . $tmp;
        } else {
            return false;
        }

        // Устанавливаем API Key сервиса PageSpeed, если он указан
        if ($tmp = $params->get('api_key', 0)) {
            $query .= '&key=' . $tmp;
        }

        // Устанавливаем стратегию тестирования (мобильные устройства или desktop)
        if ($tmp = $params->get('strategy', 0)) {
            $query .= '&strategy=' . strtoupper($tmp);
        }

        // Устанавливаем список аудитов, которые необходимо провести
        foreach (self::$pageSpeedAudits as $audit) {
            if ($params->get($audit, 1)) {
                $query .= '&category=' . strtoupper($audit);
            }
        }

        $lang = Factory::getApplication()->getLanguage()->getTag();
        $query .= '&locale=' . $lang;

        // Отправляем запрос сервису PageSpeed
        $curl_handle = curl_init($query);
        curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl_handle, CURLOPT_HEADER, false);
        $json = curl_exec($curl_handle);
        curl_close($curl_handle);        

        // Отлавливаем ошибки
        if ($json === false) {
            throw new \Exception(curl_error($curl_handle), 403);
        }
        
        // Вызываем метод сохранения результата,
        // возвращаем полученный id записи, либо false
        return self::saveNewAudit($json);
    }

    /**
     * Возвращает id вновь созданного аудита на базе предоставленного URL
     * @param string $json Данные аудита, полученные с сервера PageSpeed
     * @return string Идентификатор uniqId нового аудита в базе данных
     * @throws \Exception
     * @since 1.0.0
     */
    public static function saveNewAudit(string $json): string
    {
        $app = Factory::getApplication();
        $report = json_decode($json);        

        if (!isset($report->id) || !$report->id) {
            return false;
        }        

        $audit_result = [];
        $audit_result['url'] = $report->lighthouseResult->finalUrl;        
        $audit_result['formFactor'] = $report->lighthouseResult->configSettings->formFactor;
        $audit_result['version'] = $report->lighthouseResult->lighthouseVersion;
        $audit_result['fetchTime'] = $report->lighthouseResult->fetchTime;
        $audit_result['userAgent'] = $report->lighthouseResult->userAgent;
        $audit_result['categories'] = [];
        $audit_result['mainAudits'] = [];
        $audit_result['otherAudits'] = [];
        $audit_result['infoAudits'] = [];
                
        foreach ($report->lighthouseResult->categories as $category) {
            $audit_result['categories'][$category->id] = [
                'title' => $category->title,
                'score' => $category->score,
            ];

            foreach ($category->auditRefs as $audit) {
                $curent = $report->lighthouseResult->audits->{$audit->id};                

                if (isset($audit->group)) {
                    if ($audit->group === 'metrics') {
                        $audit_result['mainAudits'][$curent->id] = self::getAuditArray($curent);                        
                    
                    } elseif (
                        isset($curent->scoreDisplayMode) && 
                        $curent->scoreDisplayMode !== 'notApplicable' &&
                        $audit->group !== 'hidden' &&
                        $curent->score < 1
                    ) {
                        $audit_result['infoAudits'][$curent->id] = self::getAuditArray($curent);
                    }
                
                } elseif ($curent->score !== null && $curent->score < 1) {
                    $audit_result['otherAudits'][$audit->id] = self::getAuditArray($curent);
                
                } elseif (isset($curent->scoreDisplayMode) && $curent->scoreDisplayMode === 'informative') {
                    $audit_result['infoAudits'][$audit->id] = self::getAuditArray($curent);
                }
            }
        }
        
        usort($audit_result['otherAudits'], [self::class, "compareAudits"]);

        $result = json_encode(
            $audit_result, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);        
        
        // Инициализация полей новой категории
        $table = $app->bootComponent('com_iseo')
            ->getMVCFactory()
            ->createTable('Audit');

        $uniqId = $table->getUniqAlias();
        $title = 'iSeo Audit #' . $uniqId;
        $alias = $uniqId;
        $userId = self::getAdminId();
        
        $data = [
            'catid' => 23,
            'title' => $title,
            'alias' => $alias,
            'state' => 1,
            'created' => Factory::getDate()->toSql(),
            'created_by' => $userId,
            'modified' => null,
            'modified_by' => null,
            'checked_out' => null,
            'checked_out_time' => null,
            'hits' => 0,
            'url' => $audit_result['url'],
            'result' => $result,
            'uniqid' => $uniqId,
        ];

        try {
            // Устанавливаем данные в таблицу
            $table->bind($data);

            // Проверяем, что данные корректны
            $table->check();

            // Сохраняем категорию            
            $table->store(true);

            if (!$table->id) {
                return false;
            }

        } catch (\Exception $e) {
            $app->enqueueMessage(
                Text::_('COM_ISEO_ERROR_AUDIT_SAVE') . ' Error: ' . $e->getMessage(),
                'danger'
            );

            return false;
        }

        return $uniqId;
    }

    /**
     * Ищет все ссылки размеченные Markdown и заменяет на html теги
     * @param string $data Строка для обработки
     * @return string Обработанная строка
     * @since 1.0.0
     */
    public static function parseMarkdown(string $data = ''): string {
        preg_match_all(self::$pattern, $data, $matches, PREG_SET_ORDER);

        if (count($matches) > 0) {
            foreach ($matches as $match) {
                $link_label = $match[1];
                $link_url = $match[2];

                $link_html = "<a href=\"$link_url\" target=\"_blank\" rel=\"noreferrer noopener\">$link_label</a>";
                $data = str_replace($match[0], $link_html, $data);
            }
        }
    
        return $data;
    }

    /**
     * Возвращает массив полей
     * @param object $audit Объект текущего аудита из ответа PageSpeed
     * @return array Ассоциативный массив полей
     * @since 1.0.0
     */
    public static function getAuditArray(object $audit): array {
        $result = [];
        
        $result['title'] = htmlspecialchars($audit->title);
        $result['score'] = $audit->score;
        $result['description'] = self::parseMarkdown(htmlspecialchars($audit->description));

        if (isset($audit->displayValue)) {
            $result['displayValue'] = htmlspecialchars($audit->displayValue);
        }

        return $result;
    }    
    
    /**
     * Возвращает результат сравнения полей
     * @param array $a Первый элемент сравнения
     * @param array $b Второй элемент сравнения
     * @return int Результат сравнения
     * @since 1.0.0
     */
    public static function compareAudits(array $a, array $b): int {
        return $a['score'] <=> $b['score'];
    }

    /**
     * Метод валидации полей формы
     * @param array $fields Массив полей для проверки
     * @return array Массив с двумя списками: 1. поля с ошибками; 2. без ошибок
     * @throws
     * @since 1.0.0
     */
    public static function validationFields(array $fields): array
    {
        $withError = [];
        $fullValid = [];

        foreach ($fields as $key => $value) {
            if (empty($value)) {
                $withError[] = self::setError($key, 'empty', 'COM_ISEO_ERROR_REQUIRED');
            } else {
                switch ($key) {
                    case 'url':
                        if (self::isUrl($value)) {
                            $fullValid[] = $key;
                        } else {
                            $withError[] = self::setError($key, $value, 'COM_ISEO_ERROR_REQUIRED_URL');
                        }
                        break;
                    case 'email':
                        if (filter_var($value, FILTER_VALIDATE_EMAIL)) {
                            $fullValid[] = $key;
                        } else {
                            $withError[] = self::setError($key, $value, 'COM_ISEO_ERROR_REQUIRED_EMAIL');
                        }
                        break;
                    default: $fullValid[] = $key;
                }
            }
        }

        return [$withError, $fullValid];
    }

    /**
     * Проверяет URL адрес на корректность и доступность
     * @param string $url URL, который необходимо проверить
     * @return bool True, если URL корректный и доступен
     * @since 1.0.0
     */
    public static function isUrl(string $url): bool
    {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return false;            
        }
        
        // Получаем массив составных частей URL
        $partsUrl = parse_url($url);
        
        // Если не указан протокол, или протокол некорректен
        if (!array_key_exists("scheme", $partsUrl) || !in_array($partsUrl["scheme"], ["http", "https"])) {
            return false;
        }

        // Если не указан host
        if (!array_key_exists("host", $partsUrl) || empty($partsUrl["host"])) {
            return false;
        }

        return true;
    }

    /**
     * Проверяет URL адрес на корректность и доступность
     * @param string $url URL, который необходимо проверить
     * @return bool True, если URL корректный и доступен
     * @since 1.0.0
     */
    public static function isUrlAvailable(string $url): bool
    {
        $options = [
            CURLOPT_URL            => $url,     // URL страницы
            CURLOPT_RETURNTRANSFER => true,     // Результат в виде строки вместо вывода в браузер
            CURLOPT_HEADER         => true,     // Включение заголовков в вывод
            CURLOPT_NOBODY         => true,     // Исключение тела ответа из вывода
            CURLOPT_FOLLOWLOCATION => true,     // Следовать всем перенаправлениям
            CURLOPT_MAXREDIRS      => 3,        // Макс. 10 перенаправлений
            CURLOPT_ENCODING       => "",       // Содержимое заголовка "Accept-Encoding: "
            CURLOPT_USERAGENT      => $_SERVER['HTTP_USER_AGENT'], // Содержимое заголовка "User-Agent: "
            CURLOPT_CONNECTTIMEOUT => 10,       // Количество секунд ожидания при попытке соединения
            CURLOPT_TIMEOUT        => 10,       // Максимум секунд на выполнение cURL-функций
        ];
        
        $curl_handle = curl_init();
        curl_setopt_array($curl_handle, $options);

        // Если не удалось соединиться
        if (curl_exec($curl_handle) === false) {
            curl_close($curl_handle);
            return false;
        }

        // Если были ошибки
        if (curl_error($curl_handle)) {            
            curl_close($curl_handle);
            return false;
        }

        // Если ответ сервера не удовлетворяет требованиям
        if (curl_getinfo($curl_handle, CURLINFO_HTTP_CODE) != '200') {
            curl_close($curl_handle);
            return false;
        }
        
        curl_close($curl_handle);

        return true;
    }

    /**
     * Возвращает массив ошибки валидации
     * @param string $key Имя поля, которое не прошло валидацию
     * @param string $value Значение поля
     * @param string $message Сообщение об ошибке
     * @return array Массив данных ошибки
     * @since 1.0.0
     */
    public static function setError(string $key, string $value, string $message): array
    {
        return [
            'key' => $key,
            'value' => $value,
            'error' => Text::_($message),
        ];
    }

    /**
     * Установка ответа на Ajax запрос
     * @param string $type Тип сообщения (success, warning, danger)
     * @param array $validations [0] - поля c ошибками; [1] - поля без ошибок
     * @param string $message Сообщение для вывода
     * @param string|null $redirect Ссылка для перенаправления, если аудит проведен успешно
     * @return void
     * @throws \Exception
     * @since 1.0.0
     */
    public static function setResponse(string $type, array $validations, string $message, string $redirect = null)
    {
        $app = Factory::getApplication();
        header('Content-Type: application/json');

        $response = [
            'type' => $type,
            'redirect' => $redirect,
            'errors' => $validations[0],
            'valid' => $validations[1],
        ];

        echo new JsonResponse($response, $message);
        $app->close();
    }
}