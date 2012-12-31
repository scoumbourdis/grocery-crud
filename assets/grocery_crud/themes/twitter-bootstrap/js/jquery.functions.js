$(function() {

	function setupTablesorter() {
		//	Method money
		$.tablesorter.addParser({
            id: "money",
            is: function(s) {
                return true;
            },
            format: function(s) {
                return $.tablesorter.formatFloat(s.replace(/ /, '').replace('R$', '').replace(/\./, '').replace(/\,/, '.').replace(new RegExp(/[^0-9,]/g),""));
            },
            type: "numeric"
        });

		var classHeaders = {
			'text': '.sorter-text',
			'digit': '.sorter-digit',
			'currency': '.sorter-currency',
			'ipAddress': '.sorter-ipAddress',
			'url': '.sorter-url',
			'isoDate': '.sorter-isoDate',
			'usLongDate': '.sorter-usLongDate',
			'shortDate': '.sorter-shortDate',
			'time': '.sorter-time',
			'metadata': '.sorter-metadata',
			'money': '.sorter-money',
		};

		var tableHeaders = '', separator;

		$('.tablesorter').each(function (i, e) {
			$.each(classHeaders, function(key, value){
				$(this).find(value).each(function (pos) {
					if(separator == undefined)
						separator = ',';
					tableHeaders += separator +' '+ $(this).index()+' : { sorter: "'+key+'"}';
				});
			});
			console.log(tableHeaders);
			$(this).tablesorter({dateFormat: 'uk', noSorterClass: 'no-sorter', headers: tableHeaders });
		});
	}

	if($('.tablesorter')[0])
		setupTablesorter();
});