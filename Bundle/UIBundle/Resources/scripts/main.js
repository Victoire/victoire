import Initiator from './initiator';

let initiator = new Initiator();

$vic(document).ajaxSuccess(function() {
    const modal = document.getElementById('vic-modal');
    return initiator.newInits(modal);
});
