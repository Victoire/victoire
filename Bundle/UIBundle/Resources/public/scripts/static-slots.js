var slotSelects = document.querySelectorAll('.vic-slot__select');
var slotOpenModifier = 'vic-slot--open';

for (var i = 0; i < slotSelects.length; i++) {
    slotSelects[i].addEventListener('focus', slotFocus());
    slotSelects[i].addEventListener('change', slotBlur());
    slotSelects[i].addEventListener('blur', slotBlur());
};

function slotFocus() {
    return function() {
        var slot = this.parentNode;
        slot.classList.add(slotOpenModifier);
    }
};

function slotBlur() {
    return function() {
        var slotSelect = this;
        var slot = this.parentNode;
        slot.classList.remove(slotOpenModifier);
        slotSelect.selectedIndex = 0;
        slotSelect.blur();
    }
};

function slotSize() {
    var smClass = 'vic-slot--sm';
    var slots = document.querySelectorAll('.vic-slot');

    for (var i = 0; i < slots.length; i++) {
        var slot = slots[i];

        if (slot.offsetWidth > 250 && slot.classList.contains(smClass)) {
            slot.classList.remove(smClass);
        } else if (slot.offsetWidth <= 250 && slot.offsetWidth > 0) {
            slot.classList.add(smClass);
        }
    }
};

if (document.readyState != 'loading'){
    slotSize();
} else {
    document.addEventListener('DOMContentLoaded', slotSize);
};

window.addEventListener('resize', function() {
    slotSize();
});
