export default class MDFormGroups {
    constructor(elements) {
        // If `modals` is a nodelist transform it into a array
        if (elements == '[object NodeList]') {
            elements = Array.prototype.slice.call(elements);
        }

        elements.forEach(element => new MDFormGroup(element));
    }
}

class MDFormGroup {
    constructor(element) {
        this._group = element;
        this._label = this._group.querySelector('.v-form-group__label');
        this._input = this._group.querySelector('.v-form-group__input');

        this._dataGroup = {
            'name': 'data-mdform',
            'fold': 'folded',
            'unfold': 'unfolded',
        };

        if (this._label && this._input && !this._input.getAttribute('placeholder')) {
            this._init();
        }
    }

    _init() {
        this._listener();
        this.foldController();
    }

    _listener() {
        this._input.addEventListener('focus', function(event) {
            this.unfold();
        }.bind(this), false);

        this._input.addEventListener('focusout', function(event) {
            this.foldController();
        }.bind(this), false);
    }

    foldController() {
        if (this._input.value.length) {
            this.unfold();
        } else {
            this.fold();
        }
    }

    fold() {
        this._label.setAttribute(this._dataGroup.name, this._dataGroup.fold);
    }

    isFolded() {
        return this._label.getAttribute(this._dataGroup.name) === this._dataGroup.fold;
    }

    unfold() {
        this._label.setAttribute(this._dataGroup.name, this._dataGroup.unfold);
    }

    isUnfolded() {
        return this._label.setAttribute(this._dataGroup.name) === this._dataGroup.unfold;
    }
}
