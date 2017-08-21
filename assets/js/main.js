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

function State(status) {
    var loading_elem = QS(".loading")[0];
    if (loading_elem) {
        if (status === true) {
            loading_elem.style.display = 'block';
        } else {
            loading_elem.style.display = 'none';
        }
    }
}

function ge(el) {
    return (typeof el == 'string' || typeof el == 'number') ? document.getElementById(el) : el;
}

function trim(text) {
    return (text || '').replace(/^\s+|\s+$/g, '');
}

function removeClass(obj, name) {
    if (obj = ge(obj)) {
        obj.className = trim((obj.className || '').replace((new RegExp('(\\s|^)' + name + '(\\s|$)')), ' '));
    }
}

/*
 Функция для анимации плавного появления элемента
 Аналоговая из jQuery - $(element).fadeIn();
 */
function fadeIn(element, max_opacity) {
    max_opacity = typeof max_opacity !== 'undefined' ? max_opacity : 1;

    element.style.opacity = 0;

    var last = +new Date();
    var tick = function () {
        element.style.opacity = +element.style.opacity + (new Date() - last) / 400;
        last = +new Date();

        if (+element.style.opacity < max_opacity) {
            (window.requestAnimationFrame && requestAnimationFrame(tick)) || setTimeout(tick, 16);
        }
    };

    tick();
}

/*
 Функция показа страниц без перезагрузки страниц.
 */

function showContent(object, element, loading, hash, set_class) {
    event.preventDefault();
    var cont = QS(element)[0];
    var loading = QS(loading)[0];

    cont.innerHTML = loading.innerHTML;

    if (object && object.nodeType && object.nodeType === 1) {
        link = object.getAttribute("href");
    } else {
        link = object;
    }

    var http = createRequestObject();
    if (http) {
        var params = "VAHash=" + hash;
        http.open("POST", link, true);
        http.setRequestHeader("VAHash", hash);
        http.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        http.onreadystatechange = function () {
            if (http.readyState === 4) {
                cont.innerHTML = http.responseText;
                window.history.pushState({}, object.innerHTML, http.responseURL);

                if (set_class === true) {
                    var title = object.innerHTML.replace(/<[^>]+>/g, '');
                    updateTitle(title);
                    doc = QS(".menu-item");
                    for (var i = 0; i < doc.length; i++) {
                        doc[i].classList.remove("active");
                    }
                    object.parentElement.classList.add('active');
                } else {
                    if (object && object.nodeType && object.nodeType === 1) {
                        var title = document.title + " > " + object.innerHTML.replace(/<[^>]+>/g, '');
                    } else {
                        var title = document.title;
                    }
                    updateTitle(title);
                }
            }
        };
        http.send(params);
    }
    else {
        document.location = link;
    }
}

/*
 Обновление Title на странице
 */
function updateTitle(title) {
    var elm = document.getElementsByTagName('title')[0];
    elm.innerHTML = title;
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

function checkPage() {
    if (!QS(".a-main")) {
        window.location.reload();
    }
}

window.addEventListener("popstate", function (e) {
    window.location.reload();
}, false);

var orders = {
    ajax: function (url, params, callback) {
        var http = createRequestObject();
        if (http) {
            params = orders.objToString(params);
            http.open("POST", url, true);
            http.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            http.setRequestHeader("VAAjax", "yes");
            http.onreadystatechange = function () {
                if (http.readyState == 4) {
                    callback.call(http.responseText);
                }
            };
            http.send(params);
        }
    },
    objToString: function (obj) {
        var str = '';
        for (var p in obj) {
            if (obj.hasOwnProperty(p)) {
                if (str != "") {
                    str += "&";
                }
                str += p + '=' + obj[p];
            }
        }
        return str;
    },
    createOrder: function () {
        var getModal = "/projects/project_1635/ajax/type/ps_create";
        var postData = "/projects/project_1635/ajax/type/ps_create_data";
        orders.createModal(getModal, postData);
    },
    createModal: function (get_url, post_url) {
        event.preventDefault();
        State(true);
        var e_page = QS('.a-main')[0];
        var modal_class = "va__modal";
        var e_modal = QS("." + modal_class)[0];

        if (!e_modal) {
            orders.ajax(get_url, {test: 'test'}, function () {
                var response = JSON.parse(this);
                if (typeof response["error"] !== 'undefined' && response["error"] === "no") {
                    /* Создание окна */
                    e_modal = document.createElement('div');
                    e_modal.className = modal_class;

                    /* создаем сам блок */
                    e_modal_block = e_modal.appendChild(document.createElement('div'));
                    e_modal_block.className = "va__modal-block";

                    /* Суем внутрь три блока, тайтл, форма, кнопки */
                    e_modal_title = e_modal_block.appendChild(document.createElement('div'));
                    e_modal_title.className = "va__modal-block_title";
                    e_modal_title_div = e_modal_title.appendChild(document.createElement('div'));
                    e_modal_title_div.innerHTML = response["title"];
                    e_modal_title_div.className = "va__modal-block_title-name";
                    e_modal_title_close = e_modal_title.appendChild(document.createElement('div'));
                    e_modal_title_close.className = "va__modal-block_title-close";

                    e_modal_title_close.onclick = function () {
                        orders.deleteModal();
                    };

                    e_modal_form = e_modal_block.appendChild(document.createElement('div'));
                    e_modal_form.className = "va__modal-block_form";
                    e_modal_form_inner = e_modal_form.appendChild(document.createElement('form'));
                    e_modal_form_inner.innerHTML = response["data"];

                    e_modal_buttons = e_modal_block.appendChild(document.createElement('div'));
                    e_modal_buttons.className = "va__modal-block_buttons";

                    e_modal_buttons_price = e_modal_buttons.appendChild(document.createElement('div'));
                    e_modal_buttons_price.className = "va__price";

                    e_modal_buttons_send = e_modal_buttons.appendChild(document.createElement('div'));
                    e_modal_buttons_send.className = "va__modal-block_buttons-send btn";
                    e_modal_buttons_send.innerHTML = response["sendtitle"];
                    e_modal_buttons_send.setAttribute("data-url", post_url);
                    e_modal_buttons_send.onclick = function () {
                        State(true);
                        var formElements = e_modal_form_inner.elements;
                        var postData = {};
                        var status;
                        for (var i = 0; i < formElements.length; i++) {
                            if (formElements[i].type !== "submit") {
                                if (formElements[i].value.length === 0) {
                                    formElements[i].className += (formElements[i].className ? ' ' : '') + 'required';
                                    formElements[i].onfocus = function () {
                                        removeClass(this, 'required');
                                    };
                                    status = false;
                                } else {
                                    postData[formElements[i].name] = formElements[i].value;
                                }
                            }
                        }
                        if (status !== false) {
                            orders.ajax(post_url, postData, function () {
                                var e_response = JSON.parse(this);
                                if (typeof e_response["error"] !== 'undefined' && e_response["error"] === "no") {
                                    showContent(e_response["url"], '.a-body', '.loading', e_response["e_hash"], false);
                                    updateTitle(document.title + " > " + formElements[0].value);
                                    orders.deleteModal();
                                } else {
                                    orders.createError(e_response["error_text"])
                                }
                                State(false);
                            });
                        } else {
                            State(false);
                        }
                    };

                    /* Создаем окно */
                    e_page.appendChild(e_modal);

                    /* !Конец создания окна */
                } else {
                    orders.createError(response["error_text"]);
                }
                State(false);
            });
        } else {
            /* delete item */
            orders.deleteModal();
            orders.createModal(get_url, post_url);
        }
    },
    deleteModal: function () {
        modal_class = "va__modal";
        var elements = document.getElementsByClassName(modal_class);
        while (elements.length > 0) {
            elements[0].parentNode.removeChild(elements[0]);
        }
    },
    getPrice: function (obj, element) {
        element = QS(element)[0];
        if (obj.value > 15000) {
            obj.value = 15000;
        }
        if (!isNaN(parseFloat(obj.value))) {
            obj.value = Math.round(obj.value);
        }

        if (obj.value.length !== 0) {
            var postData = {price: obj.value};
            orders.ajax("/projects/project_1635/ajax/type/ps_get-price", postData, function () {
                var e_response = JSON.parse(this);
                if (typeof e_response["error"] !== 'undefined' && e_response["error"] === "no") {
                    element.innerHTML = e_response["data"];
                }
            });
        } else {
            element.innerHTML = '';
        }
    },
    createError: function (text) {
        var e_page = QS('.a-main')[0];
        var error_class = "va__error";
        var e_error = QS("." + error_class)[0];

        if (!e_error) {
            e_error = document.createElement('div');
            e_error.className = error_class;
            e_error.innerHTML = text;
            e_page.appendChild(e_error);
            setTimeout(orders.deleteError, 10000);
        } else {
            var elements = document.getElementsByClassName(error_class);
            while (elements.length > 0) {
                elements[0].parentNode.removeChild(elements[0]);
            }
            orders.createError(text);
        }
    },
    deleteError: function () {
        var error_class = "va__error";
        var elements = document.getElementsByClassName(error_class);
        while (elements.length > 0) {
            elements[0].parentNode.removeChild(elements[0]);
        }
    },
    startOrder: function (object) {
        var orderId = object.getAttribute('data-orderId');
        console.log(object);
        console.log(orderId);
    }
};

/*
 Страница загружена, можно крутить спиннер.
 */
document.addEventListener('DOMContentLoaded', function () {
    checkPage();
    console.log("page init");
});