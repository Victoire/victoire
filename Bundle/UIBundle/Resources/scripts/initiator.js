import TrowelModals from './ui-components/modals';
import MDFormGroups from './ui-components/forms';
import TrowelDrops from './ui-components/drops';
import ModeDrops from './ui-components/modeDrops';
import TrowelCollapses from './ui-components/collapses';
import Slots from './ui-components/slots';

export default class Initiator {
    constructor() {
        return this.newInits();
    }

    newInits(parent = document) {
        this.modals = new TrowelModals(document.querySelectorAll('[data-flag*="v-modal"]'));
        this.mdForm = new MDFormGroups(parent.querySelectorAll('[data-flag*="v-mdForm"]'));
        this.drops = new TrowelDrops(parent.querySelectorAll('[data-flag*="v-drop"]'));
        this.collapses = new TrowelCollapses(parent.querySelectorAll('[data-flag*="v-collapse"]'));
        this.slots = new Slots(parent.querySelectorAll('.v-slot'));
        this.modeDrops = new ModeDrops(parent.querySelectorAll('[data-flag*="v-mode-drop"]'));

        return;
    }
}
