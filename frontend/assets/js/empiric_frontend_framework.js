let event_rejected;
let event_rejected_message;
/**
 * @type {string}
 * {string} dark or light
 */
const sweetAlertColorMode = 'light'
const bgColorMode = (sweetAlertColorMode === 'dark') ? '#212121' : '#FFFFFF';
const fontColorMode = (sweetAlertColorMode === 'dark') ? '#FFFFFF' : '#595959';

function checkRejected() {
    return new Promise(resolve => {
        resolve(!!event_rejected);
    });
}

function cleanRejected() {
    event_rejected = false;
    event_rejected_message = '';
}

function getReadTime(content) {
    const wordsPerMinute = 100; // Average case.
    let textLength = content.split(" ").length; // Split by words
    if (textLength > 0) {
        let result = Math.round(((textLength / wordsPerMinute) * 60) * 1000);
        if (result < 2000) {
            return 2000;
        } else {
            return result;
        }
    }
}

function serverQuery($json, onSuccess, onError, onFatal) {
    fetch(config['serverApi'], {
        method: 'POST', headers: {
            'Accept': 'application/json;utf-8'
        }, body: JSON.stringify($json)
    }).then(response => {
        return response.json();
    }).then(json => {
        const status = getResponse(json, 'status');
        if (status) {
            onSuccess(json);
        } else {
            onError(json);
        }
    }).catch(err => {
        showMessage(err, 'error');
        //onError(err);
    });
}

async function serverQueryAsync($json, onSuccess, onError) {
    let response = await new Promise(resolve => {
        fetch(config['serverApi'], {
            method: 'POST', headers: {
                'Accept': 'application/json;utf-8'
            }, body: JSON.stringify($json)
        }).then(response => {
            resolve(response.json());
        }).then(json => {
            resolve(json);
        }).catch(err => {
            resolve(err);
        });
    });
    if (response.status) {
        onSuccess(response);
    } else {
        onError(response);
    }
    return response;
}

const parseJwt = (token) => {
    try {
        return JSON.parse(atob(token.split('.')[1]));
    } catch (e) {
        return null;
    }
};

function getCookie(name) {
    let nameEQ = name + "=";
    let ca = document.cookie.split(';');
    for (let i = 0; i < ca.length; i++) {
        let c = ca[i];
        while (c.charAt(0) === ' ') c = c.substring(1, c.length);
        if (c.indexOf(nameEQ) === 0) return c.substring(nameEQ.length, c.length);
    }
    return null;
}

function setCookie(name, value, days) {
    let expires = "";
    if (days) {
        const date = new Date();
        date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
        expires = "; expires=" + date.toUTCString();
    }
    document.cookie = name + "=" + (value || "") + expires + "; path=/";
}

function delete_cookie(name) {
    document.cookie = name + '=;expires=Thu, 01 Jan 1970 00:00:01 GMT; path=/';
}

/**
 * jsonRequest is a class for make json structuration more simple in frontend.
 */
class jsonRequest {
    constructor(EndPoint, Action) {
        this.endpoint = EndPoint.trim();
        this.action = Action.trim();
        this.jsonPrint = {};
        this.jsonPrint['endpoint'] = this.endpoint;
        this.jsonPrint['action'] = this.action;
        this.jsonArray = [];
        this.jsonResponse = "";
    }

    /**
     * add a new {key: value} to our json request
     * @param {string} key
     * @param {*}value
     * @param {*} type
     */
    add(key, value, type = 'string') {
        switch (type) {
            case "string": {
                this.jsonPrint[key] = value
                break;
            }
            case "int": {
                this.jsonPrint[key] = parseInt(value);
                break;
            }
            case 'array': {
                this.jsonPrint[key] = value
                break;
            }
            default: {
                this.jsonPrint[key] = value
                break;
            }
        }
    }

    /**
     * Add a new value to an array of values (use when you need to add values to an array from an iterator and then add them as value to a key).
     * @param value
     */
    addToArray(value) {
        this.jsonArray.push(value)
    }

    /**
     * Add a new {key, value} based on an HTML element id
     * @param {string} key
     * @param {string} elementID
     * @param {string} type
     * @param {boolean} SelectOptionText
     */
    addByElementID(key, elementID, type = 'string', SelectOptionText = false) {
        if (document.getElementById(elementID).value.trim() === '' || document.getElementById(elementID).value.trim() === '0') {
            event_rejected = true;
            event_rejected_message = 'Debe llenar todos los campos solicitados.';
        }
        switch (type) {
            case "string": {
                this.jsonPrint[key] = document.getElementById(elementID).value;
                break;
            }
            case "int": {
                this.jsonPrint[key] = parseInt(document.getElementById(elementID).value);
                break;
            }
            case "float": {
                this.jsonPrint[key] = parseFloat(document.getElementById(elementID).value);
                break;
            }
            default: {
                this.jsonPrint[key] = document.getElementById(elementID).value;
                break;
            }
        }
        if (SelectOptionText) {
            let el = document.getElementById(elementID);
            this.jsonPrint[key] = el.options[el.selectedIndex].text
        }
    }

    /**
     * Print action from JSON REQUEST
     * use element class -> avoid-numeric (for evade numeric parsing)
     * use element class -> avoid-zero (for option selects where you want to avoid 0 option)
     * use element class -> required if you want to ask for required fields
     * use element class -> optional if you want to get data from (OPTIONAL) fields if user fills
     * @return {*}
     */
    addAuto(useName = false, onSuccess = () => {
    }, container = "") {
        let requiredElements = document.getElementsByClassName('required');
        let optionalElements = document.getElementsByClassName('optional');
        if (container !== '') {
            if (!document.getElementById(container)) {
                showMessage(`[AutoAdd] No se ha podido encontrar el contenedor:  ${container}`, 'error');
                return;
            }
            let containerElements = document.getElementById(container);
            requiredElements = containerElements.getElementsByClassName('required');
            optionalElements = containerElements.getElementsByClassName('optional');
        }
        for (let currentElement of optionalElements) {
            if (currentElement.tagName.toLowerCase() === 'select') {
                if (currentElement.value === "0" || currentElement.value.trim() === "") {
                    continue;
                }
            } else {
                if (currentElement.value.trim() === "") {
                    continue;
                }
            }
            if (useName) {
                if (currentElement.getAttribute('name').trim() === '') {
                    showMessage('Uno de los elementos especificados como opcionales no tiene un nombre asignado en el atributo NAME.', 'error');
                    currentElement.focus();
                    return;
                }
                if (isNaN(currentElement.value)) {
                    this.jsonPrint[currentElement.getAttribute('name')] = currentElement.value;
                } else {
                    if (currentElement.classList.contains('avoid-numeric')) {
                        this.jsonPrint[currentElement.getAttribute('name')] = currentElement.value;
                    } else {
                        this.jsonPrint[currentElement.getAttribute('name')] = parseFloat(currentElement.value);
                    }
                }
            } else {
                if (isNaN(currentElement.value)) {
                    this.jsonPrint[currentElement.id] = currentElement.value;
                } else {
                    if (currentElement.classList.contains('avoid-numeric')) {
                        this.jsonPrint[currentElement.id] = currentElement.value;
                    } else {
                        this.jsonPrint[currentElement.id] = parseFloat(currentElement.value);
                    }
                }
            }
        }
        for (let currentElement of requiredElements) {
            if (currentElement.tagName.toLowerCase() === 'select') {
                if (currentElement.value.trim() === '') {
                    showMessage('Debe completar todos los campos requeridos.', 'error');
                    currentElement.focus();
                    return;
                }
                if (!currentElement.classList.contains('avoid-zero')) {
                    if (currentElement.value === '0') {
                        showMessage('Debe completar todos los campos requeridos.', 'error');
                        currentElement.focus();
                        return;
                    }
                }
            } else {
                if (currentElement.value.trim() === "") {
                    showMessage('Debe completar todos los campos requeridos.', 'error');
                    currentElement.focus();
                    return;
                }
            }
            if (useName) {
                if (currentElement.getAttribute('name').trim() === '') {
                    showMessage('Uno de los elementos especificados como opcionales no tiene un nombre asignado en el atributo NAME.', 'error');
                    currentElement.focus();
                    return;
                }
                if (isNaN(currentElement.value)) {
                    this.jsonPrint[currentElement.getAttribute('name')] = currentElement.value;
                } else {
                    if (currentElement.classList.contains('avoid-numeric')) {
                        this.jsonPrint[currentElement.getAttribute('name')] = currentElement.value;
                    } else {
                        this.jsonPrint[currentElement.getAttribute('name')] = parseFloat(currentElement.value);
                    }
                }
            } else {
                if (isNaN(currentElement.value)) {
                    this.jsonPrint[currentElement.id] = currentElement.value;
                } else {
                    if (currentElement.classList.contains('avoid-numeric')) {
                        this.jsonPrint[currentElement.id] = currentElement.value;
                    } else {
                        this.jsonPrint[currentElement.id] = parseFloat(currentElement.value);
                    }
                }
            }
        }
        onSuccess();
    }

    printJsonAction() {
        return this.jsonPrint['action'];
    }

    /**
     * Print key from JSON REQUEST
     * @return {*}
     */
    printJsonKey(key) {
        return this.jsonPrint[key];
    }

    /**
     * Print our JSON REQUEST as a JSON object
     * @return {*}
     */
    printAsJsonObject() {
        return this.jsonPrint;
    }

    /**
     * Print our JSON array
     * @return {*}
     */
    printAsJsonArray() {
        return this.jsonArray;
    }

    /**
     * Print our JSON REQUEST as JSON object with a key that contains an array (previously iterated)
     * @return {*}
     */
    printAsJsonWithArray(key) {
        this.jsonPrint[key] = this.jsonArray;
        return this.jsonPrint;
    }

    /**
     * Print our JSON REQUEST as string
     * @return {*}
     */
    printAsJsonStringify() {
        return JSON.stringify(this.jsonPrint);
    }

    /**
     * Print our JSON REQUEST on browser console
     * @return {*}
     */
    printConsole() {
        console.log(this.jsonPrint);
    }

    get(key) {
        return this.jsonPrint[key];
    }

    set(key, value) {
        this.jsonPrint[key] = value;
    }

    async makeServerQuery(onSuccess = function (response) {
        const message = getResponse(response, 'message');
        //showMessageAutoClose(message, 'success', getReadTime(message))
        showMessage(message, 'success');
    }, onError = function (response) {
        const message = getResponse(response, 'message');
        //showMessageAutoClose(message, 'error', getReadTime(message));
        showMessage(message, 'error');
    }, getData = false, returnResponse = false) {
        let check_reject = await checkRejected();
        if (check_reject) {
            showMessageAutoClose(event_rejected_message, 'error', 2000)
            cleanRejected();
        } else {
            if (returnResponse) {
                let dataResponse = await serverQueryAsync(this.printAsJsonObject(), function () {
                }, function () {
                });
                if (getData) {
                    return getResponse(dataResponse, 'data');
                } else {
                    return dataResponse;
                }
            } else {
                serverQuery(this.printAsJsonObject(), (json) => {
                    if (onSuccess !== null) {
                        if (getData) {
                            const data = getResponse(json, 'data');
                            onSuccess(data);
                        } else {
                            onSuccess(json);
                        }
                    }
                }, (json) => {
                    if (onError !== null) {
                        onError(json);
                    }
                });
            }
        }
    }
}

/**
 * Show a SweetAlert2 popup loader for wait while your are waiting for data from request.
 * @param {string} message
 * @param {string} backgroundColor
 */
function showLoading(message, backgroundColor = bgColorMode, isText = false) {
    let text = "";
    if (isText) {
        text = message;
        message = "";
    }
    Swal.fire({
        icon: '',
        title: message,
        html: text,
        iconColor: '#ff5821',
        background: backgroundColor,
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading()
        }
    });
}

/**
 * Load a page by Web URL using JQUERY
 * @param {string} page
 */
async function loadPage(page, container = "main-container", action = () => {
}, closeSwal = true, customLoaderId = false, jsonToPost = {}) {
    container = (container) ? container : 'main-container';
    let customLoader = (customLoaderId) ? customLoaderId : container;
    if (customLoaderId) {
        toggleLoaderContainer(customLoader);
    }
    //showLoading('');
    //setTimeout(() => {
    $('#' + container).load(page, jsonToPost, (response, status, xhr) => {
        const statusCode = xhr.status;
        const responseJson = xhr.responseText;
        if (statusCode === 401) {
            const {message} = JSON.parse(responseJson);
            showMessageWithAction(message, 'error', undefined, undefined, () => {
                delete_cookie('token');
                window.location.reload();
            });
            return;
        }
        action();
        if (customLoaderId) {
            toggleLoaderContainer(customLoader);
        }
        //One.loader('hide');
        if (closeSwal) {
            swal.close();
        }
    });
    //}, 200);
}

async function loadPageFromIframe(page, container = "main-container", action = () => {
}, closeSwal = true, customLoaderId = false, jsonToPost = {}) {
    container = (container) ? container : 'main-container';
    let customLoader = (customLoaderId) ? customLoaderId : container;
    toggleLoaderContainer(customLoader, true);
    //showLoading('');
    //setTimeout(() => {
    $('#' + container).load(page, jsonToPost, () => {
        action();
        toggleLoaderContainer(customLoader, true);
        //One.loader('hide');
        if (closeSwal) {
            swal.close();
        }
    });
    //}, 200);
}

/**
 * Load a page by their hashName.
 * @param {string} Hash
 */
async function loadHashPage(Hash) {
    location.hash = '#' + Hash;
}

/**
 * Show or Hide bootstrap modal.
 * @param {string} id
 */
function showOrHideModal(id) {
    $('#' + id).modal('toggle');
}

function showModal(id) {
    $('#' + id).modal('show');
}

function hideModal(id) {
    $('#' + id).modal('hide');
}

/**
 * Show default sweetAlert popup notification with custom message and type.
 * @param {string} message
 * @param  {string} type
 * @param  {string} backgroundColor
 * @param  {string} fontColor
 */
function showMessage(message, type, backgroundColor = bgColorMode, fontColor = fontColorMode) {
    Swal.fire({
        title: '',
        html: `<span style="color: ${fontColor}">${message}</span>`,
        background: bgColorMode,
        icon: type,
        showConfirmButton: true,
        confirmButtonText: 'Aceptar',
        allowOutsideClick: false
    });
}

/**
 * Show default sweetAlert popup notification with custom message and type.
 * @param {string} message
 * @param  {string} type
 * @param  {string} backgroundColor
 * @param  {string} fontColor
 */
function showMessageWithAction(message, type, backgroundColor = bgColorMode, fontColor = fontColorMode, action = () => {
}) {
    Swal.fire({
        title: '',
        background: bgColorMode,
        html: `<span style="color: ${fontColor}">${message}</span>`,
        icon: type,
        showConfirmButton: true,
        confirmButtonText: 'Aceptar',
        allowOutsideClick: false,
        allowEscapeKey: false,
        allowEnterKey: false,
        showClass: {
            backdrop: 'swal2-with-backdrop'
        }
    }).then(success => {
        if (success) {
            action();
        }
    });
}

/**
 * Show default sweetAlert popup notification with custom message and type.
 * @param {string} title
 * @param {string} icon
 * @param  {number}  time
 * @param {boolean} allowOutsideClick
 * @param {function} action
 */
function showMessageAutoCloseWithAction(title, icon, time = 2000, allowOutsideClick = true, action) {
    let timerInterval
    time = getReadTime(title);
    Swal.fire({
        title: title,
        html: 'Éste mensaje se cerrará en <b></b> segundos.',
        timer: time,
        icon: icon,
        allowOutsideClick: allowOutsideClick,
        backdrop: true,
        showClass: {
            backdrop: 'swal2-with-backdrop'
        },
        timerProgressBar: true,
        didOpen: () => {
            Swal.showLoading();
            const b = Swal.getHtmlContainer().querySelector('b')
            timerInterval = setInterval(() => {
                b.textContent = (parseInt(Swal.getTimerLeft()) / 1000).toFixed(0).toString();
            }, 100)
        },
        willClose: () => {
            clearInterval(timerInterval)
        }
    }).then((result) => {
        if (result.dismiss === Swal.DismissReason.timer) {
            action();
        }
    })
}

/**
 * Show default sweetAlert popup notification with custom message and type.
 * @param {string} title
 * @param {string} icon
 * @param  {number}  time
 * @param  {*} redirHash
 */
function showMessageAutoClose(title, icon, time = 2000, redirHash = false, allowOutsideClick = true, reload = false) {
    let timerInterval
    time = getReadTime(title);
    Swal.fire({
        title: title,
        html: 'Éste mensaje se cerrará en <b></b> segundos.',
        timer: time,
        icon: icon,
        allowOutsideClick: allowOutsideClick,
        backdrop: true,
        showClass: {
            backdrop: 'swal2-with-backdrop'
        },
        timerProgressBar: true,
        didOpen: () => {
            Swal.showLoading();
            const b = Swal.getHtmlContainer().querySelector('b')
            timerInterval = setInterval(() => {
                b.textContent = (parseInt(Swal.getTimerLeft()) / 1000).toFixed(0).toString();
            }, 100)
        },
        willClose: () => {
            clearInterval(timerInterval)
        }
    }).then((result) => {
        if (result.dismiss === Swal.DismissReason.timer) {
            if (reload) {
                window.location.reload();
            }
            if (redirHash) {
                window.location.hash = redirHash;
            }
        }
    })
}


/**
 * This function show a message with HTML tags on Message área.
 * @param {string} Message
 * @param {string} Type
 */
function showMessageHTML(Message, Type) {
    Swal.fire({
        icon: Type, showConfirmButton: true, confirmButtonText: 'Aceptar', allowOutsideClick: false, html: Message
    });
}

/**
 * This function show a message and redirect to #hashPage
 * @param {string} Message
 * @param {string} Type
 * @param {string} HashName
 */
function showMessageRedir(Message, Type, HashName) {
    Swal.fire({
        title: Message, icon: Type, showConfirmButton: true, confirmButtonText: 'Aceptar', allowOutsideClick: false
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.hash = HashName;
        }
    });
}

/**
 * Shows a default confirmation modal.
 * @return {Promise<unknown>}
 */
function confirmModal() {
    return new Promise(resolve => {
        Swal.fire({
            title: '¿Está seguro que desea eliminar este registro?',
            text: "No podrá revertir esta acción.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí',
            cancelButtonText: 'Cancelar',
            showClass: {
                backdrop: 'swal2-with-backdrop'
            }
        }).then((result) => {
            swal.close();
            resolve(!!result.value);
        })
    });
}

/**
 * Shows a customizable confirmation modal.
 */
function customConfirmModal(question = '¿Está seguro que desea realizar esta acción?', description = 'Recuerde que no podrá revetir esta accción.', icon = 'question', backgroundColor = bgColorMode, fontcolor = fontColorMode) {
    return new Promise(resolve => {
        Swal.fire({
            title: `<span style="color: ${fontcolor}">${question}</span>`,
            html: `<span style="color: ${fontcolor}">${description}</span>`,
            icon: icon,
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            background: backgroundColor,
            color: 'white',
            allowOutsideClick: false,
            confirmButtonText: 'Sí',
            cancelButtonText: 'No',
            showClass: {
                backdrop: 'swal2-with-backdrop'
            }
        }).then((result) => {
            swal.close();
            resolve(!!result.value);
        })
    });
}

// /**
//  * Will logout from dashboard.
//  */
// function logout() {
//     delete_cookie('token');
//     window.location.reload();
// }

function type(object) {
    let stringConstructor = "test".constructor;
    let arrayConstructor = [].constructor;
    let objectConstructor = {}.constructor;
    if (object === null) {
        return "null";
    } else if (object === undefined) {
        return "undefined";
    } else if (object.constructor === stringConstructor) {
        return "String";
    } else if (object.constructor === arrayConstructor) {
        return "Array";
    } else if (object.constructor === objectConstructor) {
        return "Object";
    } else {
        return "null";
    }
}

/**
 * Will get final response  from getResponseFromJSON function, second position [1]
 * @param {Object} jsonObj
 * @param {string} keyName
 * @return {Object}
 */
function getResponse(jsonObj, keyName) {
    let response = getResponseFromJSON(jsonObj, keyName);
    return (response === null) ? null : response[1];
}

/**
 * Will get a key from a JsonObject as array
 * @param {Object} jsonObj
 * @param {string} keyName
 * @return {Array}
 */
function getResponseFromJSON(jsonObj, keyName) {
    for (let key in jsonObj) {
        let value = jsonObj[key];
        if (keyName === key) return [keyName, value];
        if (typeof (value) === "object" && !Array.isArray(value)) {
            let y = getResponseFromJSON(value, keyName);
            if (y && y[0] === keyName) return y;
        }
        if (Array.isArray(value)) {
            for (let i in value) {
                let x = getResponseFromJSON(value[i], keyName);
                if (x && x[0] === keyName) return x;
            }
        }
    }
    return null;
}

/**
 * Will set a value to an input element.
 * @param {string} id
 * @param {*} value
 */
function set(id, value, useName = false) {
    if (useName) {
        document.querySelector(`[name='${id}']`).value = value;
        return;
    }
    document.getElementById(id).value = value;
}

/**
 * Will get a input and return their value.
 * @param {string} id
 * @param {string} type
 */
function get(id, type = 'string') {
    switch (type) {
        case 'string':
            return document.getElementById(id).value;
        case 'int':
            return parseInt(document.getElementById(id).value);
        default:
            break;
    }
}

function getAttribute(elementID, attribute) {
    return document.getElementById(elementID).getAttribute(attribute);
}

function setAttribute(elementID, attribute, value, containerId) {
    if (containerId) {
        let container = document.getElementById(containerId)
        if (container) {
            let elements = container.getElementsByTagName('button');
            let findState = true;
            for (let currentElement of elements) {
                if (currentElement.id === elementID) {
                    currentElement.setAttribute(attribute, value);
                    findState = true;
                    return;
                }
                findState = false;
            }
            if (!findState) {
                showMessage('Lo sentimos, no hemos podido establecer el atributo debido a que no fue encontrado el elemento al que se desea ubicar el atributo.', 'error')
            }
        }
        return;
    } else {
        if (document.getElementById(elementID)) {
            document.getElementById(elementID).setAttribute(attribute, value);
        } else {
            showMessage(`No se puede establecer el atributo del elemento: ${elementID}, no se encontró en el DOM.`, 'error');
        }
    }
}

function getDOM(elementID) {
    return document.getElementById(elementID);
}

function cleanContainer(elementID) {
    document.getElementById(elementID).innerHTML = '';
}

/**
 * This function will show a toast on the screen.
 * @param {string} message
 * @param {string} type
 * @param {number} time
 * @param {string} position
 * @param {string} backgroundColor
 * @param {string} titleColor
 * @param {string} barColor
 */
function showToast(message, type = 'success', time = 3000, position = 'bottom-end', backgroundColor = '#212121', titleColor = 'white', barColor = '#028DE5') {
    const Toast = Swal.mixin({
        toast: true,
        position: position,
        showConfirmButton: false,
        timer: time,
        timerProgressBar: true,
        onOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer)
            toast.addEventListener('mouseleave', Swal.resumeTimer)
            document.querySelector('.swal2-timer-progress-bar').style.backgroundColor = barColor
        }
    })

    Toast.fire({
        icon: type, background: backgroundColor, title: `<span style="color: ${titleColor};">${message}<span>`
    })
}

function uuid() {
    let uuid = "", i, random;
    for (i = 0; i < 32; i++) {
        random = Math.random() * 16 | 0;
        if (i === 8 || i === 12 || i === 16 || i === 20) {
            uuid += "-";
        }
        uuid += (i === 12 ? 4 : (i === 16 ? (random & 3 | 8) : random)).toString(16);
    }
    return uuid;
}

function capitalizeFirstLetter(string) {
    return string.charAt(0).toUpperCase() + string.slice(1);
}

function appendContent(idElementToAppend, content, scroll = false, scrollElementID) {
    $('#' + idElementToAppend).append(content);
    if (scroll) {
        $("#" + scrollElementID).animate({scrollTop: $('#' + scrollElementID).prop("scrollHeight")}, 1000);
    }
}


function cleanAndFocus(elementID) {
    document.getElementById(elementID).innerHTML = '';
    document.getElementById(elementID).focus();
}

function parseName(input) {
    let name_splitted = input.split(" ");
    firstName = name_splitted[0].toLowerCase();
    lastName = name_splitted[2].toLowerCase();
    const result = firstName.charAt(0).toUpperCase() + firstName.slice(1) + " " + lastName.charAt(0).toUpperCase() + lastName.slice(1);
    return result;
}

function printDataTable() {

    document.querySelector('.buttons-print').click();
}

function printDataTableSecondary() {
    document.querySelector('.btn-print-secondary').click();
}

function toggleLoaderContainer(containerId, shouldTop = false) {
    const refDoc = (shouldTop) ? window.top.document : document;
    if (refDoc.getElementById(containerId).classList.contains('block-mode-loading-custom')) {
        refDoc.getElementById(containerId).classList.remove('block', 'block-rounded');
        refDoc.getElementById(containerId).classList.remove('block-mode-loading-custom');
    } else {
        refDoc.getElementById(containerId).classList.add('block', 'block-rounded');
        refDoc.getElementById(containerId).classList.add('block-mode-loading-custom');
    }
}

class globalClass {
    constructor() {
        this.table = '';
        this.tableContainer = '';
        this.tableBody = '';
        this.modalAddId = '';
        this.modalUpdateId = '';
        this.messageKeyName = 'message';
        this.endpoint = ''
        this.actionReadAll = '';
        this.actionReadSingle = '';
        this.actionDelete = '';
        this.actionCreate = '';
        this.actionUpdate = ''
    }

    setTable(id) {
        this.table = id;
    }

    setTableContainer(id) {
        this.tableContainer = id;
    }

    setTableBody(id) {
        this.tableBody = id;
    }

    setModalAddId(id) {
        this.modalAddId = id;
    }

    setModalUpdateId(id) {
        this.modalUpdateId = id;
    }

    setMessageKeyName(key) {
        this.messageKeyName = key;
    }

    setEndpoint(endpoint) {
        this.endpoint = endpoint;
    }

    setActionCreate(action) {
        this.actionCreate = action;
    }

    setActionUpdate(action) {
        this.actionUpdate = action;
    }

    setActionReadAll(action) {
        this.actionReadAll = action;
    }

    setActionReadSingle(action) {
        this.actionReadSingle = action;
    }

    setActionDelete(action) {
        this.actionDelete = action;
    }

    toggleModalAdd() {
        showOrHideModal(this.modalAddId);
    }

    toggleModalUpdate() {
        showOrHideModal(this.modalUpdateId);
    }
}

function openPdfNewTab(pdfUrl) {
    window.open(`${config.pdfViewerPath}${pdfUrl}`, '_blank').focus();
}

function openNewPageBlank(url) {
    window.open(url, '_blank').focus();
}

function getElementFromString(HTMLString, IdString) {
    try {
        let result, temp = document.createElement('div');
        temp.innerHTML = HTMLString;
        result = temp.querySelector('#' + IdString).outerHTML;
        return result;
    } catch (e) {

    }
}

function downloadFileFormat(formatFile, element) {
    let a = document.createElement("a");
    a.href = config['format_files'] + formatFile;
    a.setAttribute("download", formatFile);
    a.click();
    element.classList.add('disabled');
    setTimeout(function () {
        element.classList.remove('disabled');
    }, 3000);
}

function isJson(item) {
    item = typeof item !== "string" ? JSON.stringify(item) : item;
    try {
        item = JSON.parse(item);
    } catch (e) {
        return false;
    }

    return typeof item === "object" && item !== null;
}

function initDataTable(tableId) {
    $(`#${tableId}`).DataTable({
        "language": {
            "url": config['jsonPath'] + "Spanish.json"
        }, "columnDefs": [{
            "targets": 'no-sort', "orderable": false,
        }]
    });
}

function destroyDataTable(tableId) {
    $(`#${tableId}`).DataTable().destroy();
}

function updateDataTable(tableId, customOptions = false) {
    if (customOptions) {
        if (isJson(customOptions)) {
            $(`#${tableId}`).DataTable(customOptions).draw();
        } else {
            showMessage('custom options are not valid JSON', 'error');
        }
    } else {
        $(`#${tableId}`).DataTable({
            columnDefs: [{
                "targets": 'no-sort', "orderable": false,
            }]
        }).draw();
    }
}

function beginDownload(blob, filename) {
    if (window.navigator.msSaveOrOpenBlob) {
        window.navigator.msSaveOrOpenBlob(blob, filename);
    } else {
        const a = document.createElement('a');
        document.body.append(a);
        const url = window.URL.createObjectURL(blob);
        a.href = url;
        a.download = filename;
        a.click();
        setTimeout(() => {
            Swal.close();
            window.URL.revokeObjectURL(url);
            document.body.removeChild(a);
        }, 0);
    }
}

function createToolTip(element, ...params) {
    tippy(element, {
        content: `${params[0]}`,
    });
}

function updateTooltip(element, ...params) {
    (element)._tippy.setContent(`${params[0]}`);
}

function createToolTipMultiple(elementsClass = 'tippy-tooltip', elementsAttribute = 'data-tooltip') {
    tippy(`.${elementsClass}`, {
        content: (element) => {
            return element.getAttribute(elementsAttribute);
        },
    });
}

function initBootstrapTooltip() {
    $('[data-toggle="tooltip"]').tooltip({
        trigger: "manual"
    });

    $('[data-toggle="tooltip"]').on('mouseleave', function (e) {
        if (e.target.tagName.toLowerCase() === 'i') {
            e.stopPropagation();
        }
        $(this).tooltip('hide');
    });

    $('[data-toggle="tooltip"]').on('mouseenter', function (e) {
        if (e.target.tagName.toLowerCase() === 'i') {
            e.stopPropagation();
        }
        $(this).tooltip('show');
    });

    $('[data-toggle="tooltip"]').on('click', function () {
        $(this).tooltip('hide');
    });
}

function autoFillSelect(data, textKey, valueKey, element, selectedOption = false, selectedDefault = false) {
    element.innerHTML = "";
    let optionDefault = document.createElement('option');
    optionDefault.textContent = "Seleccione una opción...";
    optionDefault.value = "0";
    optionDefault.disabled = true;
    if (selectedDefault) {
        optionDefault.selected = true;
    }
    element.appendChild(optionDefault);
    for (let item of data) {
        let optionToInsert = document.createElement('option');
        optionToInsert.textContent = item[textKey];
        optionToInsert.value = item[valueKey];
        if (selectedOption) {
            if (selectedOption === item.id) {
                optionToInsert.selected = true;
            }
        }
        element.appendChild(optionToInsert);
    }
}

function copyToClipboard(content, message = 'Enlace de pago copiado al portapapeles, por favor envíalo al cliente pegando el enlace.') {
    if (content) {
        navigator.clipboard.writeText(content)
            .then(() => {
                showMessage(message, 'success')
            })
            .catch(err => {
                showMessage("Ha ocurrido un error al copiar al portapeles.", "error");
            })
    }
}

function fullCustomConfirmModal(question = '¿Que acción desea realizar?', description = 'Recuerde que la acción que seleccione y apruebe no podrá ser revertida.', icon = 'question', firstButtonText = '', secondButtonText = '', thirdButtonText = '', fourthButtonText = '', fivethButtonText = '', firstButtonAction = () => {
}, secondButtonAction = () => {
}, thirdButtonAction = () => {
}, fourthButtonAction = () => {
}, fivethButtonAction = () => {

}) {
    let buttonTemplate = '';
    if (firstButtonText !== '') {
        buttonTemplate += `<button type="button" class="mr-1 btn btn-first btn-primary mt-2">${firstButtonText}</button>`;
    }
    if (secondButtonText !== '') {
        buttonTemplate += `<button type="button" class="mr-1 btn btn-second btn-danger mt-2">${secondButtonText}</button>`;
    }
    if (thirdButtonText !== '') {
        buttonTemplate += `<button type="button" class="mr-1 btn btn-third btn-success mt-2" >${thirdButtonText}</button>`;
    }
    if (fourthButtonText !== '') {
        buttonTemplate += `<button type="button" class="mr-1 btn btn-fourth btn-warning mt-2">${fourthButtonText}</button>`;
    }
    if (fivethButtonText !== '') {
        buttonTemplate += `<button type="button" class="mr-1 btn btn-fiveth btn-secondary mt-2">${fivethButtonText}</button>`;
    }
    return new Promise(resolve => {
        Swal.fire({
            title: `<span>${question}</span>`,
            html: `<span>${description}</span>  
                   <br> <br> <br> 
                   ${buttonTemplate}
                    `,
            icon: icon,
            showCancelButton: false,
            showConfirmButton: false,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            allowOutsideClick: false,
            allowEscapeKey: false,
            allowEnterKey: false,
            color: 'white',
            showClass: {
                backdrop: 'swal2-with-backdrop'
            },
            didOpen: () => {
                if (firstButtonText !== '') {
                    const first = document.querySelector('.btn-first')
                    first.addEventListener('click', () => {
                        firstButtonAction();
                    })
                }

                if (secondButtonText !== '') {
                    const second = document.querySelector('.btn-second')
                    second.addEventListener('click', () => {
                        secondButtonAction();
                    })
                }

                if (thirdButtonText !== '') {
                    const third = document.querySelector('.btn-third')
                    third.addEventListener('click', () => {
                        thirdButtonAction();
                    })
                }

                if (fourthButtonText !== '') {
                    const fourth = document.querySelector('.btn-fourth')
                    fourth.addEventListener('click', () => {
                        fourthButtonAction();
                    })
                }

                if (fivethButtonText !== '') {
                    const fiveth = document.querySelector('.btn-fiveth')
                    fiveth.addEventListener('click', () => {
                        fivethButtonAction();
                    })
                }

            }
        }).then((result) => {
            swal.close();
            resolve(!!result.value);
        })
    });
}

function initCountryPhoneBox(el) {
    window.intlTelInput(el, {
        allowExtensions: true,
        formatOnDisplay: true,
        autoFormat: true,
        autoHideDialCode: true,
        autoPlaceholder: true,
        initialCountry: "mx",
        ipinfoToken: "yolo",
        nationalMode: false,
        numberType: "MOBILE",
        preferredCountries: ['mx'],
        preventInvalidNumbers: true,
        separateDialCode: true,
        geoIpLookup: function (callback) {
            $.get("http://ipinfo.io", function () {
            }, "jsonp").always(function (resp) {
                var countryCode = (resp && resp.country) ? resp.country : "";
                callback(countryCode);
            });
        },
        utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/11.0.9/js/utils.js"
    });
}

function getCountryPhoneBoxCode(el) {
    const iti = window.intlTelInputGlobals.getInstance(el);
    return iti.getNumber();
}

function isLandscape(imageUrl) {
    return new Promise(resolve => {
        let img = new Image();
        img.onload = function () {
            if (img.naturalWidth > img.naturalHeight) {
                resolve(true); //landscape
            } else if (img.naturalWidth < img.naturalHeight) {
                resolve(false); //portrait
            } else {
                resolve(false);
            }
        }
        img.src = imageUrl;
    });
}

function fileToBlob(file) {
    return new Promise(resolve => {
        const reader = new FileReader();
        reader.onload = () => {
            const myBlob = new Blob([reader.result])
            resolve(myBlob);
        }
        reader.readAsArrayBuffer(file);
    })
}

function getParameter(param) {
    let url = new URL(window.location.href);
    return url.searchParams.get("dir");
}

function getParameterFromURL(param) {
    let url = new URL(window.location.href);
    return url.searchParams.get(param);
}


function replaceQueryParam(param, newval, search) {
    var regex = new RegExp("([?;&])" + param + "[^&;]*[;&]?");
    var query = search.replace(regex, "$1").replace(/&$/, '');
    return (query.length > 2 ? query + "&" : "?") + (newval ? param + "=" + newval : '');
}


/**
 * Add a URL parameter (or changing it if it already exists)
 * @param search
 * @param key
 * @param val
 */
const addUrlParam = (url, param, value) => {
    param = encodeURIComponent(param);
    let r = "([&?]|&amp;)" + param + "\\b(?:=(?:[^&#]*))*";
    let a = document.createElement('a');
    let regex = new RegExp(r);
    let str = param + (value ? "=" + encodeURIComponent(value) : "");
    a.href = url;
    const urlParams = new URLSearchParams(a.search);
    const paramExist = urlParams.get(param);
    if (paramExist) {
        urlParams.set(param, value);
        return `?${urlParams.toString()}`;
    } else {
        a.search += (a.search ? "&" : "") + str;
        return a.search;
    }
};

const removeUrlParam = (url, param) => {
    let a = document.createElement('a');
    a.href = url;
    const urlParams = new URLSearchParams(a.search);
    const paramExist = urlParams.get(param);
    if (paramExist) {
        urlParams.delete(param);
        return (urlParams.toString().length > 0) ? `?${urlParams.toString()}` : `${urlParams.toString()}`;
    } else {
        return false;
    }
}

function setDynamicUrlParams(paramName, paramValue) {
    let currentParams = location.href.match(/\?.*/) ?? [];
    // const regularExpressionToGetHash  = new RegExp(`\\?.*`);
    const regularExpressionToGetHash = new RegExp(`(^#|#)([a-z0-9]+)`);
    let currentHash = location.href.match(regularExpressionToGetHash)[0] ?? false;
    if (!currentHash) {
        console.error('Error al obtener el hash actual');
    }
    // let url = new URL();
    let currentPathName = location.href;
    currentPathName.replace(new RegExp("\\.[^\\/.]+$"), '');
    window.history.replaceState(
        null,
        '',
        `${currentHash}${addUrlParam(currentParams[0] ?? '', paramName, paramValue)}`
    );
}

function unsetDynamicUrlParams(params = []) {
    let currentParams = location.href.match(/\?.*/) ?? [];
    const regularExpressionToGetHash = new RegExp(`(^#|#)([a-z0-9]+)`);
    let currentHash = location.href.match(regularExpressionToGetHash)[0] ?? false;
    if (!currentHash) {
        console.error('Error al obtener el hash actual');
    }
    if (params.length === 0) {
        window.history.replaceState(
            null,
            '',
            `${currentHash} `
        );
        return;
    }
    let currentPathName = location.href;
    currentPathName.replace(new RegExp("\\.[^\\/.]+$"), '');
    if (!Array.isArray(params)) {
        params = [params];
    }
    let currentParamsAux = currentParams[0] ?? ''

    for (let param of params) {
        let paramHandler = removeUrlParam(currentParamsAux, param);
        if (paramHandler === false) {
            continue;
        }
        currentParamsAux = paramHandler;
    }
    if (currentParamsAux === false) {
        return;
    }
    window.history.replaceState(
        null,
        '',
        `${currentHash}${currentParamsAux}`
    );
}


function getUrlParams(url = location.href) {
    let request = {};
    let pairs = url.substring(url.indexOf((url.includes('?') ? '?' : '&')) + 1).split('&');
    if (pairs[0].includes('http') || pairs[0].includes('https')) {
        return request;
    }
    for (let pairItem of pairs) {
        if (!pairItem)
            continue;
        let pair = pairItem.split('=');
        request[decodeURIComponent(pair[0])] = decodeURIComponent(pair[1]);
    }
    return request;
}

function removeParam(key, sourceURL) {
    var rtn = sourceURL.split("?")[0],
        param,
        params_arr = [],
        queryString = (sourceURL.indexOf("?") !== -1) ? sourceURL.split("?")[1] : "";
    if (queryString !== "") {
        params_arr = queryString.split("&");
        for (var i = params_arr.length - 1; i >= 0; i -= 1) {
            param = params_arr[i].split("=")[0];
            if (param === key) {
                params_arr.splice(i, 1);
            }
        }
        if (params_arr.length) rtn = rtn + "?" + params_arr.join("&");
    }
    return rtn;
}

function getHashValue(key) {
    var matches = location.hash.match(new RegExp(key + '=([^&]*)'));
    return matches ? matches[1] : null;
}

function cleanContainerElements(containerId) {
    if (document.getElementById(containerId)) {
        const mainElement = document.getElementById(containerId);
        let inputElements = mainElement.getElementsByTagName('input');
        let textAreaElements = mainElement.getElementsByTagName('textarea');
        let selectElements = mainElement.getElementsByTagName('select');
        for (let input of inputElements) {
            input.value = '';
        }
        for (let textarea of textAreaElements) {
            textarea.value = '';
        }
        for (let select of selectElements) {
            select.selectedIndex = 0;
        }
    } else {
        showMessage(`[CleanElements] No se ha podido encontrar el contenedor: ${containerId}`, 'error');
    }
}

function showToastr(text,type='success') {
    One.helpers('notify', {type: type, from: 'bottom', align: 'center', message: `<div>${text}</div>`});
}