function validSlug(value) {
  return /^([a-zA-Z0-9-]*)?$/i.test(value);
}
function addSlugValidation(inputSlug, allowEmpty){
    displaySlugIcons(inputSlug, inputSlug.val(), allowEmpty);

    inputSlug.on('keyup', function(){
        displaySlugIcons(inputSlug, $vic(this).val(), allowEmpty);
    });
}
function displaySlugIcons(inputSlug, slug, allowEmpty){
    var inputSlugId = inputSlug.attr('id');
    var correctSlugIcon = $vic('#' + inputSlugId + '-correct');
    var notCorrectSlugIcon = $vic('#' + inputSlugId + '-not-correct');
    if (slug != 'undefined' && slug != "") {
        if (validSlug(slug)) {
            correctSlugIcon.removeClass('v-hidden');
            notCorrectSlugIcon.addClass('v-hidden');
        }else{
            correctSlugIcon.addClass('v-hidden');
            notCorrectSlugIcon.removeClass('v-hidden');
        }
    }else{
        if (allowEmpty && slug != 'undefined') {
            correctSlugIcon.removeClass('v-hidden');
            notCorrectSlugIcon.addClass('v-hidden');
        } else {
            correctSlugIcon.addClass('v-hidden');
            notCorrectSlugIcon.removeClass('v-hidden');
        }
    };
}
