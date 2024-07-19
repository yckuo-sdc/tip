<?php
if (empty($_GET['chartID'])) {
	return 0;
}

$chartID = $_GET['chartID'];

switch($chartID){
	case "enews":
        $date_today = date('Y-m-d',strtotime('now'));
		$date_3month_ago = date('Y-m-d',strtotime('-3 month'));
		$sql =" SELECT OccurrenceTime,COUNT(OccurrenceTime) as count 
				FROM security_event 
				WHERE OccurrenceTime BETWEEN '".$date_3month_ago."' AND '".$date_today."'
				GROUP BY OccurrenceTime ORDER by OccurrenceTime asc";
		$events = $db->execute($sql);
		$Occurrence = array();
		foreach($events as $event) {
			$sql =" SELECT COUNT(OccurrenceTime) as count 
				FROM security_event 
				WHERE OccurrenceTime LIKE '".$event['OccurrenceTime']."' AND Status LIKE '已結案' ";
			$time = date('Y-m-d',strtotime($event['OccurrenceTime']));
			$count = $event['count'];
			$donecount = $db->execute($sql)[0]['count'];
			$Occurrence[] = ['time' => $time, 'count' => $event['count'], 'donecount' => $donecount];
		}
		echo json_encode($Occurrence, JSON_UNESCAPED_UNICODE);
		break;
	case "ranking":
		$NowYear = date("Y");
		$LastYear = $NowYear-1;
		$LastYearsql = "SELECT MONTH(OccurrenceTime) as month,COUNT(*) as count FROM security_event WHERE YEAR(OccurrenceTime) LIKE '".$LastYear."' GROUP BY MONTH(OccurrenceTime)";
		$ThisYearsql = "SELECT MONTH(OccurrenceTime) as month,COUNT(*) as count FROM security_event WHERE YEAR(OccurrenceTime) LIKE '".$NowYear."' GROUP BY MONTH(OccurrenceTime)";
		$LastYearEvent = $db->execute($LastYearsql);
		$ThisYearEvent = $db->execute($ThisYearsql);
		$sql = "SELECT EventTypeName as name,COUNT(EventTypeName) as count FROM security_event GROUP BY EventTypeName ORDER by count desc";
		$EventType = $db->execute($sql);
		$sql = "SELECT AgencyName,COUNT(AgencyName) as count FROM security_event WHERE NOT AgencyName LIKE '' GROUP BY AgencyName ORDER by count desc LIMIT 10";
		$Agencys = $db->execute($sql);
		$AgencyName = array();
		foreach( $Agencys as $Agency) {
			$sql = "SELECT distinct IP FROM security_event WHERE AgencyName ='".$Agency['AgencyName']."'";
			$db->execute($sql);
			$IP_count = $db->getLastNumRows();
			$name = explode("_", $Agency['AgencyName']);
			$AgencyName[] = ['name' => $name[1], 'count' => $Agency['count'], 'IP_count' => $IP_count];
		}
		$sql = "SELECT IP as name,COUNT(IP) as count FROM security_event WHERE IP NOT LIKE '' GROUP BY IP ORDER by count desc LIMIT 10";
		$DestIP = $db->execute($sql);
		$res = ['LastYearEvent' => $LastYearEvent, 'ThisYearEvent' => $ThisYearEvent, 'EventType' => $EventType, 'AgencyName' => $AgencyName, 'DestIP' => $DestIP ];
		echo json_encode($res, JSON_UNESCAPED_UNICODE);
		break;
	case "client":
		$sql = "SELECT DetectorName as name,COUNT(DetectorName) as count FROM drip_client_list GROUP BY DetectorName ORDER by count desc";
		$DrIP = $db->execute($sql);
		$sql = "SELECT DetectorName as name,COUNT(DetectorName) as count FROM drip_client_list WHERE type LIKE 'computer' GROUP BY DetectorName ORDER by count desc";
		$DrIPComputers = $db->execute($sql);
		$sql = "SELECT COUNT(ID) as total_count,SUM(CASE WHEN GsAll_2 = GsAll_1 THEN 1 ELSE 0 END) as pass_count FROM gcb_client_list";
		$GCBPass = $db->execute($sql);
		$sql = "SELECT b.name as name, COUNT(b.name) as count FROM gcb_client_list as a LEFT JOIN gcb_os as b ON a.OSEnvID = b.id WHERE b.name IS NOT NULL GROUP BY b.name ORDER by count desc";
		$OSEnv = $db->execute($sql);
		$res = ['DrIP' => $DrIP, 'DrIPComputers' => $DrIPComputers, 'GCBPass' => $GCBPass, 'OSEnv' => $OSEnv];
		echo json_encode($res, JSON_UNESCAPED_UNICODE);
		break;
	case "network":
		$pa = new PaloAltoAPI();
		$report_map = ['top-attacks', 'top-denied-applications'];
		echo "<h3></h3>"; 
		echo "<?xml version=\"1.0\"?>";
		echo "<data>";
		foreach($report_map as $report_name){	
			$data = $pa->getSyncReport($report_type = 'predefined', $report_name);
            $max_count = 10;
            $count = 0;
            foreach($data['logs'] as $log){
                if($count >= $max_count){
                    break;
                }
				echo "<".$report_name.">";
                    foreach($log as $key => $val){
						echo "<".$key.">".$val."</".$key.">";
                    }
				echo "</".$report_name.">";
                $count = $count + 1;
            }
		}
		echo "</data>";
		break;
	case "app":
		$pa = new PaloAltoAPI();
		$report_map = ['top-applications'];
		echo "<h3></h3>"; 
		echo "<?xml version=\"1.0\"?>";
		echo "<data>";
		foreach($report_map as $report_name){	
			$data = $pa->getSyncReport($report_type = 'predefined', $report_name);
            foreach($data['logs'] as $log){
				echo "<".$report_name.">";
                    foreach($log as $key => $val){
						echo "<".$key.">".$val."</".$key.">";
                    }
				echo "</".$report_name.">";
            }
		}
		echo "</data>";
		break;
	case "nics":
        $data_array = array();

        $params = [
            'index' => 'gsn_asset*',
            'body' => [
                'size' => 0,
                'aggs' => [
                    'ports' => [
                        'terms' => [
                            'field' => 'Product.keyword', // Assuming 'Port' is a keyword field
                            'size' => 10 // Number of ports to return
                        ]
                    ]
                ]
            ]
        ];

        $response = $es_client->search($params);
        $aggregations = $response['aggregations']['ports']['buckets'];

        $count_array = array();

        foreach ($aggregations as $bucket) {
            $item = array(
                'name' => $bucket['key'],
                'count' => $bucket['doc_count']
            );
            $count_array[] = $item;
        }


		$data_array['topProduct'] = $count_array;

        $params = [
            'index' => 'gsn_asset*',
            'body' => [
                'size' => 0,
                'aggs' => [
                    'ports' => [
                        'terms' => [
                            'field' => 'Port', 
                            'size' => 10
                        ]
                    ]
                ]
            ]
        ];

        $response = $es_client->search($params);
        $aggregations = $response['aggregations']['ports']['buckets'];

        $count_array = array();

        foreach ($aggregations as $bucket) {
            $item = array(
                'name' => $bucket['key'],
                'count' => $bucket['doc_count']
            );
            $count_array[] = $item;
        }

		$data_array['topPort'] = $count_array;

        $params = [
            'index' => 'gsn_asset*',
            'body' => [
                'size' => 0,
                'aggs' => [
                    'classes' => [
                        'terms' => [
                            'field' => 'Class.keyword', // Assuming 'Port' is a keyword field
                            'size' => 10 // Number of ports to return
                        ]
                    ]
                ]
            ]
        ];

        $response = $es_client->search($params);
        $aggregations = $response['aggregations']['classes']['buckets'];

        $count_array = array();

        foreach ($aggregations as $bucket) {
            $item = array(
                'name' => $bucket['key'],
                'count' => $bucket['doc_count']
            );
            $count_array[] = $item;
        }


		$data_array['classes'] = $count_array;
		echo json_encode($data_array, JSON_UNESCAPED_UNICODE);
		break;
}
