export default class TrowelModals {
    constructor(modals) {
        // If `modals` is a nodelist transform it into a array
        if (modals == '[object NodeList]') {
            modals = Array.prototype.slice.call(modals);
        }

        modals.forEach(modal => new TrowelModal(modal));
    }
}

class TrowelModal {
    constructor(modal) {
        this._modal = modal;
        this._togglers = document.querySelectorAll('[data-v-modal-toggle="#' + this._modal.id + '"]');
        this._showers = document.querySelectorAll('[data-v-modal-show="#' + this._modal.id + '"]');
        this._hidders = document.querySelectorAll('[data-v-modal-hide="#' + this._modal.id + '"]');
        this._visible = false;

        this.hide();
        this._listener();
    }

    show() {
        this._modal.setAttribute('data-modal', 'show');
        return this._visible = true;
    };

    hide() {
        this._modal.setAttribute('data-modal', 'hide');
        return this._visible = false;
    };

    toggle() {
        if (this._visible) return this.hide();
        return this.show();
    };

    _listener() {
        if (this._togglers) {
            this._togglers.forEach(function(toggler) {
                toggler.addEventListener('click', function() {
                    this.toggle();
                }.bind(this));
            }.bind(this));
        }

        if (this._showers) {
            this._showers.forEach(function(shower) {
                shower.addEventListener('click', function() {
                    this.show();
                }.bind(this));
            }.bind(this));
        }

        if (this._hidders) {
            this._hidders.forEach(function(hidder) {
                hidder.addEventListener('click', function() {
                    this.hide();
                }.bind(this));
            }.bind(this));
        }
    };
}
