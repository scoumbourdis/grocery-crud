(function() {

  (function($) {
	  var my_timer = null;
	  var my_timer2 = null;
	  
    return $.fn.ajaxChosen = function(options, callback) {
      var select;
      select = this;
      this.chosen({allow_single_deselect:true});
      this.next('.chzn-container').find(".search-field > input").bind('keyup', function() {
        var field, val;
        val = $.trim($(this).attr('value'));
        if (val.length < 2 || val === $(this).data('prevVal')) return false;
        if (this.timer) clearTimeout(this.timer);
        $(this).data('prevVal', val);
        field = $(this);

        options.data = {
          term: val,
          field_name: select.attr('name') //Inserted for grocery CRUD          
        };
        if (typeof success === "undefined" || success === null) {
          success = options.success;
        }
        options.success = function(data) {
          var items;
          if (!(data != null)) return;
          select.find('option').each(function() {
            if (!$(this).is(":selected")) return $(this).remove();
          });
          items = callback(data);
          $.each(items, function(value, text) {
            return $("<option />").attr('value', value).html(text).appendTo(select);
          });
          select.trigger("liszt:updated");
          field.attr('value', val);
          if (typeof success !== "undefined" && success !== null) return success();
        };
        
        if(my_timer !== null) clearTimeout(my_timer);
        
        my_timer = setTimeout(function() {
        	return $.ajax(options);
        }, 800);
        return my_timer;
      });
      return this.next('.chzn-container').find(".chzn-search > input").bind('keyup', function() {
        var field, val;
        val = $.trim($(this).attr('value'));
        if (val.length < 2 || val === $(this).data('prevVal')) return false;
        
        field = $(this);

        options.data = {
          term: val,
          field_name: select.attr('name') //Inserted for grocery CRUD
        };
        if (typeof success === "undefined" || success === null) {
          success = options.success;
        }
        options.success = function(data) {
          var items;
          if (!(data != null)) return;
          select.find('option').each(function() {
            return $(this).remove();
          });
          items = callback(data);
          $.each(items, function(value, text) {
            return $("<option />").attr('value', value).html(text).appendTo(select);
          });
          select.trigger("liszt:updated");
          field.attr('value', val);
          if (typeof success !== "undefined" && success !== null) return success();
        };
        
        if(my_timer2 !== null) clearTimeout(my_timer2);
        
        my_timer2 = setTimeout(function() {
        	return $.ajax(options);
        }, 800);
        return my_timer2;
      });
    };
  })(jQuery);

}).call(this);
