
<html>
<head>

 	<meta charset="utf-8" />
    <meta name="format-detection" content="telephone=no" />
    <meta name="msapplication-tap-highlight" content="no" />
    <meta name="viewport" content="user-scalable=no, initial-scale=1, maximum-scale=1, minimum-scale=1, width=device-width" />
    <meta http-equiv="Content-Security-Policy" content="default-src * 'unsafe-inline'; style-src 'self' 'unsafe-inline'; media-src *" />
    <link type="text/css" rel="stylesheet" href="css/style.css"/>
    <link rel="stylesheet" type="text/css" href="css/index.css" />

    <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
    <script src="https://code.highcharts.com/highcharts.js"></script>
    <script src="https://code.highcharts.com/modules/exporting.js"></script>

    <style type="text/css">

                text {
                  font-size: 1em;
                  pointer-events: none;
                }

                text.parent {

                font-weight: bold;
                color: rgba(0,0,0,0.6);
                text-shadow: 2px 8px 6px rgba(0,0,0,0.6),
                                 0px -5px 35px rgba(255,255,255,0.6);
                }

                circle {
                  fill: #ccc;
                  stroke: #999;
                  pointer-events: all;
                }

                circle.parent {
                  fill: #1f77b4;
                  fill-opacity: .1;
                  stroke: steelblue;
                  
                }

                circle.parent:hover {
                  stroke: #ff7f0e;
                  stroke-width: 1.5px;
                }

                circle.child {
                  pointer-events: none;
                }

                #Likes {
                  fill: #98C4D9 ;  
                }
                #Comments {
                  fill: #9a5c5b ;
                }
                #Shares {
                  fill: #b4d5ac ;
                }
                #par {
                  fill: #4c789a;
                }

                #quintly_title{
                  position: absolute; 
                  top: 60px; left: 40px;
                 }
                #db_table {
                   position: absolute; 
                  top: 250px; left: 40px;
                } 
                
                table, th, td {
                  font-size: 11px;  
    border: 1px solid black;
}

    </style>



    <title> Quintly Visualization!</title>





</head>


<body>
<?php

// facebook class file
include_once("facebook.php");

// create object of facebook
$page = new Facebook();

//$access_token = "EAACEdEose0cBAGzE93Tp5ssimRVfWXLewfafayFcNv25eZAqzAiiQfwNUx5fzXxwQMyAGZCaRaCevQT1t0REvVdT1uPjuJv2B2kHyKKDPtXEToEQKTjQLlh84J8NiZBgXRqSvNPY8oaXGhjBmyRnwgMFpjv4xagsM1xnVUpoN43wmO2VqZASMZAJLuyOobIgZD";

$access_token = $_GET['accesstoken'];
$Page_id      = $_GET['page_id'];


// aggregate data
$page ->aggregate_data($Page_id,$access_token);


?>



<script src="http://d3js.org/d3.v3.min.js"></script>
    
    <script type="text/javascript">

var w = window.innerWidth,
    h = window.innerHeight,
    r = (window.innerWidth)/2,
    x = d3.scale.linear().range([0, r]),
    y = d3.scale.linear().range([0, r]),
    node,
    root;



var pack = d3.layout.pack()
    .size([window.innerWidth, window.innerHeight])
    .value(function(d) { return d.size; })

var vis = d3.select("body").insert("svg:svg", "h2")
    .attr("width", w)
    .attr("height", h)
    .append("svg:g")
    .attr("transform", "translate(" + (w - r) / 2 + "," + (h - r) / 2 + ")");
d3.json("data/data.json", function(data) {
  node = root = data;

  var nodes = pack.nodes(root);
 
  vis.selectAll("circle")
      .data(nodes)
      .enter().append("svg:circle")
      .attr("class", function(d) { return d.children ? "parent" : "child"; })
      .attr("id", function(d) { 
         switch(d.name) {
            case "Likes":
                return "Likes";
                break;
            case "Comments":
                return "Comments";
                break;
            case "Shares":
                return "Shares";
                break;
            default:
                return "par"; }
        })
      .attr("cx", function(d) { return d.x; })
      .attr("cy", function(d) { return d.y; })
      .attr("r", function(d) { return d.r; })
      .on("click", function(d) { return zoom(node == d ? root : d); });

  vis.selectAll("text")
      .data(nodes)
      .enter().append("svg:text")
      .attr("class", function(d) { return d.children ? "parent" : "child"; })
      .attr("x", function(d) { return d.x; })
      .attr("y", function(d) { return d.children ? d.y-(d.r)/1.1 : d.y; })
      .attr("dy", ".35em")
      .attr("text-anchor", "middle")
      .style("opacity", function(d) { return d.r > 20 ? 1 : 0; })
      .text(function(d) {return d.children ? d.name : d.name +'('+ d.size + ')'; });
      d3.select(window).on("load", function() { zoom(root); }); 
      d3.select(window).on("click", function() { zoom(root); });

});

  
function zoom(d, i) {
  var k = r / d.r / 2;
  x.domain([d.x - d.r, d.x + d.r]);
  y.domain([d.y - d.r, d.y + d.r]);

  var t = vis.transition()
      .duration(d3.event.altKey ? 7500 : 750);

  t.selectAll("circle")
      .attr("cx", function(d) { return x(d.x); })
      .attr("cy", function(d) { return y(d.y); })
      .attr("r", function(d) { return k * d.r; });

  t.selectAll("text")
      .attr("x", function(d) { return x(d.x);})
      .attr("y", function(d) { return d.children ? y(d.y-(d.r)/1.1) : y(d.y); })
      .style("opacity", function(d) { return k * d.r > 20 ? 1 : 0; });

  node = d;
  d3.event.stopPropagation();
}

    </script>




<div id = "quintly_title">
      <img src="img/quintly_logo.jpg"></img><br>
      <h3>Facebook post Visualization</h3></br>
      <br/>

    </div>
    <div id="db_table" >
    <?php // generate json file
    $page -> show_post_details();?>
  </div>





<!-- <div id="container" style="min-width: 310px; height: 400px; margin: 0 auto"></div>
-->



</body>
</html
