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

        document.addEventListener('watchlist_window_open_base', this.onWatchlistWindowOpenBase.bind(this));

    }

    /**
     * @param {CustomEvent} event
     */
    onWatchlistWindowOpenBase(event)
    {

        // let contentElement = this.element.querySelector('.watchlist-content');
        event.detail.container.innerHTML = event.detail.content;
    }

    /**
     *
     * @param {Event} event
     */
    onHuhWatchlistClickEvent(event)
    {
        let element = event.detail.element;
        this.watchlistShowModelAction(element, element.dataset);

        // this.doAjaxCall(element, element.dataset.actionUrl, element.dataset);




        // let action = element.dataset.action;

        // switch (action) {
        //     case 'watchlist-show-modal':
        //         this.watchlistShowModelAction(element, Object.assign({},element.dataset));
        //         break;
        // }
    }

    watchlistShowModelAction(element, data)
    {

        let config = {
            onSuccess: (response) => {
                let watchlistContainer = document.querySelector('#' + data.watchlistContainer);
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

    /**
     *
     * @param element
     * @param url
     * @param {DOMStringMap} data
     * @param closeOnSuccess
     */
    doAjaxCall (element, url, data, config = {})
    {
        let formData = new FormData();
        for (let property in data)
        {
            if (!data.hasOwnProperty(property)) {
                continue;
            }
            formData.set(property, data[property]);
        }

        element.dispatchEvent(new CustomEvent('watchlist_content_ajax_before', {
            bubbles: true
        }));

        AjaxUtil.post(url, data, config);
    }
}

document.addEventListener('DOMContentLoaded', function () {
    let watchlist = new ContaoWatchlistBundle();
    watchlist.init();
});

