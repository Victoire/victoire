export default class ModeDrops {
    constructor(triggers) {
        // If `drops` is a nodelist transform it into a array
        if (triggers == '[object NodeList]') {
            triggers = Array.prototype.slice.call(triggers);
        }

        triggers.forEach(trigger => new ModeDrop(trigger));
    }
}

class ModeDrop {
    constructor(trigger) {
        this.trigger = trigger;
        this.toggleActiveClass();
        this.listener();
    }

    get drop() {
        return document.querySelector(this.trigger.getAttribute('data-href'));
    }

    get dropAnchors() {
        return Array.prototype.slice.call(this.drop.querySelectorAll('a'));
    }

    get dropActiveAnchor() {
        return this.drop.querySelector('.v-drop__anchor--active');
    }

    get dropAnchorsClasses() {
        return this.dropAnchors.reduce((classes, anchor) => {
            classes.push(anchor.getAttribute('data-triggerclass'));
            return classes;
        }, [])
    }

    listener() {
        this.dropAnchors.forEach(anchor => anchor.addEventListener('click', this.toggleActiveClass.bind(this)));
    }

    toggleActiveClass(event = null) {
        this.dropAnchorsClasses.forEach(classname => this.trigger.classList.remove(classname));

        let target = this.dropActiveAnchor;
        if (event) target = event.target;
        if (!target) return;

        return this.trigger.classList.add(target.getAttribute('data-triggerclass'));
    }
}
