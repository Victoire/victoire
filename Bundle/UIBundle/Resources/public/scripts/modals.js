var ViTrowelModals = function(modals) {
    modals.forEach(function(modal) {
        var modal_obj = new ViTrowelModal(modal);
    })
}

var ViTrowelModal = function(modal) {
    this._modal = modal;
    this._togglers = document.querySelectorAll('[data-vi-modal-toggle="#' + this._modal.id + '"]');
    this._showers = document.querySelectorAll('[data-vi-modal-show="#' + this._modal.id + '"]');
    this._hidders = document.querySelectorAll('[data-vi-modal-hide="#' + this._modal.id + '"]');
    this._visible = false;

    this.hide();
    this._listener();
}

ViTrowelModal.prototype.show = function () {
    this._modal.setAttribute('data-modal', 'show');
    return this._visible = true;
};

ViTrowelModal.prototype.hide = function () {
    this._modal.setAttribute('data-modal', 'hide');
    return this._visible = false;
};

ViTrowelModal.prototype.toggle = function () {
    if (this._visible) return this.hide();
    return this.show();
};

ViTrowelModal.prototype._listener = function () {
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

var modals = new ViTrowelModals(document.querySelectorAll('[data-flag="vi-modal"]'))
