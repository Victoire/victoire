$vic('[data-provider="datepicker"]').datetimepicker({
    autoclose: true,
    format: 'dd/mm/yyyy',
    language: 'fr',
    minView: 'month',
    pickerPosition: 'bottom-left',
    todayBtn: true,
    startView: 'month'
});

$vic('[data-provider="datetimepicker"]').datetimepicker({
    autoclose: true,
    format: 'dd/mm/yyyy hh:ii',
    language: 'fr',
    pickerPosition: 'bottom-left',
    todayBtn: true
});

$vic('[data-provider="timepicker"]').datetimepicker({
    autoclose: true,
    format: 'hh:ii',
    formatViewType: 'time',
    maxView: 'day',
    minView: 'hour',
    pickerPosition: 'bottom-left',
    startView: 'day'
});

// Restore value from hidden input
$vic('input[type=hidden]', '.date').each(function(){
    if($vic(this).val()) {
        $vic(this).parent().datetimepicker('setValue');
    }
});
