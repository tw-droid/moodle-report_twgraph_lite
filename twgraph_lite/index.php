<?php


require('../../config.php');
require_login();
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->dirroot.'/report/twgraph_lite/lib.php');



$user_id = optional_param('id', $defaultuser, PARAM_INT);

$pageurl = new moodle_url($CFG->wwwroot."/report/twgraph_lite/index.php");

$PAGE->set_url($pageurl);
$PAGE->set_context($syscontext);
$PAGE->navbar->add("TW GRAPH Lite");
$PAGE->set_pagelayout('standard');

$PAGE->set_heading("TW GRAPH Lite");
$PAGE->set_title($SITE->shortname.": TW GRAPH Lite");



echo $OUTPUT->header();

if (!$user_id)
{
	$user_id = $USER->id; // default to self if no user selected
}

$user = core_user::get_user($user_id, '*');
print("<h2>".$user->firstname." ".$user->lastname."</h2>");

if ($courses = enrol_get_users_courses($user_id, false, 'id, shortname, showgrades'))
{
    $data_points = array();
    
    foreach ($courses as $course)
    {
        $coursecontext = context_course::instance($course->id);
        $courseshortname = format_string($course->shortname, true, array('context' => $coursecontext));
		$course_item = grade_item::fetch_all(array('courseid' => $course->id));

        foreach($course_item as $ci)
			{

		    $course_grade = new grade_grade(array('itemid'=>$ci->id, 'userid'=>$user_id));
		    if ($ci->itemtype=="mod") // mod or course or category - mod is individual assignments
				{
				    $finalgrade = $course_grade->finalgrade;
				    $grademax = $ci->grademax;
				    if ($finalgrade)
				    {
				    $dp = new data_point_lite();
                    $dp->date = $course_grade->timemodified;
                    $dp->percent = round(($finalgrade/$grademax)*100, 1);
                    $dp->course = $course->fullname;
                    $dp->assignment = $ci->get_name();
                    $data_points[] = $dp;
				    }
				}

            }
    }
    
}

if($data_points)
{
draw_graph($data_points);
}
else
{
    print("<p>No data found</p>");
}

echo $OUTPUT->footer();


?>
