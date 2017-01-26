import TrowelModals from './ui-components/modals';
import MDFormGroups from './ui-components/forms';
import TrowelDrops from './ui-components/drops';
import TrowelCollapses from './ui-components/collapses';

export default class Initiator {
    constructor() {
        const modals = new TrowelModals(document.querySelectorAll('[data-flag="v-modal"]'));
        const mdForm = new MDFormGroups(document.querySelectorAll('[data-flag="v-mdForm"]'));
        const drops = new TrowelDrops(document.querySelectorAll('[data-flag="v-drop"]'));
        const collapses = new TrowelCollapses(document.querySelectorAll('[data-flag="v-collapse"]'));
    }
}
