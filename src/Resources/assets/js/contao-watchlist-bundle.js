import {ContaoWatchlistBundle} from './contao-watchlist-bundle.class';
require('./watchlist_legacy');

document.addEventListener('DOMContentLoaded', function () {
    let watchlist = new ContaoWatchlistBundle();
    watchlist.init();
});