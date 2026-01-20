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
 * Library file.
 *
 * @package report_twgraph_lite
 * @copyright 2025 Travis Wilhelm <https://traviswilhelm.com.au/>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * */

/**
 * Data Points for graphing
 *
 *
 * @package    report_twgraph_lite
 * @copyright  2025 Travis Wilhelm <https://traviswilhelm.com.au/>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class data_point_lite {
    /**
     * @var int $date The date the assignment was marked as timestamp.
     */
    public $date;
    /**
     * @var int $percent The mark as a percentage.
     */
    public $percent;
    /**
     * @var string $course The course name.
     */
    public $course;
    /**
     * @var string $assignment The assignment name.
     */
    public $assignment;
}

/**
 * Navigation on profile
 *
 *
 * @package    report_twgraph_lite
 * @copyright  2025 Travis Wilhelm <https://traviswilhelm.com.au/>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
function report_twgraph_lite_myprofile_navigation(core_user\output\myprofile\tree $tree, $user, $iscurrentuser, $course) {
    if (empty($course)) {
        $course = get_fast_modinfo(SITEID)->get_course();
    }
        $url = new moodle_url('/report/twgraph_lite/index.php', ['id' => $user->id]);
        $node = new core_user\output\myprofile\node('reports', 'twgraph_lite', get_string('pluginname', 'report_twgraph_lite'), null, $url);
        $tree->add_node($node);
}

/**
 * Function to draw graph from data passed.
 * Uses Chart.js library
 * @param array $data - an array of data_point_lite objects
 */
function report_twgraph_lite_draw_graph(array $data) {
    global $OUTPUT;
    $result = [];
    foreach ($data as $key => $value) {
        $group = $value->course;
        if (!isset($result[$group])) {
            $result[$group] = [];
        }
        $result[$group][] = $value;
    }
    $result = array_values($result); // Remove the top level key.
    $datapoints = [];
    foreach ($result as $y) {
        $point = [];
        foreach ($y as $x) {
            if ($x->date) {
                $newx['x'] = $x->date;
                $newx['y'] = $x->percent;
                $newx['course'] = $x->course;
                $newx['assignment'] = addslashes($x->assignment);
                $newx['markerSize'] = 20;
                array_push($point, $newx);
            }
        }
        array_push($datapoints, $point);
    }

    $graphdata = ['graphs' => []];
    $dotsize = get_config('reports_twgraph_lite', 'dotsize');
    foreach ($datapoints as $key) { // Each subject.
        $tmp = [];
        foreach ($key as $x => $y) {
            $tmp[] = ['assignment' => $y['assignment'], 'x' => date("Y-m-d", $y['x']), 'y' => $y['y'], 'r' => $dotsize ];
        }
    array_push ($graphdata['graphs'], ['label' => $key[0]['course'],
                'type' => 'bubble',
                'data' => $tmp,
                ]);
    }
    echo $OUTPUT->render_from_template('report_twgraph_lite/graph', $graphdata);
}
