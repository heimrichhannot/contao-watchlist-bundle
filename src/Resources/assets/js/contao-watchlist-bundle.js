import '@hundh/contao-utils-bundle';
import Swal from "sweetalert2";

class WatchlistBundle {
    static init() {
        WatchlistBundle.initAddItemLinks();
    }

    static initAddItemLinks() {
        const links = document.querySelectorAll('.watchlist-add-item');

        if (links.length < 1) {
            return;
        }

        utilsBundle.event.addDynamicEventListener('click', '.watchlist-add-item', (element, event) => {
            event.preventDefault();

            utilsBundle.ajax.jsonPost(element.getAttribute('href'), JSON.parse(element.getAttribute('data-post-data')), {
                onSuccess: (response) => {
                    Swal.fire({
                        icon: 'success',
                        timer: 4000,
                        timerProgressBar: true,
                        showCloseButton: true,
                        showConfirmButton: false,
                        html: 'Die Datei wurde erfolgreich zu Ihrer Merkliste hinzugefügt.'
                    });
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
            })
        });
    }
}

export {WatchlistBundle};
