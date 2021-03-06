(function () {
    'use strict';

    var tinymce = window.tinymce;
    var common = window.Claroline.Common;
    var home = window.Claroline.Home;
    var modal = window.Claroline.Modal;
    var resourceManager = window.Claroline.ResourceManager;
    var translator = window.Translator;
    var routing =  window.Routing;
    var buttons = window.tinymce.claroline.buttons || {};

    /**
     * Open a directory picker from a TinyMCE editor.
     */
    var directoryPickerCallBack = function(nodes)
    {
        for (var id in nodes) {
            var val = nodes[id][4];
            var path = nodes[id][3];
        }

        //file_form_destination
        var html = '<option value="' + val + '">' + path + '</option>';
        $('#file_form_destination').append(html);
        $('#file_form_destination').val(val);
    }

    /**
     * Open a resource picker from a TinyMCE editor.
     */
    var directoryPickerOpen = function ()
    {
        if (!resourceManager.hasPicker('tinyMceDirectoryPicker')) {
            resourceManager.createPicker('tinyMceDirectoryPicker', {
                callback: directoryPickerCallBack,
                resourceTypes: ['directory'],
                isDirectorySelectionAllowed: true,
                isPickerMultiSelectAllowed: false
            }, true);
        } else {
            resourceManager.picker('tinyMceDirectoryPicker', 'open');
        }
    };

    /**
     * Add resource picker and upload files buttons in a TinyMCE editor if data-resource-picker is on.
     *
     * @param editor A TinyMCE editor object.
     *
     */
    tinymce.claroline.buttons.upload_file = function (editor)
    {
        editor.addButton('fileUpload', {
            'icon': 'none fa fa-file',
            'classes': 'widget btn',
            'tooltip': translator.trans('upload', {}, 'platform'),
            'onclick': function () {
                tinymce.activeEditor = editor;
                modal.fromRoute('claro_upload_modal', null, function (element) {
                    element.on('click', '.resourcePicker', function () {
                        tinymce.claroline.buttons.resourcePickerOpen();
                    })
                    .on('click', '.filePicker', function () {
                        $('#file_form_file').click();
                    })
                    .on('change', '#file_form_destination', function(event) {
                        if ($('#file_form_destination').val() === 'others') {
                            tinymce.claroline.buttons.resourcePickerCallBack
                        }
                    })
                    .on('change', '#file_form_file', function () {
                        common.uploadfile(
                            this,
                            element,
                            $('#file_form_destination').val(),
                            tinymce.claroline.buttons.resourcePickerCallBack
                        );
                    })
                });
            }
        });
    };
}());
