<!doctype html>
<html>
<head>
	<meta charset="utf-8">
	<title>Hetzner Traffic Per IP</title>
</head>

<body>
<?php
	@ini_set ( 'display_errors', false );
	session_start();

	if($_SESSION['delay_time'] <= time() or !$_SESSION['delay_time'])
	{
		$_SESSION['delay_time'] = time()+150;

		function docurl($url, $serverusername, $serverpassword, $ref='')
		{
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_USERPWD, "$serverusername:$serverpassword");
			curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			$output = curl_exec($ch);

			$info = curl_getinfo($ch);
			curl_close($ch);

			return $output;
		}

		$username = ""; /* Insert Your Username Web Service */
		$password = ""; /* Insert Your Password Web Service */
		$subnet = ""; /* Insert Your Subnet IP For Monitoring */

		$list = json_decode(docurl('https://robot-ws.your-server.de/traffic?type=month&from='. date("Y-m") .'-01&to='. date("Y-m-d") .'&subnet='.$subnet, $username, $password),true);

		$_SESSION['list'] = $list;
	}

	foreach($_SESSION['list'] as $kay => $value)
	{
		foreach($value['data'] as $kay2 => $value2)
		{
			$list_c .= '
					<tr>
						<td width="200">'.$kay2.'</td>
						<td width="100">'.$value2['in'].'</td>
						<td width="100">'.$value2['out'].'</td>
						<td width="100">'.$value2['sum'].'</td>
					</tr>
			';

			$apend_chart .= "['".$kay2."', ".$value2['sum']."],";
		}
	}
?>
	<script type="text/javascript" src="https://www.google.com/jsapi"></script>
	<script type="text/javascript">
		// Load the Visualization API and the piechart package.
		google.load('visualization', '1.0', {'packages':['corechart']});

		// Set a callback to run when the Google Visualization API is loaded.
		google.setOnLoadCallback(drawChart);

		// Callback that creates and populates a data table,
		// instantiates the pie chart, passes in the data and
		// draws it.
		function drawChart() {
			// Create the data table.
			var data = new google.visualization.DataTable();
			data.addColumn('string', 'Topping');
			data.addColumn('number', 'Slices');
			data.addRows([
				<?=$apend_chart?>

			]);

			// Set chart options
			var options = {'title':'Total', 'width':600, 'height':600};

			// Instantiate and draw our chart, passing in some options.
			var chart = new google.visualization.PieChart(document.getElementById('chart_div'));
			chart.draw(data, options);
		}

		setInterval(function(){
			window.location = 'hetzner_traffic.php';
		},130000)
	</script>
	<table align="center" width="1000" border="0">
		<tr>
			<td align="left" valign="top" width="500">
				<table align="center" width="500" border="0">
					<tr>
						<td align="center" valign="top">
							<div id="chart_div"></div>
						</td>
					</tr>
				</table>
			</td>
			<td align="left" valign="top" width="400">
				<table align="center" width="500" border="0">
					<tr height="30" bgcolor="#333333" style="color:#FFF; font:bold 14px Arial;">
						<td width="100">IP Address</td>
						<td width="100">Incoming</td>
						<td width="100">Outgoing</td>
						<td width="100">Total</td>
					</tr>
					<?=$list_c?>

				</table>
			</td>
		</tr>
	</table>
</body>
</html>
