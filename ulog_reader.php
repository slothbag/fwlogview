<?php

class ulogReader {

  private $dbconn;
  private $hostnames;

  function ulogReader($dbsettings) {

    if (1==0) {
      $this->dbconn = pg_connect("host=".$dbsettings['host']." 
        dbname=".$dbsettings['dbname']."
        user=".$dbsettings['user']."
        password=".$dbsettings['password']);
    }
    else {
      $this->dbconn = mysql_connect($dbsettings['host'],$dbsettings['user'],$dbsettings['password']);
      $r2 = mysql_select_db($dbsettings['dbname']);

      if (!$r2) {
        echo "Could not select DB\n";
        trigger_error(mysql_error(), E_USER_ERROR);
      }
    }
	
	 $query = "select ipaddress, hostname from known_hosts";

    $rs = mysql_query($query);
    
    if (!$rs) {
      echo "Could not execute query: $query\n";
      trigger_error(mysql_error(), E_USER_ERROR);
    }
	
	while ($row = mysql_fetch_assoc($rs)) {
		$this->hostnames[$row['ipaddress']] = $row['hostname'];
	}
  }

  function outputTable($filter) {
    $query = "select * from ulog2 
              left outer join tcp
              on ulog2._id = tcp._tcp_id
              left outer join udp
              on ulog2._id = udp._udp_id
              left outer join ip_proto
              on ulog2.ip_protocol = ip_proto._proto_id ";
			  
	if ($filter != '')
		$query .= "where oob_prefix = '$filter' ";
			  
    $query .= "order by `timestamp` desc limit 100";

    $rs = mysql_query($query);
    
    if (!$rs) {
      echo "Could not execute query: $query\n";
      trigger_error(mysql_error(), E_USER_ERROR);
    }

    print('<table class="table table-condensed">');
    print('<thead><th>Chain</th><th>Date</th><th>Interf</th><th>Proto</th><th>Src IP</th><th>Dst IP</th><th>Dest Port</th></thead>');

    while ($row = mysql_fetch_assoc($rs)) {
      if ($row['oob_prefix'] != "")
        echo '<tr><td>'.$row['oob_prefix'].'</td>';
      else
        echo '<tr><td>No chain</td>';
		
      echo '<td>' . $row['timestamp'] . '</td>';
      
      echo '<td>';
      if ($row['oob_in'] != "")
        echo $row['oob_in'];
      if ($row['oob_out'] != "")
        echo ' / '. $row['oob_out'];
	  echo '</td>';

      echo '<td>' . $row['proto_name'] . '</td>';
      echo '<td><div class="address_with_link">' . $this->renderAddr(dtr_ntop($row['ip_saddr']));
	  echo '<div class="hiddenlink"> <a href="index.php?page=settings&addr='.dtr_ntop($row['ip_saddr']).'"><span class="glyphicon glyphicon-eye-open"></span></a></div></div></td>';
      echo '<td><div class="address_with_link">' . $this->renderAddr(dtr_ntop($row['ip_daddr']));
	  echo '<div class="hiddenlink"> <a href="index.php?page=settings&addr='.dtr_ntop($row['ip_daddr']).'"><span class="glyphicon glyphicon-eye-open"></span></a></div></div></td>';
	  
      if ($row['ip_protocol'] ==6)
        echo '<td>' . $row['tcp_dport'] . '</td>';
      else
        echo '<td>' . $row['udp_dport'] . '</td>';
		
      echo "</tr>\n";
    }

    print('</table>');
  }
  function outputStats() {
    $query = "select max(timestamp) as last_timestamp, min(timestamp) as first_timestamp, count(*) as packet_count from ulog2";

    $rs = mysql_query($query);
    
    if (!$rs) {
      echo "Could not execute query: $query\n";
      trigger_error(mysql_error(), E_USER_ERROR);
    }
	
	$row = mysql_fetch_assoc($rs);

	echo 'Total packets: '.$row['packet_count'].'<br>';
	echo 'First packet: '.$row['first_timestamp'].'<br>';
	echo 'Last packet: '.$row['last_timestamp'];
  }
  
  function outputFilter() {
    $query = "select distinct oob_prefix from ulog2 where oob_prefix <> ''";

    $rs = mysql_query($query);
    
    if (!$rs) {
      echo "Could not execute query: $query\n";
      trigger_error(mysql_error(), E_USER_ERROR);
    }
	
	echo '<select id="filter">';
	echo "<option>All</option>";
	echo "<option>No chain</option>";
	while ($row = mysql_fetch_assoc($rs)) {
		echo "<option>".$row['oob_prefix']."</option>";
	}
	echo "</select>";
  }
  
  function outputTopHosts() {
    $query = "select ip_saddr, count(*) as packet_count from ulog2 group by ip_saddr order by 2 desc limit 10";

    $rs = mysql_query($query);
    
    if (!$rs) {
      echo "Could not execute query: $query\n";
      trigger_error(mysql_error(), E_USER_ERROR);
    }
	
	echo '<table class="table table-condensed">';
	echo '<thead><th>Host</th><th>Count</th></thead>';
	while ($row = mysql_fetch_assoc($rs)) {
		echo '<tr><td>'.$this->renderAddr(dtr_ntop($row['ip_saddr']))."</td><td>".$row['packet_count']."</td></tr>";
	}
	echo '</table>';
  }

  function outputTopPorts() {
    $query = "select proto_name, case when ip_protocol = 6 then tcp.tcp_dport else udp_dport end as dport, count(*) as packet_count 
				from ulog2 
				left outer join tcp
                on ulog2._id = tcp._tcp_id
                left outer join udp
                on ulog2._id = udp._udp_id
				left outer join ip_proto
                on ulog2.ip_protocol = ip_proto._proto_id
			    group by ip_saddr order by 3 desc limit 10";

    $rs = mysql_query($query);
    
    if (!$rs) {
      echo "Could not execute query: $query\n";
      trigger_error(mysql_error(), E_USER_ERROR);
    }
	
	echo '<table class="table table-condensed">';
	echo '<thead><th>Protocol</th><th>Dst Port</th><th>Count</th></thead>';
	while ($row = mysql_fetch_assoc($rs)) {
		echo '<tr><td>'.$row['proto_name'].'</td><td>'.$row['dport'].'</td><td>'.$row['packet_count']."</td></tr>";
	}
	echo '</table>';
  }
  
  function savehost($ipaddress, $hostname) {
    $query = "insert into known_hosts (ipaddress, hostname) values ('$ipaddress', '$hostname')";
	//$query = "insert into known_hosts set hostname = '$hostname' where ipaddress = '$ipaddress'";
	
    $rs = mysql_query($query);
    
    if (!$rs) {
      echo "Could not execute query: $query\n";
      trigger_error(mysql_error(), E_USER_ERROR);
    }	
  }
  
  function renderAddr($ipaddress) {
	if (array_key_exists($ipaddress,$this->hostnames))
		return '<span style="color:#6666FF;">'.$this->hostnames[$ipaddress].'</span>';
	else
		return $ipaddress;
  }
}
function dtr_ntop( $str ){
    if( strlen( $str ) == 16 OR strlen( $str ) == 4 ){
        $ip = inet_ntop( pack( "A".strlen( $str ) , $str ) );
        if (substr($ip, 0, 7) == "::ffff:")
          return substr($ip,7);
        else
          return $ip;
    }

    throw new \Exception( "Please provide a 4 or 16 byte string" );

    return false;
}
?>


