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
            if (this.modalsInitiated.indexOf(modal) > -1) return true;
            this.modalsInitiated.push(modal);
            let obj = new TrowelModal(modal);
            return true;
        });
    }

    newMdForm() {
        const mdForms = [].slice.call(document.querySelectorAll('[data-flag*="v-mdForm"]'));
        mdForms.forEach(mdForm => {
            if (this.mdFormInitiated.indexOf(mdForm) > -1) return true;
            this.mdFormInitiated.push(mdForm);
            let obj = new MDFormGroup(mdForm);
            return true;
        });
    }

    newSlots() {
        const slots = [].slice.call(document.querySelectorAll('.v-slot'));
        slots.forEach(slot => {
            if (this.slotsInitiated.indexOf(slot) > -1) return true;
            this.slotsInitiated.push(slot);
            let obj = new Slot(slot);
            return true;
        });
    }

    newModeDrops() {
        const modeDrops = [].slice.call(document.querySelectorAll('[data-flag*="v-drop"]'));
        modeDrops.forEach(modeDrop => {
            if (this.modeDropsInitiated.indexOf(modeDrop) > -1) return true;
            this.modeDropsInitiated.push(modeDrop);
            let obj = new ModeDrop(modeDrop);
            return true;
        });
    }

    newDrops() {
        const drops = [].slice.call(document.querySelectorAll('[data-flag*="v-drop"]'));
        drops.forEach(drop => {
            if (this.dropsInitiated.indexOf(drop) > -1) return true;
            this.dropsInitiated.push(drop);
            let obj = new TrowelDrop(drop);
            return true;
        });
    }

    newCollapses() {
        const collapses = [].slice.call(document.querySelectorAll('[data-flag*="v-collapse"]'));
        collapses.forEach(collapse => {
            if (this.collapsesInitiated.indexOf(collapse) > -1) return true;
            this.collapsesInitiated.push(collapse);
            let obj = new TrowelCollapse(collapse);
            return true;
        });
    }
}
