import Initiator from './initiator';

let initiator = new Initiator();

$vic(document).ajaxSuccess(function(event, xhr) {
    return initiator.newInits(xhr.responseText);
});
$vic(document).on('success.v-ic', function(elt, data, textStatus, xhr) {
    return initiator.newInits(xhr.responseText);
});
