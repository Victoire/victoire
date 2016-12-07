$vic(document).on('submit', '.sitemapPriorityForm', function(event) {
    loading(true);
    event.preventDefault();
    var pageId = $vic(this).attr('data-pageId');
    $vic.ajax({
        url         : $vic(this).attr('action'),
        context     : document.body,
        data        : $vic(this).serialize(),
        type        : $vic(this).attr('method'),
        contentType : 'application/x-www-form-urlencoded; charset=UTF-8',
        processData : false,
        async       : true,
        cache       : false,
        success     : function(jsonResponse) {
            loading(false);
            congrat(jsonResponse.message);
            $vic("#form-save-notice-" + pageId).fadeIn();
            setTimeout(function() {
                $vic("#form-save-notice-" + pageId).fadeOut();
            }, 5000);
        }
    });
});

function updateSitemapPosition(element, ui)
{
    var sorted = $vic(element).nestedSortable("toArray");
    loading(true);
    $vic.post(
        Routing.generate('victoire_sitemap_reorganize', {
            '_locale': locale
        }),
        {
            sorted: sorted
        }
    ).done(function(response) {
            loading(false);
            congrat(response.message);

            $vic('#vic-modal').replaceWith(response.html);
            $vic('#vic-modal').vicmodal({
                keyboard: true,
                backdrop: false
            });
        });
};

$vic('.modal').on('hidden', function () {
    $vic('.modal').remove();
});