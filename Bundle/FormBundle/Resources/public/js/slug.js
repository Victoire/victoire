function validSlug(value) {
  return /^([a-zA-Z0-9-]*)?$/i.test(value);
}
function addSlugValidation(inputSlug){
    displaySlugIcons(inputSlug, inputSlug.val());

    inputSlug.on('keyup', function(){
        displaySlugIcons(inputSlug, $vic(this).val());
    });
}
function displaySlugIcons(inputSlug, slug){
    var inputSlugId = inputSlug.attr('id');
    var correctSlugIcon = $vic('#' + inputSlugId + '-correct');
    var notCorrectSlugIcon = $vic('#' + inputSlugId + '-not-correct');
    if (slug != 'undefined' && slug != "") {
        if (validSlug(slug)) {
            correctSlugIcon.removeClass('vic-hidden');
            notCorrectSlugIcon.addClass('vic-hidden');
        }else{
            correctSlugIcon.addClass('vic-hidden');
            notCorrectSlugIcon.removeClass('vic-hidden');
        }
    }else{
        correctSlugIcon.removeClass('vic-hidden');
        notCorrectSlugIcon.addClass('vic-hidden');
    };
}
