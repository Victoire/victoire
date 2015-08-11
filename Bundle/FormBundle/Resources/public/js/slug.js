function validSlug(value) {
  return /^([a-zA-Z0-9-]*)?$/i.test(value);
}
function addSlugValidation(inputSlugId){
    var inputSlug = $vic('#' + inputSlugId);
    displaySlugIcons(inputSlugId, inputSlug.val());

    inputSlug.on('keyup', function(){
        displaySlugIcons(inputSlugId, $vic(this).val());
    });
}
function displaySlugIcons(inputSlugId, slug){
    var correctSlugIcon = $vic('#' + inputSlugId + '-correct');
    var notCorrectSlugIcon = $vic('#' + inputSlugId + '-not-correct');
    if (slug != 'undefined' && slug != "") {
        if (validSlug(slug)) {
            correctSlugIcon.removeClass('vic-hidden');
            notCorrectSlugIcon.addClass('vic-hidden');
        }else{
            correctSlugIcon.addClass('vic-hidden');
            notCorrectSlugIcon.removeClass('vic-hidden');
        };
    }else{
        correctSlugIcon.removeClass('vic-hidden');
        notCorrectSlugIcon.addClass('vic-hidden');
    };
}