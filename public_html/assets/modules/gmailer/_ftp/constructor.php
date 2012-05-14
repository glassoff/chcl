<?php
global $srcTplFileName;
global $TplFileName;
$srcTplFileName = 'template.html';
$TplFileName = 'template-save.html';


//save template
if($action=='save-template'){
	$template = getTemplate($path);
	$vars = getVars($template);
	
	foreach($vars as $var){
		$name = $var['name'];
		$value = "";
		if ($_POST[$name])
			$value = $_POST[$name];
		$template = setVarValue($name, $value, $template);
	}
	
	$tplHandle = fopen($path."/".$TplFileName, 'w+');
	fwrite($tplHandle, $template);
	fclose($tplHandle);
}
elseif($action=='savePage'){
	$template = getTemplate($path);

	$vars = getVars($template);
	foreach($vars as $var){
		if($var['name']=='mailtitle'){
			$mailtitle = $var['value'];
			break;
		}	
	}
		
	$content = parseTpl($template);
	saveToPage($mailtitle, $content, $folder);
	//echo $content;
}
//edit
$template = getTemplate($path);
$vars = getVars($template);

foreach($vars as $var){
	$exp = $var['exp'];
	$value = $var['value'];
	$name = $var['name'];
	$type = $var['type'];
	
	$control = '';
	if($type=='text'){
		$control = '<input style="width:98%;" type="text" name="'.$name.'" value="'.$value.'" />';	
	}
	elseif($type=='textarea'){
		$control = '<textarea style="width:98%;" name="'.$name.'">'.$value.'</textarea>';
	}
	elseif($type=='tovar-buy'){
		$content = getVarContent($type, $value, $var['params']);
		$control = '<div style="">id товара: <input size="3" type="text" name="'.$name.'" value="'.$value.'" /></div>';
		
		$control .= $content;
	}
	
	$template = setVarContent($exp, $control, $template);
	
}

echo $template;



function getTemplate($path){
	global $TplFileName;
	global $srcTplFileName;
	
	if(file_exists($path."/".$TplFileName)){
		$template = file_get_contents($path."/".$TplFileName);	
	}
	else{
		$template = file_get_contents($path."/".$srcTplFileName);
	}	
	return $template;
}
function saveToPage($title, $content, $folder){
	//global $folder;
	global $modx;//print_r($modx->config);die();

	$sql = "SELECT id FROM modx_site_content WHERE (pagetitle='$title' AND parent='$folder')";
	$result = $modx->db->query($sql);
	
	$row = $modx->db->getRow($result);
	
	$document_id = $row['id'] ? $row['id'] : 0;
	
	//get parent template
	$result = $modx->db->query("SELECT template FROM modx_site_content WHERE (id='$folder')");
	$row = $modx->db->getRow($result);
	$template = $row['template'];
	
	require_once($modx->config['base_path'].'assets/libs/docmanager/document.class.inc.php');
	
	$doc = new Document($document_id);
	$doc->Set('parent',$folder);
	$doc->Set('template',$template);
	$doc->Set('pagetitle',$title);
	$doc->Set('hidemenu', 1);
	$doc->Set('published',1);
	$doc->Set('content',$content);
	$doc->Set('deleted',0);
	$doc->Set('cacheable', 0);

	$doc->Save();
		
}
function parseTpl($template){
	$vars = getVars($template);
	foreach($vars as $var){
		$exp = $var['exp'];
		$value = $var['value'];
		$name = $var['name'];
		$type = $var['type'];		
		
		$content = getVarContent($type, $value, $var['params']);
		$template = setVarContent($exp, $content, $template);
	}
	return $template;
}
function getTovar($id){
	global $modx;
	$sql = "SELECT * FROM modx_site_ec_items 
		WHERE (id='$id')";
	
	$result = $modx->db->query($sql);
	
	$params = array();
	while($row = $modx->db->getRow($result)){
		$params = $row;
	}	
	return $params;	
}
function getTovarVars($id){
	global $modx;
	$sql = "SELECT * FROM modx_site_tmplvar_ec_itemvalues vals
		LEFT JOIN modx_site_tmplvars vars ON (vals.tmplvarid=vars.id) 
		WHERE (itemid='$id')";
	
	$result = $modx->db->query($sql);
	
	$tvars = array();
	while($row = $modx->db->getRow($result)){
		$tvars[$row['name']] = $row['value'];
		//print_r($row);	
	}	
	return $tvars;
}
function getVarContent($type, $value, $params){
	global $modx;
	if($params[2]=='delete')
		return "";
	if($type=='tovar-buy' && $value){
		$tovar = getTovar($value);
		$tvars = getTovarVars($value);
		
		$image = $modx->config['site_url'] . $tvars['image1']; 
		
		$line = '<img src="/assets/images/mail/line.jpg" />';
		if($params[2]=='noline'){
			$line = '';
		}
		
		$content = '
		<table width="100%" border="0" cellpadding="0" cellspacing="0">
			<tr>
				<td>'.$line.'</td>
				<td align="center"><img width="150" src="'.phpThumb($image, 'w=150').'" alt="'.$tovar['pagetitle'].'"/></td>
			</tr>
			<tr>
				<td colspan="2" align="center" height="50">
					<b><a target="_blank" href="/catalog/item?id='.$value.'#tovar">
						<font color="#000" size="2" face="Verdana">'.$tovar['pagetitle'].'</font>
					</a></b>
				</td>
			</tr>
			<tr>
				<td colspan="2" align="center">
					<font color="#000" size="2" face="Verdana">Цена:</font> <font color="#fc6b00" size="2" face="Verdana"><b>'.$tovar['price_opt'].'</b></font> <font color="#000" size="2" face="Verdana">руб.</font>
				</td>
			</tr>	
			<tr>
				<td colspan="2" background="/assets/images/mail/sending_fon.jpg" align="center">
					<table border="0" cellpadding="0" cellspacing="0">
						<tr>
							<td valign="center" ><img src="/assets/images/mail/in_cart.gif" /></td>
							<td valign="center" height="46" >
								<a target="_blank" href="/catalog/item?id='.$value.'#tovar">
									<img src="/assets/images/mail/buy.png" border="0"/>
								</a>
							</td>						
						</tr>
					</table>
				</td>
			</tr>
			<tr><td colspan="2" >&nbsp;</td></tr>
		</table>
		
		';
		
	}
	else{
		$content = $value;
	}

	return $content;
}
function setVarContent($exp, $value, $content){
	$exp_preg = preg_quote($exp, '#');
	return preg_replace('#'.$exp_preg.'#is', $value, $content);		
}
function setVarValue($name, $value, $content){
	$exp_preg = preg_quote($exp, '#');
	return preg_replace('#{('.$name.'.*?){.*?}}#is', '{\1{'.$value.'}}', $content);
}
function getVars($template){
	$return = array();
	preg_match_all('#{(.+?){(.*?)}}#is', $template, $matches, PREG_SET_ORDER);
	foreach($matches as $key => $match){
		//$exp = $match[0];
		
		$params_str = $match[1];
		
		//$value = $match[2];
		
		$params = explode('&', $params_str);
		//$name = $params[0];
		//$type = $params[1];

		$return[$key]['exp'] = $match[0];
		$return[$key]['value'] = $match[2];
		$return[$key]['name'] = $params[0];
		$return[$key]['type'] = $params[1];
		$return[$key]['params'] = $params;
		
	}
	return $return;
}
function phpThumb($image, $params){
	global $modx;
	define(PHPTHUMB_PATH, "assets/snippets/phpthumb/");
	include($modx->config['base_path'].PHPTHUMB_PATH.'phpThumb.config.php');
	
	$options[0] = $params;
    $src = 'src='.$image;

    // append phpThumb parameters
    if(!empty($options[0])) $options[0] = '&'.$options[0];

    // create full query
    $ptquery = $src.$options[0];
    
    // generate hash for security
    $hash = md5($ptquery.$PHPTHUMB_CONFIG['high_security_password']);
    
    // append hash to query
    $ptquery .= '&hash='.$hash;    

    // Use image.php in MODx's document root
    $phpthumb = $modx->config['site_url'].'image.php';

    // generate URL and return the result   
    //return $phpthumb.'?'.$ptquery;
    preg_match('#^(.+)/([^/]+)$#is', $image, $match);//print_r($match);die();
    $imageUrl = $match[1] . "/phpthumb/hash=$hash/$params/" . $match[2];//die($imageUrl); 
    return $imageUrl;
}
?>
<div style="margin-top:20px;">
	<input type="button" value="Сохранить шаблон" onclick="postForm('save-template');return false;" class="">
	<input type="button" value="Сохранить страницу" onclick="postForm('savePage');return false;" class="">
</div>
