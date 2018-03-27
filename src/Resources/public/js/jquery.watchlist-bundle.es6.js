var jQuery = require('jquery');

(function ($) {

    window.Watchlist = {
        onReady: function () {
            this.registerEvents();
            // this.registerAdd();
            // this.registerMultipleAdd();
            // this.registerDelete();
            // this.registerDeleteAll();
            // this.registerWatchlistModal();
            // this.registerWatchlistSelect();
            // this.registerDownloadLink();
            // this.registerMultipleSelectAdd();
        },
        registerEvents: function () {
            // show the watchlist modal
            $(document).on('click', '.watchlist-show-modal', function () {
                $('#watchlistModal').remove();
                Watchlist.showModal($(this));
            });

            // add a item to a watchlist or display the add item modal
            $(document).on('click', '.watchlist-add-item', function () {
                $('#watchlistModal').remove();

                if (undefined !== $(this).data('options')) {
                    Watchlist.showOptionModal($(this));
                }
                else {
                    Watchlist.addItem($(this));
                }
            });

            $(document).on('click', '.watchlist-add-option', function () {
                Watchlist.addItem($(this));
            });

            $(document).on('click', '.watchlist-delete-item', function () {
                Watchlist.deleteItem($(this));
            });

            $(document).on('click', '.watchlist-empty-watchlist', function () {
                Watchlist.emptyWatchlist($(this));
            });

            $(document).on('click','.watchlist-download-all',function(){
                Watchlist.downloadWatchlist($(this));
            });
        },
        showModal: function (elem) {
            var url = elem.data('action'),
                data = {
                    'moduleId': elem.data('module') ? elem.data('module') : null,
                    'watchlistId': elem.data('watchlist') ? elem.data('watchlist') : null
                };

            Watchlist.doAjaxCall(url, data, true);
        },
        addItem: function (elem) {
            var uuid = elem.data('uuid') ? elem.data('uuid') : $(document).find('#watchlist-select-input select option:selected').val(),
                url = elem.data('action'),
                data = {
                    'moduleId': elem.data('moduleId'),
                    'type': elem.data('type'),
                    'options': elem.data('options') ? elem.data('options') : null,
                    'uuid': uuid ? uuid : null
                };

            Watchlist.doAjaxCall(url, data, true);

        },
        deleteItem: function (elem) {
            var url = elem.data('action'),
                data = {
                    'moduleId': elem.data('moduleId'),
                    'itemId': elem.data('id')
                };

            Watchlist.doAjaxCallWithUpdate(url,data);
        },
        emptyWatchlist: function (elem) {
            var url = elem.data('action'),
                data = {
                    'moduleId': elem.data('moduleId'),
                    'watchlistId': elem.data('watchlistId')
                };

            Watchlist.doAjaxCallWithUpdate(url,data);
        },
        downloadWatchlist: function(elem) {
            var url = elem.data('action'),
                data = {
                    'watchlistId': elem.data('watchlist')
                };

            $.ajax({
                url: url,
                dataTyoe: 'JSON',
                method: 'POST',
                data: data,
                success: function(data) {
                    console.log(data);

                    // window.location.href = data.result.data.file;
                },
                error: function(xhr, status, error) {
                    console.log(xhr);
                    console.log(status);
                    console.log(error);
                }
            });
        },
        showOptionModal: function (elem) {
            var url = elem.data('action'),
                data = {
                    'moduleId': elem.data('module-id'),
                    'type': elem.data('type'),
                    'options': elem.data('options') ? elem.data('options') : null,
                    'uuid': elem.data('uuid') ? elem.data('uuid') : null
                };

            Watchlist.doAjaxCall(url, data, true);
        },
        doAjaxCall: function (url, data, closeOnSucces) {
            $.ajax({
                url: url,
                dataTyoe: 'JSON',
                method: 'POST',
                data: data,
                success: function (data, textStatus, jqXHR) {
                    $('body').append(data.result.data.response);

                    if (closeOnSucces) {
                        $('#watchlistModal').modal('toggle');
                    }

                    Watchlist.ajaxCompleteCallback();
                }
            });
        },
        doAjaxCallWithUpdate: function(url,data) {
            $.ajax({
                url: url,
                dataTyoe: 'JSON',
                method: 'POST',
                data: data,
                success: function (data, textStatus, jqXHR) {
                    $('body').append(data.result.data.message);
                    $(document).find('.watchlist-item-list').replaceWith(data.result.data.watchlist);

                    if (data.result.data.count > 0) {
                        $(document).find('.watchlist-show-modal .watchlist-badge').text(data.result.data.count);
                    }
                    else {
                        $(document).find('.watchlist-show-modal .watchlist-badge').remove();
                    }

                    Watchlist.ajaxCompleteCallback();
                }
            });
        },
        ajaxCompleteCallback: function () {
            // remove the loading animation
            $('.watchlist-loader').remove();

            // remove messages with a delay
            $(document).find('.watchlist-message').delay(2000).fadeOut(300, function () {
                $(this).remove();
            });
        }
    };

    $(document).ready(function () {
        Watchlist.onReady();
    });


})(jQuery);
