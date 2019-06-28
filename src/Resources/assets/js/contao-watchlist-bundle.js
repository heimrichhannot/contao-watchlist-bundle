// let bsn = require('bootstrap.native/dist/bootstrap-native-v4');
// import { WatchlistOpenWindowListener, WatchlistAddItemListener } from './watchlist-submit-listener';
import AjaxUtil from "@hundh/contao-utils-bundle/js/ajax-util";

require('../scss/style.scss');

class ContaoWatchlistBundle {
    init()
    {
        document.querySelectorAll('.huh_watchlist_element').forEach((element) => {
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
    }

    /**
     * @param {CustomEvent} event
     */
    onWatchlistWindowOpenBase(event)
    {
        let contentElement = this.element.querySelector('.watchlist-content');
        contentElement.innerHTML = event.detail.content;
    }

    /**
     *
     * @param {Event} event
     */
    onHuhWatchlistClickEvent(event)
    {
        let element = event.detail.element;
        let action = element.dataset.action;
        switch (action) {
            case 'watchlist-show-modal':
                this.watchlistShowModelAction(element, Object.assign({},element.dataset));
                break;
        }
    }

    watchlistShowModelAction(element, data)
    {
        this.doAjaxCall(element, data.ajaxUrl, data, true);
    }

    doAjaxCall (element, url, data, closeOnSuccess) {
        let formData = new FormData();
        formData.append('REQUEST_TOKEN', data.requestToken);
        delete data.requestToken;

        element.dispatchEvent(new CustomEvent('watchlist_content_ajax_before', {
            bubbles: true
        }));


        formData.append('data', JSON.stringify(data));

        // Object.keys(data).forEach(field => {
        //     formData.append(field, data[field]);
        // });

        AjaxUtil.post(url, formData, {
            onSuccess: (response) => {
                element.dispatchEvent(new CustomEvent('watchlist_window_open_' + data.frontend, {
                    bubbles: true,
                    detail: {content: response.result.data.response}

                }));
            }
        });
    }
}

document.addEventListener('DOMContentLoaded', function () {
    let watchlist = new ContaoWatchlistBundle();
    watchlist.init();
});

