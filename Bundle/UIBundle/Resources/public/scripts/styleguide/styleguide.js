var toggleSidebar = function() {
    document.querySelector('.ts-body').classList.toggle('is-nosidebar');
}

new Clipboard('.tsjs-copy');

var openSnippet = function(el, target) {
    var anchors = el.parentNode.childNodes;
    for (var i = 0; i < anchors.length; i++) {
        if (typeof anchors[i].classList !== 'undefined') {
            anchors[i].classList.remove('is-active');
        }
    }
    el.classList.add('is-active');

    var collection = el.parentNode.nextElementSibling.childNodes;
    for (var i = 0; i < collection.length; i++) {
        var snippet = collection[i];

        if (typeof snippet.classList !== 'undefined') {
            if (snippet.classList.contains('ts-snippet')) {
                snippet.classList.remove('is-active');

                if (snippet.getAttribute('data-snippet') == target) {
                    snippet.classList.add('is-active');
                }
            }
        }
    }
}
