(function(window, $) {
    'use strict';

    /*
        Use function because it can be instantiate multiple times
        window to make it available globally
     */
    window.ImageApp = function ($wrapper) {
        this.$wrapper = $wrapper;
        this.helper = new Helper(this.$wrapper, this);
        this.fileList = [];
        this.totalFile = 0;
        this.tagsList = [];
        /*
            bind so 'this' always refer to the same 'this' even in callback methods
            use currentTarget to get previous 'this' object in those methods (eg $link in handleImageDelete)
         */
        this.$wrapper.find('.js-delete-img').on('click', this.handleImageDelete.bind(this));
        this.$wrapper.find('.js-new-image-form').on('submit', this.handleNewFormSubmit.bind(this));
        this.$wrapper.find('.js-file-upload').on('change', this.handleAddFileUpload.bind(this));
        this.$wrapper.find('.js-tags-input').on('keyup', this.handleAddTag.bind(this));
        this.$wrapper.find('#js-tags-list').on('click', 'a.js-img-tag-remove', this.handleRemoveTag.bind(this));
    };

    /*
        Prototype to be able to instantiate object / use in a non static way
        extend like array_merge in PHP, not to always have to write 'window.ImageApp.prototype'...
     */
    $.extend(window.ImageApp.prototype, {
        handleImageDelete: function (e) {
            // Prevent the '#' in URL
            e.preventDefault();

            var $link = $(e.currentTarget);
            $link.addClass('text-danger');
            $link.find('.fa')
                .removeClass('fa-trash')
                .addClass('fa-spinner')
                .addClass('fa-spin');

            var deleteUrl = $link.data('url');
            var $row = $link.closest('tr');
            $.ajax({
                url: deleteUrl,
                method: 'DELETE',
                success: function() {
                    $row.fadeOut();
                }
            });
        },

        handleNewFormSubmit: function (e) {
            e.preventDefault();

            var $form = $(e.currentTarget);

            var $fileInput = $form.find('input[type="file"]');
            var $tagsInput = $form.find('#image_tags');
            var $tokenInput = $form.find('#image__token');

            var token = $tokenInput.val();

            var nameInputImage = $fileInput.attr('name');
            var nameInputToken = $tokenInput.attr('name');
            var nameInputTags = $tagsInput.attr('name');

            var $progressBar = this.$wrapper.find('progress');
            $progressBar.attr('max', this.totalFile);

            this.fileList.forEach(function (file) {
                if(file !== null) {
                    this.helper.sendFile(
                        $form.attr('action')
                        , nameInputImage, file
                        , nameInputTags, this.tagsList
                        , nameInputToken, token
                        , $progressBar
                    );
                }
            }.bind(this));

        },

        handleAddFileUpload: function (e) {
            var $input = $(e.currentTarget);
            var $htmlFileList = this.$wrapper.find('.js-list-files');

            this.fileList = [];
            this.totalFile = $input.prop('files').length;

            for (var i = 0; i < this.totalFile; i++) {
                this.fileList.push($input.prop('files')[i]);
            }

            this.helper.clearHtmlList($htmlFileList);
            this.helper.addHtmlListFiles($input[0].files, $htmlFileList);
        },

        handleAddTag: function (e) {
            var $tagsListHtml = this.$wrapper.find('#js-tags-list');
            var $tagsInput = $(e.currentTarget);

            if(e.keyCode === 32 || e.keyCode === 13) {
                var word = $tagsInput.val().trim();

                if(this.tagsList.indexOf(word) === -1) {
                    this.tagsList.push(word);
                    $tagsListHtml.append(
                        '<span class="badge badge-primary img-tag">'
                        + word
                        + ' <a href="#" class="text-danger js-img-tag-remove" data-value="'+word+'"><i class="fa fa-times"></i></a>'
                        + '</span>');
                    $tagsInput.val('');
                }


            }
        },

        handleRemoveTag: function(e) {
            e.preventDefault();
            var $link = $(e.currentTarget);
            var word = $link.data('value');
            this.tagsList.splice(this.tagsList.indexOf(word), 1);
            $link.closest('span').fadeOut();
        }
    });

    /**
     * A "private" object
     */
    var Helper = function ($wrapper, imageApp) {
        this.$wrapper = $wrapper;
        this.imageApp = imageApp;
    };

    $.extend(Helper.prototype, {
        handleRemoveFromHtmlList: function (e) {
            e.preventDefault();
            var $link = $(e.currentTarget);

            var i = $link.data('id');
            this.imageApp.fileList[i] = null;
            $link.closest('li').fadeOut();
        },

        sendFile: function(url, nameInputFile, file, nameInputTags, tags, nameInputToken, token, $progressBar) {
            var formData = new FormData();
            formData.append(nameInputFile, file);
            formData.append(nameInputToken, token);
            formData.append(nameInputTags, tags.join(','));

            console.log(nameInputToken);
            console.log(token);


            $.ajax({
                url: url,
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (data, textStatus, jqXHR) {
                    var incrementCount = Number($progressBar.attr('value')) + 1;
                    $progressBar.attr('value', Number($progressBar.attr('value')) + 1);
                    if(incrementCount === this.imageApp.totalFile) {
                        $('#success-modal').modal('toggle');
                    }
                }.bind(this),
                error: function (jqXHR, textStatus, errorThrown) {
                    $('#fail-modal').modal('toggle');
                }
            });
        },

        addHtmlListFiles: function(files, $htmlFileList) {
            var html = '';
            for (var i = 0; i < files.length; ++i) {
                html +=
                    '<li class="list-group-item">'
                    + '<span class="limit-text-length">' + files.item(i).name + '</span>'
                    + '<a href="#" class="pull-right text-danger js-remove-file-from-ul" data-id="'+ i +'">'
                    + '<i class="fa fa-times"></i></a>'
                    + '</li>';
            }
            $htmlFileList.append(html);
            this.$wrapper.find('.js-remove-file-from-ul').on('click', this.handleRemoveFromHtmlList.bind(this));
        },
        
        clearHtmlList: function ($htmlFileList) {
            $htmlFileList.contents().remove();
        }
    });

})(window, jQuery);