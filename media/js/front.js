/*
 * @package    com_iseo
 * @author     Pavel Lange <pavel@ilange.ru>
 * @link       https://github.com/i-lange/com_iseo
 * @copyright  (C) 2023 Pavel Lange <https://ilange.ru>
 * @license    GNU General Public License version 2 or later
 */

// Ищем первую форму на странице, относящуюся к компоненту COM_ISEO
const iseo_forms = document.querySelectorAll('[data-iseo-form]');
iseo_forms.forEach(function (form) {
    form.addEventListener('submit', function (event) {
        event.preventDefault();
        event.stopPropagation();
        iseoProcess(form);
    });
});

// Обрабатываем данные формы
function iseoProcess(form) {
    const buttons = form.querySelectorAll('[type="submit"]');
    const fields = form.querySelectorAll('input');
    let input_data = {};

    // Данные всех полей пишем в массив input_data
    fields.forEach(function (input) {
        const type = input.getAttribute('type');
        if (type !== 'file' && type !== 'reset' && type !== 'submit') {
            input_data[input.getAttribute('name')] = input.value;
        }
    });

    // Вызываем функцию отправки данных на сервер
    iseoRequest(form, buttons, input_data);
}

function iseoRequest(form, buttons, input_data) {
    Joomla.request({
        url: '?option=com_iseo&task=addAudit&format=json',
        method: 'POST',
        headers: {
            'Cache-Control': 'no-cache',
            'Content-Type': 'application/json'
        },
        data: JSON.stringify({
            'fields': input_data
        }),
        onBefore: function () {
            // Показываем спиннер
            form.classList.add('loading');

            // Все кнопки деактивируем
            buttons.forEach(function (button) {
                button.setAttribute('disabled', '1');
            });
        },
        onSuccess: function (response) {
            const data = JSON.parse(response);
            iseoClear(form, data);
            iseoAlert(form, data);
            iseoUpdate(form, data);
            if (data.data.redirect) {
                location.href = data.data.redirect;
            }
        },
        onError: function () {
            iseoAlert(form, {"type": "danger", "text": "Unable to send data"});
        },
        onComplete: function () {
            // Скрываем спиннер
            form.classList.remove('loading');

            // Все кнопки активируем
            buttons.forEach(function (button) {
                button.removeAttribute('disabled');
            });
        }
    });
}

// Очищаем форму и очищаем форматирование
function iseoClear(form, data) {
    const inputs = form.querySelectorAll('input');
    inputs.forEach(function (input) {
        input.classList.remove('is-valid', 'is-invalid');
    });

    const alerts = form.querySelectorAll('.alert');
    alerts.forEach(function (alert) {
        alert.remove();
    });

    if (data.data.type === 'success') {
        form.reset();
    }
}

// Показываем сообщение
function iseoAlert(form, data) {
    const alert = document.createElement('div');

    alert.classList.add('alert', 'alert-' + data.data.type);
    alert.setAttribute('role', 'alert');
    alert.innerText = data.message;

    form.appendChild(alert);
}

// Обновляем статусы полей в форме
function iseoUpdate(form, data) {
    if (data.data.errors.length !== 0) {
        data.data.errors.forEach(function (error) {
            if (error.key !== 'header') {
                const input = form.querySelector('[name="' + error.key + '"]');
                input.classList.add('is-invalid');
            }            
        });
    }

    if (data.data.valid.length !== 0) {
        data.data.valid.forEach(function (valid) {
            const input = form.querySelector('[name="' + valid + '"]');
            input.classList.add('is-valid');
        });
    }
}