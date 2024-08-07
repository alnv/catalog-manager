document.addEventListener('DOMContentLoaded', function () {
    'use strict';
    var arrFields = document.querySelectorAll('input.awesomplete-field');

    function isNull(varValue) {
        return varValue === null && typeof varValue === "object";
    }

    function setupAwesomplete(objInput) {
        var objConfig = {
            list: [],
            sort: false,
            autoFirst: false
        };
        if (objInput.classList.contains('multiple')) {
            objConfig.filter = function (strText, input) {
                return Awesomplete.FILTER_CONTAINS(strText, input.match(/[^ ]*$/)[0]);
            };

            objConfig.item = function (strText, input) {
                return Awesomplete.ITEM(strText, input.match(/[^ ]*$/)[0]);
            };

            objConfig.replace = function (strText) {
                var before = this.input.value.match(/^.+ \s*|/)[0];
                this.input.value = before + strText + " ";
            };
        }

        var objAwesomplete = new Awesomplete(objInput, objConfig);

        objInput.addEventListener('keyup', function (objEvent) {
            objEvent.preventDefault();
            var intCode = (objEvent.keyCode || objEvent.which);
            if (intCode === 37 || intCode === 38 || intCode === 39 || intCode === 40 || intCode === 27 || intCode === 13) {
                return false;
            }
            var objAutoCompletionRequest = new XMLHttpRequest();
            var strQuery = objEvent.target.value;
            var strName = this.name;
            var strBind = location.href.indexOf('?') !== -1 ? '&' : '?';
            var strRequest = location.href.replace(location.hash, '') + strBind + 'ctlg_fieldname=' + (strName ? strName : '').replace(/[\[\]']+/g, '');
            if (strQuery) {
                var arrQueries = strQuery.split(' ');
                var intQueryLength = arrQueries.length;
                strQuery = arrQueries[intQueryLength - 1].replace(/ /g, '');
            }
            if (strQuery.length > 1 && strName) {
                objAutoCompletionRequest.open("GET", strRequest + '&ctlg_autocomplete_query=' + strQuery, true);
                objAutoCompletionRequest.onload = function () {
                    objAwesomplete.list = JSON.parse(objAutoCompletionRequest.responseText).words;
                };
                objAutoCompletionRequest.send();
            }

            return false;
        });
    }

    if (!isNull(arrFields) && typeof arrFields === 'object' && typeof arrFields.length !== 'undefined') {
        for (var index = 0; index < arrFields.length; index++) {
            setupAwesomplete(arrFields[index]);
        }
    }
});