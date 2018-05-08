var jQuery = require('jquery');

(function($) {

    window.Watchlist = {
        onReady: function() {
            this.registerEvents();
        },
        registerEvents: function() {
            // show the watchlist modal
            $(document).on('click', '.watchlist-show-modal', function() {
                Watchlist.showModal($(this));
            });

            // add a item to a watchlist or display the add item modal
            $(document).on('click', '.watchlist-add-item, .watchlist-add-option', function() {
                Watchlist.addItem($(this));
            });

            $(document).on('click', '.watchlist-delete-item', function() {
                Watchlist.deleteItem($(this));
            });

            $(document).on('click', '.watchlist-empty-watchlist', function() {
                Watchlist.emptyWatchlist($(this));
            });

            $(document).on('click', '.watchlist-download-all', function() {
                Watchlist.downloadWatchlist($(this));
            });

            $(document).on('click', 'button.watchlist-download-link', function() {
                Watchlist.generateDownloadLink($(this));
            });

            $(document).on('click', '.watchlist-delete-watchlist', function() {
                Watchlist.deleteWatchlist($(this));
            });

            $(document).on('click', '.watchlist-new-and-add', function() {
                Watchlist.newAndAdd($(this));
            });

            $(document).on('click', '.watchlist-select-add', function() {
                Watchlist.addItemToSelectedWatchlist($(this));
            });

            $(document).on('change', '.watchlist-options select', function() {
                Watchlist.updateWatchlist($(this));
            });

            $(document).on('hide.bs.modal', '#watchlistModal', function() {
                setTimeout(function() {
                    $(document).find('#watchlistModal').remove();
                }, 500);
            });
        },
        showModal: function(elem) {
            var url = elem.data('action'),
                data = {
                    moduleId: '' !== elem.data('module') ? elem.data('module') : null,
                    watchlistId: '' !== elem.data('watchlist') ? elem.data('watchlist') : null,
                };

            Watchlist.doAjaxCall(url, data, true);
        },
        addItem: function(elem) {
            var uuid = elem.data('uuid') ? elem.data('uuid') : $(document).find('.item-options option:selected').val(),
                title = elem.data('title') ? elem.data('title') : $(document).find('.item-options option:selected').text(),
                url = elem.data('action'),
                downloadable = elem.data('downloadable'),
                data = {
                    'moduleId': elem.data('moduleId'),
                    'type': elem.data('type'),
                    'itemData': {
                        'options': elem.data('options') ? elem.data('options') : null,
                        'uuid': uuid ? uuid : null,
                        'title': title ? title : null,
                        'downloadable': downloadable,
                    },
                };

            Watchlist.doAjaxCallWithUpdate(url, data);
        },
        addItemToSelectedWatchlist: function(elem) {
            var url = elem.data('action'),
                uuid = elem.data('uuid') ? elem.data('uuid') : $(document).find('.item-options option:selected').val(),
                watchlist = elem.data('watchlistId') ? elem.data('watchlistId') : $(document).find('.watchlist-options option:selected').val(),
                data = {
                    'watchlistId': watchlist,
                    'type': elem.data('type'),
                    'itemData': {
                        'uuid': uuid,
                    },
                };

            Watchlist.doAjaxCallWithUpdate(url, data);
        },
        deleteItem: function(elem) {
            var url = elem.data('action'),
                data = {
                    'moduleId': elem.data('moduleId'),
                    'itemId': elem.data('id'),
                };

            Watchlist.doAjaxCallWithUpdate(url, data);
        },
        emptyWatchlist: function(elem) {
            var url = elem.data('action'),
                data = {
                    'moduleId': elem.data('moduleId'),
                    'watchlistId': elem.data('watchlistId'),
                };

            Watchlist.doAjaxCallWithUpdate(url, data);
        },
        deleteWatchlist: function(elem) {
            var url = elem.data('action'),
                data = {
                    'moduleId': elem.data('moduleId'),
                    'watchlistId': elem.data('watchlistId'),
                };

            Watchlist.doAjaxCallWithUpdate(url, data);
        },
        downloadWatchlist: function(elem) {
            var url = elem.data('action'),
                data = {
                    'watchlistId': elem.data('watchlist'),
                    'moduleId': elem.data('moduleId'),
                };

            $.ajax({
                url: url,
                dataType: 'JSON',
                method: 'POST',
                data: data,
                success: function(data) {
                    window.location.href = window.location.href + '?file=' + data.result.data.file;
                },
            });
        },
        showOptionModal: function(elem) {
            var url = elem.data('action'),
                data = {
                    'moduleId': elem.data('module-id'),
                    'type': elem.data('type'),
                    'options': elem.data('options') ? elem.data('options') : null,
                    'uuid': elem.data('uuid') ? elem.data('uuid') : null,
                };

            Watchlist.doAjaxCall(url, data, true);
        },
        generateDownloadLink: function(elem) {
            var url = elem.data('action'),
                data = {
                    'moduleId': elem.data('moduleId') ? elem.data('moduleId') : null,
                    'watchlistId': elem.data('watchlistId'),
                };

            $.ajax({
                url: url,
                dataType: 'JSON',
                method: 'POST',
                data: data,
                success: function(data) {
                    if (data.result.data.link) {
                        $(document).find('.watchlist-download-link-href').text(data.result.data.link);
                        $(document).find('.watchlist-download-link-href').attr('href', data.result.data.link);
                    }

                    if (data.result.data.message) {
                        $('body').append(data.result.data.message);
                        Watchlist.ajaxCompleteCallback();
                    }
                },
            });
        },
        newAndAdd: function(elem) {
            var url = elem.data('action'),
                selected = $(document).find('.item-options').length ? $(document).find('.item-options option:selected').val() : null,
                uuid = elem.data('uuid') ? elem.data('uuid') : null,
                title = elem.data('title') ? elem.data('title') : null,
                singleItem = {'uuid': uuid, 'title': title},
                data = {
                    'moduleId': elem.data('moduleId'),
                    'itemData': selected ? selected : singleItem,
                    'watchlist': $(document).find('#watchlist-name').val(),
                    'type': 'file',
                    'durability': $(document).find('#watchlist-durability').length ? $(document).find('#watchlist-durability option:selected').val() : null,
                };

            Watchlist.doAjaxCallWithUpdate(url, data);
        },
        updateWatchlist: function(elem) {
            var url = elem.data('action'),
                data = {
                    'moduleId': elem.data('moduleId') ? elem.data('moduleId') : null,
                    'watchlistId': elem.find('option:selected').val(),
                };

            Watchlist.doAjaxCallWithUpdate(url, data);
        },
        doAjaxCall: function(url, data, closeOnSucces) {
            $.ajax({
                url: url,
                dataType: 'JSON',
                method: 'POST',
                data: data,
                success: function(data, textStatus, jqXHR) {
                    $('body').append(data.result.data.response);

                    if (closeOnSucces) {
                        $('#watchlistModal').modal('toggle');
                    }

                    Watchlist.ajaxCompleteCallback();
                },
            });
        },
        doAjaxCallWithUpdate: function(url, data) {
            console.log(data);
            console.log(url);
            $.ajax({
                url: url,
                dataType: 'JSON',
                method: 'POST',
                data: data,
                success: function(data, textStatus, jqXHR) {

                    if (undefined !== data.result.data.message) {
                        $('body').append(data.result.data.message);
                    }

                    if (undefined !== data.result.data.watchlist) {
                        $(document).find('.watchlist-body').replaceWith(data.result.data.watchlist);
                    }

                    if (undefined !== data.result.data.modal) {
                        $('body').append(data.result.data.modal);
                        $('#watchlistModal').modal('toggle');
                    }

                    if (undefined !== data.result.data.modalTitle) {
                        $(document).find('#watchlist-modalTitle').text(data.result.data.modalTitle);
                    }

                    if (undefined !== data.result.data.message && undefined === data.result.data.watchlist) {
                        $('#watchlistModal').modal('toggle');
                    }

                    if (data.result.data.count > 0) {
                        if ($(document).find('#watchlist-badge').length) {
                            $(document).find('#watchlist-badge').text(data.result.data.count);
                        } else {
                            $(document).find('.watchlist-show-modal').prepend('<span class="watchlist-badge">' + data.result.data.count + '</span>');
                        }
                    }
                    else {
                        $(document).find('.watchlist-show-modal .watchlist-badge').remove();
                    }

                    Watchlist.ajaxCompleteCallback();
                },
            });
        },
        ajaxCompleteCallback: function() {
            // remove the loading animation
            $('.watchlist-loader').remove();

            // remove messages with a delay
            $(document).find('.watchlist-message').delay(2500).fadeOut(300, function() {
                $(this).remove();
            });
        },
    };

    module.exports = Watchlist;

    $(document).ready(function() {
        Watchlist.onReady();
    });

})(jQuery);
