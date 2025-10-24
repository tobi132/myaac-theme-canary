<style>
  #eventscheduletable {
	border-collapse: collapse;
	table-layout: fixed;
	border-spacing: 1px;
	padding: 1px;
	border: 1px;
	background: #d4c0a1;
	border-color: #5f4d41;
	-moz-box-shadow: 2px 2px 3px 3px #7c5231;
	-webkit-box-shadow: 2px 2px 3px 3px #7c5231;
	-ms-box-shadow: 2px 2px 3px 3px #7c5231;
	box-shadow: 2px 2px 3px 3px #7c5231;
  }

  #eventscheduletable td {
	border: 1px solid #faf0d7;
	height: 24px;
	overflow: hidden;
	font-weight: bold;
	color: #fff;
  }

  .eventscheduleheadertop {
	margin: auto;
	width: 100%;
	display: flex;
	min-width: 400px;
  }

  .eventscheduleheaderblockleft {
	margin-left: auto;
	margin-right: auto;
	text-align: center;
	position: relative;
  }

  .eventscheduleheaderdateblock {
	position: absolute;
	width: 150px;
	text-align: center;
  }

  .eventscheduleheaderleft {
	float: left;
  }

  .eventscheduleheaderright {
	float: right;
  }

  .eventscheduleheaderblockright {
	text-align: right;
	white-space: nowrap;
	margin-right: 5px;
  }

  td#default {
	color: #5f4d41;
	background-color: #e7d1af;
  }

  td#today {
	color: #5f4d41;
	background-color: #f3e5d0;
  }

  td#other_day {
	color: #5f4d41;
	background-color: #d4c0a1;
	border: none;
  }

  .day {
	font-weight: bold;
	margin-left: 3px;
	margin-bottom: 2px;
  }

  .activated {
	font-size: 12pt;
	font-weight: bold;
	word-break: break-word;
  }

  .event_name {
	color: #fff;
	width: 100%;
	font-weight: bold;
	overflow: hidden;
	text-overflow: ellipsis;
	white-space: nowrap;
	padding: 1% 1% 1% 3px;
	margin-bottom: 2px;
  }
</style>
<?php
defined('MYAAC') or die('Direct access not allowed!');
$title = 'Event Schedule';

$currentYear = date('Y');
$currentMonth = date('n');

$getYear	= $_GET['year'] ?? $currentYear;
$getMonth	= $_GET['month'] ?? $currentMonth;

$dateObj	= DateTime::createFromFormat('!m', $getMonth);
$monthName	= $dateObj->format('F'); // March

function showWeeks(): string
{
	$out = "";
	$weeks = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
	for ($i = 0; $i < 7; $i++) $out .= "<td>$weeks[$i]</td>";
	return $out;
}

function generateIndicator($event, $currentDay): string
{
	$isStartOrEnd = '';

	$explodeStartDate = explode('/', $event['startdate']);
	$explodeEndDate = explode('/', $event['enddate']);

	if ($currentDay == $explodeStartDate[1] ||
		$currentDay == $explodeEndDate[1]
	) {
		$isStartOrEnd = '*';
	}

	$out = "<span style='width: 120px;' class='HelperDivIndicator'";
	$div = "<div class='activated'>{$event['name']}:</div><div style='margin-bottom: 20px'>&amp;bull; {$event->description['description']}</div>";
	$out .= 'onmouseover="ActivateHelperDiv($(this), &quot;&quot;, &quot;' . $div . '&quot;, &quot;&quot;);"';
	$out .= 'onmouseout="$(&quot;#HelperDivContainer&quot;).hide();">';
	$out .= "<div class='event_name' style='background: {$event->colors['colordark']};'>{$isStartOrEnd}{$event['name']}</div></span>";

	return $out;
}

function showCalendar($month, $year): string
{
	$amountDays = date('t', mktime(0, 0, 0, $month, 1, $year));
	$currentDay = 0;

	$firstDayOfWeek = jddayofweek(cal_to_jd(CAL_GREGORIAN, $month, "01", $year), 0) - 1;

	$outDays = "<tr style='text-align:center; width:120px; background-color:#5f4d41;'>" . showWeeks() . "</tr>";

	$events_xml = config('data_path') . 'XML/events.xml';
	if (file_exists($events_xml)) {
		$xml = simplexml_load_file($events_xml);

		$events = [];
		foreach ($xml->event as $event) {
			$events[] = $event;
		}

		function compareEvent($obj1, $obj2): int {
			return ((int)$obj1->details['displaypriority'] <=> (int)$obj2->details['displaypriority']);
		}

		usort($events, 'compareEvent');
	}

	for ($row = 0; $row < 5; $row++) {
		$outDays .= "<tr>";
		for ($column = 0; $column < 7; $column++) {
			$outDays .= "<td style='height:82px; background-clip: padding-box; overflow: hidden; vertical-align:top;' ";
			$color = "other_day";
			if ($currentDay == (date('d') - 1) && date('m') == $month) {
				$color = "today";
			} else {
				if (($currentDay + 1) <= $amountDays) {
					$color = ($column < $firstDayOfWeek && $row == 0) ? "other_day" : "default";
				}
			}

			$outDays .= "id='$color'>";

			if ($currentDay + 1 <= $amountDays) {
				if ($column < $firstDayOfWeek && $row == 0) {
					$outDays .= " ";
				} else {
					$outDays .= "<div class='day'><span style='vertical-align: text-bottom;'>" . ++$currentDay . " </span></div>";

					if (isset($events)) {
						$current_date = "$month/$currentDay/$year";

						foreach ($events as $event) {
							$start_date = strtotime($event['startdate']);
							$end_date = strtotime($event['enddate']);
							$current_date_time = strtotime($current_date);

							if ($current_date_time >= $start_date && $current_date_time <= $end_date) {
								$outDays .= generateIndicator($event, $currentDay);
							}
						}
					}
				}
			} else {
				break;
			}
			$outDays .= "</td>";
		}
		$outDays .= "</tr>";
	}

	return $outDays;
}
?>

<div class="BoxContent" style="background-image:url(https://static.tibia.com/images/global/content/scroll.gif);">
	<div id="eventscheduletablecontainer">
		<div class="TableContainer">
			<div class="CaptionContainer">
				<div class="CaptionInnerContainer">
					<span class="CaptionEdgeLeftTop" style="background-image:url(https://static.tibia.com/images/global/content/box-frame-edge.gif);"></span>
					<span class="CaptionEdgeRightTop" style="background-image:url(https://static.tibia.com/images/global/content/box-frame-edge.gif);"></span>
					<span class="CaptionBorderTop" style="background-image:url(https://static.tibia.com/images/global/content/table-headline-border.gif);"></span>
					<span class="CaptionVerticalLeft" style="background-image:url(https://static.tibia.com/images/global/content/box-frame-vertical.gif);"></span>

					<div class="Text">
						<div class="eventscheduleheadertop">
							<div class="eventscheduleheaderblockleft">
								<div class="eventscheduleheaderdateblock">
									<span class="eventscheduleheaderleft">
										<?php

										$year = $getYear;
										$month = $getMonth - 1;

										if ( $getMonth == 1 ) {
											$year = $getYear - 1;
											$month = 12;
										}

										if ($getMonth > $currentMonth || $getYear > $currentYear) {
											echo '<a href="' . getLink('event-schedule') . '?year=' . $year .
										'&month=' . $month . '" style = "color:white;" > «</a>';
										}

										?>
									</span>
									<?= $monthName . ' ' . $getYear; ?>
									<span class="eventscheduleheaderright">

										<?php

										$year = $getYear;
										$month = $getMonth + 1;

										if ( $getMonth == 12 ) {
											$year = $getYear + 1;
											$month = 1;
										}

										echo '<a href="' . getLink('event-schedule') . '?year=' . $year . '&month=' . $month . '" style = "color:white;" > »</a>';

										?>
									</span>
								</div>
							</div>
							<div class="eventscheduleheaderblockright"><?= date('Y-m-d H:i') ?></div>
						</div>
					</div>

					<span class="CaptionVerticalRight" style="background-image:url(https://static.tibia.com/images/global/content/box-frame-vertical.gif);"></span>
					<span class="CaptionBorderBottom" style="background-image:url(https://static.tibia.com/images/global/content/table-headline-border.gif);"></span>
					<span class="CaptionEdgeLeftBottom" style="background-image:url(https://static.tibia.com/images/global/content/box-frame-edge.gif);"></span>
					<span class="CaptionEdgeRightBottom" style="background-image:url(https://static.tibia.com/images/global/content/box-frame-edge.gif);"></span>
				</div>
			</div>
			<table class="Table1" cellpadding="0" cellspacing="0" style="background-color: rgb(241, 224, 197);">
				<tbody>
				<tr>
					<td>
						<div class="InnerTableContainer" style="padding: 10px;">
							<table style="width:100%;" id="eventscheduletable">
								<tbody>
								<?= showCalendar($getMonth, $getYear) ?>
								</tbody>
							</table>
						</div>
					</td>
				</tr>
				</tbody>
			</table>
		</div>
	</div>
	<br>
	<div>* Event starts/ends at server save of this day.</div>
</div>
