/* 
 * contain code for jquery extend
 * created 15-02-2017
 */

jQuery.fn.extend({
    chkSelectAll: function ()
    {
        return this.each(function()
        {
            var _this = $(this);
            var target = $(this).attr("data-href");
            
            $(this).change(function ()
            {
                $(target).prop("checked", this.checked);
            });
            
            $(target).change(function ()
            {
                var checked = $(target).length == $(target + ":checked").length;
                
                _this.prop("checked", checked);
            });
            
            var checked = $(target).length == $(target + ":checked").length;
            _this.prop("checked", checked);
        });
    }
});