<?php

/**
 * Document Manager Module - interaction.inc.php
 * 
 * Purpose: Contains the main visual output functions for the module
 * Author: Garry Nutting (Mark Kaplan - Menu Index functionalty, Luke Stokes - Document Permissions concept)
 * For: MODx CMS (www.modxcms.com)
 * Date:29/09/2006 Version: 1.6
 * 
 */

function buttonCSS() {
	global $theme;

	$output .= '
			<style type="text/css">
			.topdiv {
			border: 0;
		}
		
		.subdiv {
			border: 0;
		}
	
		li {list-style:none;}
		
		.tplbutton {
			text-align: right;
		}
		
		#bttn .bttnheight {
			height: 25px !important;
			padding: 0px;
			padding-top: 6px;
			float: left;
			vertical-align: middle !important;
		}
		
		ul.sortableList {
			padding-left: 20px;
			margin: 0px;
			width: 300px;
			font-family: Arial, sans-serif;
		}
		
		ul.sortableList li {
			font-weight: bold;
			cursor: move;
			color: grey;
			padding: 2px 2px;
			margin: 2px 0px;
			border: 1px solid #000000;
			background-image: url("media/style' . $theme . '/images/bg/grid_hdr.gif");
			background-repeat: repeat-x;
		}
		
			#bttn .bttnheight {
				height: 25px !important;
				padding: 0px; 
				padding-top: 6px;
				float: left;
				vertical-align:		middle !important;
			
			}
			#bttn a{
				cursor: 			default !important;
				font: 				icon !important;
				color:				black !important;
				border:				0px !important;
				padding:			5px 5px 7px 5px!important;
				white-space:		nowrap !important;
				vertical-align:		middle !important;
				background:	transparent !important;
				text-decoration: none;
			}
			
			#bttn a:hover {
				border:		1px solid darkgreen !important;
				padding:			4px 4px 6px 4px !important;		
				background-image:	url("media/style' . $theme . '/images/bg/button_dn.gif") !important;
				text-decoration: none;
			}
			
			#bttn a img {
				vertical-align: middle !important;
			}
			
			.go a {
				cursor: default !important;
				font: icon !important;
				color: black !important;
				border: 0px !important;
				padding: 5px 5px 7px 5px !important;
				white-space: nowrap !important;
				vertical-align: middle !important;
				background: transparent;
				text-decoration: none;
			}
			
			.go a:hover {
				border: 1px solid darkgreen !important;
				padding: 4px 4px 6px 4px !important;
				background: url("media/style' . $theme . '/images/bg/button_dn.gif");
				text-decoration: none;
			}
			
			.go a img {
				vertical-align: middle !important;
			}
			
			</style>';

	return $output;

}

/**
 * showTemplateVariables - shows the main template variable form
 * 
 */
function show1CInfo() {
	global $modx;
	global $_lang;
	global $theme;
	global $_1c_data_dir;	
	$info_file = $_1c_data_dir."info.php";
	$info_fh = fopen($info_file,'r');		
	if ($info_fh === false) {
		return $_lang['_1C_file_opening_warning'];		
	} else {
		$all_info = fread($info_fh, filesize($info_file));
		fclose($info_fh);
	}
	$all_info = unserialize($all_info);
	$output = '<p>' . $_lang['1c_info_desc'] . '</p><br />';
	$output = '<p>' . $_lang['1c_info_last_upload_date'] .': <b>'. $all_info['last_upload'] . '</b></p><br />';
	
	$alt = 0;
	if (count($all_info) === 0) {
		return $_lang['1C_info_no_records'];
	} else 
	
	foreach($all_info['regions'] as $region) {
		$output.='<br><p class="warning">'. $region['region']. '</p>';
		$output.='<table style="width:100%">';	
		$output.='<td class="gridHeader">'.$_lang['1C_info_column_num'].'</td>';
		$output.='<td class="gridHeader">'.$_lang['1C_info_column_name'].'</td>';
		$output.='<td class="gridHeader">'.$_lang['1C_info_column_id'].'</td>';
		$output.='<td class="gridHeader">'.$_lang['1C_info_column_model_quantity'].'</td>';
		$output.='<td class="gridHeader">'.$_lang['1C_info_column_total_quantity'].'</td>';
		$output.='</tr>';
		foreach($region['shops'] as $k => $shop) {
			$output.='<tr><td '.($alt==0 ? 'class="gridItem"' : 'class="gridAltItem"').'>';
			$output.= $k+1;
			$output.='</td><td '.($alt==0 ? 'class="gridItem"' : 'class="gridAltItem"').'>';
			$output.= $shop['name'];
			$output.='</td><td '.($alt==0 ? 'class="gridItem"' : 'class="gridAltItem"').'>';
			$output.= $shop['id'];		
			$output.='</td><td '.($alt==0 ? 'class="gridItem"' : 'class="gridAltItem"').'>';
			$output.= $shop['model_quantity'];		
			$output.='</td><td '.($alt==0 ? 'class="gridItem"' : 'class="gridAltItem"').'>';
			$output.= $shop['total_quantity'];
			$output.='</td></tr>';
			if($alt == 0) $alt=1;
			else $alt = 0;
		}
		$output.='</table>';
	} 	
	return $output;
}

/**
 * showDocGroups - shows the main document permissions form for the tabbed interface
 * 
 */
function showSettingChangeForm() {
	global $modx;
	global $_lang;
	global $theme;
	global $_1c_data_dir;	
	
	$info_file = $_1c_data_dir."info.php";
	$info_fh = fopen($info_file,'r');		
	if ($info_fh === false) {
		return $_lang['_1C_file_opening_warning'];		
	} else {
		$all_info = fread($info_fh, filesize($info_file));
		fclose($info_fh);
	}
	$all_info = unserialize($all_info);
	if (count($all_info) === 0) {
		return $_lang['1C_info_no_records'];
	} else {	
		include_once($_1c_data_dir."settings.php");
		$output = '<p>' . $_lang['1c_change_settings_desc'] . '</p><br />';
		$output.='<form action="" name=\'save_settings\' method="POST"> 
				  <input type="hidden" name="tabAction" value="_1c_change_settings" />'; 
		$output.='<select name="settings1C[default_pr_id]">';	
		foreach($all_info['regions'] as $region) {
			$output.='<optgroup label="'. $region['region'] .'">';		
			foreach($region['shops'] as $k => $shop) {
				 $selected = ($settings1C['default_pl_id'] == $shop['id']) ? 'selected' : '';
				 $output.='<option '.$selected.' value="'. $shop['id'] .'">'. $shop['name'] .'</options>';			
			}
			$output.='</optgroup>';
		}
		$output.='</select>';
		$output.='<p>&nbsp;</p> 
				  <input type="submit" value="' . $_lang['1C_save'] . '"/> 
				  </form';
	}
	return $output;
}

/**
 * showSortMenu - shows the main Sort Menu output for the tabbed interface
 * 
 */
function show1CUploadForm() {
	global $_lang;
	global $theme;
	$output = '<p>' . $_lang['1C_upload_form'] . '</p><br />';
	$output .= ' 
							<form action="" name=\'upload_data\' method="POST" enctype="multipart/form-data"> 
							<input type="hidden" name="tabAction" value="_1c_upload_data" /> 						
							<input name="upload_file" type="file" size="60"/>		
							</form';

	$output .= '<div class="go" style="margin-top:10px;">
				<a id="Button1" onclick="upload();">
				<img src="media/style' . $theme . '/images/icons/save.gif" alt="'.$_lang['1C_go'].'" />' . $_lang['1C_go'] . '</a>
				<br /><br />
				</div>';

	return $output;

}






/**
 * showInteraction - shows the 'Range/Treeview' form for module
 * 
 */
function showInteraction($showTree = true) {
	global $_lang;
	global $theme;

	//-- initiate desired interaction method 
	if (isset ($_POST['tswitch'])) {
		$output .= '<div id="interaction">
											<div class="sectionHeader">&nbsp;' . $_lang['1C_tree_title'] . '</div> 
											<div class="sectionBody"> 
											<form name="module" action="" method="post"> 
											<input type="hidden" name="opcode" value="tree" /> 
											<input type="hidden" name="pids" value="" />
											<input type="hidden" name="setoption" value="" />  
											<input type="hidden" name="newvalue" value="" />
											<input type="hidden" name="date_pubdate" value="" />
											<input type="hidden" name="date_unpubdate" value="" />
											<input type="hidden" name="date_createdon" value="" />
											<input type="hidden" name="date_editedon" value="" />
											<input type="hidden" name="author_createdby" value="" />
											<input type="hidden" name="author_editedby" value="" />
											<input type="hidden" name="tabAction" value="" /> 
											<input type="submit" name="fsubmit" onclick="postForm(\'tree\');return false;" value="' . $_lang['1C_select_submit'] . '" /><br /><br />';

		$output .= getDocTree();
		$output .= '				</form><br />
											<form name="switch" action="" method="post"> 
											<input type="submit" name="rswitch" value="' . $_lang['1C_select_range'] . '" /> 
											<input type="hidden" id="selectedTV" name="selectedTV" value="" />
											</form> 
											<div style="clear:both;"></div> 
											</div></div>';

	} else {
		$output .= '<div id="interaction">
											<div class="sectionHeader">&nbsp;' . $_lang['1C_range_title'] . '</div> 
											<div class="sectionBody"> 
											<form id="range" action="" name="range" method="post"> 
											<input type="hidden" name="opcode" value="range" /> 
											<input type="hidden" name="newvalue" value="" />
											<input type="hidden" name="setoption" value="" />
											<input type="hidden" name="date_pubdate" value="" />
											<input type="hidden" name="date_unpubdate" value="" />
											<input type="hidden" name="date_createdon" value="" />
											<input type="hidden" name="date_editedon" value="" />
											<input type="hidden" name="author_createdby" value="" />
											<input type="hidden" name="author_editedby" value="" />
											<input type="hidden" name="tabAction" value ="" /> 
											<input name="pids" type="text" style="width:90%;" /> 
											<input type="submit" name="fsubmit" onclick="postForm(\'range\');return false;" value="' . $_lang['1C_select_submit'] . '" /> 
											</form><br /> 
											';
		$output .= $_lang['1C_select_range_text'];

		if ($showTree) $output .= '	<br /><form name="switch" action="" method="post"> 
											<input type="submit" style="" name="tswitch" value="' . $_lang['1C_select_tree'] . '" /> 
											<input type="hidden" id="selectedTV" name="selectedTV" value="" />
											</form>'; 
		$output .= '<div style="clear:both;"></div> 
					</div></div>';
	}

	return $output;
}

/**
 * getDocTree - encapsulates a modified MakeMap function to display the document tree
 * 
 */
function getDocTree() {
	global $modx;
	global $table;
	global $theme;

	$subdiv = true;

	// $siteMapRoot [int] 
	$siteMapRoot = 0;

	// $removeNewLines [ true | false ] 
	$removeNewLines = (!isset ($removeNewLines)) ? false : ($removeNewLines == true);
	// $maxLevels [ int ] 
	$maxLevels = 0;
	// $textOfLinks [ string ] 
	$textOfLinks = (!isset ($textOfLinks)) ? 'menutitle' : "$textOfLinks";
	// $titleOfLinks [ string ] 
	$titleOfLinks = (!isset ($titleOfLinks)) ? 'description' : "$titleOfLinks";
	// $pre [ string ] 
	$pre = (!isset ($pre)) ? '' : "$pre";
	// $post [ string ] 
	$post = (!isset ($post)) ? '' : "$post";
	// $selfAsLink [ true | false ] 
	$selfAsLink = (!isset ($selfAsLink)) ? false : ($selfAsLink == true);
	// $hereClass [ string ] 
	$hereClass = (!isset ($hereClass)) ? 'here' : $hereClass;
	// $topdiv [ true | false ] 
	// Indicates if the top level UL is wrapped by a containing DIV block 
	$topdiv = (!isset ($topdiv)) ? false : ($topdiv == true);
	// $topdivClass [ string ] 
	$topdivClass = (!isset ($topdivClass)) ? 'topdiv' : "$topdivClass";
	// $topnavClass [ string ] 
	$topnavClass = (!isset ($topnavClass)) ? 'topnav' : "$topnavClass";

	// $useCategoryFolders [ true | false ] 
	// If you want folders without any content to render without a link to be used
	// as "category" pages (defaults to true). In order to use Category Folders,  
	// the template must be set to (blank) or it won't work properly. 
	$useCategoryFolders = (!isset ($useCategoryFolders)) ? true : "$useCategoryFolders";
	// $categoryClass [ string ] 
	// CSS Class for folders with no content (e.g., category folders) 
	$categoryClass = (!isset ($categoryClass)) ? 'category' : "$categoryClass";
	// $subdiv [ true | false ] 
	$subdiv = (!isset ($subdiv)) ? false : ($subdiv == true);

	// $subdivClass [ string ] 
	$subdivClass = (!isset ($subdivClass)) ? 'subdiv' : "$subdivClass";

	// $orderBy [ string ] 
	$orderBy = (!isset ($orderBy)) ? 'menuindex' : "$orderBy";

	// $orderDesc [true | false] 
	$orderDesc = (!isset ($orderDesc)) ? false : ($orderDesc == true);

	// ########################################### 
	// End config, the rest takes care of itself # 
	// ########################################### 

	$debugMode = false;

	// Initialize 
	$MakeMap = "";
	$siteMapRoot = (isset ($startDoc)) ? $startDoc : $siteMapRoot;
	$maxLevels = (isset ($levelLimit)) ? $levelLimit : $maxLevels;
	$ie = ($removeNewLines) ? '' : "\n";
	//Added by Remon: (undefined variables php notice) 
	$activeLinkIDs = array ();
	$subnavClass = '';

	//display expand/collapse exclusion for top level 
	$startRoot = $siteMapRoot;

	// Overcome single use limitation on functions 
	global $MakeMap_Defined;

	if (!isset ($MakeMap_Defined)) {
		function filterHidden($var) {
			return (!$var['hidemenu'] == 1);
		}
		function filterEmpty($var) {
			return (!empty ($var));
		}
		function MakeMap($modx, $listParent, $listLevel, $description, $titleOfLinks, $maxLevels, $inside, $pre, $post, $selfAsLink, $ie, $activeLinkIDs, $topdiv, $topdivClass, $topnavClass, $subdiv, $subdivClass, $subnavClass, $hereClass, $useCategoryFolders, $categoryClass, $showDescription, $descriptionField, $textOfLinks, $orderBy, $orderDesc, $debugMode) {
			global $theme;

			//-- get ALL children 
			$table = $modx->getFullTableName('site_content');
			$csql = $modx->db->select('*', $table, 'parent="' . $listParent . '"');
			$children = array ();
			for ($i = 0; $i < @ $modx->db->getRecordCount($csql); $i++) {
				array_push($children, @ $modx->db->getRow($csql));
			}

			$numChildren = count($children);

			if (is_array($children) && !empty ($children)) {

				// determine if it's a top category or not 
				$toplevel = !$inside;

				// build the output 
				$topdivcls = (!empty ($topdivClass)) ? ' class="' . $topdivClass . '"' : '';
				$topdivblk = ($topdiv) ? "<div$topdivcls id=\"$listParent\">" : '';
				$topnavcls = (!empty ($topnavClass)) ? ' class="' . $topnavClass . '"' : '';
				$subdivcls = (!empty ($subdivClass)) ? ' class="' . $subdivClass . '"' : '';
				$subdivblk = ($subdiv) ? "<div$subdivcls id=\"$listParent\">$ie" : '';
				$subnavcls = (!empty ($subnavClass)) ? ' class="' . $subnavClass . '"' : '';
				//-- output the div and add the expand/collapse if required 
				$output .= ($toplevel) ? "$topdivblk<ul$topnavcls>$ie" : "$ie" .
				 (($listParent != $startRoot) ? '' : '') . "$subdivblk<ul$subnavcls>$ie";

				//loop through and process subchildren 
				foreach ($children as $child) {

					// get highlight colour 
					if ($child['deleted'] == 1) {
						$color = '#000'; //black 
					}
					elseif ($child['hidemenu'] == 1) {
						$color = '#ff9933'; //orange 
					}
					elseif ($child['published'] == 0) {
						$color = '#ff6600'; //red 
					} else {
						$color = '#339900'; //green 
					}

					// figure out if it's a containing category folder or not  
					$numChildren--;
					$isFolder = $child['isfolder'];
					$itsEmpty = ($isFolder && ($child['template'] == '0'));
					$itm = "";

					// if menutitle is blank fall back to pagetitle for menu link 
					$textOfLinks = (empty ($child['menutitle'])) ? 'pagetitle' : "$textOfLinks";

					// If at the top level 
					if (!$inside) {
						$itm .= ((!$selfAsLink && ($child['id'] == $modx->documentIdentifier)) || ($itsEmpty && $useCategoryFolders)) ? $pre . $child[$textOfLinks] . $post .
						 (($debugMode) ? ' self|cat' : '') : $pre . $child[$textOfLinks] . $post;
						$itm .= ($debugMode) ? ' top' : '';
					}

					// it's a folder and it's below the top level 
					elseif ($isFolder && $inside) {
						$itm .= "<img src='media/style" . $theme . "/images/tree/folder.gif' alt='Folder' onclick=\"switchMenu(" . $child['id'] . ")\" />" .
						"&nbsp;<input type=\"checkbox\" class=\"pids\" id=\"check" . $child['id'] . "\" name=\"check\" value=\"" .
						$child['id'] . "\" />" . $pre . '<span class="document" style="color:' .
						$color . ';">&nbsp;&nbsp;' . $child[$textOfLinks] . ' (Template:' . $child['template'] . ')</span>' . $post .
						 (($debugMode) ? ' subfolder F' : '');
					}

					// it's a document inside a folder 
					else {
						$itm .= ($child['alias'] > '0' && !$selfAsLink && ($child['id'] == $modx->documentIdentifier)) ? $child[$textOfLinks] : "<img src='media/style" . $theme . "/images/tree/page-blank.gif' alt='Page' />&nbsp;<input type=\"checkbox\" class=\"pids\" id=\"check" . $child['id'] . "\" name=\"check\" value=\"" .
						$child['id'] . "\" />" . '<span style="color:' . $color . ';">&nbsp;&nbsp;' .
						$child[$textOfLinks] . ' (Template:' . $child['template'] . ')</span>';
						$itm .= ($debugMode) ? ' doc' : '';
					}
					$itm .= ($debugMode) ? "$useCategoryFolders $isFolder $itsEmpty" : '';

					// loop back through if the doc is a folder and has not reached the max levels 
					if ($isFolder && (($maxLevels == 0) || ($maxLevels > $listLevel +1))) {
						$itm .= MakeMap($modx, $child['id'], $listLevel +1, $description, $titleOfLinks, $maxLevels, true, $pre, $post, $selfAsLink, $ie, $activeLinkIDs, $topdiv, $topdivClass, $topnavClass, $subdiv, $subdivClass, $subnavClass, $hereClass, $useCategoryFolders, $categoryClass, false, '', $textOfLinks, $orderBy, $orderDesc, $debugMode);
					}

					if ($itm) {
						$output .= "<li$class>$itm</li>$ie";
						$class = '';
					}
				}
				$output .= "</ul>$ie";
				$output .= ($toplevel) ? (($topdiv) ? "</div>$ie" : "") : (($subdiv) ? "</div>$ie" : "");
			}
			return $output;
		}
		$MakeMap_Defined = true;
	}

	// return the output 
	return MakeMap($modx, $siteMapRoot, 0, false, $titleOfLinks, $maxLevels, true, $pre, $post, $selfAsLink, $ie, $activeLinkIDs, $topdiv, $topdivClass, $topnavClass, $subdiv, $subdivClass, $subnavClass, $hereClass, $useCategoryFolders, $categoryClass, false, '', $textOfLinks, $orderBy, $orderDesc, $debugMode);
}

/**
 * updateHeader - contains the common Update header html used in the module
 * 
 */
function updateHeader() {
	global $theme;
	global $siteURL;
	global $_lang;

	$output = '<html'.($modx->config['manager_direction'] == 'rtl' ? 'dir="rtl"' : '').' lang="'.$modx->config['manager_lang_attribute'].'"><head>
							<title>Update</title>
							<link rel="stylesheet" type="text/css" href="media/style' . $theme . '/style.css" />';
	$output.='				<style type="text/css"> 
							.topdiv {border:0;} 
							.subdiv {border:0;} 
							ul, li {list-style:none;} 
							</style>';
							
	$output .= ButtonCSS();
	$output .= '</head><body> 
					       <div class="subTitle" id="bttn"> 
								<span class="right"><img src="media/style' . $theme . '/images/_tx_.gif" width="1" height="5" alt="" /><br />' . $_lang['1C_module_title'] . '</span> 
					            <div class="bttnheight"><a id="Button5" onclick="document.location.href=\'index.php?a=106\';">
					                    <img src="media/style' . $theme . '/images/icons/close.gif" alt="" /> '.$_lang['1C_close'].'</a>
					            </div>
											
					        </div> 
								    				 
							<div class="sectionHeader">&nbsp;' . $_lang['1C_update_title'] . '</div> 
							<div class="sectionBody"> 
						    ';

	return $output;
}
?>
