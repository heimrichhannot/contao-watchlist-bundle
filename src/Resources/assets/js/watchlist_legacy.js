import { ContaoWatchlistBundle} from './contao-watchlist-bundle.class';

window.Watchlist = {
    init: function () {
        this.registerEvents();
    },
    registerEvents: function () {
        document.addEventListener('submit', function (e) {
            if (e.target && 'watchlist-download-link' === e.target.id) {
                e.preventDefault();
                Watchlist.generateDownloadLink(document.getElementById(e.target.id));
            }

            if (e.target && e.target.id.includes('watchlist-add-item')) {
                e.preventDefault();
                Watchlist.addItem(document.getElementById(e.target.id));
            }

            if (e.target && 'watchlist-downloadLink-form' === e.target.id) {
                e.preventDefault();
                Watchlist.sendDownloadNotification(document.getElementById(e.target.id));
            }

            if (e.target && 'watchlist-download-all' == e.target.id) {
                e.preventDefault();
                Watchlist.downloadWatchlist(e.target);
            }

            if (e.target && e.target.id.includes('watchlist-delete-item')) {
                e.preventDefault();
                Watchlist.deleteItem(e.target);
            }

            if (e.target && 'watchlist-empty-watchlist' == e.target.id) {
                e.preventDefault();
                Watchlist.emptyWatchlist(e.target);
            }

            if (e.target && 'watchlist-delete-watchlist' == e.target.id) {
                e.preventDefault();
                Watchlist.deleteWatchlist(document.getElementById(e.target.id));
            }

            if (e.target && 'watchlist-new-and-add' == e.target.id) {
                e.preventDefault();
                Watchlist.newAndAdd(document.getElementById(e.target.id));
            }

            if (e.target && 'watchlist-select-add' == e.target.id) {
                e.preventDefault();
                Watchlist.addItemToSelectedWatchlist(document.getElementById(e.target.id));
            }

            if (e.target && 'watchlist-options select' == e.target.id) {
                e.preventDefault();
                Watchlist.updateWatchlist(document.getElementById(e.target.id));
            }

        });
    },
    addItem: function (form) {
        if(!(formData = Watchlist.serialize(form))) {
            return;
        }

        let url = form.action,
            uuid = formData['uuid'] ? formData['uuid'] : document.querySelector('.item-options option:selected').value,
            title = formData['title'] ? formData['title'] : document.querySelector('.item-options option:selected').textContent,
            downloadable = formData['downloadable'],
            moduleId = formData['moduleId'],
            type = formData['type'],
            options = formData['options'] ? formData['options'] : null,
            requestToken = formData['REQUEST_TOKEN'],
            data = {
                'moduleId': moduleId,
                'type': type,
                'itemData': {
                    'options': options,
                    'uuid': uuid ? uuid : null,
                    'title': title ? title : null,
                    'downloadable': downloadable
                },
                'REQUEST_TOKEN': requestToken
            };

        Watchlist.doAjaxCallWithUpdate(url, data);
    },
    deleteItem: function (form) {
        let formData = Watchlist.serialize(form);
        if (!Array.isArray(formData))
        {
            return;
        }

        let url = form.action,
            moduleId = formData['moduleId'],
            itemId = formData['itemId'],
            request_token = formData['REQUEST_TOKEN'],
            data = {
                'moduleId': moduleId,
                'itemId': itemId,
                'REQUEST_TOKEN': request_token
            };

        Watchlist.doAjaxCallWithUpdate(url, data);
    },
    emptyWatchlist: function (form) {
        let formData = Watchlist.serialize(form);
        if (!Array.isArray(formData))
        {
            return;
        }

        let url = form.action,
            moduleId = formData['moduleId'],
            watchlistId = formData['watchlistId'],
            request_token = formData['REQUEST_TOKEN'],
            data = {
                'moduleId': moduleId,
                'watchlistId': watchlistId,
                'REQUEST_TOKEN': request_token
            };

        Watchlist.doAjaxCallWithUpdate(url, data);
    },
    downloadWatchlist: function (form) {
        let formData = Watchlist.serialize(form);
        if (!Array.isArray(formData))
        {
            return;
        }

        let url = form.action,
            moduleId = formData['moduleId'],
            watchlistId = formData['watchlistId'],
            data = {
                'moduleId': moduleId,
                'watchlistId': watchlistId
            };

        Watchlist.doAjaxCallWithUpdate(url, data);
    },
    generateDownloadLink: function (form) {
        if(!(formData = Watchlist.serialize(form))) {
            return;
        }

        let url = form.action,
            moduleId = formData['moduleId'],
            watchlistId = formData['watchlistId'],
            request_token = formData['REQUEST_TOKEN'],
            data = {
                'moduleId': moduleId,
                'watchlistId': watchlistId,
                'REQUEST_TOKEN': request_token
            };

        Watchlist.ajax({
            url: url,
            type: 'POST',
            data: data,
            success: function (data) {
                let response = JSON.parse(data.responseText).result.data;

                if (undefined !== response.form) {
                    Watchlist.initModal(response.form);
                }

                if (undefined !== response.link) {
                    let linkElement = document.querySelector('.watchlist-download-link-href');
                    linkElement.textContent = response.link;
                    linkElement.setAttribute('href', response.link);
                }

                if (undefined !== response.message) {
                    document.getElementsByTagName('body')[0].appendChild(response.message);
                }

                Watchlist.ajaxCompleteCallback();
            }
        });
    },
    deleteWatchlist: function (form) {
        if(!(formData = Watchlist.serialize(form))) {
            return
        }

        let url = form.action,
            moduleId = formData['moduleId'],
            watchlistId = formData['watchlistId'],
            request_token = formData['REQUEST_TOKEN'],
            data = {
                'moduleId': moduleId,
                'watchlistId': watchlistId,
                'REQUEST_TOKEN': request_token
            };

        Watchlist.doAjaxCallWithUpdate(url, data);
    },
    newAndAdd: function (form) {
        if(!(formData = Watchlist.serialize(form))) {
            return
        }

        let url = form.action,
            moduleId = formData['moduleId'],
            watchlistId = formData['watchlistId'],
            selected = document.querySelector('.item-options').length ? document.querySelector('.item-options option:selected').value : null,
            uuid = formData['uuid'] ? ['uuid'] : null,
            title = formData['itemTitle'] ? formData['itemTitle'] : null,
            downloadable = formData['downloadable'],
            durability = document.getElementById('#watchlist-durability') ? document.querySelector('#watchlist-durability option:selected').value : null,
            data = {
                'moduleId': moduleId,
                'itemData': {
                    'uuid': selected ? selected : uuid,
                    'title': title
                },
                'watchlist': watchlistId,
                'type': 'file',
                'durability': durability,
                'downloadable': downloadable
            };

        Watchlist.doAjaxCallWithUpdate(url, data);
    },
    addItemToSelectedWatchlist: function (form) {
        if(!(formData = Watchlist.serialize(form))) {
            return
        }

        let url = form.action,
            uuid = formData['uuid'] ? formData['uuid'] : document.querySelector('.item-options option:selected').value,
            watchlistId = formData['watchlistId'] ? formData['watchlistId'] : document.querySelector('.watchlist-options option:selected').value,
            downloadable = formData['downloadable'],
            title = formData['title'] ? formData['title'] : document.querySelector('.item-options option:selected').textContent,
            type = formData['type'],
            options = formData['options'] ? formData['options'] : null,
            data = {
                'watchlistId': watchlistId,
                'type': type,
                'itemData': {
                    'options': options,
                    'uuid': uuid ? uuid : null,
                    'title': title ? title : null,
                    'downloadable': downloadable
                }
            };

        Watchlist.doAjaxCallWithUpdate(url, data);
    },
    updateWatchlist: function (form) {
        if(!(formData = Watchlist.serialize(form))) {
            return
        }

        let url = form.action,
            moduleId = formData['moduleId'] ? formData['moduleId'] : null,
            watchlistId = formData['watchlistId'] ? formData['watchlistId'] : null,
            data = {
                'moduleId': moduleId,
                'watchlistId': watchlistId
            };

        Watchlist.doAjaxCallWithUpdate(url, data);
    },
    sendDownloadNotification: function (form) {
        if(!(formData = Watchlist.serialize(form))) {
            return
        }

        let url = form.action,
            data = {};

        for (const [key, value] of Object.entries(formData)) {
            data[key] = value;
        }

        Watchlist.doAjaxCallWithUpdate(url, data, true);
    },
    doAjaxCall: function (url, data, closeOnSuccess) {
        Watchlist.addLoader();

        Watchlist.ajax({
            url: url,
            dataType: 'JSON',
            type: 'POST',
            data: data,
            success: function (data, textStatus, jqXHR) {
                let response = JSON.parse(data.responseText);

                Watchlist.initModal(response.result.data.response);
                Watchlist.ajaxCompleteCallback();
            },

            error: function (data, textStatus, jqXHR) {
                Watchlist.ajaxCompleteCallback();
            }
        });
    },
    doAjaxCallWithUpdate: function (url, data, closeOnSuccess) {
        Watchlist.addLoader();
        Watchlist.ajax({
            url: url,
            dataType: 'JSON',
            type: 'POST',
            data: data,
            success: function (data, textStatus, jqXHR) {
                let response = JSON.parse(data.responseText).result.data;

                if (undefined !== response.message) {
                    Watchlist.setMessage(response.message);
                }

                if (undefined !== response.file) {
                    window.location = response.file;
                }

                if (undefined !== response.watchlist) {
                    let updatedWatchlist = document.createElement('div');
                    updatedWatchlist.innerHTML = response.watchlist;

                    document.querySelector('.watchlist-body').replaceWith(updatedWatchlist);
                }

                if (undefined !== response.modal) {
                    Watchlist.initModal(response.modal);
                }

                if (response.hasOwnProperty('headline'))
                {
                    document.querySelector('.huh_watchlist_window_headline').textContent = response.headline;
                }

                if (response.hasOwnProperty('count'))
                {
                    let countData = {
                        count: response.count
                    };
                    if (response.hasOwnProperty('watchlistId'))
                    {
                        countData.watchlist = response.watchlistId;
                    }
                    ContaoWatchlistBundle.updateWatchlistCount(countData);
                }

                if (closeOnSuccess && document.getElementById('watchlistModal')) {
                    document.getElementById('watchlistModal').remove();

                    if (document.querySelector('.modal-backdrop')) {
                        document.querySelector('.modal-backdrop').remove();
                    }

                    document.querySelector('body').classList.remove('modal-open');
                }

                // $('#watchlistModal').modal('toggle');
                Watchlist.ajaxCompleteCallback();
            },

            error: function (data, textStatus, jqXHR) {
                Watchlist.ajaxCompleteCallback();
            }
        });
    },
    setMessage: function (message) {
        let messageElement = document.createElement('div'),
            notifyer = document.getElementById('watchlist-notify');

        messageElement.innerHTML = message;
        notifyer.append(messageElement);
        notifyer.style.display = 'block';

        setTimeout(function () {
            messageElement.remove();
        }, 3500);
    },
    initModal: function (content) {
        let element = document.querySelector('.watchlist-content');


        // let modalElement = Watchlist.getWatchlistWrapper();
        // let modal = new bsn.Modal(modalElement, {
        //     content: content
        // });
        //
        // modal.show();
    },
    ajax: function (config) { // $.ajax(...) without jquery.

        if (typeof(config.url) === "undefined") {
            try {
                config.url = location.href;
            } catch (e) {
                var ajaxLocation;
                ajaxLocation = document.createElement("a");
                ajaxLocation.href = "";
                config.url = ajaxLocation.href;
            }
        }
        if (typeof(config.type) === "undefined") {
            config.type = "GET";
        }
        if (typeof(config.data) === "undefined") {
            config.data = null;
        }


        if (typeof(config.statusCode) === "undefined") { // 4
            config.statusCode = {};
        }
        if (typeof(config.beforeSend) === "undefined") { // 1
            config.beforeSend = function () {
            };
        }
        if (typeof(config.success) === "undefined") { // 4 et sans erreur
            config.success = function () {
            };
        }
        if (typeof(config.error) === "undefined") { // 4 et avec erreur
            config.error = function () {
            };
        }
        if (typeof(config.complete) === "undefined") { // 4
            config.complete = function () {
            };
        }
        typeof(config.statusCode["404"]);

        let xhr = null;

        if (window.XMLHttpRequest || window.ActiveXObject) {
            if (window.ActiveXObject) {
                try {
                    xhr = new ActiveXObject("Msxml2.XMLHTTP");
                } catch (e) {
                    xhr = new ActiveXObject("Microsoft.XMLHTTP");
                }
            }
            else {
                xhr = new XMLHttpRequest();
            }
        } else {
            alert("Votre navigateur ne supporte pas l'objet XMLHTTPRequest...");
            return null;
        }

        xhr.onreadystatechange = function () {
            if (xhr.readyState === 1) {
                config.beforeSend();
            }
            if (xhr.readyState === 4) {
                config.complete(xhr, xhr.status);
                if (xhr.status === 200 || xhr.status === 0) {
                    config.success(xhr);
                } else {
                    config.error(xhr.status);
                    if (typeof(config.statusCode[xhr.status]) !== "undefined") {
                        config.statusCode[xhr.status]();
                    }
                }
            }
        };

        if (config.type === "POST") {
            xhr.open(config.type, config.url, true);
            xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded; charset=UTF-8\n");
            xhr.send("data=" + JSON.stringify(config.data));
        } else {
            xhr.open(config.type, config.url + "?data=" + data, true);
            xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");
            xhr.send(null);
        }
    },
    ajaxCompleteCallback: function () {
        // remove messages with a delay
        setTimeout(function () {
            if (document.getElementById('watchlist-loader')) {
                document.getElementById('watchlist-loader').remove();
            }
        }, 3500);
    },
    addLoader: function () {
        let loader = document.createElement('div');
        loader.setAttribute('id', 'watchlist-loader');
        loader.innerHTML = '<i class="fa fa-spinner fa-pulse fa-3x fa-fw"></i>';

        document.body.appendChild(loader);
    },
    serialize: function (form) {
        let field,
            formData = [],
            hasError = false;

        if (typeof form === 'object' && "FORM" === form.nodeName) {
            for (let i = 0; i < form.elements.length; i++) {
                field = form.elements[i];

                let name = field.name,
                    value = field.value;

                if ('checkbox' === field.type || 'radio' === field.type) {
                    value = field.checked;
                }

                if(field.required && !value) {
                    let error = document.createElement('span');
                    error.setAttribute('class', 'pt-1 d-block text-danger');
                    error.textContent = 'Bitte füllen Sie dieses Feld aus!';

                    let node = ('checkbox' === field.type || 'radio' === field.type) ?  field.parentNode.parentNode : field.parentNode;
                    node.appendChild(error);
                    hasError = true;
                }

                formData[name] = value;
            }
        }

        if(hasError) {
            return false;
        }

        return formData;
    }
};


document.addEventListener('DOMContentLoaded', function () {
    Watchlist.init();

    // jquery support
    if (window.jQuery) {
        window.jQuery(document).ajaxComplete(function () {
            Watchlist.init();
        });
    }

    // mootools support
    if (window.MooTools) {
        window.addEvent('ajax_change', function () {
            Watchlist.init();
        });
    }
});