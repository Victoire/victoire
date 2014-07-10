/*global $vic*/

var businessProperty = {};

businessProperty.initPropertyHelper = function ()
{
    "use strict";
    businessProperty.currentInput = null;
    
    //if an input or a textarea lose the focus
    $vic("form").find('input, textarea').focusout(function () {
        //we memorize the input
        businessProperty.currentInput = $vic(this);
    });
};


//we add the {{ item.XXX }} string to the current input
businessProperty.addPropertyToCurrentInput = function (businessPropertyId)
{
    "use strict";
    
    //get the current input
    var currentInput = businessProperty.currentInput;
    
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
    }
};