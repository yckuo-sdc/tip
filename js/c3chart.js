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
	//bind show_chart_btn
	$('#show_chart_btn').click(function (){
		c3_chart_Ranking_ajax(url);
	});
	
	c3_chart_enews_ajax(url);
	c3_chart_ranking_ajax(url);
	c3_chart_client_ajax(url);
	c3_chart_network_ajax(url);
});

function c3_chart_enews_ajax(url){
	$.ajax({    
         url: url+'/ajax/chart.php',
		 cache: false,
		 dataType:'json',
		 type:'GET',
		 data: {chartID:'enews'},
		 error: function(xhr) {
			 console.log('Ajax failed');
		 },success: function(data) {
			 //console.log(data);
			 var countArray = [], timeArray = [], donecountArray = [];
			 var len = data.length;
			 for(var i=0; i<len; i++){
				 var time = data[i].time;
				 var count = data[i].count;
				 var donecount = data[i].donecount;
				 timeArray.push(time);
			 	 countArray.push(count);
			 	 donecountArray.push(donecount);
			 }

			//add the label of bar chart
			countArray.unshift('資安事件數量');
			donecountArray.unshift('資安事件已結案數量');
			
			var chart = c3.generate({
					bindto: '#chartA',
					data: {
						columns: [
							countArray,
							donecountArray
						],
						type:  'area-step'
					},axis: {
						x: {
							type: 'category',
							categories: timeArray,
							tick: {
								count: 3
							}
						}
					},
					size:{
						height: '100%'
					}
			});
			
		 }
	});
	return 0;
 }

function c3_chart_ranking_ajax(url){
	$.ajax({
		url: url+'/ajax/chart.php',
		cache: false,
		dataType:'json',
		type:'GET',
		data: {chartID:'ranking'},
		error: function(xhr) {
			console.log('Ajax failed');
		},success: function(data) {	 
			//console.log(data);
			var tmp_data, len;
			var lastyearcountArray = [], lastyearnameArray = [];
			var thisyearcountArray = [], thisyearnameArray = [];
			var eventtypeArray = [];
			var agencynameArray = [], agencycountArray = [], agencyipArray = [];
			var destipnameArray = [], destipcountArray = [];
			var total_ip = 0;
			tmp_data = data.LastYearEvent;
			len = tmp_data.length;
			for(var i=0; i<len; i++){
				var month = tmp_data[i].month;
				var count = tmp_data[i].count;
				lastyearnameArray.push(month);
				lastyearcountArray.push(count);
			}
			tmp_data = data.ThisYearEvent;
			len = tmp_data.length;
			for(var i=0; i<len; i++){
				var name = tmp_data[i].month;
				var count = tmp_data[i].count;
				thisyearnameArray.push(month);
				thisyearcountArray.push(count);
			}
			tmp_data = data.EventType;
			len = tmp_data.length;
			for(var i=0; i<len; i++){
				var name = tmp_data[i].name;
				var count = tmp_data[i].count;
				eventtypeArray.push([name, count]);
			}
			tmp_data = data.AgencyName;
			len = tmp_data.length;
			for(var i=0; i<len; i++){
				var name = tmp_data[i].name;
				var count = tmp_data[i].count;
				var IP_count = tmp_data[i].IP_count;
				agencynameArray.push(name);
				agencycountArray.push(count);
				agencyipArray.push(IP_count);
			}
			tmp_data = data.DestIP;
			len = tmp_data.length;
			for(var i=0; i<len; i++){
				var name = tmp_data[i].name;
				var count = tmp_data[i].count;
				destipnameArray.push(name);
				destipcountArray.push(count);
			}
			
			//add the label of bar chart
			var d = new Date();
			var n = d.getFullYear();
			lastyearcountArray.unshift((n-1)+'資安事件(數量)');
			thisyearcountArray.unshift(n+'資安事件(數量)');
			agencycountArray.unshift('機關資安事件數量');
			agencyipArray.unshift('機關資安事件IP數量');
			destipcountArray.unshift('攻擊目標IP數量');
		
			//the setting of pie chart
			var cht_width = '500px';	//default width
			var arr = $('.post_title');
			for(i = 0; i < arr.length; i++){
				if((width = $(arr[i]).css('width')) !== '0px'){
					cht_width = width.replace(/px/,'');
					break;	
				}
			}
			var cht_height = cht_width * 0.4205;

			var chart = c3.generate({
				bindto: '#chartB',
				data: {
					columns: [
						lastyearcountArray,thisyearcountArray
					],
					type: 'bar'
				},axis: {
					x: {
						type: 'category',
						categories: lastyearnameArray,
						tick: {
				        	format: function (x) { 
								return (x+1)+'月'; 
							}
						}
					}
				},bar: {
					width: {
						ratio: 0.5 // this makes bar width 50% of length between ticks	
					}
				}
			});
		
			var chart = c3.generate({
				bindto: '#eventType_chart',
				data: {
				columns:
					eventtypeArray,
				type : 'pie'
				},
				size:{
					height: '100%'
				},
				onresize: function(){
			
				}
			});
			
			var chart = c3.generate({
				bindto: '#topEvent_chart',
				data:{ 
					columns: [agencycountArray,agencyipArray],
					type: 'bar',
					groups: [
						['機關資安事件數量', '機關資安事件IP數量']
					]
				},axis: {
					rotated: true,
					x: {
						type: 'category',
						categories: agencynameArray,
						rotated: true
					}
				},bar: {
					width: {
						ratio: 0.5 // this makes bar width 50% of length between ticks	
					}
				},size:{
					height: '100%'
				},grid: {
					x: {
						show: true
					},
					y: {
						show: true
					}
				}
			});
			
			var chart = c3.generate({
				bindto: '#topDestIP_chart',
				data:{ 
					columns: [destipcountArray],
					type: 'bar'
				},axis: {
					rotated: true,
					x: {
						type: 'category',
						categories: destipnameArray,
						rotated: true
					}
				},bar: {
					width: {
						ratio: 0.5 // this makes bar width 50% of length between ticks	
					}
				},size:{
					height: '100%'
				},grid: {
					x: {
						show: true
					},
					y: {
						show: true
					}
				}
			});

		}
	});
	return 0;
	
}	

function c3_chart_client_ajax(url){
	$.ajax({
		 url: url+'/ajax/chart.php',
		 cache: false,
		 dataType:'json',
		 type:'GET',
		 data: {chartID:'client'},
		 error: function(xhr) {
			 console.log('Ajax failed');
		 },success: function(data) {
			//console.log(data);	
			var tmp_data, len;
			var dripArray = [], dripComputersArray = [], gcbpassArray = [], osArray = [];
			var total_ip = 0;
			tmp_data = data.DrIP;
			len = tmp_data.length;
			for(var i=0; i<len; i++){
				var name = tmp_data[i].name;
				var count = tmp_data[i].count;
				dripArray.push([name, count]);
				total_ip = total_ip + parseInt(count);
			}

			tmp_data = data.DrIPComputers;
			len = tmp_data.length;
			for(var i=0; i<len; i++){
				var name = tmp_data[i].name;
				var count = tmp_data[i].count;
				dripComputersArray.push([name, count]);
				total_ip = total_ip + parseInt(count);
			}

			tmp_data = data.GCBPass;
			len = tmp_data.length;
			for(var i=0; i<len; i++){
				var total_count = tmp_data[i].total_count;
				var pass_count = tmp_data[i].pass_count;
				gcbpassArray.push(pass_count / total_count * 100);
			}

			tmp_data = data.OSEnv;
			len = tmp_data.length;
			for(var i=0; i<len; i++){
				var name = tmp_data[i].name;
				var count = tmp_data[i].count;
				osArray.push([name, count]);
			}
			
			//add the label of chart
			gcbpassArray.unshift('通過率');
			
			//the setting of pie chart
			var cht_width = '500px';	//default width
			var arr = $('.post_title');
			for(i = 0; i < arr.length; i++){
				if((width = $(arr[i]).css('width')) !== '0px'){
					cht_width = width.replace(/px/,'');
					break;	
				}
			}
			var cht_height = cht_width * 0.4205;	//default height

			var chart = c3.generate({
				bindto: '#gcbOS_chart',
				data: {
				columns:
					osArray,
				type : 'pie'
				},
				pie:{
				},
				size:{
					height: '100%'
				},
				tooltip:{ 
					format: { 
						value: function (value, ratio, id) {
							return Math.round(ratio * 1000)/10+'% | '+value;
						} 
					} 
				}
			});
			
			var chart = c3.generate({
				bindto: '#drip_chart',
				data: {
				columns:
					dripArray,
				type : 'donut'
				},
				donut:{
					//title: "IP總數:"+total_ip,
					label: {
					}
				},
				size:{
					height: '100%'
				},
				tooltip:{ 
					format: { 
						value: function (value, ratio, id) {
							return Math.round(ratio * 1000)/10+'% | '+value;
						} 
					} 
				},
				onrendered: function () {
					var data = this.api.data.shown.call (this.api);
					var total = data.reduce (function (subtotal, t) {
						return subtotal + t.values.reduce (function (subsubtotal,b) { return subsubtotal + b.value; }, 0);
					}, 0);
					d3.select(this.config.bindto + " .c3-chart-arcs-title").text("IP總數: "+total);
				}
			});
			
			var chart = c3.generate({
				bindto: '#drip_computers_chart',
				data: {
				columns:
					dripComputersArray,
				type : 'donut'
				},
				donut:{
					//title: "IP總數:"+total_ip,
					label: {
					}
				},
				size:{
					height: '100%'
				},
				tooltip:{ 
					format: { 
						value: function (value, ratio, id) {
							return Math.round(ratio * 1000)/10+'% | '+value;
						} 
					} 
				},
				onrendered: function () {
					var data = this.api.data.shown.call (this.api);
					var total = data.reduce (function (subtotal, t) {
						return subtotal + t.values.reduce (function (subsubtotal,b) { return subsubtotal + b.value; }, 0);
					}, 0);
					d3.select(this.config.bindto + " .c3-chart-arcs-title").text("電腦總數: "+total);
				}
			});
			
			var chart = c3.generate({
				bindto: '#gcbPass_chart',
				data: {
					columns: [
						gcbpassArray
					],
					type: 'gauge',
					onclick: function (d, i) { /*console.log("onclick", d, i);*/ },
					onmouseover: function (d, i) { /*console.log("onmouseover", d, i);*/ },
					onmouseout: function (d, i) { /*console.log("onmouseout", d, i);*/ }
				},
				gauge: {
				},
				color: {
					pattern: ['#FF0000', '#F97600', '#F6C600', '#60B044'], // the three color levels for the percentage values.
					threshold: {
						values: [30, 60, 90, 100]
					}
				},
				size: {
					height: 180
				}
			});

		 }
	});
	return 0;
 }

function c3_chart_network_ajax(url){
	$.ajax({
		 url: url+'/ajax/chart.php',
		 cache: false,
		 dataType:'html',
		 type:'GET',
		 data: {chartID:'network'},
		 error: function(xhr) {
			 console.log('Ajax failed');
		 },success: function(data) {
		    var attackName=[],attackCount=[];
			var deniedappName=[],deniedappCount=[];
			
			$(data).find("top-attacks").each(function(){
				 var name=$(this).children("threatid").text();
				 var count=$(this).children("count").text();
				 attackName.push(name);
				 attackCount.push(count);
			});
			
			$(data).find("top-denied-applications").each(function(){
				 var name=$(this).children("app").text();
				 var count=$(this).children("repeatcnt").text();
				 deniedappName.push(name);
				 deniedappCount.push(count);
			});
	
			//add the label of chart
			attackCount.unshift('觸發數');
			deniedappCount.unshift('repeat count');
			
			var chart = c3.generate({
				bindto: '#topAttack_chart',
				data:{ 
					columns: [attackCount],
					type: 'bar'
				},axis: {
					rotated: true,
					x: {
						type: 'category',
						categories: attackName,
						rotated: true
					}
				},bar: {
					width: {
						ratio: 0.5 // this makes bar width 50% of length between ticks	
					}
				},size:{
					height: '100%'
				},grid: {
					x: {
						show: true
					},
					y: {
						show: true
					}
				}
			});

			var chart = c3.generate({
				bindto: '#topDeny_chart',
				data:{ 
					columns: [deniedappCount],
					type: 'bar'
				},axis: {
					rotated: true,
					x: {
						type: 'category',
						categories: deniedappName,
						rotated: true
					}
				},bar: {
					width: {
						ratio: 0.5 // this makes bar width 50% of length between ticks	
					}
				},size:{
					height: '100%'
				},grid: {
					x: {
						show: true
					},
					y: {
						show: true
					}
				}
			});
		 }
	});
	return 0;
 }

