var jQuery = require('jquery');

(function ($) {

    window.Watchlist = {
        onReady: function () {
            this.registerEvents();
            this.registerAdd();
            this.registerMultipleAdd();
            this.registerDelete();
            this.registerDeleteAll();
            // this.registerWatchlistModal();
            this.registerWatchlistSelect();
            this.registerDownloadLink();
            this.registerMultipleSelectAdd();
        },
        registerEvents: function () {
            // show the watchlist modal
            $(document).on('click', '.watchlist-show-modal', function () {
                Watchlist.showModal();
            });

            // add a item to a watchlist or display the add item modal
            $(document).on('click', '.watchlist-add-item', function () {
                Watchlist.addItem($(this));
            });
        },
        showModal: function () {
            $('#watchlistModal').remove();
            $.ajax({
                url: $('.watchlist-show-modal').data('action'),
                dataType: 'JSON',
                method: 'POST',
                data: {'moduleId': $('.watchlist-show-modal').data('module')},
                success: function (data, textStatus, jqXHR) {
                    console.log(data);

                    $('body').append(data.result.html);
                    $('#watchlistModal').modal('toggle');
                }
            });
        },
        addItem: function (elem) {
            $('#watchlistModal').remove();

            $.ajax({
                url: elem.data('action'),
                dataTyoe: 'JSON',
                method: 'POST',
                data: {
                    'itemId': elem.data('item-id'),
                    'moduleId': elem.data('module-id'),
                    'dataContainer': elem.data('dc')
                },
                success: function (data, textStatus, jqXHR) {
                    $('body').append(data.result.html.modal);
                    $('#watchlistModal-' + data.result.html.id).modal('toggle');
                },
                error: function (xhr, status, error) {
                    console.log(xhr);
                    console.log(status);
                    console.log(error);
                }
            });


            $.ajax({
                url: $('.watchlist-show-modal').data('watchlistShowModalAction'),
                success: function (data, textStatus, jqXHR) {
                    $('body').append(data.result.html);
                    $('#watchlistModal').modal('toggle');
                }
            });
        },

        registerMultipleAdd: function () {
            $(document).on('click', '.watchlist-multiple-add', function () {
                var btn = $(this);
                var input = $('#watchlist-input-' + btn.data('id'));
                var durability = $('#watchlist-select-durability-' + btn.data('id')).find(':selected').val();
                if (durability === 'undefined') {
                    durability = 0;
                }
                if (!input.val()) {
                    input.addClass('watchlist-empty-input');
                    return;
                }
                $.ajax({
                    url: $('#watchlist-multiple-add-' + btn.data('id')).data('watchlistAddAction') + '&watchlist=' + input.val() + '&durability=' + durability,
                    success: function (data, textStatus, jqXHR) {
                        Watchlist.showNotification(data.result.html.notification);
                        $('#watchlistModal-' + data.result.html.id).modal('hide');
                    }
                });
            });
        },
        registerMultipleSelectAdd: function () {
            $(document).on('click', '.watchlist-multiple-select-add', function () {
                var btn = $(this);
                var select = $('#watchlist-select-input-' + btn.data('id'));
                if (select.find(':selected').val() <= 0) {
                    select.children('div').addClass('watchlist-empty-input');
                    return;
                }
                $.ajax({
                    url: $('#watchlist-multiple-select-add-' + btn.data('id')).data('watchlistAddAction') + '&watchlist=' + select.find(':selected').val(),
                    success: function (data, textStatus, jqXHR) {
                        Watchlist.showNotification(data.result.html.notification);
                        $('#watchlistModal-' + data.result.html.id).modal('hide');
                    }
                });
            });
        },
        showNotification: function (notification) {
            $('.watchlist-notification').replaceWith(notification);
            $('#watchlist-notify').fadeIn(600);
            setTimeout(function () {
                $('#watchlist-notify').fadeOut();
            }, 3000);
        },
        registerAdd: function () {
            $(document).on('click', '.watchlist-add', function () {
                var btn = $(this);
                $.ajax({
                    url: $('#watchlist-add-' + btn.data('id')).data('watchlistAddAction'),
                    success: function (data, textStatus, jqXHR) {
                        $('#watchlist-add-' + data.result.html.id).removeClass('watchlist-add');
                        $('#watchlist-add-' + data.result.html.id).addClass('watchlist-delete-item watchlist-added');
                        Watchlist.showNotification(data.result.html.notification);
                    }
                });
            });
        },
        registerWatchlistModal: function () {
            $(document).on('click', '.watchlist-add-modal', function () {
                var btn = $(this);
                $('#watchlistModal-' + btn.data('id')).remove();
                $.ajax({
                    url: $('#watchlist-add-modal-' + btn.data('id')).data('watchlistShowModalAddAction'),
                    success: function (data, textStatus, jqXHR) {
                        $('body').append(data.result.html.modal);
                        $('#watchlistModal-' + data.result.html.id).modal('toggle');
                    }
                });
            });


        },
        registerDelete: function () {
            $(document).on('click', '.watchlist-delete-item', function () {
                var btn = $(this);
                $.ajax({
                    url: $('#watchlist-delete-item-' + btn.data('id')).data('watchlistDeleteAction'),
                    success: function (data, textStatus, jqXHR) {
                        $('#watchlist-add-' + data.result.html.id).removeClass('watchlist-delete-item watchlist-added');
                        $('#watchlist-add-' + data.result.html.id).addClass('watchlist-add');
                        Watchlist.showNotification(data.result.html.notification);
                        Watchlist.watchlistUpdate();
                    }
                });
            });
        },
        registerDeleteAll: function () {
            $(document).on('click', '.watchlist-delete-all-button', function () {
                $.ajax({
                    url: $('.watchlist-delete-all-button').data('watchlistDeleteAllAction'),
                    success: function (data, textStatus, jqXHR) {
                        $('.watchlist-added').addClass('watchlist-add');
                        $('.watchlist-added').removeClass('watchlist-delete-item watchlist-added');
                        Watchlist.showNotification(data.result.html.notification);
                        Watchlist.watchlistUpdate();
                    }
                });
            });
        },
        watchlistUpdate: function () {
            $('.watchlist-loader').show();
            $.ajax({
                url: $('.watchlist-show-modal').data('watchlistUpdateAction'),
                success: function (data, textStatus, jqXHR) {
                    $('.watchlist-body').replaceWith(data.result.html);
                    $('.watchlist-download-link-href').html('&nbsp;');
                    $('.watchlist-download-link-text').removeClass('active');
                    $('.watchlist-loader').hide();
                }
            });
        },
        registerWatchlistSelect: function () {
            $(document).on('change', '#watchlist-selector', function () {
                $.ajax({
                    url: $('#watchlist-selector').data('watchlistSelectAction') + '&id=' + $('#watchlist-selector').find(':selected').val(),
                    success: function (data, textStatus, jqXHR) {
                        Watchlist.watchlistUpdate();
                    }
                });
            });
        },
        registerDownloadLink: function () {
            $(document).on('click', '.watchlist-download-link-button', function () {
                $.ajax({
                    url: $('.watchlist-download-link-button').data('watchlistDownloadLinkAction'),
                    success: function (data, textStatus, jqXHR) {
                        if (data.result.html !== false) {
                            $('.watchlist-download-link-href').attr('href', data.result.html);
                            $('.watchlist-download-link-href').html(data.result.html);
                            $('.watchlist-download-link-text').addClass('active');
                        }
                    }
                });
            });
        }
    };

    $(document).ready(function () {
        Watchlist.onReady();
    });


})(jQuery);
