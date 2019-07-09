// let bsn = require('bootstrap.native/dist/bootstrap-native-v4');
import { WatchlistAddItemListener } from './watchlist-submit-listener';
import AjaxUtil from "@hundh/contao-utils-bundle/js/ajax-util";
require('./watchlist_legacy');

require('../scss/style.scss');

class ContaoWatchlistBundle {
    init()
    {
        document.querySelectorAll('.huh_watchlist_action').forEach((element) => {
            element.addEventListener('click', (event) => {
                event.target.dispatchEvent(new CustomEvent('huh_watchlist_click', {
                    bubbles: true,
                    detail: {
                        element: element
                    },
                }));
            });
        });

        document.addEventListener('huh_watchlist_click', this.onHuhWatchlistClickEvent.bind(this));

        // Frontend dependend listeners
        document.addEventListener('watchlist_window_open_bs4', this.onWatchlistWindowOpenBs4.bind(this));
        document.addEventListener('watchlist_create_count_element_base', this.onWatchlistCreateCountElementBase.bind(this));



        document.addEventListener('submit', (event) => {
            let watchlistElement = null;

            if (event.target && event.target.id.includes('watchlist-add-item')) {
                watchlistElement = new WatchlistAddItemListener(null);
            }

            if (null !== watchlistElement) {
                watchlistElement.onSubmitEvent(event);
            }
        });
    }

    /**
     * @param {CustomEvent} event
     */
    onWatchlistWindowOpenBs4(event)
    {
        let modalElement = event.detail.container.querySelector('.modal');
        if (null !== modalElement) {
            $(modalElement).modal();
        }
    }

    onWatchlistCreateCountElementBase(event)
    {
        let prefixNode = document.createTextNode('(');
        let suffixNode = document.createTextNode(')');
        let badge = document.createElement('span');
        badge.classList.add(event.detail.countSelector);
        if (event.detail.hasOwnProperty('watchlist'))
        {
            badge.classList.add('watchlist-' + event.detail.watchlist);
        }
        badge.textContent = event.detail.count;

        let watchlistButton = document.querySelectorAll('.' + event.detail.openWatchlistSelector + '.watchlist-' + event.detail.watchlist);
        if (watchlistButton.length < 1) {
            watchlistButton = document.querySelectorAll('.' + event.detail.openWatchlistSelector);
        }
        if (watchlistButton.length > 0)
        {
            watchlistButton.forEach((element) => {
                element.appendChild(prefixNode);
                element.appendChild(badge);
                element.appendChild(suffixNode);
            });
        }
    }

    /**
     *
     * @param {Event} event
     */
    onHuhWatchlistClickEvent(event)
    {
        let element = event.detail.element;
        if (element.dataset.actionType === 'update')
        {
            this.updateAction(element);
        }
        else {
            this.watchlistShowModelAction(element, element.dataset);
        }
    }

    watchlistShowModelAction(element, data)
    {

        let config = {
            onSuccess: (response) => {
                let watchlistContainer = document.querySelector('#' + data.watchlistContainer);
                watchlistContainer.innerHTML = response.response;
                element.dispatchEvent(new CustomEvent('watchlist_window_open_' + data.frontend, {
                    bubbles: true,
                    detail: {
                        container: watchlistContainer,
                        content: response.response
                    }
                }));
            }
        };
        this.doAjaxCall(element, element.dataset.actionUrl, element.dataset, config);
    }

    updateAction(element)
    {
        let config = {
            onSuccess: (response) => {
                let data = JSON.parse(response.responseText);

                let countSelector = 'huh_watchlist_item_count';
                let countElements = document.querySelectorAll('.' + countSelector + '.watchlist-' + data.watchlist);
                if (countElements.length < 1) {
                    countElements = document.querySelectorAll('.' + countSelector);
                }
                if (data.hasOwnProperty('count') && data.count > 0) {
                    if (countElements.length > 0)
                    {
                        countElements.forEach((countElement) => {
                            countElement.textContent = data.count;
                        });
                    } else {
                        element.dispatchEvent(new CustomEvent('watchlist_create_count_element_' + element.dataset.frontend, {
                            bubbles: true,
                            detail: {
                                count: data.count,
                                watchlist: data.watchlist,
                                openWatchlistSelector: 'huh_watchlist_action',
                                countSelector: countSelector,

                            }
                        }));
                    }
                }
                else {
                    countElement.remove();
                }
            }
        };
        this.doAjaxCall(element, element.dataset.actionUrl, element.dataset, config);
    }

    /**
     *
     * @param element
     * @param url
     * @param {DOMStringMap} data
     * @param closeOnSuccess
     */
    doAjaxCall (element, url, data, config = {})
    {
        element.dispatchEvent(new CustomEvent('watchlist_content_ajax_before', {
            bubbles: true
        }));

        AjaxUtil.post(url, data, config);
    }

    doAjaxCallWithUpdateOld(url, data, closeOnSuccess)
    {
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

                if (undefined !== response.modalTitle) {
                    document.getElementById('watchlist-modalTitle').textContent = response.modalTitle;
                }

                if (response.count > 0) {
                    let badge = document.getElementById('watchlist-badge');

                    if (null !== badge) {
                        badge.textContent = response.count;
                    } else {
                        badge = document.createElement('span');
                        badge.setAttribute('id', 'watchlist-badge');
                        badge.textContent = response.count;

                        document.querySelector('.watchlist-show-modal .btn-primary').prepend(badge);
                    }
                } else if (document.getElementById('watchlist-badge')) {
                    document.getElementById('watchlist-badge').remove();
                }

                if (closeOnSuccess && document.getElementById('watchlistModal')) {
                    document.getElementById('watchlistModal').remove();

                    if (document.querySelector('.modal-backdrop')) {
                        document.querySelector('.modal-backdrop').remove();
                    }

                    document.querySelector('body').classList.remove('modal-open')
                }

                // $('#watchlistModal').modal('toggle');
                Watchlist.ajaxCompleteCallback();
            },

            error: function (data, textStatus, jqXHR) {
                Watchlist.ajaxCompleteCallback();
            }
        });
    };
}

document.addEventListener('DOMContentLoaded', function () {
    let watchlist = new ContaoWatchlistBundle();
    watchlist.init();
});

