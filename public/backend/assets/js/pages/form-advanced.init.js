! function(a) {
    "use strict";
    function e() {}
    e.prototype.init = function() {
        a(".select2").select2();
    }, a.AdvancedForm = new e, a.AdvancedForm.Constructor = e
}(window.jQuery),
function() {
    "use strict";
    window.jQuery.AdvancedForm.init()
}();