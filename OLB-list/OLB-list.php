<?php
/*
  Plugin Name: OLB Lightweight List
  Plugin URI:
  Description: Lightweight, scrollable schedule list for OLB System
  Version: 0.9.2
  Author: Naoki FUJIEDA
  Author URI: https://github.com/nfproc/
  License: GPLv2
 */

class olblist {
  public static function enqueueFront () {
    $base = plugin_dir_url(__FILE__);
    wp_enqueue_style('olblist_style', $base.'OLB-list.css');
    wp_enqueue_script('olblist_js', $base.'OLB-list.js');
  }

  private static function getQueryDate () {
    $qdate = date('Y-m-d', current_time('timestamp'));
    if (isset($_SERVER['QUERY_STRING'])) {
      parse_str($_SERVER['QUERY_STRING'], $qs);
      if (isset($qs['date'])) {
        if (preg_match('/^([2-9][0-9]{3})-(0[1-9]{1}|1[0-2]{1})-(0[1-9]{1}|[1-2]{1}[0-9]{1}|3[0-1]{1})$/', $qs['date'])) {
          $qdate = $qs['date'];
        }
      }
    }
    return $qdate;
  }

  private static function getOLBSettings () {
    $options = get_option('olbsystem');
    if (isset($options['settings'])) {
      return $options['settings'];
    }
  }

  private static function getOLBRooms () {
    $args = array(
      'meta_key' => 'olbgroup',
      'meta_value' => 'teacher',
      'meta_compare' => '=',
    );
    $roomlist = get_users($args);
    $rooms = array();
    foreach ($roomlist as $room) {
      $rooms[$room->ID] = array();
      $rooms[$room->ID]['name'] = $room->user_nicename;
      $rooms[$room->ID]['desc'] = get_user_meta($room->ID, 'user_address', true);
    }
    asort($rooms);
    return $rooms;
  }

  private static function getOLBSchedule ($qdate) {
    global $wpdb;
    $query = "select time, room_id from wp_olb_timetable where date = %s";
    $timetable = $wpdb->get_results($wpdb->prepare($query, array($qdate)), ARRAY_A);
    $query = "select time, room_id, user_id from wp_olb_history where date = %s";
    $history = $wpdb->get_results($wpdb->prepare($query, array($qdate)), ARRAY_A);
    $schedule = array();
    foreach ($timetable as $result) {
      if (! isset($schedule[$result['time']])) {
        $schedule[$result['time']] = array();
      }
      $schedule[$result['time']][$result['room_id']] = 0;
    }
    foreach ($history as $result) {
      if (! isset($schedule[$result['time']])) {
        $schedule[$result['time']] = array();
      }
      $schedule[$result['time']][$result['room_id']] = $result['user_id'];
    }
    return $schedule;
  }

  private static function showPageNavi ($qdate) {
    $today = date('Y-m-d', current_time('timestamp'));
    $last  = date('Y-m-d', strtotime('last day of next month', current_time('timestamp')));
    ob_start();
    echo <<<EOD
<div id="olblist_pagenavi" class="olblist_pagenavi">
<input type="button" value="&lt;前日" onclick="olblistPrevDate();">
<select id="olblist_date" class="olblist_date">
EOD;
    $day = $today;
    while (true) {
      if ($day == $qdate) {
        printf('<option selected>%s *</option>', $day);
      } else {
        printf('<option>%s</option>', $day);
      }
      if ($day == $last) {
        break;
      } else {
        $day = date('Y-m-d', strtotime($day . ' +1 day'));
      }
    }
    echo <<< EOD
</select>
<input type="button" value="翌日>" onclick="olblistNextDate();">
<input type="button" value="移動" onclick="olblistTransit();">
</div>
EOD;
    $html = ob_get_contents();
    ob_end_clean();
    return $html;
  }
  
  private static function showScheduleTable ($qdate, $settings, $rooms, $schedule) {
    $user_id = get_current_user_id();
    $width = 80 * (count($rooms) + 1);
    $colors = array('#e8f8f8', '#d0f0f0', '#e8e8f8', '#d0d0f0');
    $statuses = array(
      'close' => ' closed">Close',
      'open'  => ' open"><a rel="nofollow" href="%RSV_URL%">Open</a>',
      'you'   => ' you"><a rel="nofollow" href="%RSV_URL%">You</a>',
      'none'  => '">-');

    ob_start();
    echo '<div style="overflow-x: scroll">';
    printf('<table id="daily_schedule" class="daily_schedule" style="table-layout: fixed; min-width: %dpx">', $width);
    echo '<thead><tr class="head"><th class="olblist_times">サーバ</th>';
    $groupcolor = 0;
    $groupname = '';
    foreach ($rooms as $id => $room) {
      $roomname = $room['name'];
      $roomdesc = ($room['desc'] === "") ? "" : ("<br>(" . $room['desc'] . ")");
      if ($groupname != substr($roomname, 0, 3)) {
        $groupcolor = 1 - $groupcolor;
        $groupname = substr($roomname, 0, 3);
      }
      $color = $groupcolor * 2 + (intval(substr($roomname, -2)) % 2);
      printf('<th style="background-color: %s">', $colors[$color]);
      printf('<a rel="nofollow" href="%s/vm/%s">%s</a>%s</th>', home_url(), $roomname, $roomname, $roomdesc);
    }
    echo '</tr></thead><tbody>';
    $starttime = strtotime($qdate . ' ' . $settings['starttime']);
    $endtime   = strtotime($qdate . ' ' . $settings['endtime']);
    $exptime   = current_time('timestamp') + $settings['reserve_deadline'] * 60;
    for ($tm = $starttime; $tm < $endtime; $tm += $settings['interval'] * 60) {
      $curtime = date('H:i:s', $tm);
      $cur_t   = date('Y-m-d_Hi', $tm);
      $expired = ($tm < $exptime);
      printf('<tr class="%s">', ($expired) ? 'past' : 'valid');
      printf('<th class="olblist_times">%s</th>', date('H:i', $tm));
      foreach ($rooms as $id => $room) {
        if ($expired) {
          $status = 'close';
        } else if (! isset($schedule[$curtime])) {
          $status = 'none';
        } else if (! isset($schedule[$curtime][$id])) {
          $status = 'none';
        } else if ($schedule[$curtime][$id] == 0) {
          $status = 'open';
        } else if ($schedule[$curtime][$id] == $user_id) {
          $status = 'you';
        } else {
          $status = 'close';
        }
        $rsvlink = sprintf('%s/reservation?t=%s&room_id=%d', home_url(), $cur_t, $id);
        $cellstr = sprintf('<td class="status%s</td>', $statuses[$status]);
        echo str_replace('%RSV_URL%', $rsvlink, $cellstr);
      }
      echo '</tr>';
    }
    echo '</tbody></table></div>';

    $html = ob_get_contents();
    ob_end_clean();
    return $html;
  }

  public static function showDaily () {
    $qdate = self::getQueryDate();
    $settings = self::getOLBSettings();
    $rooms = self::getOLBRooms();
    $schedule = self::getOLBSchedule($qdate);

    ob_start();
    echo self::showPageNavi($qdate);
    echo self::showScheduleTable($qdate, $settings, $rooms, $schedule);
    $html = ob_get_contents();
    ob_end_clean();
    return $html;
  }
}

add_action('wp_enqueue_scripts', array('olblist', 'enqueueFront'));
add_shortcode('olblist_daily', array('olblist', 'showDaily'));

?>