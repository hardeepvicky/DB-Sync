jQuery.fn.extend({
    srDatatable: function ()
    {
        return this.each(function ()
        {
            var _table = $(this);
            
            if ( _table.hasClass("sr-data-table") )
            {
                return;
            }
            
            _table.addClass("sr-data-table");
            
            _table.find("> thead > tr > th").each(function(index, value)
            {
                var _th = $(this);
                var search_clear = _th.attr("data-search-clear");
                var require_search = _th.attr("data-search");
                var sort_type = _th.attr("data-sort");
                
                var html = _th.html();
                var html_change = false;
                if (typeof sort_type != "undefined" && sort_type)
                {
                    html += "<span class='sort-direction'><i class='fa fa-sort'></i></span>";
                    html_change = true;
                }
                
                if (typeof require_search != "undefined" && require_search)
                {
                    html += "<span class='icon-search'><i class='fa fa-search'></i></span><div class='search-block'><input type='text' data-col_index='" + index + "' /></div>";
                    html_change = true;
                }
                
                if (typeof search_clear != "undefined" && search_clear)
                {
                    html += "<span class='icon-search-clear'><i class='fa fa-remove'></i></span>";
                    html_change = true;
                }
                    
                if (html_change)
                {
                    _th.html(html);
                }
                    
                if (typeof require_search != "undefined" && require_search)
                {
                    _th.find(".icon-search").click(function()
                    {
                        var _th = $(this).parent();
                        var search = _th.find(".search-block input").val().trim().toLowerCase();
                        var hide_counter = 0;
                        _table.find("> tbody > tr").each(function()
                        {
                            var _td = $(this).find("td:eq(" + index +")");
                            if (search)
                            {
                                var v = _td.text().toLowerCase();

                                if (v.indexOf(search) < 0)
                                {
                                    _td.addClass("sr-datatable-hide");                                    
                                    hide_counter++;
                                }
                                else
                                {
                                    _td.removeClass("sr-datatable-hide");
                                    _th.find(".icon-search").removeClass("active");
                                }
                            }
                            else
                            {
                                _td.removeClass("sr-datatable-hide");
                            }
                        });
                        
                        
                        if (hide_counter > 0)
                        {
                            _th.find(".icon-search").addClass("active");
                        }
                        else
                        {
                            _th.find(".icon-search").removeClass("active");
                        }
                        
                        _table.find(">tbody > tr").removeClass("sr-hidden");
                        _table.find(">tbody > tr:has(td.sr-datatable-hide)").addClass("sr-hidden");
                    });
                    
                    _th.find(".search-block input").keyup(function(e)
                    {
                        _th.find(".icon-search").trigger("click");                        
                    });                   
                }
                
                if (typeof search_clear != "undefined" && search_clear)
                {
                    _th.find(".icon-search-clear").click(function()
                    {
                        $(this).closest("tr").find(".search-block").find("input").val("");
                        
                        $(this).closest("tr").find(".icon-search").removeClass("active");
                        
                        _table.find(">tbody > tr > td").removeClass("sr-datatable-hide");
                        _table.find(">tbody > tr").removeClass("sr-hidden");
                    });
                }
                
                if (typeof sort_type != "undefined" && sort_type)
                {
                    _th.find(".sort-direction").click(function()
                    {
                        _th = $(this).parent();
                        var sort_type = _th.attr("data-sort");
                        var sort_dir = "ASC";
                        
                        if ($(this).find(".fa-sort-asc").length > 0)
                        {
                            sort_dir = "DESC";
                        }
                        
                        var list = [];
                        
                        _table.find("> tbody > tr").each(function(row_index, value)
                        {
                            var _td = $(this).find("td:eq(" + index +")");
                            var text = _td.text();
                            if (sort_type == "numeric")
                            {
                                text = parseFloat(text);
                            }
                            list.push({row : row_index, text : text});
                        });
                        
                        list.sort(function(a, b)
                        {
                            if (sort_dir == "ASC")
                            {
                                if (a.text < b.text)
                                {
                                    return -1;
                                }
                                if (a.text > b.text)
                                {
                                    return 1;
                                }
                            }
                            else
                            {
                                if (a.text < b.text)
                                {
                                    return 1;
                                }
                                if (a.text > b.text)
                                {
                                    return -1;
                                }
                            }
                            
                            return 0;
                        });
                        
                        var trs = _table.find(">tbody > tr");
                        
                        _table.find(">tbody").html("");
                        
                        console.log(trs);
                        console.log(list);
                        
                        for(var i in list)
                        {
                            var obj = list[i];
                            _table.find(">tbody").append(trs[obj.row]);
                        }
                        
                        if (sort_dir == "ASC")
                        {
                            $(this).html('<i class="fa fa-sort-asc"></i>');
                        }
                        else
                        {
                            $(this).html('<i class="fa fa-sort-desc"></i>');
                        }
                    });
                }
            });
        });
    },
});