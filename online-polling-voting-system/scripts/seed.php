<?php
declare(strict_types=1);
if(PHP_SAPI!=='cli') { http_response_code(404); exit; }
date_default_timezone_set('Asia/Kolkata');
$c=require (is_file(__DIR__.'/../app/config.php')?__DIR__.'/../app/config.php':__DIR__.'/../app/config.example.php');
$db=new PDO("mysql:host={$c['host']};port={$c['port']};dbname={$c['database']};charset=utf8mb4",$c['username'],$c['password'],[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
$db->exec("SET time_zone = '+05:30'");
$admin=$db->query("SELECT id FROM users WHERE role='admin' ORDER BY id LIMIT 1")->fetchColumn();
if(!$admin) { echo "Create an admin first: php scripts/create-admin.php\n"; exit(1); }
if($db->query('SELECT COUNT(*) FROM polls')->fetchColumn()>0) { echo "Polls already exist; sample data skipped.\n"; exit; }
$examples=[
 ['What should our next campus event be?','Let’s plan something worth looking forward to. Pick the event you’d love to attend.','Events','-1 day','+7 days',['Cultural evening','Sports tournament','Tech showcase','Open mic night']],
 ['Which skill would you like to learn next?','Help us choose the topic for our next hands-on student workshop.','Academics','-1 day','+5 days',['Web development','UI/UX design','Data science','Cybersecurity']],
 ['Give our reading spaces a refresh','What would make the biggest difference to your library experience?','Campus','-1 day','+10 days',['More charging points','Quiet study zones','Longer opening hours']],
 ['Choose our next club collaboration','A new collaboration is on the horizon. Voting opens tomorrow.','Clubs','+1 day','+8 days',['Photography walk','Community clean-up','Student podcast']],
 ['How should we celebrate community week?','This example poll has closed. Create a new poll to start collecting real votes.','Events','-10 days','-2 days',['Music and arts','Team games','Volunteer day']],
 ['What matters most in a student portal?','Tell us which feature you would use most often.','General','-1 day','+12 days',['Event calendar','Study resources','Club updates','Peer mentoring']]
];
$db->beginTransaction();
foreach($examples as [$title,$description,$category,$start,$end,$options]) {
 $s=$db->prepare('INSERT INTO polls (title,description,category,starts_at,ends_at,created_by) VALUES (?,?,?,?,?,?)');
 $s->execute([$title,$description,$category,date('Y-m-d H:i:s',strtotime($start)),date('Y-m-d H:i:s',strtotime($end)),$admin]);
 $id=$db->lastInsertId(); $s=$db->prepare('INSERT INTO poll_options (poll_id,label) VALUES (?,?)');
 foreach($options as $o) $s->execute([$id,$o]);
}
$db->commit(); echo "Six example polls created. No fabricated votes or member accounts were added.\n";
