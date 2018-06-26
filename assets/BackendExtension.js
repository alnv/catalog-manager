var CatalogManager = CatalogManager || {};

(function () {

    'use strict';

    if ( typeof window.addEventListener !== "undefined" ) {

        window.addEventListener( 'DOMContentLoaded', initialize, false );
    }

    function initialize() {

        CatalogManager.CatalogRelationWizard = function ( objElement, strCommand, strID ) {

            var table = $(strID),
                tbody = table.getElement('tbody'),
                parent = $(objElement).getParent('tr'),
                rows = tbody.getChildren(),
                tabindex = tbody.get('data-tabindex'),
                input, childs, i, j;

            Backend.getScrollOffset();

            switch (strCommand) {

                case 'copy':

                    var tr = new Element('tr');
                    childs = parent.getChildren();

                    for (i=0; i<childs.length; i++) {

                        var next = childs[i].clone(true).inject(tr, 'bottom');

                        if (input = childs[i].getFirst('input')) {

                            next.getFirst('input').value = input.value;

                            if (input.type === 'checkbox') {

                                next.getFirst('input').checked = input.checked ? 'checked' : '';
                            }
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
            }

            rows = tbody.getChildren();

            for (i=0; i<rows.length; i++) {

                childs = rows[i].getChildren();

                for (j=0; j<childs.length; j++) {

                    if (input = childs[j].getFirst('input') ) {

                        reIndexInput( input, tabindex );

                        if ( input = input.getNext('input') ) {

                            reIndexInput( input, tabindex );
                        }
                    }
                }
            }

            function reIndexInput( objInput, tabindex ) {

                objInput.set( 'tabindex', tabindex++ );
                objInput.name = objInput.name.replace(/\[[0-9]+]/g, '[' + i + ']');

                if ( objInput.type === 'checkbox' ) {

                    objInput.id = objInput.name.replace(/\[[0-9]+]/g, '').replace(/\[/g, '_').replace(/]/g, '') + '_' + i;
                }

                if ( objInput.type === 'text' ) {

                    objInput.id = objInput.name.replace(/\[[0-9]+]/g, '').replace(/\[/g, '_').replace(/]/g, '') + '_' + i;
                }
            }

            new Sortables(tbody, {

                constrain: true,
                opacity: 0.6,
                handle: '.drag-handle'
            });
        };


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

                        if (input = childs[i].getFirst('input')) {

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

                    if (input = childs[j].getFirst('select') ) {

                        input.set('tabindex', tabindex++);
                        input.name = input.name.replace(/\[[0-9]+]/g, '[' + i + ']');

                        new Chosen( childs[j].getFirst('select.tl_chosen') );
                    }

                    if (input = childs[j].getFirst('input') ) {

                        input.set('tabindex', tabindex++ );
                        input.name = input.name.replace(/\[[0-9]+]/g, '[' + i + ']');
                    }
                }
            }

            new Sortables( tbody, {

                constrain: true,
                opacity: 0.6,
                handle: '.drag-handle'
            });
        };


        CatalogManager.CatalogToggleVisibility = function ( objElement, strID, strVisibleIcon, strInVisibleIcon, strAjaxPath ) {

            objElement.blur();

            var objImage = $( objElement ).getFirst('img');
            var intPublished = ( objImage.get('data-state') === 1 );

            if ( !intPublished ) {

                objImage.src = strInVisibleIcon;
                objImage.set( 'data-state', 1 );

                new Request.Contao( { 'url': window.location.href + '&' + strAjaxPath, 'followRedirects': false } ).get( {'tid': strID, 'state': 1, 'rt': Contao.request_token } );

            } else {

                objImage.src = strVisibleIcon;
                objImage.set('data-state', 0);

                new Request.Contao( { 'url': window.location.href + '&' + strAjaxPath, 'followRedirects': false } ).get( {'tid': strID, 'state': 0, 'rt': Contao.request_token } );
            }

            return false;
        };
    }
})();