import '@hundh/contao-utils-bundle';
import Swal from "sweetalert2";

class WatchlistBundle {
    static init() {
        WatchlistBundle.initOpenLink();
        WatchlistBundle.initWatchlistLinks();
        WatchlistBundle.initAddItemLinks();
    }

    static initOpenLink() {
        utilsBundle.event.addDynamicEventListener('click', '.open-watchlist:not([data-bs-toggle])', (element, event) => {
            event.preventDefault();

            let contentWrapper = element.nextElementSibling;

            if (contentWrapper.classList.contains('opened')) {
                contentWrapper.classList.remove('opened');
            } else {
                contentWrapper.classList.add('opened');
            }
        });
    }

    static updateWatchlist() {
        let wrapper = document.querySelector('.watchlist-wrapper');

        if (null === wrapper) {
            return;
        }

        utilsBundle.ajax.get(wrapper.getAttribute('data-watchlist-update-url'), {}, {
            onSuccess: (response) => {
                document.querySelector('.watchlist-content').innerHTML = response.responseText;
                wrapper.dispatchEvent(new CustomEvent('huh_watchlist_list_updated', {
                    bubbles: true
                }));
            }
        });
    }

    static initWatchlistLinks() {
        // clear watchlist
        utilsBundle.event.addDynamicEventListener('click', '.mod_watchlist .clear, .watchlist-content .clear', (element, event) => {
            event.preventDefault();

            const url = element.getAttribute('href'),
                config = utilsBundle.ajax.setDefaults({
                    onSuccess: (response) => {
                        WatchlistBundle.updateWatchlist();

                        document.querySelectorAll('.watchlist-add-item.added').forEach((link) => {
                            WatchlistBundle.toggleAddItemLink(link);
                        });
                    },
                    onError: (response) => {
                        Swal.fire({
                            icon: 'error',
                            timer: 0,
                            timerProgressBar: true,
                            showCloseButton: true,
                            showConfirmButton: false,
                            html: response.responseText
                        });
                    }
                }),
                request = utilsBundle.ajax.initializeRequest('DELETE', url, config),
                submitData = {
                    config: config,
                    action: url,
                    data: {}
                };

            utilsBundle.ajax.doAjaxSubmit(request, submitData);
        });

        // delete items
        utilsBundle.event.addDynamicEventListener('click', '.mod_watchlist .delete-item, .watchlist-content .delete-item', (element, event) => {
            event.preventDefault();

            const data = JSON.parse(element.getAttribute('data-post-data'));

            data.delete = 1;

            utilsBundle.ajax.jsonPost(element.getAttribute('href'), data, {
                onSuccess: (response) => {
                    WatchlistBundle.updateWatchlist();

                    // toggle add item links (insert tags)
                    document.querySelectorAll('.watchlist-add-item[data-hash="' + element.getAttribute('data-hash') + '"]').forEach((link) => {
                        WatchlistBundle.toggleAddItemLink(link);
                    });
                },
                onError: (response) => {
                    Swal.fire({
                        icon: 'error',
                        timer: 0,
                        timerProgressBar: true,
                        showCloseButton: true,
                        showConfirmButton: false,
                        html: response.responseText
                    });
                }
            });
        });

        // share
        utilsBundle.event.addDynamicEventListener('click', '.mod_watchlist .share, .watchlist-content .share', (element, event) => {
            event.preventDefault();

            Swal.fire({
                icon: 'success',
                showCloseButton: true,
                showConfirmButton: false,
                html: '<div class="share-url">' +
                    '<p>' + element.getAttribute('data-text') + '</p>' +
                    '<p><input class="url form-control" type="text" value="' + element.getAttribute('href') + '"><br>' +
                    '<a class="watchlist-copy-to-clipboard btn btn-primary btn-sm" href="#">' + element.getAttribute('data-copy-to-clipboard-text') + '</a></p>' +
                    '</div>'
            });
        });

        utilsBundle.event.addDynamicEventListener('click', '.watchlist-copy-to-clipboard', (element, event) => {
            event.preventDefault();

            if (!window.isSecureContext) {
                console.error('Copying to clipboard is only allowed with HTTPS');
                return;
            }

            let textInput = element.closest('.share-url').querySelector('.url');

            navigator.clipboard.writeText(textInput.value).then(function() {
                element.classList.add('added');
                textInput.classList.add('added');
            });
        });
    }

    static toggleAddItemLink(element) {
        if (element.classList.contains('added')) {
            element.classList.remove('added');

            element.innerText = element.getAttribute('data-add-item-message');
        } else {
            element.classList.add('added');

            element.innerText = element.getAttribute('data-delete-item-message');
        }
    }

    static initAddItemLinks(scope = null) {
        document.addEventListener('click', (event) => {
            if (event.target.classList.contains('watchlist-add-item')) {
                let element = event.target;

                const data = JSON.parse(element.getAttribute('data-post-data'));

                event.preventDefault();

                data.delete = element.classList.contains('added') ? 1 : 0;

                utilsBundle.ajax.jsonPost(element.getAttribute('href'), data, {
                    onSuccess: (response) => {
                        WatchlistBundle.toggleAddItemLink(element);

                        WatchlistBundle.updateWatchlist();

                        if (!data.delete) {
                            Swal.fire({
                                icon: 'success',
                                timer: 0,
                                timerProgressBar: true,
                                showCloseButton: true,
                                showConfirmButton: false,
                                html: element.getAttribute('data-item-added-message')
                            });
                        }
                    },
                    onError: (response) => {
                        Swal.fire({
                            icon: 'error',
                            timer: 0,
                            timerProgressBar: true,
                            showCloseButton: true,
                            showConfirmButton: false,
                            html: response.responseText
                        });
                    }
                });
            }
        });
    }
}

export {WatchlistBundle};
