<?
	if($_SERVER['REQUEST_URI'] == '/catalog/1735')
	{
		$catalog_page = 'comp_games';

	}
	


	elseif($_SERVER['REQUEST_URI'] == '/catalog/1707')
	{
		$catalog_page = 'child_games';
	}
	
	elseif($_SERVER['REQUEST_URI'] == '/catalog/1735/2052')
	{
		$catalog_page = 'action';
	}
	
	elseif($_SERVER['REQUEST_URI'] == '/catalog/1735/2058')
	{
		$catalog_page = 'erot_games';
	}
	elseif($_SERVER['REQUEST_URI'] == '/catalog/1735/2046')
	{
		$catalog_page = 'strategy';
	}
elseif($_SERVER['REQUEST_URI'] == '/catalog/1735/2034')
	{
		$catalog_page = 'simulators';
	}
elseif($_SERVER['REQUEST_URI'] == '/catalog/1735/2034/2036')
	{
		$catalog_page = 'air_simul';
	}
elseif($_SERVER['REQUEST_URI'] == '/catalog/1735/2046/2049')
	{
		$catalog_page = 'shag_strat';
	}
	elseif($_SERVER['REQUEST_URI'] == '/catalog/1707/1710')
	{
		$catalog_page = 'games_girl';
	}
	else
	{
		$catalog_page = '';
	}

	if($catalog_page)
	{
		$seotxtfilename = '/var/www/cddiski/data/www/cddiski.ru/docs/assets/templates/cd/inc/'.$catalog_page.'.php';
		if(file_exists($seotxtfilename))
		{
			print "          <BR><BR>\r\n";
			include $seotxtfilename;
		}
	}	
?>