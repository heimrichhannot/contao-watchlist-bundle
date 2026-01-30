import Swal from "sweetalert2";

export default function initWatchlistBundle() {
    if (!window || !window.document) {
        return;
    }
    if (window.huhWatchlistBundleInitialized) {
        return;
    }
    window.huhWatchlistBundleInitialized = true;

    initOpenLink().then();
    initWatchlistLinks().then();
    initAddItemLinks().then();
}

export function swalError(text) {
    Swal.fire({
        icon: 'error',
        timer: 0,
        timerProgressBar: true,
        showCloseButton: true,
        showConfirmButton: false,
        html: text,
    });
}

export function dynamicEventListener(eventName, selector, callback, options) {
    const handler = async (event) => {
        const element = event.target?.closest?.(selector);
        if (!element || !document.body.contains(element)) return;
        await callback(element, event);
    };
    document.body.addEventListener(eventName, handler, options);
    return () => document.body.removeEventListener(eventName, handler, options);
}

export async function initOpenLink() {
    dynamicEventListener(
        'click',
        '.open-watchlist:not([data-bs-toggle])',
        async (element, event) => {
            event.preventDefault();

            let contentWrapper = element.nextElementSibling;
            if (!contentWrapper) {
                return;
            }

            contentWrapper.classList.toggle('opened');
            contentWrapper.parentElement.classList.toggle('opened');
        }
    );
}

export async function updateWatchlist() {
    let wrapper = document.querySelector('.watchlist-wrapper');
    if (!wrapper) {
        return;
    }

    const url = wrapper.getAttribute('data-watchlist-update-url');
    if (!url) {
        return;
    }

    const response = await fetch(url, {
        method: 'GET',
        credentials: 'same-origin',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
        }
    });

    if (!response.ok) {
        return;
    }

    const contentElement = document.querySelector('.watchlist-content');
    if (contentElement) {
        contentElement.innerHTML = await response.text();
    }

    wrapper.dispatchEvent(new CustomEvent('huh_watchlist_list_updated', {
        bubbles: true
    }));
}

export async function initWatchlistLinks() {
    // clear watchlist
    dynamicEventListener(
        'click',
        '.mod_watchlist .clear, .watchlist-content .clear',
        async (element, event) => {
            event.preventDefault();

            const url = element.getAttribute('href');
            if (!url) {
                swalError('No URL found');
                return;
            }

            const response = await fetch(url, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json; charset=utf-8',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin'
            });

            if (!response.ok) {
                swalError(await response.text());
                return;
            }

            await updateWatchlist();

            for (const link of document.querySelectorAll('.watchlist-add-item.added')) {
                toggleAddItemLink(link);
            }
        }
    );

    // delete items
    dynamicEventListener(
        'click',
        '.mod_watchlist .delete-item, .watchlist-content .delete-item',
        async (element, event) => {
            event.preventDefault();

            let data = JSON.parse(element.getAttribute('data-post-data')) || {};
            data.delete = 1;

            const url = element.getAttribute('href');
            if (!url) {
                swalError('No URL found');
                return;
            }

            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json; charset=utf-8',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify(data),
            });

            if (!response.ok) {
                swalError(await response.text());
                return;
            }

            await updateWatchlist();

            const hash = element.getAttribute('data-hash');
            if (!hash) {
                return;
            }

            for (const link of document.querySelectorAll(`.watchlist-add-item[data-hash="${hash}"]`)) {
                toggleAddItemLink(link);
            }
        }
    );

    // share
    dynamicEventListener(
        'click',
        '.mod_watchlist .share, .watchlist-content .share',
        async (element, event) => {
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
        }
    );

    dynamicEventListener(
        'click',
        '.watchlist-copy-to-clipboard',
        async (element, event) => {
            event.preventDefault();

            if (!window.isSecureContext) {
                console.error('Copying to clipboard is only allowed with HTTPS');
                return;
            }

            let textInput = element.closest('.share-url').querySelector('.url');
            if (!textInput || !textInput.value.length) {
                swalError('No URL found');
                return;
            }

            try {
                await navigator.clipboard.writeText(textInput.value);
            } catch (error) {
                swalError('Failed to copy to clipboard: ' + error.message);
                return;
            }

            element.classList.add('added');
            textInput.classList.add('added');
        }
    );
}

export function toggleAddItemLink(element) {
    if (element.classList.contains('added')) {
        element.classList.remove('added');
        element.innerText = element.getAttribute('data-add-item-message');
    } else {
        element.classList.add('added');
        element.innerText = element.getAttribute('data-delete-item-message');
    }
}

export async function initAddItemLinks(scope = null) {
    dynamicEventListener(
        'click',
        '.watchlist-add-item',
        async (element, event) => {
            event.preventDefault();

            const data = JSON.parse(element.getAttribute('data-post-data'));
            data.delete = element.classList.contains('added') ? 1 : 0;

            const url = element.getAttribute('href');
            if (!url) {
                return;
            }

            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json; charset=utf-8',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify(data),
            });

            if (!response.ok) {
                swalError(await response.text());
                return;
            }

            toggleAddItemLink(element);

            await updateWatchlist();

            if (!data.delete) {
                Swal.fire({
                    icon: 'success',
                    timer: 0,
                    timerProgressBar: true,
                    showCloseButton: true,
                    showConfirmButton: false,
                    html: element.getAttribute('data-item-added-message') || 'Item added'
                });
            }
        }
    );
}
