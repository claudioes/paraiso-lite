(function ( $ ) {
    "use strict";
    
    $.fn.ajaxForm = function (options) {
        var defaults = {
            container: false,
            beforeSend: false,
            onSuccess: false,
            onError: false,
        };

        var settings = $.extend({}, defaults, options);

        var container = document.getElementById(settings.container) || this.parent();

        var $globalError = $('<div class="alert alert-danger" role="alert"></div>')
            .hide()
            .prependTo(container);

        var showError = function (errorMessage, $form, inputName, inputIndex) {
            var $input = $form.find('[name="' + inputName + '"]');

            if (typeof inputIndex !== 'undefined') {
                $input = $input.eq(inputIndex);
            }

            if ($input.length) {
                $input.addClass('is-invalid');

                var $inputGroup = $input.parents('div.input-group');
                
                if ($inputGroup.length) {
                    // https://github.com/twbs/bootstrap/issues/23454
                    $inputGroup.after('<div class="d-block invalid-feedback">' +  errorMessage + '</div>');
                } else {
                    $input.after('<div class="invalid-feedback">' +  errorMessage + '</div>');
                }

            }
        };

        this.submit(function (e) {
            e.preventDefault();

            if (settings.beforeSend) {
                settings.beforeSend(this);
            }

            var $form = $(this);
            var data;
            var withData = this.enctype == 'multipart/form-data';

            if (withData) {
                data = new FormData(this);
            } else {
                data = $form.serialize();
            }

            $globalError.hide();

            var $buttons = $form.find(':submit');
            $buttons.prop('disabled', true);

            $.ajax({
                url: this.getAttribute('action'),
                type: this.getAttribute('method'),
                data: data,
                dataType: "json",
                cache: withData? false: true,
                contentType: withData? false: 'application/x-www-form-urlencoded; charset=UTF-8',
                processData: withData? false: true
            }).done(function (result) {
                $form.find(':input').removeClass('is-invalid');
                $form.find('div.invalid-feedback').remove();
                $buttons.prop('disabled', false);

                if (settings.onSuccess) {
                    settings.onSuccess(result);
                } else {
                    var url = result.redirect.split("#");

                    window.location.replace(url[0])

                    if (url.length > 1) {
                        window.location.hash = url[1];
                        window.location.reload(true);
                    }
                }
            }).fail(function (xhr, textStatus, errorThrown) {
                var globalErrorMessage = '';

                if (settings.onError) {
                    settings.onError(xhr, textStatus, errorThrown);
                } else {
                    if (xhr.status == 400) {
                        var errors = xhr.responseJSON;

                        $form.find(':input').removeClass('is-invalid');
                        $form.find('div.invalid-feedback').remove();

                        for (var field in errors) {
                            var message = errors[field];

                            if (typeof message === 'object') {
                                for (var index in message) {
                                    showError(message[index], $form, field + '[]', index);
                                }
                            } else if (field == 'error') {
                                globalErrorMessage = message + '<br>';
                            } else {
                                showError(message, $form, field);
                            }
                        }
                    } else {
                        globalErrorMessage = 'Ocurrió un error al intentar procesar la consulta. Vuelva a intentarlo y, si el problema persiste, pongase en contacto con el administrador.';
                    }

                    if (globalErrorMessage) {
                        $globalError.html('<i class="fa fa-fw fa-exclamation-circle mr-2"></i>' + globalErrorMessage).show();
                        $globalError.get(0).scrollIntoView();
                    }
                }

                $buttons.prop('disabled', false);
            });
        });
    };
}( jQuery ));