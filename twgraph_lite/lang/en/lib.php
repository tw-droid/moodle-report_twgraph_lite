<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * @package report_twgraph_lite
 * @copyright 2025 Travis Wilhelm <https://traviswilhelm.com.au/>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * */
 
class data_point_lite{
	public $date;
	public $percent;
	public $course;
	public $assignment;
}



function report_twgraph_lite_myprofile_navigation(core_user\output\myprofile\tree $tree, $user, $iscurrentuser, $course) {
    if (empty($course)) {
        $course = get_fast_modinfo(SITEID)->get_course();
    }
 
        $url = new moodle_url('/report/twgraph_lite/index.php', array('id' => $user->id));
        $node = new core_user\output\myprofile\node('reports', 'twgraph_lite', get_string('pluginname', 'report_twgraph_lite'), null, $url);
        $tree->add_node($node);
 
}



function draw_graph(array $data) {
  $result = [];
  foreach($data as $key=>$value) {
    $group = $value->course;
    if(!isset($result[$group])) { 
		$result[$group]=[];
		}
    $result[$group][]=$value;
    }
  $result = array_values($result); // remove the top level key
  $dataPoints = array();
  foreach ($result as $y) {
    $point = [];
    foreach($y as $x) {
      if($x->date) {
        $newx['x'] = $x->date; 
		$newx['y'] = $x->percent;
		$newx['course'] = $x->course;
		$newx['assignment'] = addslashes($x->assignment);
		$newx['markerSize'] = 20;
		array_push($point, $newx);
		}
	  }
	array_push($dataPoints, $point);
	}
?>
	<div>
  <canvas id="myChart"></canvas>
</div>
<script src="chartjs/chart.js"></script>
<script src="chartjs/chartjs-adapter-date-fns.bundle.min.js"></script>
<script src="chartjs/hammer.min.js"></script>
<script src="chartjs/chartjs-plugin-zoom.js"></script>
<script>



const ctx = document.getElementById('myChart');


 var mc = new Chart(ctx, {
    type: 'bubble',
    data: {
      datasets: [
	  <?php
	  $sc = 0;
	  foreach($dataPoints as $key)
	  { // each subject
		  if ($sc>0){print(",");}
		  print("\n{ label: \"".$key[0]['course']."\", data: [");
		   // now each assignment
		   $ac = 0;
		   foreach($key as $x=>$y)
		   {
			   //print_r($y);
		if ($ac>0){print(",");}
			//{assignment: 'pythagoras', x: '2024-05-01', y: 19, weight: 31, r: 31}
			print("\n{assignment: \"".$y['assignment']."\", 
			x: '".date("Y-m-d", $y['x'])."', 
			y: ".$y['y']." , 
			r: 10  }");
		   $ac++;
		   }
		  print("],}");
		  $sc++;
	  }

	  ?>

  ]
    },
    options: {
		plugins: {
			zoom:   { zoom: 
			{ mode: 'x', wheel: { enabled: true }}
			,
				pan: { enabled: true, mode: 'x'	}
			},
			legend: {position: 'bottom' },
			title: { display: true, text: 'TWGRAPH'},
	    tooltip: {
		enabled: true,
        callbacks: {
            label: 
			(context) => {  //console.log(context);
var tt = [context.dataset.label];
tt.push(context.raw.x);
if (context.raw.assignment){tt.push(context.raw.assignment);}
if (context.raw.weight){tt.push('Weight: '+context.raw.weight);}

tt.push(context.raw.y + '%');			
return tt;
}
			}
			}
			},
      scales: {
        y: {
          beginAtZero: true
        }
		,x: {
		type: 'time',
          time: {                     displayFormats: {
                        day: 'dd MMM yyyy',
                    }
					}
        }
      }
    }
  }
  

  
  
);
function toggle(){

  mc.data.datasets.forEach(function(ds) {
    ds.hidden = !ds.hidden;
  });
  mc.update();
}
function resetZoomBtn() {
  
  mc.resetZoom()
  
};
</script>
<button id="toggle" onClick="toggle();">toggle all</button>
<button id="toggle" onClick="resetZoomBtn();">reset zoom</button>
<h5>Scroll wheel to zoom, click and drag to pan horizontally</h5>

	<?php
}




?>
