/*
 * Copyright (c) 2017. ProVision Media Group Ltd. <http://provision.bg>
 * Venelin Iliev <http://veneliniliev.com>
 */

(function () {

    $('body').on('click', 'button.media-manager', mediaManagerRun);

    var htmlModalMarkup = '<div class="modal modal-default" \
    id="mediaManagerModal" \
    role="dialog" \
    aria-labelledby="mediaManagerModalLabel" \
    aria-hidden="true"> \
        <div class="modal-dialog"> \
        <div class="modal-content"> \
        <div class="modal-header"> \
        <button type="button" class="close" aria-label="Close"> \
        <span aria-hidden="true">×</span></button> \
    <h4 class="modal-title"><i class="fa fa-picture-o" aria-hidden="true"></i> Media manager </h4> \
    </div>\
    <div class="modal-body">\
        <div class="mediaManager-filter">\
            <!-- show filters --> \
        </div>\
        <div class="clearfix"></div>\
        <div class="mediaManager-items-container">\
            <!-- show items --> \
        </div>\
        <div class="clearfix"></div>\
    </div>\
    <div class="clearfix"></div>\
    <div class="modal-footer">\
        <div class="progress">\
            <div class="progress-bar progress-bar-success progress-bar-striped active" role="progressbar"></div>\
        </div>\
        <div class="clearfix"></div>\
        <div class="btn-group btn-group-sm pull-left actions-group" role="group">\
            <button type="button" class="btn btn-danger btn-delete-selected ladda-button" data-style="zoom-in"><i class="fa fa-trash-o"></i> delete selected </button>\
            <button type="button" class="btn btn-default btn-select-all" data-status="true"><i class="fa fa-check-square-o" aria-hidden="true"></i> select all</button>\
            <button type="button" class="btn btn-default btn-select-all" data-status="false"><i class="fa fa-square-o" aria-hidden="true"></i> deselect all</button>\
            <button type="button" class="btn btn-success btn-reload ladda-button" data-style="zoom-in"><i class="fa fa-refresh"></i> reload files </button>\
        </div>\
        <div class="pull-right">\
            <span class="btn btn-sm btn-primary upload-button fileinput-button">\
                <i class="fa fa-upload" aria-hidden="true"></i>\
                <span>upload</span>\
                <input class="upload-input" type="file" name="file" multiple>\
            </span>\
        </div>\
    </div>\
    </div>\
    </div>\
    </div>';

    function mediaManagerRun(e) {
        e.preventDefault();

        var $this = $(this); //button object

        /*
         Set options
         */
        var config = $.extend({
            "default": true
        }, $this.data('config'));

        console.log(config);

        $('body').append(htmlModalMarkup);
        console.log('append modal');

        var mediaManagerModal = $('#mediaManagerModal');

        //add events
        mediaManagerModal.on('show.bs.modal', function () {
            console.log('show.bs.modal');

            var itemsContainer = mediaManagerModal.find('.mediaManager-items-container');

            /*
             List files
             */
            function loadItems(callback) {
                $.ajax({
                    method: "GET",
                    url: config.routes.index,
                    data: config.filters
                }).done(function (response) {
                    itemsContainer.html('');
                    itemsContainer.append(response);

                    //run callback after load
                    if (callback !== undefined) {
                        callback();
                    }
                });
            }

            function loadItem(id, callback) {
                $.ajax({
                    method: "GET",
                    url: config.routes.index + '/' + id,
                }).done(function (response) {

                    itemsContainer.find('div.media-item[data-id=' + id + ']').replaceWith(response);

                    //run callback after load
                    if (callback !== undefined) {
                        callback();
                    }
                });
            }

            loadItems();

            /*
             Upload files
             @todo: инсталиране на библиотеката ако я няма...
             */
            mediaManagerModal.find('input.upload-input').fileupload({
                url: config.routes.index,
                method: 'POST',
                formData: config.filters,
                add: function (e, data) {
                    data.submit();
                },
                done: function (e, data) {
                    itemsContainer.find('div.callout').remove(); //скриване съобщението за липсващи елементи
                    itemsContainer.append(data.result);
                },
                stop: function () {
                    mediaManagerModal.find('.progress').hide();
                },
                start: function () {
                    mediaManagerModal.find('.progress-bar').css(
                        'width',
                        '0%'
                    );
                    mediaManagerModal.find('.progress').show();
                },
                progressall: function (e, data) {
                    var progress = parseInt(data.loaded / data.total * 100, 10);
                    mediaManagerModal.find('.progress-bar').css(
                        'width',
                        progress + '%'
                    );
                }
            }).prop('disabled', !$.support.fileInput).parent().addClass($.support.fileInput ? undefined : 'disabled');

            /*
             sortable
             */
            if (itemsContainer.sortable("instance") !== undefined) {
                itemsContainer.sortable("destroy");
            }
            itemsContainer.sortable({
                cancel: '.callout', //да не може да се драгва съобщението за липсващи елементи
                update: function (a, b) {

                    var $sorted = b.item;

                    var $previous = $sorted.prev();
                    var $next = $sorted.next();

                    if ($previous.length > 0) {
                        var postData = {
                            type: 'sort',
                            sortType: 'moveAfter',
                            positionEntityId: $previous.data('id')
                        };
                    } else if ($next.length > 0) {
                        var postData = {
                            type: 'sort',
                            sortType: 'moveBefore',
                            positionEntityId: $next.data('id')
                        };
                    } else {
                        alert('MediaManager: Something wrong!');
                        return;
                    }

                    $.ajax({
                        type: "PUT",
                        url: config.routes.index + '/' + b.item.data('id'),
                        data: postData,
                        success: function (data) {
                            //code on success
                        },
                        error: function (data) {
                            alert(data);
                        }
                    });
                }
            });

            itemsContainer.disableSelection();

            /*
             select / deselect all
             */
            mediaManagerModal.find('.btn-select-all').unbind('click').on('click', function () {
                var $this = $(this);

                itemsContainer.find('>.media-item input[type=checkbox]').each(function () {
                    $(this).prop('checked', ($this.attr('data-status') == 'true' ? true : false));
                });
            });

            /*
             reload files
             */
            mediaManagerModal.find('.btn-reload').unbind('click').on('click', function () {
                console.log('reload');
                var l = Ladda.create(this);
                l.start();
                loadItems(function () {
                    l.stop();
                });
            });

            /*
             file rename
             */
            itemsContainer.on('click', '.media-item a.file-rename', function (e) {
                e.preventDefault();

                var elementRow = $(this).closest('.media-item').data('row');

                $.confirm({
                    title: 'File rename',
                    content: '<div class="form-group">\
                        <label class="control-label">New file name</label>\
                    <input autofocus type="text" value="' + elementRow.file + '" placeholder="Enter file name" class="form-control">\
                    <p class="text-danger help-block" style="display:none"></p>\
                    </div>',
                    buttons: {
                        Save: {
                            text: 'Save',
                            //btnClass: 'btn-warning',
                            action: function () {
                                var input = this.$content.find('input');
                                var errorText = this.$content.find('.text-danger');
                                if (input.val() == '') {
                                    errorText.html('Please don\'t keep the name field empty!').slideDown(200);
                                    return false;
                                } else {
                                    $.ajax({
                                        type: "PUT",
                                        url: config.routes.index + '/' + elementRow.id,
                                        data: {
                                            'type': 'rename',
                                            'name': input.val()
                                        },
                                        success: function (data) {
                                            /*
                                             @todo: да презареди само елемента който е бутан!
                                             */
                                            loadItem(elementRow.id);
                                        },
                                        error: function (data) {
                                            $.alert({
                                                title: 'Error rename file',
                                                content: data,
                                            });
                                        }
                                    });
                                }
                            }
                        },
                        Close: function () {
                            // do nothing.
                        }
                    }
                });

            });

            /*
            Rotate
             */
            itemsContainer.on('click', '.media-item a.rotate', function (e) {
                e.preventDefault();

                var $this = $(this);
                var elementRow = $this.closest('.media-item').data('row');

                $.ajax({
                    type: "PUT",
                    url: config.routes.index + '/' + elementRow.id,
                    data: {
                        type: 'rotate',
                        angle: $this.data('angle')
                    },
                    success: function (data) {
                        /*
                         @todo: да презареди само елемента който е бутан!
                         */
                        loadItem(elementRow.id);
                    },
                    error: function (data) {
                        $.alert({
                            title: 'Error rotate!',
                            content: data,
                        });
                    }
                });
            });

            /*
             edit description & visibility
             */
            itemsContainer.on('click', '.media-item a.edit-visibility', function (e) {
                e.preventDefault();

                var $this = $(this);

                var elementRow = $this.closest('.media-item').data('row');

                $.confirm({
                    title: 'File description',
                    content: 'url:' + config.routes.index + '/' + elementRow.id + '/edit',
                    buttons: {
                        Save: {
                            text: 'Save',
                            //btnClass: 'btn-warning',
                            action: function () {
                                $.ajax({
                                    type: "PUT",
                                    url: config.routes.index + '/' + elementRow.id,
                                    data: $('div.media-manager-form form').serialize() + '&type=update',
                                    success: function (data) {
                                        /*
                                         @todo: да презареди само елемента който е бутан!
                                         */
                                        loadItem(elementRow.id);
                                    },
                                    error: function (data) {
                                        $.alert({
                                            title: 'Error description',
                                            content: data,
                                        });
                                    }
                                });
                            }
                        },
                        Close: function () {
                            // do nothing.
                        }
                    }
                });
            });


            /*
             delete selected files
             */
            mediaManagerModal.find('.btn-delete-selected').unbind('click').on('click', function () {
                var $this = $(this);

                var l = Ladda.create(this);
                l.start();

                var checked = [];
                itemsContainer.find('>.media-item input[type=checkbox]:checked').each(function () {
                    checked.push($(this).val());
                });

                if (checked.length < 1) {
                    return false;
                }

                $.confirm({
                    title: translates.confirm_title,
                    content: translates.confirm_text,
                    confirmButtonClass: 'btn-danger',
                    cancelButtonClass: 'btn-info',
                    buttons: {
                        confirm: function () {
                            $.ajax({
                                type: "DELETE",
                                url: config.routes.index + '/' + config.filters.mediaable_id,
                                data: {
                                    checked: checked
                                },
                                success: function (data) {
                                    $.each(data, function (e, id) {
                                        $('div.media-item[data-id=' + id + ']').fadeOut();
                                    });
                                    l.stop();
                                },
                                error: function (data) {
                                    $.alert({
                                        title: 'Mass delete error',
                                        content: ''
                                    });
                                    l.stop();
                                }
                            });
                        },
                        cancel: function () {
                        }
                    }
                });


            });


            /*
             delete single item
             */
            mediaManagerModal.on('click', 'button.btn-delete', function () {

                var element = $(this).closest('.media-item');

                $.confirm({
                    title: translates.confirm_title,
                    content: translates.confirm_text,
                    buttons: {
                        confirm: function () {
                            $.ajax({
                                type: "DELETE",
                                url: config.routes.index + '/' + element.attr('data-id'),
                                success: function (data) {
                                    element.fadeOut();
                                },
                                error: function (data) {
                                    $.alert({
                                        title: 'Delete error',
                                        content: ''
                                    });
                                }
                            });
                        },
                        cancel: function () {
                        }
                    }
                });
            });
        });

        mediaManagerModal.on('hidden.bs.modal', function (e) {
            console.log('hidden.bs.modal');
            //destroy markup
            mediaManagerModal.remove();
        })

        //show modal
        mediaManagerModal.modal('show');

        //close modal button
        mediaManagerModal.find('button.close').unbind('close').click(function () {
            //hide modal
            mediaManagerModal.modal('hide');
        });
    }

})();