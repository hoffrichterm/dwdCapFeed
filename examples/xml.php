<?php
require('../conf/config.php');

new horizonLoader();


horizonMySQL::getInstance(array(
	'user' => DBUSER,
	'password' => DBPASSWORD,
	'host' => DBHOST,
	'database' => DATABASE,
	'options' => array(
		PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
		PDO::ATTR_PERSISTENT => true,
		PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
	)
));

$sql = '';

$sql .= 'SELECT ';
$sql .= 'd.cap_id, ';
$sql .= 'd.guid, ';
$sql .= 'd.sent, ';
$sql .= 'd.effective, ';
$sql .= 'd.onset, ';
$sql .= 'd.cap_data_iicode, ';
$sql .= 'd.expires, ';
$sql .= 'd.cap_data_iicode, ';
$sql .= 'dse.senderName, ';
$sql .= 'dse.web, ';
$sql .= 'dse.contact, ';
$sql .= 'dse.email, ';
$sql .= 'dm.cap_msgType_type_name, ';
$sql .= 'dr.cap_responseType_type_name, ';
$sql .= 'da.code, ';
$sql .= 'da.altitude, ';
$sql .= 'da.ceiling, ';
$sql .= 'da.name as areaDescription, ';
$sql .= 'ts.cap_severity_type_name, ';
$sql .= 'tc.cap_category_type_name, ';
$sql .= 'di.headline, ';
$sql .= 'di.instruction, ';
$sql .= 'di.description, ';
$sql .= 'di.language, ';
$sql .= 'tu.cap_urgency_type_name, ';
$sql .= 'tce.cap_certainty_type_name, ';
$sql .= 'dg.name AS groupName, ';
$sql .= 'dg.description AS groupDescription, ';
$sql .= 'dp.id AS paramId, ';
$sql .= 'dp.value AS paramValue, ';
$sql .= 'dp.name AS paramName, ';
$sql .= 'dp.unit AS paramUnit ';

$sql .= 'FROM cap_data AS d ';
$sql .= 'INNER JOIN view_warncells AS da ON d.cap_id = da.id AND d.status = 1 AND d.scope = 1 AND d.published = 1 AND d.expires > NOW() ';
$sql .= 'INNER JOIN cap_types_msgType AS dm ON d.msg_type = dm.cap_msgType_type_id ';
$sql .= 'INNER JOIN cap_types_responseType AS dr ON d.responseType = dr.cap_responseType_type_id ';
$sql .= 'INNER JOIN cap_types_certainty AS tce ON d.certainty_id = tce.cap_certainty_type_id ';
$sql .= 'INNER JOIN cap_data_sender AS dse ON d.sender_id = dse.cap_sender_id ';
$sql .= 'INNER JOIN cap_data_info AS di ON d.cap_id = di.cap_id ';
$sql .= 'INNER JOIN cap_types_severity AS ts ON ts.cap_severity_type_id = d.severity_type ';
$sql .= 'INNER JOIN cap_types_urgency AS tu ON tu.cap_urgency_type_id = d.urgency ';
$sql .= 'INNER JOIN cap_types_category AS tc ON tc.cap_category_type_id = d.category ';
$sql .= 'LEFT JOIN view_groups AS dg ON dg.id = d.cap_id ';
$sql .= 'LEFT JOIN view_parameters AS dp ON dp.cap_id = d.cap_id ';

if (horizonMySQL::query($sql)){
	while($result = horizonMySQL::fetch(PDO::FETCH_ASSOC)){
		$severity = $result['cap_severity_type_name'];
		$urgency = $result['cap_urgency_type_name'];
		$capid = $result['cap_id'];
		$guid = $result['guid'];

		$sent = strtotime($result['sent']);
		$start = strtotime($result['onset']);
		$end = strtotime($result['expires']);
		$now = time();
		$tomorrow = time() + 3600 * 24;
		$yesterday = time() - 3600 * 24;
		$sentstr = '';
		$datestr = '';
		if (date('Y-m-d',$start) == date('Y-m-d',$end)){
			if (date('Y-m-d',$start) == date('Y-m-d',$now)){
				$datestr = 'heute, '.date('H:i',$start)." bis ".date('H:i',$end).' Uhr';
			} elseif (date('Y-m-d',$start) == date('Y-m-d',$tomorrow)) {
				$datestr = 'morgen, '.date('H:i',$start)." bis ".date('H:i',$end).' Uhr';
			} else {
				$datestr = date('d.m.Y',$start).', '.date('H:i',$start)." bis ".date('H:i',$end).' Uhr';
			}
		} else {
			if (date('Y-m-d',$start) == date('Y-m-d',$now)){
				$datestr = 'heute, '.date('H:i',$start);
			} elseif (date('Y-m-d',$start) == date('Y-m-d',$yesterday)) {
				$datestr = 'gestern, '.date('H:i',$start);
			} elseif (date('Y-m-d',$start) == date('Y-m-d',$tomorrow)) {
				$datestr = 'morgen, '.date('H:i',$start);
			} else {
				$datestr = date('d.m.Y',$start).', '.date('H:i',$start);
			}
			$datestr .= ' bis ';
			if (date('Y-m-d',$end) == date('Y-m-d',$now)){
				$datestr .= 'heute, '.date('H:i',$end).' Uhr';
			} elseif (date('Y-m-d',$end) == date('Y-m-d',$tomorrow)) {
				$datestr .= 'morgen, '.date('H:i',$end).' Uhr';
			} else {
				$datestr .= date('d.m.Y',$end).', '.date('H:i',$end).' Uhr';
			}
		}
		if (date('Y-m-d',$sent) == date('Y-m-d',$now)){
			$sentstr = 'heute, '.date('H:i',$sent).' Uhr';
		} elseif (date('Y-m-d',$sent) == date('Y-m-d',$yesterday)) {
			$sentstr = 'gestern, '.date('H:i',$sent).' Uhr';
		} else {
			$sentstr = date('d.m.Y',$sent).', '.date('H:i',$sent).' Uhr';
		}



		if (!isset($res[$capid])){
			$res[$capid] = array(
				'severity' => $severity,
				'sent' => date('c',$sent),
				'effective' => date('c',strtotime($result['effective'])),
				'expires' => date('c',$end),
				'onset' => date('c',$start),
				'guid' => $result['guid'],
				'msgType' => $result['cap_msgType_type_name'],
				'responseType' => $result['cap_responseType_type_name'],
				'category' => $result['cap_category_type_name'],
				'certainty' => $result['cap_certainty_type_name'],
				'urgency' => $urgency,
				'iicode' => $result['cap_data_iicode'],
				'senderName' => $result['senderName'],
				'web' => $result['web'],
				'contact' => $result['contact'],
				'email' => $result['email'],
				'datestr' => $datestr,
				'sentstr' => $sentstr
				
			);
		}
		
		if (!isset($res[$capid]['text'])){
			$res[$capid]['text'] = array();
		}
		if (!isset($res[$capid]['text'][$result['language']])){
			$res[$capid]['text'][$result['language']] = array(
				'headline' => $result['headline'],
				'description' => $result['description'],
				'instruction' => $result['instruction']
			);
		}
		if (!isset($res[$capid]['warncells'])){
			$res[$capid]['warncells'] = array();
		}
		$code = $result['code'];
		if (!isset($res[$capid]['warncells'][$code])){
			$res[$capid]['warncells'][$code] = array(
				'altitude' => $result['altitude'],
				'ceiling' => $result['ceiling'],
				'description' => $result['areaDescription']
			);
		}

		if (!isset($res[$capid]['sender'])){
			$res[$capid]['sender'] = array(
				'name' => $result['senderName'],
				'web' => $result['web'],
				'contact' => $result['contact'],
				'email' => $result['email']
			);
		}
		if ($result['paramValue'] != '' && $result['paramValue'] != null){
			if (!isset($res[$capid]['parameters'])){
				$res[$capid]['parameters'] = array();
			}
			$tmp = array();
			$tmp['name'] = $result['paramName'];
			$tmp['value'] = $result['paramValue'];
			$tmp['unit'] = $result['paramUnit'];
			$res[$capid]['parameters'][$result['paramId']] = $tmp;
		}
		if ($result['groupName'] != '' && $result['groupName'] != null){
			$res[$capid]['groups'][$result['groupName']] = $result['groupDescription'];
		}
	}
}

header('Content-Type:text/xml');
echo '<?xml version="1.0" encoding="utf-8"?'.'>'."\n";
echo '<messages xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="https://api.marc-hoffrichter.de/xsd/dwdwarnings.xsd">'."\n";
foreach($res as $capid => $value){
	echo '	<message ';
	echo ' id="'.$capid.'"';
	echo ' guid="'.$value['guid'].'"';
	echo ' msgType="'.$value['msgType'].'"';
	echo ' responseType="'.$value['responseType'].'"';
	echo ' severity="'.$value['severity'].'"';
	echo ' category="'.$value['category'].'"';
	echo ' certainty="'.$value['certainty'].'"';
	echo ' urgency="'.$value['urgency'].'"';
	echo ' iicode="'.$value['iicode'].'"';
	echo '>'."\n";
	echo '		<dates>'."\n";
	echo '			<sent>'.$value['sent'].'</sent>'."\n";
	echo '			<effective>'.$value['effective'].'</effective>'."\n";
	echo '			<expires>'.$value['expires'].'</expires>'."\n";
	echo '			<onset>'.$value['onset'].'</onset>'."\n";
	echo '		</dates>'."\n";
	echo '		<sender>'."\n";
	echo '			<name><![CDATA['.$value['senderName'].']]></name>'."\n";
	echo '			<email><![CDATA['.$value['email'].']]></email>'."\n";
	echo '			<web><![CDATA['.$value['web'].']]></web>'."\n";
	echo '			<contact><![CDATA['.$value['contact'].']]></contact>'."\n";
	echo '		</sender>'."\n";
	echo '		<messageBody>'."\n";
	foreach($value['text'] as $lang => $innervalue){
		echo '			<text lang="'.$lang.'">'."\n";
		echo '				<headline><![CDATA['.$innervalue['headline'].']]></headline>'."\n";
		echo '				<description><![CDATA['.$innervalue['description'].']]></description>'."\n";
		if ($innervalue['instruction'] != null){
			echo '				<instruction><![CDATA['.$innervalue['instruction'].']]></instruction>'."\n";
		}
		echo '			</text>'."\n";
	}
	echo '		</messageBody>'."\n";
	if (isset($value['warncells']) && is_array($value['warncells'])){
		echo '		<warncells>'."\n";
		foreach($value['warncells'] as $warncell => $innervalue){
			echo '			<warncell';
			echo ' id="'.$warncell.'"';
			if ($innervalue['altitude'] != null){
				echo ' altitude="'.$innervalue['altitude'].'"';
			}
			if ($innervalue['ceiling'] != null){
				echo ' ceiling="'.round($innervalue['ceiling']/3.28).'"';
			}
			echo '>'."\n";
			echo '				<name><![CDATA['.$innervalue['description'].']]></name>'."\n";
			echo '			</warncell>'."\n";
		}
		echo '		</warncells>'."\n";
	} else {
		echo '		<warncells/>'."\n";
	}
	if (isset($value['groups']) && is_array($value['groups'])){
		echo '		<groups>'."\n";
		foreach($value['groups'] as $key => $description){
			echo '			<group type="'.$key.'"><![CDATA['.$description.']]></group>'."\n";
		}
		echo '		</groups>'."\n";
	}
	if (isset($value['parameters']) && is_array($value['parameters'])){
		echo '		<parameters>'."\n";
		foreach($value['parameters'] as $paramId => $innervalue){
			echo '			<parameter>'."\n";
			echo '				<name><![CDATA['.$innervalue['name'].']]></name>'."\n";
			echo '				<value><![CDATA['.$innervalue['value'].']]></value>'."\n";
			echo '				<unit><![CDATA['.$innervalue['unit'].']]></unit>'."\n";
			echo '			</parameter>'."\n";
		}
		echo '		</parameters>'."\n";
	}
	echo '	</message>'."\n";
}
echo '</messages>'."\n";
?>