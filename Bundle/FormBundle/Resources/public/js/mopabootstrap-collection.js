/* ============================================================
 * mopabootstrap-collection.js v3.0.0
 * http://bootstrap.mohrenweiserpartner.de/mopa/bootstrap/forms/collections
 * ============================================================
 * Copyright 2012 Mohrenweiser & Partner
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * ============================================================ */

!function ($vic) {
    "use strict";

    /* Collection PUBLIC CLASS DEFINITION
     * ============================== */

    var Collection = function (element, options) {
        this.$vicelement = $vic(element);
        this.options = $vic.extend({}, $vic.fn.collection.defaults, options);

        // This must work with "collections" inside "collections", and should
        // select its children, and not the "collection" inside children.
        var $viccollection = $vic('div' + this.options.collection_id);

        // Indexes must be different for every Collection
        if (typeof this.options.index === 'undefined') {
            this.options.index = {};
        }
        if (!this.options.initial_size) {
            this.options.initial_size = $viccollection.children().size();
        }

        this.options.index[this.options.collection_id] = this.options.initial_size;
    };
    Collection.prototype = {
        constructor: Collection,
        add: function () {
            console.log('collection item add');
            // this leads to overriding items
            this.options.index[this.options.collection_id] = this.options.index[this.options.collection_id] + 1;
            var index = this.options.index[this.options.collection_id];
            if ($vic.isFunction(this.options.addcheckfunc) && ! this.options.addcheckfunc()) {
                if ($vic.isFunction(this.options.addfailedfunc)) {
                    this.options.addfailedfunc();
                }
                return;
            }
            this.addPrototype(index);
        },
        addPrototype: function (index) {
            console.log('collection item addPrototype');
            var $viccollection = $vic(this.options.collection_id);
            console.log($viccollection);
            var prototype_name = $viccollection.data('prototype-name');
            var prototype_label = $viccollection.data('prototype-label');

            // Just in case it doesn't get it
            if (typeof prototype_name === 'undefined') {
                prototype_name = '__name__';
            }

            if (typeof prototype_label === 'undefined') {
                prototype_label = '__name__label__';
            }

            var name_replace_pattern = new RegExp(prototype_name, 'g');
            var label_replace_pattern = new RegExp(prototype_label, 'g');
            var rowContent = $viccollection.attr('data-prototype')
                    .replace(label_replace_pattern, index)
                    .replace(name_replace_pattern, index);
            var row = $vic(rowContent);
            console.log(rowContent);
            $viccollection.append(row);
            $vic(window).triggerHandler('add.mopa-collection-item', [$viccollection, row, index])
        },
        remove: function (row) {
            var $viccollection = $vic(this.options.collection_id);

            if (typeof row == 'undefined') {
                row = this.$vicelement.closest('.collection-item');
            }

            if (typeof row != 'undefined') {
                if (row instanceof $vic) {
                    row = row.get(0);
                }

                var oldIndex = this.getIndex(row);

                if (oldIndex == - 1) {
                    throw new Error('row not contained in collection');
                }

                $vic(window).triggerHandler('before-remove.mopa-collection-item', [$viccollection, row, oldIndex]);
                row.remove();
                $vic(window).triggerHandler('remove.mopa-collection-item', [$viccollection, row, oldIndex]);
            }
        },
        /**
         * Get the index of the current row zero based
         * return -1 if not found
         */
        getIndex: function (row) {
            if (row instanceof $vic) {
                row = row.get(0);
            }

            var $viccollection = $vic(this.options.collection_id);
            var items = $viccollection.children();

            for (var i = 0; i < items.size(); i ++) {
                if (row == items[i]) {
                    return i;
                }
            }
            return - 1;
        },
        getItem: function (index) {
            var items = this.getItems();

            return items[index];
        },
        getItems: function (index) {
            var $viccollection = $vic(this.options.collection_id);
            var items = $viccollection.children();

            return items;
        }
    };

    /* COLLECTION PLUGIN DEFINITION
     * ======================== */

    $vic.fn.collection = function (option) {
        var coll_args = arguments;

        return this.each(function () {
            var $victhis = $vic(this),
                collection_id = $victhis.data('collection-vic-add-btn'),
                data = $victhis.data('collection'),
                options = typeof option == 'object' ? option : {};

            if (collection_id) {
                options.collection_id = collection_id;
            }
            else if ($victhis.closest(".collection-vic-items").attr('id')) {
                options.collection_id = '#' + $victhis.closest(".collection-vic-items").attr('id');
            } else {
                options.collection_id = this.id.length === 0 ? false : '#' + this.id;
                if (!options.collection_id) {
                    throw new Error('Could not load collection id');
                }
            }
            if (!data) {
                $victhis.data('collection', (data = new Collection(this, options)));
            }
            if (coll_args.length > 1) {
                var arg1 = coll_args[1];
                var returnval;
            }
            if (option == 'add') {
                data.add();
            }
            if (option == 'remove') {
                data.remove(arg1);
            }
            if (option == 'getIndex') {
                returnval = data.getIndex(arg1);
            }
            if (option == 'getItem') {
                returnval = data.getItem(arg1);
            }
            if (option == 'getItems') {
                returnval = data.getItems();
            }
            if (coll_args.length > 2 && typeof coll_args[2] == 'function') {
                coll_args[2].call(this, returnval);
            }
        });
    };

    $vic.fn.collection.defaults = {
        collection_id: null,
        initial_size: 0,
        addcheckfunc: false,
        addfailedfunc: false
    };

    $vic.fn.collection.Constructor = Collection;

    /* COLLECTION DATA-API
     * =============== */

    $vic(function () {
        $vic('body').on('click.collection.data-api', '[data-collection-vic-add-btn]', function (e) {
            console.log('add item');
            var $vicbtn = $vic(e.target);
            if (! $vicbtn.hasClass('vic-btn')) {
                $vicbtn = $vicbtn.closest('.vic-btn');
            }
            console.log($vicbtn);
            $vicbtn.collection('add');
            e.preventDefault();
        });
        $vic('body').on('click.collection.data-api', '[data-collection-vic-remove-btn]', function (e) {
            var $vicbtn = $vic(e.target);

            if (! $vicbtn.hasClass('vic-btn')) {
                $vicbtn = $vicbtn.closest('.vic-btn');
            }
            $vicbtn.collection('remove');
            e.preventDefault();
        });
    });

}(window.$vic);
