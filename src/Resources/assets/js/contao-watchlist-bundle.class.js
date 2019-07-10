import AjaxUtil from "@hundh/contao-utils-bundle/js/ajax-util";

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
                    });
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
}

export { ContaoWatchlistBundle };