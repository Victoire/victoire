import Initiator from './initiator';

let initiator = new Initiator();

$vic(document).ajaxSuccess(function() {
    const modal = document.getElementById('vic-modal');
    if (modal) return initiator.newInits(modal);
    return;
});
