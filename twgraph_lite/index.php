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
 * Display the graph.
 *
 * @package report_twgraph_lite
 * @copyright 2025 Travis Wilhelm <https://traviswilhelm.com.au/>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * */

require('../../config.php');
require_login();
require_once($CFG->dirroot . '/report/twgraph_lite/lib.php');
$userid = optional_param('id', $defaultuser, PARAM_INT);

if (!$userid) {
    $userid = $USER->id; // Default to self if no user selected.
}

$pageurl = new moodle_url($CFG->wwwroot . "/report/twgraph_lite/index.php");
$PAGE->set_url($pageurl);
$PAGE->set_context(context_user::instance($userid));
$PAGE->navbar->add("TW GRAPH Lite");
$PAGE->set_pagelayout('standard');
$PAGE->set_heading("TW GRAPH Lite");
$PAGE->set_title($SITE->shortname . ": TW GRAPH Lite");

echo $OUTPUT->header();

if ($USER->id == $userid) {
        $context = context_user::instance($userid);
        require_capability("report/twgraph_lite:viewuserreports", $context);
} else {
        $context = context_user::instance($userid);
        require_capability("report/twgraph_lite:viewotheruserreports", $context);
}

$user = core_user::get_user($userid, '*');
print("<h2>" . get_string('graph_title', 'report_twgraph_lite', ['first' => $user->firstname, 'last' => $user->lastname]) . "</h2>");
if ($courses = enrol_get_users_courses($userid, false, 'id, shortname, showgrades')) {
    $datapoints = [];
    foreach ($courses as $course) {
        $coursecontext = context_course::instance($course->id);
        $courseshortname = format_string($course->shortname, true, ['context' => $coursecontext]);
        $courseitem = grade_item::fetch_all(['courseid' => $course->id]);
        foreach ($courseitem as $ci) {
            $coursegrade = new grade_grade(['itemid' => $ci->id, 'userid' => $userid]);
            if ($ci->itemtype == "mod") { // Mod is individual assignments.
                $finalgrade = $coursegrade->finalgrade;
                $grademax = $ci->grademax;
                if ($finalgrade) {
                    $dp = new data_point_lite();
                    $dp->date = $coursegrade->timemodified;
                    $dp->percent = round(($finalgrade / $grademax) * 100, 1);
                    $dp->course = $course->fullname;
                    $dp->assignment = $ci->get_name();
                    $datapoints[] = $dp;
                }
            }
        }
    }
}

if ($datapoints) {
    report_twgraph_lite_draw_graph($datapoints);
} else {
    print("<p>" . get_string('no_data', 'report_twgraph_lite') . "</p>");
}

echo $OUTPUT->footer();
