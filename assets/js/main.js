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
    return (typeof el === 'string' || typeof el === 'number') ? document.getElementById(el) : el;
}

function ga(el) {
    return (typeof el === 'string' || typeof el === 'number') ? QS(el) : el;
}

function trim(text) {
    return (text || '').replace(/^\s+|\s+$/g, '');
}

window.whitespaceRegex = /[\t\r\n\f]/g;

function hasClass(obj, name) {
    obj = ge(obj);
    if (obj &&
        obj.nodeType === 1 &&
        (" " + obj.className + " ").replace(window.whitespaceRegex, " ").indexOf(" " + name + " ") >= 0) {
        return true;
    }

    return false;
}

function addClass(obj, name) {
    if ((obj = ge(obj)) && !hasClass(obj, name)) {
        obj.className = (obj.className ? obj.className + ' ' : '') + name;
    }
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

function fadeOut(element) {
    var op = 0.1;  // initial opacity
    element.style.display = 'block';
    var timer = setInterval(function () {
        if (op >= 1) {
            clearInterval(timer);
        }
        element.style.opacity = op;
        element.style.filter = 'alpha(opacity=' + op * 100 + ")";
        op += op * 0.1;
    }, 10);
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
    createModal: function (get_url, post_url, width, post) {
        event.preventDefault();
        width = typeof width !== 'undefined' ? width : 700;
        post = typeof post !== 'undefined' ? post : {};
        State(true);
        var e_page = QS('.a-main')[0];
        var modal_class = "va__modal";
        var e_modal = QS("." + modal_class)[0];

        if (!e_modal) {
            orders.ajax(get_url, post, function () {
                var response = JSON.parse(this);
                if (typeof response["error"] !== 'undefined' && response["error"] === "no") {
                    /* Создание окна */
                    e_modal = document.createElement('div');
                    e_modal.className = modal_class;

                    /* создаем сам блок */
                    e_modal_block = e_modal.appendChild(document.createElement('div'));
                    e_modal_block.className = "va__modal-block";
                    e_modal_block.style.width = width + "px";

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
                    e_modal_form_inner.onsubmit = function () {
                        event.preventDefault();
                    };
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
                                    addClass(formElements[i], 'required');
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
                                    if (typeof e_response["url"] !== 'undefined' && typeof e_response["e_hash"] !== 'undefined') {
                                        showContent(e_response["url"], '.a-body', '.loading', e_response["e_hash"], false);
                                        if (typeof e_response["title"] !== 'undefined') {
                                            updateTitle(document.title + " > " + e_response["title"]);
                                        }
                                    }

                                    if (typeof e_response["update"] !== 'undefined') {
                                        if (isObject(e_response["update"])) {
                                            each(e_response["update"], function (key, value) {
                                                var element = ga(key)[0];
                                                element.innerHTML = value;
                                            });
                                        }
                                    }

                                    if (typeof e_response["htmlText"] !== 'undefined') {
                                        orders.showBaloon(e_response["htmlText"]);
                                    }
                                    orders.deleteModal();
                                } else {
                                    orders.createError(e_response["error_text"]);
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
            orders.createModal(get_url, post_url, width);
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
    showBaloon: function (text) {
        var e_page = QS('.a-main')[0];
        var baloon_class = "top_result_baloon_wrap position_center";
        var baloon_inner_class = "top_result_baloon";
        var e_baloon = QS("." + baloon_class)[0];

        if (!e_baloon) {
            e_baloon = document.createElement('div');
            e_baloon.className = baloon_class;
            e_baloon_inner = e_baloon.appendChild(document.createElement('div'));
            e_baloon_inner.className = baloon_inner_class;
            e_baloon_inner.innerHTML = text;
            e_page.appendChild(e_baloon);
            setTimeout(orders.deleteBaloon, 5000);
        } else {
            var elements = document.getElementsByClassName(baloon_class);
            while (elements.length > 0) {
                elements[0].parentNode.removeChild(elements[0]);
            }
            orders.showBaloon(text);
        }
    },
    deleteBaloon: function () {
        var baloon_class = "top_result_baloon_wrap";
        var elements = document.getElementsByClassName(baloon_class);
        while (elements.length > 0) {
            elements[0].parentNode.removeChild(elements[0]);
        }
    },
    startOrder: function (object) {
        State(true);
        var orderId = object.getAttribute('data-orderId');
        var orderHash = object.getAttribute('data-hash');
        var postUrl = "/projects/project_1635/ajax/type/ps_start_order";

        orders.ajax(postUrl, {id: orderId, hash: orderHash}, function () {
            var e_response = JSON.parse(this);
            if (typeof e_response["error"] !== 'undefined' && e_response["error"] === "no") {
                if (typeof e_response["update"] !== 'undefined') {
                    if (isObject(e_response["update"])) {
                        each(e_response["update"], function (key, value) {
                            var element = ga(key)[0];
                            element.innerHTML = value;
                        });
                    }
                }
                if (typeof e_response["htmlText"] !== 'undefined') {
                    orders.showBaloon(e_response["htmlText"]);
                }
            } else {
                orders.createError(e_response["error_text"]);
            }
            State(false);
        });
    },
    endOrder: function (object) {
        var orderId = object.getAttribute('data-orderId');
        var orderHash = object.getAttribute('data-hash');
        var postQuery = {id: orderId, hash: orderHash};

        var getModal = "/projects/project_1635/ajax/type/ps_end_order";
        var postData = "/projects/project_1635/ajax/type/ps_end_order_data";
        orders.createModal(getModal, postData, 700, postQuery);
    },
    acceptOrder: function (object) {
        State(true);
        var orderId = object.getAttribute('data-orderId');
        var orderHash = object.getAttribute('data-hash');
        var postUrl = "/projects/project_1635/ajax/type/ps_accept_order";
        var postData = {id: orderId, hash: orderHash};

        orders.processOrder(postUrl, postData);
    },
    declineOrder: function (object) {
        State(true);
        var orderId = object.getAttribute('data-orderId');
        var orderHash = object.getAttribute('data-hash');
        var postUrl = "/projects/project_1635/ajax/type/ps_decline_order";
        var postData = {id: orderId, hash: orderHash};

        orders.processOrder(postUrl, postData);
    },
    processOrder: function (postUrl, postData) {
        orders.ajax(postUrl, postData, function () {
            var e_response = JSON.parse(this);
            console.log(e_response);
            if (typeof e_response["error"] !== 'undefined' && e_response["error"] === "no") {
                if (typeof e_response["update"] !== 'undefined') {
                    if (isObject(e_response["update"])) {
                        each(e_response["update"], function (key, value) {
                            var element = ga(key)[0];
                            element.innerHTML = value;
                        });
                    }
                }
                if (typeof e_response["htmlText"] !== 'undefined') {
                    orders.showBaloon(e_response["htmlText"]);
                }
            } else {
                orders.createError(e_response["error_text"]);
            }
            State(false);
        });
    },
    createModalAddBalance: function () {
        var getModal = "/projects/project_1635/ajax/type/m_create";
        var postData = "/projects/project_1635/ajax/type/m_create_data";
        orders.createModal(getModal, postData, 300);
    },
    minMax: function (object, min, max) {
        if (typeof object.value !== 'undefined') {
            if (object.value > max) {
                object.value = max;
            }
        }
    }
};

/*
 Страница загружена, можно крутить спиннер.
 */
document.addEventListener('DOMContentLoaded', function () {
    checkPage();
    console.log("page init");
});