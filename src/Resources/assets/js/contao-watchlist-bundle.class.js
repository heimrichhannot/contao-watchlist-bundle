import AjaxUtil from "@hundh/contao-utils-bundle/js/ajax-util";

class ContaoWatchlistBundle {
    init()
    {
        this.initEventDispatcher(document);

        document.addEventListener('huh_watchlist_click', this.onHuhWatchlistClickEvent.bind(this));

        // Frontend dependend listeners
        document.addEventListener('watchlist_window_open_bs4', this.onWatchlistWindowOpenBs4.bind(this));
        document.addEventListener('watchlist_create_count_element_base', this.onWatchlistCreateCountElementBase.bind(this));
        document.addEventListener('watchlist_create_count_element_bs4', this.onWatchlistCreateCountElementBs4.bind(this));
    }

    initEventDispatcher(rootElement)
    {
        rootElement.querySelectorAll('.huh_watchlist_action').forEach((element) => {
            element.addEventListener('click', (event) => {
                event.target.dispatchEvent(new CustomEvent('huh_watchlist_click', {
                    bubbles: true,
                    detail: {
                        element: element
                    },
                }));
            });
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
        let badge = document.createElement('span');
        badge.setAttribute('class', event.detail.cssClass);
        badge.textContent = event.detail.count;
        event.target.appendChild(badge);
    }

    onWatchlistCreateCountElementBs4(event)
    {
        let badge = document.createElement('span');
        badge.setAttribute('class', event.detail.cssClass);
        badge.classList.add('badge', 'badge-secondary');
        badge.textContent = event.detail.count;
        event.target.appendChild(badge);
    }

    /**
     *
     * @param {Event} event
     */
    onHuhWatchlistClickEvent(event)
    {
        let element = event.detail.element;
        if (!element.dataset.hasOwnProperty('actionType')) {
            console.throw("No action defined for watchlist click event!");
        }

        let action = element.dataset.actionType;

        switch (action) {
            case 'update':
                this.updateAction(element);
                break;
            case 'toggle':
                this.watchlistShowModelAction(element, element.dataset);
                break;
            case 'download':
                this.downloadAction(element);
                return;
            default:
                this.doAjaxCall(element, element.dataset.actionUrl, element.dataset);
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
                this.initEventDispatcher(watchlistContainer);
            }
        };
        this.doAjaxCall(element, element.dataset.actionUrl, element.dataset, config);
    }

    static updateWatchlistCount(data)
    {
        if (data.hasOwnProperty('count')) {
            if (!data.hasOwnProperty('watchlist'))
            {
                data.watchlist = 0;
            }

            let parentElements = document.querySelectorAll('.huh_watchlist_show_count.watchlist-' + data.watchlist);
            if (parentElements.length < 1) {
                parentElements = document.querySelectorAll('.huh_watchlist_show_count');
            }
            if (parentElements.length < 1) {
                return;
            }

            let countSelector = 'huh_watchlist_item_count';
            let countElements = document.querySelectorAll('.' + countSelector + '.watchlist-' + data.watchlist);
            if (countElements.length < 1) {
                countElements = document.querySelectorAll('.' + countSelector);
            }

            let cssClass = countSelector;
            if (data.watchlist > 0) {
                cssClass = cssClass.concat(' watchlist-' + data.watchlist);
            }

            if (data.count > 0) {
                if (countElements.length > 0)
                {
                    countElements.forEach((countElement) => {
                        countElement.textContent = data.count;
                    });
                } else {
                    parentElements.forEach((parentElement) => {
                        parentElement.dispatchEvent(new CustomEvent('watchlist_create_count_element_' + parentElement.dataset.frontend, {
                            bubbles: true,
                            detail: {
                                count: data.count,
                                cssClass: cssClass,
                                watchlist: data.watchlist,
                                openWatchlistSelector: 'huh_watchlist_open_watchlist_window',
                                countSelector: countSelector,

                            }
                        }));
                    });

                }
            }
            else {
                if (countElements.length > 0) {
                    countElements.forEach((countElement) => {
                        countElement.remove();
                    });
                }
            }
        }
    }

    updateAction(element)
    {
        let config = {
            onSuccess: (response) => {
                let data = JSON.parse(response.responseText);

                ContaoWatchlistBundle.updateWatchlistCount(data);

                if (data.hasOwnProperty('watchlistContent'))
                {
                    let watchlistContainer = document.querySelectorAll('.watchlist-content.watchlist-' + data.watchlist);
                    if (watchlistContainer.length < 1)
                    {
                        watchlistContainer = document.querySelectorAll('.watchlist-content');
                    }
                    watchlistContainer.forEach((element) => {
                        element.innerHTML = data.watchlistContent;
                        this.initEventDispatcher(element);
                    });
                }
            }
        };
        this.doAjaxCall(element, element.dataset.actionUrl, element.dataset, config);
    }
    
    downloadAction(element)
    {
        let config = {
            responseType: 'blob',
            onSuccess: (response) => {
                try {
                    let result = JSON.parse(response.responseText);
                } catch (e) {
                    let disposition = response.getResponseHeader('content-disposition');
                    let matches = /"([^"]*)"/.exec(disposition);
                    let filename = (matches != null && matches[1] ? matches[1] : 'watchlist.zip');

                    let a = document.createElement('a');
                    let url = URL.createObjectURL(response.response);
                    a.href = url;
                    a.download = filename;
                    document.body.append(a);
                    a.click();
                    a.remove();
                    window.URL.revokeObjectURL(url);
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
        if (data.hasOwnProperty('langauge'))
        {

        }
        element.dispatchEvent(new CustomEvent('watchlist_content_ajax_before', {
            bubbles: true
        }));

        AjaxUtil.post(url, data, config);
    }
}

export { ContaoWatchlistBundle };