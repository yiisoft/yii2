yii.gii = (function ($) {
    var isActive = $('.default-view').length > 0;

    var initHintBlocks = function () {
        $('.hint-block').each(function () {
            var $hint = $(this);
            $hint.parent().find('label').addClass('help').popover({
                html: true,
                trigger: 'hover',
                placement: 'right',
                content: $hint.html()
            });
        });
    };

    var initStickyInputs = function () {
        $('.sticky:not(.error)').find('input[type="text"],select,textarea').each(function () {
            var value;
            if (this.tagName === 'SELECT') {
                value = this.options[this.selectedIndex].text;
            } else if (this.tagName === 'TEXTAREA') {
                value = $(this).html();
            } else {
                value = $(this).val();
            }
            if (value === '') {
                value = '[empty]';
            }
            $(this).before('<div class="sticky-value">' + value + '</div>').hide();
        });
        $('.sticky-value').on('click', function () {
            $(this).hide();
            $(this).next().show().get(0).focus();
        });
    };

    var initPreviewDiffLinks = function () {
        $('.preview-code, .diff-code, .modal-refresh, .modal-previous, .modal-next').on('click', function () {
            var $modal = $('#preview-modal');
            var $link = $(this);
            $modal.find('.modal-refresh').attr('href', $link.attr('href'));
            if ($link.hasClass('preview-code') || $link.hasClass('diff-code')) {
                $modal.data('action', ($link.hasClass('preview-code') ? 'preview-code' : 'diff-code'))
            }
            $modal.find('.modal-title').text($link.data('title'));
            $modal.find('.modal-body').html('Loading ...');
            $modal.modal('show');
            var checkbox = $('a.' + $modal.data('action') + '[href="' + $link.attr('href') + '"]').closest('tr').find('input').get(0);
            var checked = false;
            if (checkbox) {
                checked = checkbox.checked;
                $modal.find('.modal-checkbox').removeClass('disabled');
            } else {
                $modal.find('.modal-checkbox').addClass('disabled');
            }
            $modal.find('.modal-checkbox span').toggleClass('glyphicon-check', checked).toggleClass('glyphicon-unchecked', !checked);
            $.ajax({
                type: 'POST',
                cache: false,
                url: $link.prop('href'),
                data: $('.default-view form').serializeArray(),
                success: function (data) {
                    if (!$link.hasClass('modal-refresh')) {
                        var filesSelector = 'a.' + $modal.data('action');
                        var $files = $(filesSelector);
                        var index = $files.filter('[href="' + $link.attr('href') + '"]').index(filesSelector);
                        var $prev = $files.eq(index - 1);
                        var $next = $files.eq((index + 1 == $files.length ? 0 : index + 1));
                        $modal.data('current', $files.eq(index));
                        $modal.find('.modal-previous').attr('href', $prev.attr('href')).data('title', $prev.data('title'));
                        $modal.find('.modal-next').attr('href', $next.attr('href')).data('title', $next.data('title'));
                    }
                    $modal.find('.modal-body').html(data);
                    $modal.find('.content').css('max-height', ($(window).height() - 200) + 'px');
                },
                error: function (XMLHttpRequest, textStatus, errorThrown) {
                    $modal.find('.modal-body').html('<div class="error">' + XMLHttpRequest.responseText + '</div>');
                }
            });
            return false;
        });

        $('#preview-modal').on('keydown', function (e) {
            if (e.keyCode === 37) {
                $('.modal-previous').trigger('click');
            } else if (e.keyCode === 39) {
                $('.modal-next').trigger('click');
            } else if (e.keyCode === 82) {
                $('.modal-refresh').trigger('click');
            } else if (e.keyCode === 32) {
                $('.modal-checkbox').trigger('click');
            }
        });

        $('.modal-checkbox').on('click', checkFileToggle);
    };

    var checkFileToggle = function () {
        var $modal = $('#preview-modal');
        var $checkbox = $modal.data('current').closest('tr').find('input');
        var checked = !$checkbox.prop('checked');
        $checkbox.prop('checked', checked);
        $modal.find('.modal-checkbox span').toggleClass('glyphicon-check', checked).toggleClass('glyphicon-unchecked', !checked);
        return false;
    };

    var checkAllToggle = function () {
        $('#check-all').prop('checked', !$('.default-view-files table .check input:enabled:not(:checked)').length);
    };

    var initConfirmationCheckboxes = function () {
        var $checkAll = $('#check-all');
        $checkAll.click(function () {
            $('.default-view-files table .check input:enabled').prop('checked', this.checked);
        });
        $('.default-view-files table .check input').click(function () {
            checkAllToggle();
        });
        checkAllToggle();
    };

    var initToggleActions = function () {
        $('#action-toggle :input').change(function () {
            $(this).parent('label').toggleClass('active', this.checked);
            $('.' + this.value, '.default-view-files table').toggle(this.checked).find('.check input').attr('disabled', !this.checked);
            checkAllToggle();
        });
    };

    return {
        autocomplete: function (counter, data) {
            var datum = new Bloodhound({
                datumTokenizer: function (d) {
                    return Bloodhound.tokenizers.whitespace(d.word);
                },
                queryTokenizer: Bloodhound.tokenizers.whitespace,
                local: data
            });
            datum.initialize();
            jQuery('.typeahead-' + counter).typeahead(null, {displayKey: 'word', source: datum.ttAdapter()});
        },
        init: function () {
            initHintBlocks();
            initStickyInputs();
            initPreviewDiffLinks();
            initConfirmationCheckboxes();
            initToggleActions();

            // model generator: hide class name input when table name input contains *
            $('#model-generator #generator-tablename').change(function () {
                $('#model-generator .field-generator-modelclass').toggle($(this).val().indexOf('*') == -1);
            }).change();

            // model generator: translate table name to model class
            $('#generator-tablename').on('blur', function () {
                var tableName = $(this).val();
                if ($('#generator-modelclass').val()=='' && tableName && tableName.indexOf('*') === -1){
                    var modelClass='';
                    $.each(tableName.split('_'), function() {
                        if(this.length>0)
                            modelClass+=this.substring(0,1).toUpperCase()+this.substring(1);
                    });
                    $('#generator-modelclass').val(modelClass);
                }
            });

            // hide message category when I18N is disabled
            $('form #generator-enablei18n').change(function () {
                $('form .field-generator-messagecategory').toggle($(this).is(':checked'));
            }).change();

            // hide Generate button if any input is changed
            $('.default-view .form-group input,select,textarea').change(function () {
                $('.default-view-results,.default-view-files').hide();
                $('.default-view button[name="generate"]').hide();
            });

            $('.module-form #generator-moduleclass').change(function () {
                var value = $(this).val().match(/(\w+)\\\w+$/);
                var $idInput = $('#generator-moduleid');
                if (value && value[1] && $idInput.val() == '') {
                    $idInput.val(value[1]);
                }
            });
        }
    };
})(jQuery);
