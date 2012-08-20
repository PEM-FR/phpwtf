// this file handles the charts generation from the djson file
d3.json('./djson', function(data){
	time_scale = d3.time.scale()
	    .domain([
	        new Date(d3.min(data, function(d){return d.time})),
	        new Date(d3.max(data, function(d){return d.time}))
	    ])
	    .range([30,730])
                
	value_scale = d3.scale.linear()
	    .domain([0, d3.max(data, function(d){return d.yes_rsvp_count})])
	    .range([280, 20])
	
	xAxis = d3.svg.axis().scale(time_scale)
	yAxis = d3.svg.axis().scale(value_scale).orient("left")
	
	svg = d3.select("#trend")
	    .append("svg");
	    
	svg.selectAll("circle")
	    .data(data)
	    .enter()
	    .append("circle")
	    .attr("class","brill")
	    .attr("cx", function(d){return time_scale( new Date(d.time) )})
	    .attr("cy", function(d){return value_scale(d.total);})
	    .attr("r", 6)
	    .style("fill","red")
	    .style("fill-opacity", 0.8)
	    .style("stroke", "black");
	
	line = d3.svg.line()
	    .x(function(d){return time_scale( new Date(d.time));})
	    .y(function(d){return value_scale(d.total);})
	
	svg.append("svg:path")
	      .attr("class", "line")
	      .attr("d", line(data));
	
	svg.append("svg:g")
	    .attr("class", "xaxis")
	    .attr("transform","translate(0,280)")
	    .call(xAxis);
	
	svg.append("svg:g")
	    .attr("class", "yaxis")
	    .attr("transform", "translate(30,0)")
	    .call(yAxis);
});