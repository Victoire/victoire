/*global $vic*/

//we add the {{ item.XXX }} string to the current input
var addPropertyToCurrentInput = function (button, businessPropertyId) {
    var currentInput = $vic(button).closest('.v-form-group').find('input');
    var label = $vic(button).closest('.v-form-group').find('.v-form-group__label');

    //there was an input
    if (currentInput !== null) {
        //the string to insert
        var insertString = '{{' + 'item.' + businessPropertyId + '}}';
        //we get the current string
        var currentString = currentInput.val();
        //we add the item
        var newString = currentString + insertString;        
        //set the new string
        currentInput.val(newString);

        // unfold label
        label.attr('data-mdform', 'unfolded');
    }
};