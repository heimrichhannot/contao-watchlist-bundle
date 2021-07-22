import '@hundh/contao-utils-bundle';
import Swal from "sweetalert2";

class WatchlistBundle {
    static init() {
        WatchlistBundle.initWatchlistLinks();
        WatchlistBundle.initAddItemLinks();
    }

    static updateWatchlist() {
        let wrapper = document.querySelector('.watchlist-wrapper');

        if (null === wrapper) {
            return;
        }

        utilsBundle.ajax.get(wrapper.getAttribute('data-watchlist-update-url'), {}, {
            onSuccess: (response) => {
                wrapper.querySelector('.watchlist-content').innerHTML = response.responseText.trim();
            },
        });
    }

    static initWatchlistLinks() {
        utilsBundle.event.addDynamicEventListener('click', '.watchlist-delete-item', (element, event) => {
            const data = JSON.parse(element.getAttribute('data-post-data'));

            event.preventDefault();

            data.delete = 1;

            utilsBundle.ajax.jsonPost(element.getAttribute('href'), data, {
                onSuccess: (response) => {
                    WatchlistBundle.updateWatchlist();
                },
                onError: (response) => {
                    Swal.fire({
                        icon: 'error',
                        timer: 6000,
                        timerProgressBar: true,
                        showCloseButton: true,
                        showConfirmButton: false,
                        html: response.responseText
                    });
                }
            });
        });
    }

    static initAddItemLinks() {
        const links = document.querySelectorAll('.watchlist-add-item');

        if (links.length < 1) {
            return;
        }

        utilsBundle.event.addDynamicEventListener('click', '.watchlist-add-item', (element, event) => {
            const data = JSON.parse(element.getAttribute('data-post-data'));

            event.preventDefault();

            data.delete = element.classList.contains('added') ? 1 : 0;

            utilsBundle.ajax.jsonPost(element.getAttribute('href'), data, {
                onSuccess: (response) => {
                    if (element.classList.contains('added')) {
                        element.classList.remove('added');

                        element.innerText = element.getAttribute('data-add-item-message');
                    } else {
                        element.classList.add('added');

                        element.innerText = element.getAttribute('data-delete-item-message');
                    }

                    WatchlistBundle.updateWatchlist();

                    if (!data.delete) {
                        Swal.fire({
                            icon: 'success',
                            timer: 4000,
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
                        timer: 6000,
                        timerProgressBar: true,
                        showCloseButton: true,
                        showConfirmButton: false,
                        html: response.responseText
                    });
                }
            });
        });
    }
}

export {WatchlistBundle};
