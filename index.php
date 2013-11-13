<?php
require_once('config.php');
require_once('ulog_reader.php');
$r = new ulogReader($dbsettings);

if (isset($_POST['hostname'])) {
	$ipaddress = $_POST['addr'];
	$hostname = $_POST['hostname'];
	$r->savehost($ipaddress, $hostname);
}
$page = "";
if (isset($_GET['page'])) $page = $_GET['page'];

$filter = '';
if (isset($_GET['filter'])) $filter = $_GET['filter'];
if ($filter == 'All') $filter = '';
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>FWLogView</title>

    <!-- Bootstrap core CSS -->
    <link href="./bootstrap/css/bootstrap.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="css/narrow.css" rel="stylesheet">

    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
      <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
    <![endif]-->
	
	<style>
	.hiddenlink {
		visibility: hidden;
		display: inline;
	}
	.address_with_link:hover .hiddenlink {
		visibility: visible;
	}
	</style>
  </head>

  <body>

    <div class="container">
      <div class="header">
        <ul class="nav nav-pills pull-right">
          <li <? if ($page == '') echo 'class="active"' ?>><a href="index.php">IPTables</a></li>
		  <li <? if ($page == 'conntrack') echo 'class="active"' ?>><a href="index.php?page=conntrack">Conntrack</a></li>
		  <li <? if ($page == 'arp') echo 'class="active"' ?>><a href="index.php?page=arp">ARP</a></li>
          <li <? if ($page == 'settings') echo 'class="active"' ?>><a href="index.php?page=settings">Settings</a></li>
          <li <? if ($page == 'about') echo 'class="active"' ?>><a href="index.php?page=about">About</a></li>
        </ul>
        <h3 class="text-muted">FWLogView</h3>
      </div>

	<? if ($page == ""): ?>
	  <div class="row">
        <div class="col-md-9">
		<?
		$r->outputTable($filter);
		?>
		</div>
        <div class="col-md-3">
			<!-- DB Stats -->
			<div class="panel panel-default">
				<div class="panel-heading">Database stats</div>
				<div class="panel-body" style="font-size: 8pt;">
				<?
				$r->outputStats();
				?>
				</div>
			</div>
			
			<!-- Filter -->
			<div class="panel panel-default">
				<div class="panel-heading">Packet filter</div>
				<div class="panel-body">
				<?
				$r->outputFilter();
				?>
				</div>
			</div>
			
			<!-- Top Hosts -->
			<div class="panel panel-default">
				<div class="panel-heading">Top hosts</div>
				<div class="panel-body" style="font-size: 8pt;">
				<?
				$r->outputTopHosts();
				?>
				</div>
			</div>
			
			<!-- Top Ports -->
			<div class="panel panel-default">
				<div class="panel-heading">Top ports</div>
				<div class="panel-body" style="font-size: 8pt;">
				<?
				$r->outputTopPorts();
				?>
				</div>
			</div>
		</div>
      </div>
	  <? elseif ($page == 'conntrack'):
		//CONNTRACK
	    exec("/usr/sbin/conntrack -L", $output, $result);
		echo '<table class="table table-condensed">';
		echo '<thead><th>Proto</th><th>TTL</th><th>State</th><th>Src</th><th>Dst</th><th>Src Port</th><th>Dst Port</th><th>Src</th><th>Dst</th><th>Src Port</th><th>Dst Port</th></thead>';
		foreach ($output as $line) {
		    $fields = preg_split('/ /', $line, -1, PREG_SPLIT_NO_EMPTY);
			echo '<tr>';
			echo '<td>'.$fields[0].'</td>';
			if ($fields[0] == 'tcp') {
				echo '<td>'.$fields[2].'</td>';
				echo '<td>'.$fields[3].'</td>';
				echo '<td>'.keyvalue_value($fields[4]).'</td>';
				echo '<td>'.keyvalue_value($fields[5]).'</td>';
				echo '<td>'.keyvalue_value($fields[6]).'</td>';
				echo '<td>'.keyvalue_value($fields[7]).'</td>';
				echo '<td>'.keyvalue_value($fields[8]).'</td>';
				echo '<td>'.keyvalue_value($fields[9]).'</td>';
				echo '<td>'.keyvalue_value($fields[10]).'</td>';
				echo '<td>'.keyvalue_value($fields[11]).'</td>';
			}
			else {
				echo '<td>'.$fields[2].'</td>';
				echo '<td></td>';
				echo '<td>'.keyvalue_value($fields[3]).'</td>';
				echo '<td>'.keyvalue_value($fields[4]).'</td>';
				echo '<td>'.keyvalue_value($fields[5]).'</td>';
				echo '<td>'.keyvalue_value($fields[6]).'</td>';
				echo '<td>'.keyvalue_value($fields[7]).'</td>';
				echo '<td>'.keyvalue_value($fields[8]).'</td>';
				echo '<td>'.keyvalue_value($fields[9]).'</td>';
				echo '<td>'.keyvalue_value($fields[10]).'</td>';			
			}
			echo '</tr>';
		}
		echo '</table>';
	  elseif ($page == 'arp'):
		//ARP
	    exec("/usr/sbin/arp", $output, $result);
		echo '<table class="table table-condensed">';
		echo '<thead><th>Address</th><th>MAC</th><th>Interface</th></thead>';
		
		array_shift($output);
			
		foreach ($output as $line) {
		    $fields = preg_split('/ /', $line, -1, PREG_SPLIT_NO_EMPTY);
			echo '<tr>';
			if ($fields[1] == '(incomplete)')
				echo '<td>'.$fields[0].'</td><td>'.$fields[1].'</td><td>'.$fields[2].'</td>';
			else
				echo '<td>'.$fields[0].'</td><td>'.$fields[2].'</td><td>'.$fields[4].'</td>';
			echo '</tr>';
		}
		echo '</table>';
	  elseif ($page == 'settings'):
		if (isset($_GET['addr'])) {
			$addr = $_GET['addr'];
			echo '<form action="index.php" method="post">';
			echo '<input name="addr" value="'.$addr.'"><br>';
			echo '<input name="hostname" value=""><br>';
			echo '<button type="submit" class="btn btn-default">Save</button>';
			echo '</form>';
		}
		else
			echo 'Not implement yet';
	  ?>
	  <? elseif ($page == 'about'): ?>
	  <p>A complete firewall inspection utility.</p>
	  <p>This project aims to be a simple and fast / low resource way to view firewall relevant information. 
	  Primarily the three area's I have found important are IPTable logging for past intrusion attempts, Conntrack to view current connections and the ARP table to view physically connected devices.
	  There are not many firewall log viewers that have been updated/maintained in the past 5 years and I wanted something that was compatible with the latest ulog2 process.</p>
	  <p>Feel free to log issues and submit pull requests on the <a href="https://github.com/slothbag/fwlogview">fwlogview</a> github page.</p>
	  <p>If you like this software or find it useful please consider donating to my bitcoin address below.</p>
	  <p>If you are interested in paid support or sponsored development for this software, contact me via my email listed on github.</p>
	  <? endif; ?>

      <div class="footer">
        <p>Donations accepted: 14zYqNfs6ubktnLkqCCmQdYA8qmGMXA9L5</p>
      </div>

    </div> <!-- /container -->


    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
	<script src="jquery/jquery-2.0.3.min.js"></script>
	
	<script language="javascript">
	$(function() {
		
		//select current drop down
		var selectObj = $('#filter').get(0);
		for (var i = 0; i < selectObj.options.length; i++) {
			if (selectObj.options[i].text == '<?= $filter ?>') {
				selectObj.options[i].selected = true;
				break;
			}
		}
		
		//set the event for filter drop down
		$('#filter').change(function() {
			window.location = 'index.php?filter='+$(this).val();
		});
	});
	</script>
  </body>
</html>

<?
function keyvalue_value($keyvalue) {
	$split = explode('=', $keyvalue);
	if (count($split) == 1)
		return $keyvalue;
	else
		return $split[1];
}
?>