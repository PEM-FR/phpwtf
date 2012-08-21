// this file handles the charts generation from the djson file
d3.json('./djson', function(data){
	
	if (!Array.prototype.some)
	{
	  Array.prototype.some = function(fun /*, thisp */)
	  {
	    "use strict";
	 
	    if (this == null)
	      throw new TypeError();
	 
	    var t = Object(this);
	    var len = t.length >>> 0;
	    if (typeof fun != "function")
	      throw new TypeError();
	 
	    var thisp = arguments[1];
	    for (var i = 0; i < len; i++)
	    {
	      if (i in t && fun.call(thisp, t[i], i, t))
	        return true;
	    }
	 
	    return false;
	  };
	}
	
	time_scale = d3.time.scale()
	    .domain([
	        new Date(d3.min(data, function(d){return d.time})),
	        new Date(d3.max(data, function(d){return d.time}))
	    ])
	    .range([30,1000])
    ;

	var sel = d3.select("#trend-main"),
		svg = null,
		line = null,
		file = null
	;
	if(!sel.empty()){
		svg = sel.append("svg");
		
		value_scale = d3.scale.linear()
			.domain([0, d3.max(data, function(d){return d.total})])
			.range([270, 20])
		;

		line = d3.svg.line()
			.x(function(d){return time_scale( new Date(d.time));})
			.y(function(d){return value_scale(d.total);})
		;
	}else{
		sel = d3.select("#trend-file");
		file = d3.select("title").html();
		svg = sel.append("svg");
		
		value_scale = d3.scale.linear()
			.domain([0, d3.max(data, function(d){
				var value = 0;
				d.wtfs.some(function(wtf){
					if(file == wtf.file){
						value = wtf.total;
						return true;
					}
					return false;
				}, this);
				return value;
			})])
			.range([270, 20])
		;
		
		line = d3.svg.line()
			.x(function(d){return time_scale( new Date(d.time));})
			.y(function(d){
				var value = 0;
				d.wtfs.some(function(wtf){
					if(file == wtf.file){
						value = wtf.total;
						return true;
					}
					return false;
				}, this);
				return value_scale(value);
			})
		;
		
	}
	
	xAxis = d3.svg.axis().scale(time_scale);
	yAxis = d3.svg.axis().scale(value_scale).orient("left");

	svg.append("svg:path")
		.attr("class", "line")
		.attr("d", line(data))
	;

	svg.append("svg:g")
		.attr("class", "xaxis")
		.attr("transform","translate(0,270)")
		.call(xAxis)
	;

	svg.append("svg:g")
	    .attr("class", "yaxis")
	    .attr("transform", "translate(30,0)")
	    .call(yAxis)
    ;
});