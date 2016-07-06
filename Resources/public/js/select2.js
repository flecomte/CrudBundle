$(function () {
    $(".content select:not('required')").select2({
        placeholder: "",
        allowClear: true
    });
    $(".content select:required").select2({
        placeholder: "",
        allowClear: false
    });
});
