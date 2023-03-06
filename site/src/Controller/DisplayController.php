<?php
/**
 * @package    com_iseo
 * @author     Pavel Lange <pavel@ilange.ru>
 * @link       https://github.com/i-lange/com_iseo
 * @copyright  (C) 2023 Pavel Lange <https://ilange.ru>
 * @license    GNU General Public License version 2 or later
 */

namespace Ilange\Component\Iseo\Site\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Session\Session;
use Joomla\Input\Json;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Ilange\Component\Iseo\Site\Helper\RouteHelper;
use Ilange\Component\Iseo\Administrator\Helper\IseoHelper;

/**
 * Контроллер отображения компонента com_iseo
 * @since 1.0.0
 */
class DisplayController extends BaseController
{   
    /**
     * Метод отображения шаблона вывода
     * @param bool $cachable Если true, вывод будет кэшироваться
     * @param array $urlparams Массив параметров URL и их типов, см. {@link InputFilter::clean()}.
     * @return DisplayController
     * @throws \Exception
     * @since 1.0.0
     */
    public function display($cachable = false, $urlparams = []): DisplayController
    {
        $url = $this->input->getString('url');
        $view = $this->input->getCmd('view', 'online');

        if ($url && $view === 'online') {
            $uniqid = $this->getModel('Online', 'Site')->getNewItem($url);
            
            if ($uniqid) {
                $this->setRedirect(Route::_(RouteHelper::getOnlineRoute($uniqid)));
                $this->redirect();
            }
        }      

        return parent::display($cachable, $urlparams);
    }

    /**
     * Метод используется при обращении Ajax из формы создания нового аудита 
     * @throws \Exception
     * @since 1.0.0
     */
    public function addAudit()
    {
        if (!Session::checkToken()) {
            IseoHelper::setResponse(
                'success',
                [],
                Text::_('JINVALID_TOKEN'));
        }

        $fields = [];
        $data = new Json();
        $input_data = $data->getArray();
     
        // Получаем необходимые поля формы
        if (isset($input_data['fields']['url'])) {
            $fields['url'] = filter_var($input_data['fields']['url'], FILTER_SANITIZE_URL);
        }
        //$fields['email'] = filter_var($input_data['fields']['email'], FILTER_SANITIZE_EMAIL);

        // Валидация полученных полей
        // $validations[0] - ошибки; $validations[1] - поля без ошибок
        $validations = IseoHelper::validationFields($fields);
        if (count($validations[0]) === 0) {  
            if (!IseoHelper::isUrlAvailable($fields['url'])) {
                // Если url недоступен - добавляем сообщение об ошибке
                $validations[0][] = IseoHelper::setError(
                                        'header', 
                                        Text::_('COM_ISEO_ERROR_UNAVAILABLE'), 
                                        'COM_ISEO_ERROR_URL_NOT_OK');
                // Возвращаем ответ
                IseoHelper::setResponse(
                    'danger',
                    $validations,
                    Text::_('COM_ISEO_ERROR_UNAVAILABLE'));
            } else {
                // Если ошибок нет и адрес доступен - вызываем метод модели
                // для проведения аудита и сохранения в базу
                $redirect = $this->getModel('Online', 'Site')->getNewItem($fields['url']);
                if ($redirect) {
                    $redirect = Route::_(RouteHelper::getOnlineRoute($redirect));
                }
                // Возвращаем ответ
                IseoHelper::setResponse(
                    'success',
                    $validations,
                    Text::_('COM_ISEO_ERROR_NO'),
                    $redirect);
            }
        } else {
            // Если есть ошибки валидации - возвращаем соответсвующий ответ
            IseoHelper::setResponse(
                'warning',
                $validations,
                Text::_('COM_ISEO_ERROR_FIELDS'));            
        }
    }
}