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
                Watchlist.showModal();
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

            $(document).on('click', '.watchlist-add-option', function(){
                Watchlist.addItem($(this));
            });
        },
        showModal: function () {
            var url = $('.watchlist-show-modal').data('action'),
                data = {
                    'moduleId': $('.watchlist-show-modal').data('module')
                };

            Watchlist.doAjaxCallForModal(url, data);
        },
        addItem: function (elem) {
            var uuid = elem.data('uuid') ? elem.data('uuid') : $(document).find('#watchlist-select-input select option:selected').val(),
                url = elem.data('action'),
                data = {
                    'moduleId': elem.data('moduleId'),
                    'type': elem.data('file')
                };



            console.log(uuid);

        },
        showOptionModal: function(elem) {
            var url = elem.data('action'),
                data = {
                    'moduleId': elem.data('module-id'),
                    'type': elem.data('type'),
                    'options': elem.data('options') ? elem.data('options') : null,
                    'uuid': elem.data('uuid') ? elem.data('uuid') : null
                };

            Watchlist.doAjaxCallForModal(url, data);
        },
        doAjaxCallForModal: function(url, data) {
            $.ajax({
                url: url,
                dataTyoe: 'JSON',
                method: 'POST',
                data: data,
                success: function (data, textStatus, jqXHR) {
                    $('body').append(data.result.data.modal);
                    $('#watchlistModal').modal('toggle');
                    $('.watchlist-loader').remove();
                }
            });
        }
    };

    $(document).ready(function () {
        Watchlist.onReady();
    });


})(jQuery);
