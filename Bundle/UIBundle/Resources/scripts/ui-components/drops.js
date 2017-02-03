let isObject = item => {
    return (item && typeof item === 'object' && !Array.isArray(item) && item !== null);
}

let mergeDeep = (target, source) => {
    let output = Object.assign({}, target);

    if (isObject(target) && isObject(source)) {
        Object.keys(source).forEach(key => {
            if (isObject(source[key])) {
                if (!(key in target))
                Object.assign(output, { [key]: source[key] });
                else
                output[key] = mergeDeep(target[key], source[key]);
            } else {
                Object.assign(output, { [key]: source[key] });
            }
        });
    }
    return output;
}

export default class TrowelDrops {
    constructor(elements) {
        // If `modals` is a nodelist transform it into a array
        if (elements == '[object NodeList]') {
            elements = Array.prototype.slice.call(elements);
        }

        elements.forEach(element => new TrowelDrop(element));
    }
}

class TrowelDrop {
    constructor(trigger, options = {}) {
        if (window.Tether === undefined) {
            throw new Error('Trowel drops require Tether (http://tether.io/)')
        }

        if (typeof(trigger) == 'object') {
            this._trigger = trigger;
            this._drop = document.querySelector(this._trigger.getAttribute('data-droptarget'));
            this._options = this.setOptions(options);
            this._tether = new Tether(this.getTetherOptions(this._options));
            this._visible = this._options.visible;
            this.turnVisibility();
            this.setGutterPositions();
            this._listener();
        }

    }

    setOptions(options) {
        const defaultOptions = {
            visible: false,
            behavior: 'click',
            position: 'bottomout leftin',
        };

        let fullOptions = mergeDeep(defaultOptions, options);

        for (let option in defaultOptions) {
            const dataOption = this._trigger.getAttribute(`data-${option}`);

            if (dataOption) {
                fullOptions[option] = dataOption;
            }
        }


        const { posY, posX } = this.getPositions(fullOptions);

        if (!['click', 'hover'].includes(fullOptions.behavior)) {
            throw new Error('Trowel drops behavior option must be \'click\' or \'hover\'')
        }

        if (fullOptions.position.split(' ').length != 2) {
            throw new Error('Trowel drops position option must be a string within two words describing Y (\'top\', \'middle\' or \'bottom\') and X (\'left\', \'center\' or \'right\') position')
        }

        if (!['topin', 'topout', 'middle', 'bottomin', 'bottomout'].includes(posY)) {
            throw new Error('Trowel drops position option first word must be \'topin\', \'topout\', \'middle\', \'bottomin\' or \'bottomout\'')
        }

        if (!['leftin', 'leftout', 'center', 'rightin', 'rightout'].includes(posX)) {
            throw new Error('Trowel drops position option second word must be \'leftin\', \'leftout\', \'center\', \'rightin\' or \'rightout\'')
        }

        return fullOptions;
    }

    getPositions(options) {
        return {
            options: options,
            posY: options.position.split(' ')[0],
            posX: options.position.split(' ')[1],
        }
    }

    getTetherOptions(options) {
        const { posY, posX } = this.getPositions(options);
        let attachmentX, attachmentY, targetAttachmentX, targetAttachmentY, gutterX, gutterY;

        switch (posY) {
            case 'topout':
                attachmentY = 'bottom';
                targetAttachmentY = 'top';
                break;
            case 'topin':
                attachmentY = 'top';
                targetAttachmentY = 'top';
                break;
            case 'bottomin':
                attachmentY = 'bottom';
                targetAttachmentY = 'bottom';
                break;
            case 'bottomout':
                attachmentY = 'top';
                targetAttachmentY = 'bottom';
                break;
            default:
                attachmentY = 'center';
                targetAttachmentY = 'center';
        }

        switch (posX) {
            case 'leftout':
                attachmentX = 'right';
                targetAttachmentX = 'left';
                break;
            case 'leftin':
                attachmentX = 'left';
                targetAttachmentX = 'left';
                break;
            case 'rightin':
                attachmentX = 'right';
                targetAttachmentX = 'right';
                break;
            case 'rightout':
                attachmentX = 'left';
                targetAttachmentX = 'right';
                break;
            default:
                attachmentX = 'center';
                targetAttachmentX = 'center';
        }

        let config = {
            element: this._drop,
            target: this._trigger,
            attachment: `${attachmentY} ${attachmentX}`,
            targetAttachment: `${targetAttachmentY} ${targetAttachmentX}`,
        };


        return config;
    }

    setGutterPositions() {
        const { posY, posX } = this.getPositions(this._options);
        let gutterY, gutterX;

        switch (posY) {
            case 'topout':
                gutterY = 'bottom';
                break;
            case 'bottomout':
                gutterY = 'top';
                break;
            default:
                gutterY = 'none';
        }

        switch (posX) {
            case 'leftout':
                gutterX = 'right';
                break;
            case 'rightout':
                gutterX = 'left';
                break;
            default:
                gutterX = 'none';
        }

        this._drop.setAttribute('data-gutter', `${gutterY} ${gutterX}`)
    }

    show() {
        this._visible = true;
        this.turnVisibility();
    }

    hide() {
        this._visible = false;
        this.turnVisibility();
    }

    isShown() {
        return this._drop.style.display == 'block';
    }

    isHidden() {
        return this._drop.style.display == 'none';
    }

    toggle() {
        this._visible = !this._visible;
        this.turnVisibility();
    }

    turnVisibility() {
        if (this._visible) {
            this._generateEvent('show.trowel.drops');
            this._drop.style.display = 'block';
            this._generateEvent('shown.trowel.drops');
        } else {
            this._generateEvent('hide.trowel.drops');
            this._drop.style.display = 'none';
            this._generateEvent('display.trowel.drops');
        }

        this._tether.position();
    }

    _listener() {
        switch (this._options.behavior) {
            case 'click':
                this._trigger.addEventListener('click', function(event) {
                    this.toggle();
                }.bind(this), false);

                document.addEventListener('click', function(event) {
                    var isClickInside = this._trigger.contains(event.target);

                    if (!isClickInside && this.isShown()) {
                        this.hide();
                    }
                }.bind(this), false);
                break;
            case 'hover':
                this._trigger.addEventListener('mouseenter', function(event) {
                    this.show();
                }.bind(this), false);

                this._trigger.addEventListener('mouseout', function(event) {
                    if (!this._trigger.contains(event.toElement)) {
                        this.hide();
                    }
                }.bind(this), false);

                break;
        }
    }

    _generateEvent(name) {
        let event = new Event(name);

        // Dispatch the event.
        this._drop.dispatchEvent(event);
    }

    _tetherHorizontalPos(item) {
        console.log(item);
        if (item.attachment.left == 'right' && item.attachment.top == 'top' && item.targetAttachment.left == 'left' && item.targetAttachment.top == 'bottom') {
            config.attachment = 'top right';
            config.targetAttachment = 'bottom right';

            this._tether.setOptions(config, false);
        }
    }
}
