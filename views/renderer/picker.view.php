<script type="text/javascript">
    require(
            [
                'jquery-nos',
                'jquery-ui.datepicker.i18n',
                'jquery-ui.datetimepicker.i18n',
                'jquery-ui.wijcheckbox.i18n'
            ],
            function($) {
                $(function() {
                    var $input = $('input#<?= $id ?>');

                    var $enabled = $('input#<?= $id ?>__enabled');
                    if ($input.val() != '') {
                        $enabled.attr('checked', 'checked');
                        $enabled.wijcheckbox('refresh');
                    }
                    $enabled.change(function() {
                        updateDateTime();
                    });

                    var $date = $('input#<?= $id ?>__date');
                    $.datepicker.setDefaults($.datepicker.regional[$.nosLang.substr(0, 2)]);
                    $date<?= !empty($wrapper) ? '.wrap('.\Format::forge()->to_json($wrapper).')' : '' ?>.datepicker($date.data('datepicker-options'));
                    $date.val($input.data('date'));
                    $date.change(function(){
                        updateDateTime();
                    });


                    var $time = $('input#<?= $id ?>__time');
                    $.timepicker.setDefaults($.timepicker.regional[$.nosLang.substr(0, 2)]);
                    $time.timepicker($time.data('timepicker-options'));
                    $time.val($input.data('time'));
                    $time.change(function(){
                        updateDateTime();
                    });

                    updateDateTime();

                    function updateDateTime() {
                        if ($enabled.is(':checked')) {
                            $time.parent().show();
                            $date.parent().show();
                            var value = $date.val();
                            if ($time.length > 0) {
                                value += ' ' + $time.val();
                            }
                            $input.val(value);
                        } else {
                            $time.parent().hide();
                            $date.parent().hide();
                            $input.val('');
                        }
                    }
                });
            });
</script>
