
<!DOCTYPE html>
<?php
	if(isset($_POST['name']) && isset($_POST['host']))
	{
		$port = 80;

		if(!empty($_POST['port'])) $port = $_POST['port'];

		addServer($_POST['name'], $_POST['host'], $port);

		header('Location: index.php');
	}
	else if(isset($_GET['del']))
	{
		$index = (int) $_GET['del'];
		if($index >= 0) deleteServer($index);
	}
$page = $_SERVER['PHP_SELF'];
$sec = "10";
?>
<html>
<link rel="shortcut icon" type="image/x-icon" href="/logo2.ico" />
   
	
	<head>
	<meta name="Description" content="Check if Perfect World servers are up or down">
        <meta name="Keywords" content="Perfect World International, Perfect World, Twilight Temple. Etherblade, Dawnglory, Tideswell, server, up, down">
        <meta charset="utf-8">
        <title>PWI Server(s) Status</title>
        <link href="css/bootstrap.css" rel="stylesheet">
        <link href="css/bootstrap-theme.css" rel="stylesheet">
        <link rel="stylesheet" type="text/css" href="mystyles.css" media="screen" />
        <meta http-equiv="refresh" content="<?php echo $sec?>;URL='<?php echo $page?>'">	
		
		
<!-- Global site tag (gtag.js) - Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=UA-104704130-1"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'UA-104704130-1');
</script>


    </head>
    <body>

 <center><br>
 
 <?php echo "The page will reload itself every 10 seconds!";?></center><br><br>
    	<div class="container">
    		<h3><b>Server(s) Status</b></h3>
    		<table class="table table-bordered">
				<tr>
				
				    <th class="text-center">Name</th>
					<th class="text-center">Domain</th>
					<th class="text-center">IP</th>
					<th class="text-center">Port</th>
					<th class="text-center">Status</th>
			<!--	<th class="text-center deleteMode" style="width:75px">Delete</th>-->
				</tr>
           <?php parser(); ?>
			</table>
		<!--		<input id="editMode" type="button" value="Edit mode" checked="checked" class="btn btn-default pull-right" />
			<form class="form-inline" role="form" action="index.php" method="post">
				<div class="form-group">
					<input type="text" class="form-control" id="name" name="name" placeholder="Name">
				</div>
				<div class="form-group">
					<input type="text" class="form-control" onkeyup="javascript:checkForm(this)" id="host" name="host" placeholder="Domain / IP">
				</div>
				<div class="form-group">
					<input type="text" size="5" class="form-control" id="port" name="port" placeholder="Port">
				</div>
				<button type="submit" class="btn btn-default" id="add-button">Add</button>
			</form> -->
			<br>
			<footer>
    			
    		</footer> 
    	</div>
    	<script src="js/jquery.js" type="text/javascript"></script>
        <script src="js/app.js" type="text/javascript"></script>
        <script src="js/bootstrap.min.js" type="text/javascript"></script>   	
    </body>

</html>
<?php

function getStatus($ip, $port)
{
	$socket = @fsockopen($ip, $port, $errorNo, $errorStr, 2);
	if (!$socket) return false;
	else return true;
}

function addServer($name, $host, $port)
{
	// TODO : rewrite the opening part correctly (better errors management)
	$i = 0;
	$filename = 'servers.xml';

	$servers = file_get_contents("servers.xml");
	if (trim($servers) == '')
	{
		exit();
	}
	else
	{
		$servers = simplexml_load_file("servers.xml");
		foreach ($servers as $server) $i++;
	}

	$servers = simplexml_load_file($filename);
	$server = $servers->addChild('server');

	$server->addAttribute('id', (string) $i);
	if(strlen($name) == 0) $name = $host;
	$server->addChild('name', (string)$name);
	$server->addChild('host', (string)$host);
	$server->addChild('port', (string)$port);
	$servers->asXML($filename);
}

function parser()
{
	//TODO : Fix errors when no valid XML content inside file.
	$file = "servers.xml";
	if(file_exists($file))
	{
		$servers = file_get_contents("servers.xml");
		if (trim($servers) == '') //File exists but empty
		{	
			$content = "<?xml version=\"1.0\" encoding=\"UTF-8\"?><servers></servers>";
			file_put_contents($file, $content, FILE_APPEND | LOCK_EX);
		}
		else
		{
			$servers = simplexml_load_file("servers.xml");
			foreach ($servers as $server)
			{
				echo "<tr>";
				echo "<td>".$server->name."</td>";
				if(filter_var($server->host, FILTER_VALIDATE_IP))
				{
					echo "<td class=\"text-center\">N/A</td><td class=\"text-center\">".$server->host."</td>";	
				}
				else
				{
					echo "<td class=\"text-center\">".$server->host."</td><td class=\"text-center\">".gethostbyname($server->host)."</td>";
				}

				echo "<td class=\"text-center\">".$server->port."</td>";

				if (getStatus((string)$server->host, (string)$server->port))
				{
					echo "<td class=\"text-center\"><span class=\"label label-success\">Online</span></td>";
				}
				else 
				{
					echo "<td class=\"text-center\"><span class=\"label label-danger\">Offline</span></td>";
				}
				;
				echo "</tr>";
			}
		}
	}
	else
	{
		// TODO : detect creation errors (ex : permissions)
		$content = "<?xml version=\"1.0\" encoding=\"UTF-8\"?><servers></servers>";
		file_put_contents($file, $content, FILE_APPEND | LOCK_EX);
	}
}

function deleteServer($index)
{
	$file = "servers.xml";

	$serverFile = new DOMDocument;
	$serverFile->formatOutput = true;
	$serverFile->load($file);
	$servers = $serverFile->documentElement;
	$list = $servers->getElementsByTagName('server');
	$nodeToRemove = null;

	foreach ($list as $server)
	{
		$attrValue = $server->getAttribute('id');
		if ((int)$attrValue == $index) $nodeToRemove = $server;
	}

	if ($nodeToRemove != null) $servers->removeChild($nodeToRemove);

	$serverFile->save($file);
	header('Location: index.php');


}
