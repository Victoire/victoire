import Clipboard from '../../config/node_modules/clipboard';
import hljs from '../../config/node_modules/highlight.js';

new Clipboard('.tsjs-copy');
hljs.initHighlightingOnLoad();

document.querySelector('[data-toggle="sidebar"]').addEventListener('click', function(event) {
    document.querySelector('.ts-body').classList.toggle('is-nosidebar');
});


const openSnippet = function(event) {
    const el = event.target;
    const target = el.getAttribute('data-snippet');

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


Array.prototype.slice.call(document.querySelectorAll('[data-snippet]')).forEach(element => {
    element.addEventListener('click', openSnippet);
});