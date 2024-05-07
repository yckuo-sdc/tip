$(document).ready(function(){
	var url,hostname;
	if(location.hostname == 'vision.tainan.gov.tw'){
        hostname = 'vision.tainan.gov.tw/common/sdciss_lib';
	}else{											  
        hostname = location.hostname;
    }
	if (location.protocol == 'https:'){ 
        url='https://' + hostname;
	}else{
        url='http://' + hostname;
    }

	// Load the Visualization API and the corechart package.
	google.charts.load('current', {'packages':['corechart']});
	// Set a callback to run when the Google Visualization API is loaded.
	google.charts.setOnLoadCallback(drawPieChart);

	// Load the Visualization API and the corechart package.
    google.charts.load('current', {'packages':['treemap']});
	// Set a callback to run when the Google Visualization API is loaded.
	google.charts.setOnLoadCallback(drawTreeMapChart);

	// Callback that creates and populates a data table,
	// instantiates the pie chart, passes in the data and
	// draws it.
	function drawPieChart() {
		// Create the data table.
		var data = new google.visualization.DataTable();
		data.addColumn('string', 'Topping');
		data.addColumn('number', 'Slices');
		data.addRows([
		  ['Mushrooms', 3],
		  ['Onions', 1],
		  ['Olives', 1],
		  ['Zucchini', 1],
		  ['Pepperoni', 2]
		]);

		// Set chart options
		var options = {'title':'How Much Pizza I Ate Last Night',
					   'width':400,
					   'height':300};

	    var div = document.getElementById('pie_chart_div');
        if(div != null){
            // Instantiate and draw our chart, passing in some options.
            var chart = new google.visualization.PieChart(div);
            chart.draw(data, options);    
        }
    }

	function drawTreeMapChart() {
        $.ajax({
             url: url+'/ajax/chart.php',
             cache: false,
             dataType:'html',
             type:'GET',
             data: {chartID:'app'},
             error: function(xhr) {
                 console.log('Ajax failed');
             },success: function(data) {
                var appArray = [];
                $(data).find("top-applications").each(function(){
                     var name = $(this).children("name").text();
                     var nbytes = $(this).children("nbytes").text();
                     var nsess = $(this).children("nsess").text();
                     appArray.push([name, 'Top Applications', parseInt(nsess,10), parseInt(nbytes,10)]);
                });

                appArray.unshift(['Top Applications', null, 0, 0]);
                appArray.unshift(['Location', 'Parent', 'Sessions(size)', 'Bytes(color)']);
                    	
                var gdata = google.visualization.arrayToDataTable(appArray);
                var div = document.getElementById('topApp_chart'); 

                if(div != null){
                    var tree = new google.visualization.TreeMap(div);
					var options = { 
                        maxDepth: 1,
						maxPostDepth: 2,
						minHighlightColor: '#8c6bb1',
						midHighlightColor: '#9ebcda',
						maxHighlightColor: '#edf8fb',
						minColor: '#009688',
						midColor: '#f7f7f7',
						maxColor: '#ee8100', 
						headerHeight: 15,
						fontColor: 'black',
                    	height: 350,
						showScale: true,
					    generateTooltip: showFullTooltip
					};

                    tree.draw(gdata, options);

                    function showFullTooltip(row, size, value) {
                        return '<div class="g-tooltip" style="background:#fd9; padding:10px; border-style:solid">' +
                            '<span style="font-family:Courier"><b>' + 
                            gdata.getValue(row, 0) +
                            '</b></span><br>' +
                            '同時連線數: ' + gdata.getValue(row, 2) + '<br>' +
                            '位元組: ' + formatBytes(gdata.getValue(row, 3)) + 
                            '</div>';
                    }

                    function formatBytes(bytes){
                        if (bytes === 0) return '0 Bytes';
                        var decimals = 2;
                        var k = 1024;
                        var dm = decimals < 0 ? 0 : decimals;
                        var sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
                        var i = Math.floor(Math.log(bytes) / Math.log(k));
                        return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
                    } 

                }

			 }
        });

	}

});

