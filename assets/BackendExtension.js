var CatalogManager = {};

(function () {

    'use strict';

    if ( typeof window.addEventListener != "undefined" ) {

        window.addEventListener( 'DOMContentLoaded', initialize, false );
    }

    function initialize() {

        CatalogManager.CatalogOrderByWizard = function ( objElement, strCommand, strID ) {

            var table = $(strID),
                tbody = table.getElement('tbody'),
                parent = $(objElement).getParent('tr'),
                rows = tbody.getChildren(),
                tabindex = tbody.get('data-tabindex'),
                input, childs, i, j;

            Backend.getScrollOffset();

            switch ( strCommand ) {

                case 'copy':

                    var tr = new Element('tr');
                    childs = parent.getChildren();

                    for (i=0; i<childs.length; i++) {

                        var next = childs[i].clone(true).inject(tr, 'bottom');

                        if (input = childs[i].getFirst('select')) {

                            next.getFirst().value = input.value;
                        }
                    }

                    tr.inject(parent, 'after');

                    break;

                case 'up':

                    if (tr = parent.getPrevious('tr')) {

                        parent.inject(tr, 'before');

                    } else {

                        parent.inject(tbody, 'bottom');
                    }

                    break;

                case 'down':

                    if (tr = parent.getNext('tr')) {

                        parent.inject(tr, 'after');
                        
                    } else {

                        parent.inject(tbody, 'top');
                    }

                    break;

                case 'delete':

                    if (rows.length > 1) {

                        parent.destroy();
                    }

                    break;
            }

            rows = tbody.getChildren();

            for (i=0; i<rows.length; i++) {

                childs = rows[i].getChildren();

                for (j=0; j<childs.length; j++) {

                    $$( childs[j].getElement('.chzn-container') ).destroy();
                    $$( childs[j].getElement('.tl_select_column') ).destroy();

                    if (input = childs[j].getFirst('select')) {

                        input.set('tabindex', tabindex++);
                        input.name = input.name.replace(/\[[0-9]+]/g, '[' + i + ']');

                        new Chosen( childs[j].getFirst('select.tl_chosen') );
                    }
                }
            }

            new Sortables(tbody, {

                constrain: true,
                opacity: 0.6,
                handle: '.drag-handle'
            });
        }
    }

})();