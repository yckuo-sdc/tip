$(document).ready(function(){
    chartjs_ajax();
});

function chartjs_ajax() {
	$.ajax({    
         url: '/ajax/chart',
		 cache: false,
		 dataType:'json',
		 type:'GET',
		 data: {chartID:'nics'},
		 error: function(xhr) {
			 console.log('Ajax failed');
		 },success: function(data) {
			console.log(data);
			var tmp_data, len;
			var classesArray = [];
            

            new Chart(
                $('#topProductChart'),
                {
                    type: 'polarArea',
                    data: {
                        labels: data.topProduct.map(row => row.name),
                        datasets: [
                            {
                                label: 'Class Rank',
                                data: data.topProduct.map(row => row.count)
                            }
                        ]
                    }
                }
            );

            new Chart(
                $('#topPortChart'),
                {
                    type: 'polarArea',
                    data: {
                        labels: data.topPort.map(row => row.name),
                        datasets: [
                            {
                                label: 'Class Rank',
                                data: data.topPort.map(row => row.count)
                            }
                        ]
                    }
                }
            );

            new Chart(
                $('#classRankChart'),
                {
                    type: 'bar',
                    data: {
                        labels: data.classes.map(row => row.name),
                        datasets: [
                            {
                                label: 'Organization',
                                data: data.classes.map(row => row.count),
                            }
                        ]
                    },
                    options: {
                        indexAxis: 'y',
                    }
                }
            );

            new Chart(
                $('#lineChart'),
                {
                    type: 'line',
                    data: {
                        labels: data.topPort.map(row => row.name),
                        datasets: [
                            {
                                label: 'Port',
                                data: data.topPort.map(row => row.count),
                                fill: false,
                                borderColor: 'rgb(75, 192, 192)',
                                tension: 0.1
                            }
                        ]
                    }
                }
            );


		 } // end of success
	});
	return 0;
 }

