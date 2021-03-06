<?php
	require "scripts/pi-hole/php/header.php";
	require "scripts/pi-hole/php/savesettings.php";
	// Reread ini file as things might have been changed
	$setupVars = parse_ini_file("/etc/pihole/setupVars.conf");
?>
<style type="text/css">
	.tooltip-inner {
		max-width: none;
		white-space: nowrap;
	}
	@-webkit-keyframes Pulse{
		from {color:#630030;-webkit-text-shadow:0 0 9px #333;}
		50% {color:#e33100;-webkit-text-shadow:0 0 18px #e33100;}
		to {color:#630030;-webkit-text-shadow:0 0 9px #333;}
	}
	p.lookatme {
		-webkit-animation-name: Pulse;
		-webkit-animation-duration: 2s;
		-webkit-animation-iteration-count: infinite;
	}
</style>

<?php if(isset($debug)){ ?>
<div id="alDebug" class="alert alert-warning alert-dismissible fade in" role="alert">
    <button type="button" class="close" data-hide="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <h4><i class="icon fa fa-warning"></i> Debug</h4>
    <?php print_r($_POST); ?>
</div>
<?php } ?>

<?php if(strlen($success) > 0){ ?>
<div id="alInfo" class="alert alert-info alert-dismissible fade in" role="alert">
    <button type="button" class="close" data-hide="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <h4><i class="icon fa fa-info"></i> Info</h4>
    <?php echo $success; ?>
</div>
<?php } ?>

<?php if(strlen($error) > 0){ ?>
<div id="alError" class="alert alert-danger alert-dismissible fade in" role="alert">
    <button type="button" class="close" data-hide="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <h4><i class="icon fa fa-ban"></i> Error</h4>
    <?php echo $error; ?>
</div>
<?php } ?>

<div class="row">
	<div class="col-md-6">
<?php
	// Networking
	if(isset($setupVars["PIHOLE_INTERFACE"])){
		$piHoleInterface = $setupVars["PIHOLE_INTERFACE"];
	} else {
		$piHoleInterface = "unknown";
	}
	if(isset($setupVars["IPV4_ADDRESS"])){
		$piHoleIPv4 = $setupVars["IPV4_ADDRESS"];
	} else {
		$piHoleIPv4 = "unknown";
	}
	if(isset($setupVars["IPV6_ADDRESS"])){
		$piHoleIPv6 = $setupVars["IPV6_ADDRESS"];
	} else {
		$piHoleIPv6 = "unknown";
	}
	$hostname = trim(file_get_contents("/etc/hostname"), "\x00..\x1F");
?>
		<div class="box box-warning">
			<div class="box-header with-border">
				<h3 class="box-title">Networking</h3>
			</div>
			<div class="box-body">
				<div class="form-group">
					<label>Pi-Hole Ethernet Interface</label>
					<div class="input-group">
						<div class="input-group-addon"><i class="fa fa-plug"></i></div>
						<input type="text" class="form-control" disabled value="<?php echo $piHoleInterface; ?>">
					</div>
				</div>
				<div class="form-group">
					<label>Pi-Hole IPv4 address</label>
					<div class="input-group">
						<div class="input-group-addon"><i class="fa fa-plug"></i></div>
						<input type="text" class="form-control" disabled value="<?php echo $piHoleIPv4; ?>">
					</div>
				</div>
				<div class="form-group">
					<label>Pi-Hole IPv6 address</label>
					<div class="input-group">
						<div class="input-group-addon"><i class="fa fa-plug"></i></div>
						<input type="text" class="form-control" disabled value="<?php echo $piHoleIPv6; ?>">
					</div>
					<?php if (!defined('AF_INET6')){ ?><p style="color: #F00;">Warning: PHP has been compiled without IPv6 support.</p><?php } ?>
				</div>
				<div class="form-group">
					<label>Pi-Hole hostname</label>
					<div class="input-group">
						<div class="input-group-addon"><i class="fa fa-laptop"></i></div>
						<input type="text" class="form-control" disabled value="<?php echo $hostname; ?>">
					</div>
				</div>
			</div>
		</div>
<?php
	// Pi-Hole DHCP server
	if(isset($setupVars["DHCP_ACTIVE"]))
	{
		if($setupVars["DHCP_ACTIVE"] == 1)
		{
			$DHCP = true;
		}
		else
		{
			$DHCP = false;
		}
		// Read setings from config file
		$DHCPstart = $setupVars["DHCP_START"];
		$DHCPend = $setupVars["DHCP_END"];
		$DHCProuter = $setupVars["DHCP_ROUTER"];
		// This setting has been added later, we have to check if it exists
		if(isset($setupVars["DHCP_LEASETIME"]))
		{
			$DHCPleasetime = $setupVars["DHCP_LEASETIME"];
			if(strlen($DHCPleasetime) < 1)
			{
				// Fallback if empty string
				$DHCPleasetime = 24;
			}
		}
		else
		{
			$DHCPleasetime = 24;
		}
	}
	else
	{
		$DHCP = false;
		// Try to guess initial settings
		if($piHoleIPv4 !== "unknown") {
			$DHCPdomain = explode(".",$piHoleIPv4);
			$DHCPstart  = $DHCPdomain[0].".".$DHCPdomain[1].".".$DHCPdomain[2].".201";
			$DHCPend    = $DHCPdomain[0].".".$DHCPdomain[1].".".$DHCPdomain[2].".251";
			$DHCProuter = $DHCPdomain[0].".".$DHCPdomain[1].".".$DHCPdomain[2].".1";
		}
		else {
			$DHCPstart  = "";
			$DHCPend    = "";
			$DHCProuter = "";
		}
		$DHCPleasetime = 24;
	}
	if(isset($setupVars["PIHOLE_DOMAIN"])){
		$piHoleDomain = $setupVars["PIHOLE_DOMAIN"];
	} else {
		$piHoleDomain = "local";
	}
?>
		<div class="box box-warning">
			<div class="box-header with-border">
				<h3 class="box-title">Pi-Hole DHCP Server</h3>
			</div>
			<div class="box-body">
				<form role="form" method="post">
				<div class="col-md-6">
					<div class="form-group">
						<div class="checkbox"><label><input type="checkbox" name="active" <?php if($DHCP){ ?>checked<?php } ?> id="DHCPchk"> DHCP server enabled</label></div>
					</div>
				</div>
				<div class="col-md-6">
					<p id="dhcpnotice" <?php if(!$DHCP){ ?>hidden<?php } ?>>Make sure your router's DHCP server is disabled when using the Pi-hole DHCP server!</p>
				</div>
					<div class="col-md-12">
						<label>Range of IP addresses to hand out</label>
					</div>
					<div class="col-md-6">
					<div class="form-group">
						<div class="input-group">
							<div class="input-group-addon">From</div>
								<input type="text" class="form-control DHCPgroup" name="from" value="<?php echo $DHCPstart; ?>" data-inputmask="'alias': 'ip'" data-mask <?php if(!$DHCP){ ?>disabled<?php } ?>>
						</div>
					</div>
					</div>
					<div class="col-md-6">
					<div class="form-group">
						<div class="input-group">
							<div class="input-group-addon">To</div>
								<input type="text" class="form-control DHCPgroup" name="to" value="<?php echo $DHCPend; ?>" data-inputmask="'alias': 'ip'" data-mask <?php if(!$DHCP){ ?>disabled<?php } ?>>
						</div>
					</div>
					</div>
					<div class="col-md-12">
					<label>Router (gateway) IP address</label>
					<div class="form-group">
						<div class="input-group">
							<div class="input-group-addon">Router</div>
								<input type="text" class="form-control DHCPgroup" name="router" value="<?php echo $DHCProuter; ?>" data-inputmask="'alias': 'ip'" data-mask <?php if(!$DHCP){ ?>disabled<?php } ?>>
						</div>
					</div>
					</div>
					<div class="col-md-12">
					<div class="box box-warning collapsed-box">
						<div class="box-header with-border">
							<h3 class="box-title">Advanced DHCP settings</h3>
							<div class="box-tools pull-right"><button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-plus"></i></button></div>
						</div>
						<div class="box-body">
							<div class="col-md-12">
							<label>Pi-Hole domain name</label>
							<div class="form-group">
								<div class="input-group">
									<div class="input-group-addon">Domain</div>
										<input type="text" class="form-control DHCPgroup" name="domain" value="<?php echo $piHoleDomain; ?>" <?php if(!$DHCP){ ?>disabled<?php } ?>>
								</div>
							</div>
							</div>
							<div class="col-md-12">
							<label>DHCP lease time</label>
							<div class="form-group">
								<div class="input-group">
									<div class="input-group-addon">Lease time in hours</div>
										<input type="text" class="form-control DHCPgroup" name="leasetime" id="leasetime" value="<?php echo $DHCPleasetime; ?>" data-inputmask="'mask': '9', 'repeat': 7, 'greedy' : false" data-mask <?php if(!$DHCP){ ?>disabled<?php } ?>>
								</div>
							</div>
								<p>Hint: 0 = infinite, 24 = one day, 168 = one week, 744 = one month, 8760 = one year</p>
							</div>
						</div>
					</div>
					</div>
<?php if($DHCP) {

	// Read leases file
	$leasesfile = true;
	$dhcpleases = fopen('/etc/pihole/dhcp.leases', 'r') or $leasesfile = false;
	$dhcp_leases  = [];

	function convertseconds($argument) {
		$seconds = round($argument);
		if($seconds < 60)
		{
			return sprintf('%ds', $seconds);
		}
		elseif($seconds < 3600)
		{
			return sprintf('%dm %ds', ($seconds/60), ($seconds%60));
		}
		elseif($seconds < 86400)
		{
			return sprintf('%dh %dm %ds',  ($seconds/3600%24),($seconds/60%60), ($seconds%60));
		}
		else
		{
			return sprintf('%dd %dh %dm %ds', ($seconds/86400), ($seconds/3600%24),($seconds/60%60), ($seconds%60));
		}
}

	while(!feof($dhcpleases) && $leasesfile)
	{
		$line = explode(" ",trim(fgets($dhcpleases)));
		if(count($line) == 5)
		{
			$counter = intval($line[0]);
			if($counter == 0)
			{
				$time = "Infinite";
			}
			elseif($counter <= 315360000) // 10 years in seconds
			{
				$time = convertseconds($counter);
			}
			else // Assume time stamp
			{
				$time = convertseconds($counter-time());
			}

			if(strpos($line[2], ':') !== false)
			{
				// IPv6 address
				$type = 6;
			}
			else
			{
				// IPv4 lease
				$type = 4;
			}

			$host = $line[3];
			if($host == "*")
			{
				$host = "<i>unknown</i>";
			}

			$clid = $line[4];
			if($clid == "*")
			{
				$clid = "<i>unknown</i>";
			}

			array_push($dhcp_leases,["TIME"=>$time, "hwaddr"=>$line[1], "IP"=>$line[2], "host"=>$host, "clid"=>$clid, "type"=>$type]);
		}
	}
	?>
				<div class="col-md-12">
				<div class="box box-warning collapsed-box">
					<div class="box-header with-border">
						<h3 class="box-title">DHCP leases</h3>
						<div class="box-tools pull-right"><button type="button" class="btn btn-box-tool" data-widget="collapse" id="leaseexpand"><i class="fa fa-plus"></i></button></div>
					</div>
					<div class="box-body">
					<div class="col-md-12">
						<table id="DHCPLeasesTable" class="table table-striped table-bordered dt-responsive nowrap" cellspacing="0" width="100%">
							<thead>
								<tr>
									<th>IP address</th>
									<th>Hostname</th>
								</tr>
							</thead>
							<tbody>
								<?php foreach($dhcp_leases as $lease) { ?><tr data-placement="auto" data-container="body" data-toggle="tooltip" title="Lease type: IPv<?php echo $lease["type"]; ?><br/>Remaining lease time: <?php echo $lease["TIME"]; ?><br/>DHCP UID: <?php echo $lease["clid"]; ?>"><td><?php echo $lease["IP"]; ?></td><td><?php echo $lease["host"]; ?></td></tr><?php } ?>
							</tbody>
						</table>
					</div>
					</div>
				</div>
				</div>
<?php } ?>
			</div>
			<div class="box-footer">
				<input type="hidden" name="field" value="DHCP">
				<button type="submit" class="btn btn-primary pull-right">Save</button>
			</div>
			</form>
		</div>

<?php
	// DNS settings
	if(isset($setupVars["PIHOLE_DNS_1"])){
		if(isset($primaryDNSservers[$setupVars["PIHOLE_DNS_1"]]))
		{
			$piHoleDNS1 = $primaryDNSservers[$setupVars["PIHOLE_DNS_1"]];
		}
		else
		{
			$piHoleDNS1 = "Custom";
		}
	} else {
		$piHoleDNS1 = "unknown";
	}

	if(isset($setupVars["PIHOLE_DNS_2"])){
		if(isset($secondaryDNSservers[$setupVars["PIHOLE_DNS_2"]]))
		{
			$piHoleDNS2 = $secondaryDNSservers[$setupVars["PIHOLE_DNS_2"]];
		}
		else
		{
			$piHoleDNS2 = "Custom";
		}
	} else {
		$piHoleDNS2 = "unknown";
	}

	if(isset($setupVars["DNS_FQDN_REQUIRED"])){
		if($setupVars["DNS_FQDN_REQUIRED"])
		{
			$DNSrequiresFQDN = true;
		}
		else
		{
			$DNSrequiresFQDN = false;
		}
	} else {
		$DNSrequiresFQDN = true;
	}

	if(isset($setupVars["DNS_BOGUS_PRIV"])){
		if($setupVars["DNS_BOGUS_PRIV"])
		{
			$DNSbogusPriv = true;
		}
		else
		{
			$DNSbogusPriv = false;
		}
	} else {
		$DNSbogusPriv = true;
	}
?>
		<div class="box box-warning">
			<div class="box-header with-border">
				<h3 class="box-title">Upstream DNS Servers</h3>
			</div>
			<div class="box-body">
				<form role="form" method="post">
				<div class="col-lg-6">
					<label>Primary DNS Server</label>
					<div class="form-group">
						<?php foreach ($primaryDNSservers as $key => $value) { ?> <div class="radio"><label><input type="radio" name="primaryDNS" value="<?php echo $value;?>" <?php if($piHoleDNS1 === $value){ ?>checked<?php } ?> ><?php echo $value;?> (<?php echo $key;?>)</label></div> <?php } ?>
						<label>Custom</label>
						<div class="input-group">
							<div class="input-group-addon"><input type="radio" name="primaryDNS" value="Custom"
							<?php if($piHoleDNS1 === "Custom"){ ?>checked<?php } ?>></div>
							<input type="text" name="DNS1IP" class="form-control" data-inputmask="'alias': 'ip'" data-mask
							<?php if($piHoleDNS1 === "Custom"){ ?>value="<?php echo $setupVars["PIHOLE_DNS_1"]; ?>"<?php } ?>>
						</div>
					</div>
				</div>
				<div class="col-lg-6">
					<label>Secondary DNS Server</label>
					<div class="form-group">
						<?php foreach ($secondaryDNSservers as $key => $value) { ?> <div class="radio"><label><input type="radio" name="secondaryDNS" value="<?php echo $value;?>" <?php if($piHoleDNS2 === $value){ ?>checked<?php } ?> ><?php echo $value;?> (<?php echo $key;?>)</label></div> <?php } ?>
						<label>Custom</label>
						<div class="input-group">
							<div class="input-group-addon"><input type="radio" name="secondaryDNS" value="Custom"
							<?php if($piHoleDNS2 === "Custom"){ ?>checked<?php } ?>></div>
							<input type="text" name="DNS2IP" class="form-control" data-inputmask="'alias': 'ip'" data-mask
							<?php if($piHoleDNS2 === "Custom"){ ?>value="<?php echo $setupVars["PIHOLE_DNS_2"]; ?>"<?php } ?>>
						</div>
					</div>
				</div>
				<div class="col-lg-12">
				<div class="box box-warning collapsed-box">
					<div class="box-header with-border">
						<h3 class="box-title">Advanced DNS settings</h3>
						<div class="box-tools pull-right"><button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-plus"></i></button></div>
					</div>
					<div class="box-body">
						<div class="col-lg-12">
							<div class="form-group">
								<div class="checkbox"><label><input type="checkbox" name="DNSrequiresFQDN" <?php if($DNSrequiresFQDN){ ?>checked<?php } ?> title="domain-needed"> never forward non-FQDNs</label></div>
							</div>
							<div class="form-group">
								<div class="checkbox"><label><input type="checkbox" name="DNSbogusPriv" <?php if($DNSbogusPriv){ ?>checked<?php } ?> title="bogus-priv"> never forward reverse lookups for private IP ranges</label></div>
							</div>
							<p>Note that enabling these two options may increase your privacy slightly, but may also prevent you from being able to access local hostnames if the Pi-Hole is not used as DHCP server</p>
						</div>
					</div>
				</div>
				</div>
			</div>
			<div class="box-footer">
				<input type="hidden" name="field" value="DNS">
				<button type="submit" class="btn btn-primary pull-right">Save</button>
			</div>
			</form>
		</div>
	</div>
	<div class="col-md-6">
<?php
	// Query logging
	if(isset($setupVars["QUERY_LOGGING"]))
	{
		if($setupVars["QUERY_LOGGING"] == 1)
		{
			$piHoleLogging = true;
		}
		else
		{
			$piHoleLogging = false;
		}
	} else {
		$piHoleLogging = true;
	}
?>
		<div class="box box-primary">
			<div class="box-header with-border">
				<h3 class="box-title">Query Logging<?php if($piHoleLogging) { ?> (size of log <?php echo formatSizeUnits(filesize("/var/log/pihole.log")); ?>)<?php } ?></h3>
			</div>
			<div class="box-body">
				<p>Current status:
				<?php if($piHoleLogging) { ?>
					Enabled (recommended)
				<?php }else{ ?>
					Disabled
				<?php } ?></p>
				<?php if($piHoleLogging) { ?>
					<p>Note that disabling will render graphs on the web user interface useless</p>
				<?php } ?>
			</div>
			<div class="box-footer">
				<form role="form" method="post">
				<button type="button" class="btn btn-default confirm-flushlogs">Flush logs</button>
				<input type="hidden" name="field" value="Logging">
				<?php if($piHoleLogging) { ?>
					<input type="hidden" name="action" value="Disable">
					<button type="submit" class="btn btn-primary pull-right">Disable query logging</button>
				<?php } else { ?>
					<input type="hidden" name="action" value="Enable">
					<button type="submit" class="btn btn-primary pull-right">Enable query logging</button>
				<?php } ?>
				</form>
			</div>
		</div>
<?php
	// Excluded domains in API Query Log call
	if(isset($setupVars["API_EXCLUDE_DOMAINS"]))
	{
		$excludedDomains = explode(",", $setupVars["API_EXCLUDE_DOMAINS"]);
	} else {
		$excludedDomains = [];
	}

	// Exluded clients in API Query Log call
	if(isset($setupVars["API_EXCLUDE_CLIENTS"]))
	{
		$excludedClients = explode(",", $setupVars["API_EXCLUDE_CLIENTS"]);
	} else {
		$excludedClients = [];
	}

	// Exluded clients
	if(isset($setupVars["API_QUERY_LOG_SHOW"]))
	{
		$queryLog = $setupVars["API_QUERY_LOG_SHOW"];
	} else {
		$queryLog = "all";
	}

	// Privacy Mode
	if(isset($setupVars["API_PRIVACY_MODE"]))
	{
		$privacyMode = $setupVars["API_PRIVACY_MODE"];
	} else {
		$privacyMode = false;
	}

	if(istrue($setupVars["API_GET_UPSTREAM_DNS_HOSTNAME"]))
	{
		$resolveForward = true;
	}
	else
	{
		$resolveForward = false;
	}

	if(istrue($setupVars["API_GET_CLIENT_HOSTNAME"]))
	{
		$resolveClients = true;
	}
	else
	{
		$resolveClients = false;
	}

?>
		<div class="box box-success">
			<div class="box-header with-border">
				<h3 class="box-title">API</h3>
			</div>
			<form role="form" method="post">
			<div class="box-body">
				<h4>Top Lists</h4>
				<p>Exclude the following domains from being shown in</p>
				<div class="col-lg-6">
					<div class="form-group">
					<label>Top Domains / Top Advertisers</label>
					<textarea name="domains" class="form-control" rows="4" placeholder="Enter one domain per line"><?php foreach ($excludedDomains as $domain) { echo $domain."\n"; } ?></textarea>
					</div>
				</div>
				<div class="col-lg-6">
					<div class="form-group">
					<label>Top Clients</label>
					<textarea name="clients" class="form-control" rows="4" placeholder="Enter one IP address per line"><?php foreach ($excludedClients as $client) { echo $client."\n"; } ?></textarea>
					</div>
				</div>
				<h4>Reverse DNS lookup</h4>
				<p>Try to determine the domain name via querying the Pi-hole for</p>
				<div class="col-lg-6">
					<div class="form-group">
						<div class="checkbox"><label><input type="checkbox" name="resolve-forward" <?php if($resolveForward){ ?>checked<?php } ?>> Forward Destinations</label></div>
					</div>
				</div>
				<div class="col-lg-6">
					<div class="form-group">
						<div class="checkbox"><label><input type="checkbox" name="resolve-clients" <?php if($resolveClients){ ?>checked<?php } ?>> Top Clients</label></div>
					</div>
				</div>
				<h4>Query Log</h4>
				<div class="col-lg-6">
					<div class="form-group">
						<div class="checkbox"><label><input type="checkbox" name="querylog-permitted" <?php if($queryLog === "permittedonly" || $queryLog === "all"){ ?>checked<?php } ?>> Show permitted queries</label></div>
					</div>
				</div>
				<div class="col-lg-6">
					<div class="form-group">
						<div class="checkbox"><label><input type="checkbox" name="querylog-blocked" <?php if($queryLog === "blockedonly" || $queryLog === "all"){ ?>checked<?php } ?>> Show blocked queries</label></div>
					</div>
				</div>
				<h4>Privacy mode</h4>
				<div class="col-lg-12">
					<div class="form-group">
						<div class="checkbox"><label><input type="checkbox" name="privacyMode" <?php if($privacyMode){ ?>checked<?php } ?>> Don't show query results for permitted requests</label></div>
					</div>
				</div>
			</div>
			<div class="box-footer">
				<input type="hidden" name="field" value="API">
				<button type="submit" class="btn btn-primary pull-right">Save</button>
			</div>
			</form>
		</div>
<?php
	// CPU temperature unit
	if(isset($setupVars["TEMPERATUREUNIT"]))
	{
		$temperatureunit = $setupVars["TEMPERATUREUNIT"];
	}
	else
	{
		$temperatureunit = "C";
	}
	// Use $boxedlayout value determined in header.php
?>
		<div class="box box-success">
			<div class="box-header with-border">
				<h3 class="box-title">Web User Interface</h3>
			</div>
			<form role="form" method="post">
			<div class="box-body">
<?php /*
				<h4>Query Log Page</h4>
				<div class="col-lg-6">
					<div class="form-group">
						<label>Default value for <em>Show XX entries</em></label>
						<select class="form-control" disabled>
							<option>10</option>
							<option>25</option>
							<option>50</option>
							<option>100</option>
							<option>All</option>
						</select>
					</div>
				</div>
*/ ?>
				<h4>Interface appearance</h4>
				<div class="form-group">
					<div class="checkbox"><label><input type="checkbox" name="boxedlayout" value="yes" <?php if($boxedlayout){ ?>checked<?php } ?> >Use boxed layout (helpful when working on large screens)</label></div>
				</div>
				<h4>CPU Temperature Unit</h4>
				<div class="form-group">
					<div class="radio"><label><input type="radio" name="tempunit" value="C" <?php if($temperatureunit === "C"){ ?>checked<?php } ?> >Celsius</label></div>
					<div class="radio"><label><input type="radio" name="tempunit" value="K" <?php if($temperatureunit === "K"){ ?>checked<?php } ?> >Kelvin</label></div>
					<div class="radio"><label><input type="radio" name="tempunit" value="F" <?php if($temperatureunit === "F"){ ?>checked<?php } ?> >Fahrenheit</label></div>
				</div>
			</div>
			<div class="box-footer">
				<input type="hidden" name="field" value="webUI">
				<button type="submit" class="btn btn-primary pull-right">Save</button>
			</div>
			</form>
		</div>
<?php /*
		<div class="box box-info">
			<div class="box-header with-border">
				<h3 class="box-title">Blocking Page</h3>
			</div>
			<div class="box-body">
				<p>Show page with details if a site is blocked</p>
				<div class="form-group">
					<div class="radio"><label><input type="radio" name="blockingPage" value="Yes" <?php if($pishowBlockPage){ ?>checked<?php } ?> disabled>Yes</label></div>
					<div class="radio"><label><input type="radio" name="blockingPage" value="No" <?php if(!$pishowBlockPage){ ?>checked<?php } ?> disabled>No</label></div>
					<p>If Yes: Hide content for in page ads?</p>
					<div class="checkbox"><label><input type="checkbox" <?php if($pishowBlockPageInpage) { ?>checked<?php } ?> disabled>Enabled (recommended)</label></div>
				</div>
			</div>
		</div>
*/ ?>
		<div class="box box-danger">
			<div class="box-header with-border">
				<h3 class="box-title">System Administration</h3>
			</div>
			<div class="box-body">
				<button type="button" class="btn btn-default confirm-reboot">Restart system</button>
				<button type="button" class="btn btn-default confirm-restartdns">Restart DNS server</button>
				<button type="button" class="btn btn-default confirm-flushlogs">Flush logs</button>

				<form role="form" method="post" id="rebootform">
					<input type="hidden" name="field" value="reboot">
				</form>
				<form role="form" method="post" id="restartdnsform">
					<input type="hidden" name="field" value="restartdns">
				</form>
				<form role="form" method="post" id="flushlogsform">
					<input type="hidden" name="field" value="flushlogs">
				</form>
			</div>
		</div>
	</div>
</div>

<?php
    require "scripts/pi-hole/php/footer.php";
?>

<script src="scripts/vendor/jquery.inputmask.js"></script>
<script src="scripts/vendor/jquery.inputmask.extensions.js"></script>
<script src="scripts/vendor/jquery.confirm.min.js"></script>
<script src="scripts/pi-hole/js/settings.js"></script>
