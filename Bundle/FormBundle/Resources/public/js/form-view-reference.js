// Enable Select2
var allOptions = null;

$( document ).ready(function() {
    // Load data
    $.ajax({
        type: 'GET',
        url: Routing.generate("victoire_view_reference_get", {})
    }).then(function (data) {
        allOptions = data;

        // Data is loaded, bind event for tags that will appear
        $(document).on("focus", 'select[data-is-view-reference-field]', function() {
            // Don't load data if it has already been done
            if (!allOptions || true === $(this).data('values-loaded')) {
                return;
            }

            var i;
            var item;
            var option;
            var allOptionsLength = allOptions.length;

            for (i = 0; i < allOptionsLength; i++) {
                item = allOptions[i];
                option = new Option(item.text, item.id);
                // Add value
                $(this).append(option);
            }

            $vic($(this)).select2();

            $(this).data('values-loaded', true);
        });
    });
});
