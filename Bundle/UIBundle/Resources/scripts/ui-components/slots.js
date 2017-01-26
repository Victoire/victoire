export default class Slots {
    constructor(elements) {
        // If `modals` is a nodelist transform it into a array
        if (elements == '[object NodeList]') {
            elements = Array.prototype.slice.call(elements);
        }

        elements.forEach(element => new Slot(element));
    }
}

class Slot {
    constructor(element) {
        this.element = element;
        this.evalSize();
        this.listeners();
    }

    get select() {
        return this.element.querySelector('.v-slot__select');
    }

    get openClass() {
        return 'v-slot--open';
    }

    evalSize() {
        const smallClass = 'v-slot--sm';

        if (this.element.offsetWidth > 250 && this.element.classList.contains(smallClass)) {
            this.element.classList.remove(smallClass);
        } else if (this.element.offsetWidth <= 250 && this.element.offsetWidth > 0) {
            this.element.classList.add(smallClass);
        }
        return;
    }

    selectFocus() {
        return this.element.classList.add(this.openClass);
    }

    selectBlur() {
        this.element.classList.remove(this.openClass);
        this.select.selectedIndex = 0;
        this.select.blur();
        return;
    }

    listeners() {
        window.addEventListener('resize', this.evalSize.bind(this));
        this.select.addEventListener('focus', this.selectFocus.bind(this));
        this.select.addEventListener('change', this.selectBlur.bind(this));
        this.select.addEventListener('blur', this.selectBlur.bind(this));
        return;
    }
}
