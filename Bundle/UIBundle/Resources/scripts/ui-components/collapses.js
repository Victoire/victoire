export default class TrowelCollapse {
    constructor(collapse, nested = true) {
        this.collapse = collapse;
        this.nested = nested;

        if (this.isVisible) {
            this.show()
        } else {
            this.hide();
        }

        return this.listeners();
    }

    show() {
        this.collapse.setAttribute('data-state', 'visible');

        this.triggers
            .forEach(trigger => trigger.addActiveclass());

        this.otherCollapsesFromGroup
            .forEach(collapse => collapse.hide());
        return;
    }

    hide() {
        this.collapse.setAttribute('data-state', 'hidden');

        return this.triggers
            .forEach(trigger => trigger.removeActiveclass());
    }

    toggle() {
        if (this.isVisible) return this.hide();
        return this.show();
    }

    get isVisible () {
        return this.collapse.getAttribute('data-state') == 'visible';
    }

    get isHidden () {
        return this.collapse.getAttribute('data-state') == 'hidden';
    }

    get groupName () {
        return this.collapse.dataset.group;
    }

    get isEffectingOtherCollapsesFromGroup () {
        return this.groupName && this.nested;
    }

    get otherCollapsesFromGroup () {
        if (!this.isEffectingOtherCollapsesFromGroup) return [];
        const groupList = document.querySelectorAll(`[data-group="${this.groupName}"]`);

        return Array.prototype.slice.call(groupList) // convert the nodelist as array
            .filter(collapse => collapse != this.collapse) // exclude `this` from the arr
            .map(collapse => new TrowelCollapse(collapse, false))
    }

    listeners() {
        if (!this.nested) return false;

        this.toggleTriggers
            .forEach(trigger => trigger.domEl.addEventListener('click', () => this.toggle()));

        this.showTriggers
            .forEach(trigger => trigger.domEl.addEventListener('click', () => this.show()));

        this.hideTriggers
            .forEach(trigger => trigger.domEl.addEventListener('click', () => this.hide()));
    }

    get triggers () {
        const triggerDomList = document.querySelectorAll(`[data-collapse][data-href="#${this.collapse.id}"]`);
        return Array.prototype.slice.call(triggerDomList) // convert the nodelist as array
            .map(trigger => new TrowelCollapseTrigger(trigger));
    }

    get toggleTriggers () {
        return this.triggers
            .filter(trigger => trigger.isToggleAction);
    }

    get showTriggers () {
        return this.triggers
            .filter(trigger => trigger.isShowAction);
    }

    get hideTriggers () {
        return this.triggers
            .filter(trigger => trigger.isHideAction);
    }
}


class TrowelCollapseTrigger {
    constructor(domEl) {
        this.domEl = domEl;
    }

    get activeclass () {
        return this.domEl.dataset.activeclass;
    }

    get action () {
        return this.domEl.dataset.collapse;
    }

    get isToggleAction () {
        return this.action == 'toggle';
    }

    get isShowAction () {
        return this.action == 'show';
    }

    get isHideAction () {
        return this.action == 'hide';
    }

    addActiveclass() {
        return this.domEl.classList.add(this.activeclass);
    }

    removeActiveclass() {
        return this.domEl.classList.remove(this.activeclass);
    }

    toggleActiveclass() {
        return this.domEl.classList.toggle(this.activeclass);
    }
}
