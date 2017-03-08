import TrowelModal from './ui-components/modals';
import MDFormGroup from './ui-components/forms';
import TrowelDrop from './ui-components/drops';
import ModeDrop from './ui-components/modeDrops';
import TrowelCollapse from './ui-components/collapses';
import Slot from './ui-components/slots';

export default class Initiator {
    constructor() {
        this.modalsInitiated = [];
        this.mdFormInitiated = [];
        this.slotsInitiated = [];
        this.modeDropsInitiated = [];
        this.dropsInitiated = [];
        this.collapsesInitiated = [];
        return this.newInits();
    }

    newInits(parent = document) {
        this.newModals();
        this.newMdForm();
        this.newSlots();
        this.newModeDrops();
        this.newDrops();
        this.newCollapses();
    }

    newModals() {
        const modals = [].slice.call(document.querySelectorAll('[data-flag*="v-modal"]'));
        modals.forEach(modal => {
            if (this.modalsInitiated.includes(modal)) return true;
            this.modalsInitiated.push(modal);
            let obj = new TrowelModal(modal);
            return true;
        });
    }

    newMdForm() {
        const mdForms = [].slice.call(document.querySelectorAll('[data-flag*="v-mdForm"]'));
        mdForms.forEach(mdForm => {
            if (this.mdFormInitiated.includes(mdForm)) return true;
            this.mdFormInitiated.push(mdForm);
            let obj = new MDFormGroup(mdForm);
            return true;
        });
    }

    newSlots() {
        const slots = [].slice.call(document.querySelectorAll('.v-slot'));
        slots.forEach(slot => {
            if (this.slotsInitiated.includes(slot)) return true;
            this.slotsInitiated.push(slot);
            let obj = new Slot(slot);
            return true;
        });
    }

    newModeDrops() {
        const modeDrops = [].slice.call(document.querySelectorAll('[data-flag*="v-drop"]'));
        modeDrops.forEach(function(modeDrop) {
            if (this.modeDropsInitiated.includes(modeDrop)) return true;
            this.modeDropsInitiated.push(modeDrop);
            let obj = new ModeDrop(modeDrop);
            return true;
        }, this);
    }

    newDrops() {
        const drops = [].slice.call(document.querySelectorAll('[data-flag*="v-drop"]'));
        drops.forEach(drop => {
            if (this.dropsInitiated.includes(drop)) return true;
            this.dropsInitiated.push(drop);
            let obj = new TrowelDrop(drop);
            return true;
        });
    }

    newCollapses() {
        const collapses = [].slice.call(document.querySelectorAll('[data-flag*="v-collapse"]'));
        collapses.forEach(collapse => {
            if (this.collapsesInitiated.includes(collapse)) return true;
            this.collapsesInitiated.push(collapse);
            let obj = new TrowelCollapse(collapse);
            return true;
        });
    }
}
