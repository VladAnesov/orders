/**
 * Created by HOME on 27.07.2017.
 */

/*
 Функция предназначена для выборки элемента по классу или идентификатору
 Аналоговая из jQuery - $(element);
 */
function QS(element) {
    return document.querySelectorAll(element);
}

/*
 Функция для анимации плавного появления элемента
 Аналоговая из jQuery - $(element).fadeIn();
 */
function fadeIn(element) {
    element.style.opacity = 0;

    var last = +new Date();
    var tick = function () {
        element.style.opacity = +element.style.opacity + (new Date() - last) / 400;
        last = +new Date();

        if (+element.style.opacity < 1) {
            (window.requestAnimationFrame && requestAnimationFrame(tick)) || setTimeout(tick, 16);
        }
    };

    tick();
}

/*
 Функция показа страниц без перезагрузки страниц.
 */

function showContent(object, element, loading, hash) {
    event.preventDefault();
    var cont = QS(element)[0];
    var loading = QS(loading)[0];

    cont.innerHTML = loading.innerHTML;

    link = object.getAttribute("href");

    var http = createRequestObject();
    if (http) {
        http.open('get', link);
        http.setRequestHeader("VAHash", hash);
        http.onreadystatechange = function () {
            if (http.readyState == 4) {
                cont.innerHTML = http.responseText;
                window.history.pushState({}, null, http.responseURL);
                doc = QS(".menu-item");
                for (var i = 0; i < doc.length; i++) {
                    doc[i].classList.remove("active");
                }
                object.parentElement.classList.add('active');
                http.setRequestHeader("VAHash", ' ');
            }
        }
        http.send(null);
    }
    else {
        document.location = link;
    }
}

/*
 Доп. фича, аналог AJAX
 */
function createRequestObject() {
    try {
        return new XMLHttpRequest()
    }
    catch (e) {
        try {
            return new ActiveXObject('Msxml2.XMLHTTP')
        }
        catch (e) {
            try {
                return new ActiveXObject('Microsoft.XMLHTTP')
            }
            catch (e) {
                return null;
            }
        }
    }
}

/*
 Проходим по классу
 */
function each(object, callback) {
    if (!isObject(object) && typeof object.length !== 'undefined') {
        for (var i = 0, length = object.length; i < length; i++) {
            var value = object[i];
            if (callback.call(value, i, value) === false) break;
        }
    } else {
        for (var name in object) {
            if (!Object.prototype.hasOwnProperty.call(object, name)) continue;
            if (callback.call(object[name], name, object[name]) === false)
                break;
        }
    }

    return object;
}

function isObject(obj) {
    return Object.prototype.toString.call(obj) === '[object Object]';
}

/*
 Страница загружена, можно крутить спиннер.
 */
document.addEventListener('DOMContentLoaded', function () {
    console.log("page init");
});

