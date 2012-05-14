<?php

	// Modified by Raymond for use with Template Variables

	//added by  Apodigm - DocVars - web@apodigm.com
	//note... this was lifted from Open-Realty, which is released under GNU GPL.
	//I have modified it substantially to fit the requirments for Etomite.

	// DISPLAY FORM ELEMENTS

	function renderFormElement($field_type, $field_name, $default_text, $field_elements, $field_value, $field_style='') {
		global $base_url;
		global $rb_base_url;
		global $manager_theme;
		global $_lang;

		$field_html = '';
		$field_value = ($field_value!="" ? $field_value : $default_text);

		switch ($field_type) {

			case "text": // handler for regular text boxes
			case "rawtext"; // non-htmlentity converted text boxes
			case "email": // handles email input fields
			case "number": // handles the input of numbers
				$field_html .=  '<input type="text" id="tv'.$field_name.'" name="tv'.$field_name.'" value="'.htmlspecialchars($field_value).'" '.$field_style.' tvtype="'.$field_type.'" onchange="documentDirty=true;setVariableModified(\''.$field_name.'\');" style="width:100%" />';
				break;
			case "textareamini": // handler for textarea mini boxes
				$field_html .=  '<textarea id="tv'.$field_name.'" name="tv'.$field_name.'" cols="40" rows="5" onchange="documentDirty=true;setVariableModified(\''.$field_name.'\');" style="width:100%">' . htmlspecialchars($field_value) .'</textarea>';
				break;
			case "textarea": // handler for textarea boxes
			case "rawtextarea": // non-htmlentity convertex textarea boxes
			case "htmlarea": // handler for textarea boxes (deprecated)
			case "richtext": // handler for textarea boxes
				$field_html .=  '<textarea id="tv'.$field_name.'" name="tv'.$field_name.'" cols="40" rows="15" onchange="documentDirty=true;setVariableModified(\''.$field_name.'\');" style="width:100%;">' . htmlspecialchars($field_value) .'</textarea>';
				break;
			case "date":
                if($field_value=='') $field_value=0;
                $cal = 'cal' . $field_name;

				$field_html .=  '<input id="tv'.$field_name.'" name="tv'.$field_name.'" type="hidden" value="' . ($field_value==0 || !isset($field_value) ? "" : $field_value) . '" onblur="documentDirty=true;setVariableModified(\''.$field_name.'\');">';

				$field_html .=  '	<table width="250" border="0" cellspacing="0" cellpadding="0">';
				$field_html .=  '	  <tr>';
				$field_html .=  '		<td width="160" style="border: 1px solid #808080;"><span id="tv'.$field_name.'_show" class="inputBox"> ' . ($field_value==0 || !isset($field_value) ? '(not set)' : $field_value) . '</span> </td>';

				$field_html .=  '		<td>&nbsp;';
				$field_html .=  '			<a onClick="documentDirty=false; '.$cal.'.popup();" onMouseover="window.status=\'Select a date\'; return true;" onMouseout="window.status=\'\'; return true;" style="cursor:pointer; cursor:hand"><img src="media/style/'.($manager_theme ? "$manager_theme/":"").'images/icons/cal.gif" width="16" height="16" border="0"></a>';
				$field_html .=  '			<a onClick="document.forms[\'mutate\'].elements[\'tv'.$field_name.'\'].value=\'\';document.forms[\'mutate\'].elements[\'tv'.$field_name.'\'].onblur();document.getElementById(\'tv'.$field_name.'_show\').innerHTML=\'(not set)\'; return true;" onMouseover="window.status=\'clear the date\'; return true;" onMouseout="window.status=\'\'; return true;" style="cursor:pointer; cursor:hand"><img src="media/style/'.($manager_theme ? "$manager_theme/":"").'images/icons/cal_nodate.gif" width="16" height="16" border="0" alt="No date"></a>';

				$field_html .=  '		</td>';
				$field_html .=  '	  </tr>';
				$field_html .=  '    </table>';

				$field_html .=  '<script type="text/javascript">';
				$field_html .=  '	var '.$cal.' = new calendar1(document.forms[\'mutate\'].elements[\'tv'.$field_name.'\'], document.getElementById("tv'.$field_name.'_show"));';
				$field_html .=  '   '.$cal.'.path="' . str_replace("index.php", "media/", $_SERVER["PHP_SELF"]) . '";';

				$field_html .=  '	'.$cal.'.year_scroll = true;';
				$field_html .=  '   '.$cal.'.time_comp = true;';

				$field_html .=  '</script>';

				break;
			case "dropdown": // handler for select boxes
				$field_html .=  '<select id="tv'.$field_name.'" name="tv'.$field_name.'" size="1" onchange="documentDirty=true;setVariableModified(\''.$field_name.'\');">';
				$index_list = ParseIntputOptions(ProcessTVCommand($field_elements, $field_name));
				while (list($item, $itemvalue) = each ($index_list))
				{
					list($item,$itemvalue) =  (is_array($itemvalue)) ? $itemvalue : explode("==",$itemvalue);
					if (strlen($itemvalue)==0) $itemvalue = $item;
					$field_html .=  '<option value="'.htmlspecialchars($itemvalue).'"'.($itemvalue==$field_value ?' selected="selected"':'').'>'.htmlspecialchars($item).'</option>';
				}
				$field_html .=  "</select>";
				break;
			case "listbox": // handler for select boxes
				$field_html .=  '<select id="tv'.$field_name.'" name="tv'.$field_name.'" onchange="documentDirty=true;setVariableModified(\''.$field_name.'\');" size="8">';	
				$index_list = ParseIntputOptions(ProcessTVCommand($field_elements, $field_name));
				while (list($item, $itemvalue) = each ($index_list))
				{
					list($item,$itemvalue) =  (is_array($itemvalue)) ? $itemvalue : explode("==",$itemvalue);
					if (strlen($itemvalue)==0) $itemvalue = $item;
					$field_html .=  '<option value="'.htmlspecialchars($itemvalue).'"'.($itemvalue==$field_value ?' selected="selected"':'').'>'.htmlspecialchars($item).'</option>';
				}
				$field_html .=  "</select>";
				break;
			case "listbox-multiple": // handler for select boxes where you can choose multiple items
				$field_value = explode("||",$field_value);
				$field_html .=  '<select id="tv'.$field_name.'[]" name="tv'.$field_name.'[]" multiple="multiple" onchange="documentDirty=true;setVariableModified(\''.$field_name.'\');" size="8">';
				$index_list = ParseIntputOptions(ProcessTVCommand($field_elements, $field_name));
				while (list($item, $itemvalue) = each ($index_list))
				{
					list($item,$itemvalue) =  (is_array($itemvalue)) ? $itemvalue : explode("==",$itemvalue);
					if (strlen($itemvalue)==0) $itemvalue = $item;
					$field_html .=  '<option value="'.htmlspecialchars($itemvalue).'"'.(in_array($itemvalue,$field_value) ?' selected="selected"':'').'>'.htmlspecialchars($item).'</option>';
				}
				$field_html .=  "</select>";
				break;
			case "url": // handles url input fields
				$urls= array(''=>'--', 'http://'=>'http://', 'https://'=>'https://', 'ftp://'=>'ftp://', 'mailto:'=>'mailto:');
				$field_html ='<table border="0" cellspacing="0" cellpadding="0"><tr><td><select id="tv'.$field_name.'_prefix" name="tv'.$field_name.'_prefix" onchange="documentDirty=true; setVariableModified(\''.$field_name.'\');">';
				foreach($urls as $k => $v){
					if(strpos($field_value,$v)===false) $field_html.='<option value="'.$v.'">'.$k.'</option>';
					else{
						$field_value = str_replace($v,"",$field_value);
						$field_html.='<option value="$v" selected="selected">'.$k.'</option>';
					}
				}
				$field_html .='</select></td><td>';
				$field_html .=  '<input type="text" id="tv'.$field_name.'" name="tv'.$field_name.'" value="'.htmlspecialchars($field_value).'" width="100" '.$field_style.' onchange="documentDirty=true;setVariableModified(\''.$field_name.'\');" /></td></tr></table>';
				break;
			case "checkbox": // handles check boxes
				$field_value = explode("||",$field_value);
				$index_list = ParseIntputOptions(ProcessTVCommand($field_elements, $field_name));
				static $i=0;
				$field_html .= '<div style="height:200px;overflow:scroll">';  
				while (list($item, $itemvalue) = each ($index_list))
				{
					list($item,$itemvalue) =  (is_array($itemvalue)) ? $itemvalue : explode("==",$itemvalue);
					if (strlen($itemvalue)==0) $itemvalue = $item;
					$field_html .=  '<input type="checkbox" value="'.htmlspecialchars($itemvalue).'" id="tv_'.$i.'" name="tv'.$field_name.'[]" '. (in_array($itemvalue,$field_value)?" checked='checked'":"").' onchange="documentDirty=true;setVariableModified(\''.$field_name.'\');" /><label for="tv_'.$i.'">'.$item.'</label><br />';
					$i++;
				}
				$field_html .= '</div>'; 
				break;
			
			case "ajaxitemselector": // handles check boxes
				$field_value = explode("||",$field_value);
				$index_list = ParseIntputOptions(ProcessTVCommand($field_elements, $field_name));
				static $i=0;
				$field_html .= '<div style="height:200px;overflow:scroll">';  
				while (list($item, $itemvalue) = each ($index_list))
				{
					list($item,$itemvalue) =  (is_array($itemvalue)) ? $itemvalue : explode("==",$itemvalue);
					if (strlen($itemvalue)==0) $itemvalue = $item;
					$field_html .=  '<input type="checkbox" value="'.htmlspecialchars($itemvalue).'" id="tv_'.$i.'" name="tv'.$field_name.'[]" '. (in_array($itemvalue,$field_value)?" checked='checked'":"").' onchange="documentDirty=true;setVariableModified(\''.$field_name.'\');" /><label for="tv_'.$i.'">'.$item.'</label><br />';
					$i++;
				}
				$field_html .= '</div>'; 
				break;	
					
			case "option": // handles radio buttons
				$index_list = ParseIntputOptions(ProcessTVCommand($field_elements, $field_name));
				while (list($item, $itemvalue) = each ($index_list))
				{
					list($item,$itemvalue) =  (is_array($itemvalue)) ? $itemvalue : explode("==",$itemvalue);
					if (strlen($itemvalue)==0) $itemvalue = $item;
					$field_html .=  '<input type="radio" value="'.htmlspecialchars($itemvalue).'" name="tv'.$field_name.'" '.($itemvalue==$field_value ?'checked="checked"':'').' onchange="documentDirty=true;setVariableModified(\''.$field_name.'\');" />'.$item.'<br />';
				}
				break;
			case "uploadimage": // handles radio buttons
				
				if (!empty($field_value)) 
				$field_html .='<a href="'.$base_url.$field_value.'"  target="_blank" id="tva'.$field_name.'" rel="lightbox"><img alt="'.$field_value.'" src="'.$base_url.'image.php?w=100&src='.$base_url.$field_value.'" id="tvi'.$field_name.'"></a><br>';
				else $field_html = '';				
				
				$field_html .='<input  onchange="documentDirty=true;setVariableModified(\''.$field_name.'\');" type="file"  style="width:600px" size="100" id="tv'.$field_name.'" name="tv'.$field_name.'"  value="'.$field_value .'" '.$field_style.'/>';
				
				if (!empty($field_value)) 
				$field_html .='&nbsp;<br><input type="checkbox" onchange="documentDirty=true;setVariableModified(\''.$field_name.'\');" name="removetvcmd'.$field_name.'" value="1" >'.$_lang['delete'];
				
				break;	
			case "image":	// handles image fields using htmlarea image manager
				global $_lang;
				global $ResourceManagerLoaded;
				global $content,$use_editor,$which_editor;
				if (!$ResourceManagerLoaded && !(($content['richtext']==1 || $_GET['a']==4) && $use_editor==1 && $which_editor==3)){					
					$field_html .="					
					<script type=\"text/javascript\">
							var lastImageCtrl;
							var lastFileCtrl;
							var lasti;
							var lasta;
							function SetImage(ctrli,ctrla,url) {
								document.getElementById(ctrli).src = '".$base_url."image.php?w=100&src=".$base_url."'+url;	
								document.getElementById(ctrla).href = '$base_url'+url;							
							}
							function OpenServerBrowser(url, width, height ) {
								var iLeft = (screen.width  - width) / 2 ;
								var iTop  = (screen.height - height) / 2 ;

								var sOptions = 'toolbar=no,status=no,resizable=yes,dependent=yes' ;
								sOptions += ',width=' + width ;
								sOptions += ',height=' + height ;
								sOptions += ',left=' + iLeft ;
								sOptions += ',top=' + iTop ;

								var oWindow = window.open( url, 'FCKBrowseWindow', sOptions ) ;
							}			
							function BrowseServer(ctrl) {
								lastImageCtrl = ctrl;
								var w = screen.width * 0.7;
								var h = screen.height * 0.7;
								OpenServerBrowser('".$base_url."manager/media/browser/mcpuk/browser.html?Type=images&Connector=".$base_url."manager/media/browser/mcpuk/connectors/php/connector.php&ServerPath=".$base_url."', w, h);
							}
							
							function BrowseFileServer(ctrl) {
								lastFileCtrl = ctrl;
								var w = screen.width * 0.7;
								var h = screen.height * 0.7;
								OpenServerBrowser('".$base_url."manager/media/browser/mcpuk/browser.html?Type=files&Connector=".$base_url."manager/media/browser/mcpuk/connectors/php/connector.php&ServerPath=".$base_url."', w, h);
							}
							
							function SetUrl(url, width, height, alt){
								if(lastFileCtrl) {
									var c = document.mutate[lastFileCtrl];
									if(c) c.value = url;
									lastFileCtrl = '';
								} else if(lastImageCtrl) {
									var c = document.mutate[lastImageCtrl];
									if(c) {
										c.value = url;
										document.getElementById(lasta).href = '".$base_url."'+url;
										//document.getElementById(lasti).style.display = 'block'; 
										document.getElementById(lasti).src = '".$base_url."image.php?w=100&src=".$base_url."'+url;										
									}	
									lastImageCtrl = '';
								} else {
									return;
								}
							}
					</script>";
					$ResourceManagerLoaded  = true;					
				} 
				
				$field_html .='<a href="'.$base_url.$field_value.'"  target="_blank" id="tva'.$field_name.'" rel="lightbox"><img src="'.$base_url.'image.php?w=100&src='.$base_url.$field_value.'" id="tvi'.$field_name.'"></a><br>';
				$field_html .='<input type="text" id="tv'.$field_name.'" name="tv'.$field_name.'"  value="'.$field_value .'" '.$field_style.' onchange="documentDirty=true;setVariableModified(\''.$field_name.'\');SetImage(\'tvi'.$field_name.'\',\'tva'.$field_name.'\',this.value)" />&nbsp;<input type="button" value="'.$_lang['insert'].'" onclick="setVariableModified(\''.$field_name.'\');BrowseServer(\'tv'.$field_name.'\');lasti=\'tvi'.$field_name.'\'; lasta=\'tva'.$field_name.'\'"/>';
				break;
			case "file": // handles the input of file uploads
			/* Modified by Timon for use with resource browser */
                global $_lang;
				global $ResourceManagerLoaded;
				global $content,$use_editor,$which_editor;
				if (!$ResourceManagerLoaded && !(($content['richtext']==1 || $_GET['a']==4) && $use_editor==1 && $which_editor==3)){
				/* I didn't understand the meaning of the condition above, so I left it untouched ;-) */ 
					$field_html .="
					<script type=\"text/javascript\">
							var lastImageCtrl;
							var lastFileCtrl;
							function OpenServerBrowser(url, width, height ) {
								var iLeft = (screen.width  - width) / 2 ;
								var iTop  = (screen.height - height) / 2 ;

								var sOptions = 'toolbar=no,status=no,resizable=yes,dependent=yes' ;
								sOptions += ',width=' + width ;
								sOptions += ',height=' + height ;
								sOptions += ',left=' + iLeft ;
								sOptions += ',top=' + iTop ;

								var oWindow = window.open( url, 'FCKBrowseWindow', sOptions ) ;
							}
							
								function BrowseServer(ctrl) {
								lastImageCtrl = ctrl;
								var w = screen.width * 0.7;
								var h = screen.height * 0.7;
								OpenServerBrowser('".$base_url."manager/media/browser/mcpuk/browser.html?Type=images&Connector=".$base_url."manager/media/browser/mcpuk/connectors/php/connector.php&ServerPath=".$base_url."', w, h);
							}
										
							function BrowseFileServer(ctrl) {
								lastFileCtrl = ctrl;
								var w = screen.width * 0.7;
								var h = screen.height * 0.7;
								OpenServerBrowser('".$base_url."manager/media/browser/mcpuk/browser.html?Type=files&Connector=".$base_url."manager/media/browser/mcpuk/connectors/php/connector.php&ServerPath=".$base_url."', w, h);
							}
							
							function SetUrl(url, width, height, alt){
								if(lastFileCtrl) {
									var c = document.mutate[lastFileCtrl];
									if(c) c.value = url;
									lastFileCtrl = '';
								} else if(lastImageCtrl) {
									var c = document.mutate[lastImageCtrl];
									if(c) c.value = url;
									lastImageCtrl = '';
								} else {
									return;
								}
							}
					</script>";
					$ResourceManagerLoaded  = true;					
				} 
				$field_html .='<input type="text" id="tv'.$field_name.'" name="tv'.$field_name.'"  value="'.$field_value .'" '.$field_style.' onchange="documentDirty=true;setVariableModified(\''.$field_name.'\');" />&nbsp;<input type="button" value="'.$_lang['insert'].'" onclick="setVariableModified(\''.$field_name.'\');BrowseFileServer(\'tv'.$field_name.'\')" />';
                
				break;
			default: // the default handler -- for errors, mostly
				$field_html .=  '<input type="text" id="tv'.$field_name.'" name="tv'.$field_name.'" value="'.htmlspecialchars($field_value).'" '.$field_style.' onchange="documentDirty=true;setVariableModified(\''.$field_name.'\');" />';

		} // end switch statement
		return $field_html;
	} // end renderFormElement function


	function ParseIntputOptions($v) {
		$a = array();
		if(is_array($v)) return $v;
		else if(is_resource($v)) {
			while ($cols = mysql_fetch_row($v)) $a[] = $cols;
		}
		else $a = explode("||", $v);
		return $a;
	}
	
?>
