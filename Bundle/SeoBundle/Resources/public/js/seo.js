

var seo = {};

seo.initPropertyHelper = function ()
{
    seo.currentInput = null;
    
    //if an input or a textarea lose the focus
    $("form[name='seo_page']").find('input, textarea').focusout(function () {
        //we memorize the input
        seo.currentInput = $(this);
     });
};


//we add the {{ item.XXX }} string to the current input
seo.addPropertyToCurrentInput = function (businessProperty)
{
    //get the current input
    var currentInput = seo.currentInput;
    
    //there was an input
    if (currentInput !== null) {
        //the string to insert
        var insertString = '{{' + 'item.' + businessProperty + '}}';
        //we get the current string
        var currentString = currentInput.val();
        //we add the item
        var newString = currentString + insertString;        
        //set the new string
        currentInput.val(newString);
    }
};