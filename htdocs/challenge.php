<?php

define('IN_FILE', true);
require('../include/general.inc.php');
enforceAuthentication();

verifyValidID($_GET['id']);

head('Challenge details');

$stmt = $db->prepare('SELECT
                        ch.title,
                        ch.description,
                        ca.title AS category_title
                      FROM challenges AS ch
                      LEFT JOIN categories AS ca ON ca.id = ch.category
                      WHERE ch.id=:id
                      ');
$stmt->execute(array('id'=>$_GET['id']));
$submission = $stmt->fetch(PDO::FETCH_ASSOC);

sectionHead($submission['title']);

$stmt = $db->query('SELECT COUNT(*) AS num FROM users');
$user_count = $stmt->fetch(PDO::FETCH_ASSOC);


$stmt = $db->prepare('SELECT
                        u.id AS user_id,
                        u.team_name,
                        s.added,
                        s.pos,
                        c.available_from
                      FROM users AS u
                      LEFT JOIN submissions AS s ON s.user_id = u.id
                      LEFT JOIN challenges AS c ON c.id = s.challenge
                      WHERE s.challenge=:id AND s.correct = 1
                     ');
$stmt->execute(array('id'=>$_GET['id']));

if ($stmt->rowCount()) {

  //sectionHead('Solved by');

  echo 'This challenge has been solved by ',(number_format(($stmt->rowCount() / $user_count['num']) * 100)),'% of users.';

  echo '
  <table class="table table-striped table-hover">
    <thead>
      <tr>
        <th>Position</th>
        <th>Challenge</th>
        <th>Solved</th>
      </tr>
    </thead>
    <tbody>
   ';
  $i = 1;
  while ($submission = $stmt->fetch(PDO::FETCH_ASSOC)) {

      echo '
          <tr>
            <td>',number_format($i),' ',getPositionMedal($submission['pos']),'</td>
            <td><a href="user.php?id=',htmlspecialchars($submission['user_id']),'">',htmlspecialchars($submission['team_name']),'</a></td>
            <td>',getTimeElapsed($submission['added'], $submission['available_from']),' after release, ',getTimeElapsed($submission['added']),' ago (',getDateTime($submission['added']),')</td>
          </tr>
          ';
    $i++;
  }

  echo '
    </tbody>
  </table>
      ';
}

else {
  echo '
  <div class="alert alert-info">
      <i>Unsolved</i>
  </div>
  ';
}

foot();