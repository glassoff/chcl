<?php 
$style="background-color: #cccccc;
    
    margin-bottom: 4px;
padding:8px;
    border-style: solid;
    border-width: 1px;
    border-color: #4292c6";
    $templatevar='<select name="templatevar"><option ></option>';
    $tbl2 = $modx->dbConfig['dbase'] . "." . $modx->dbConfig['table_prefix'] . "site_tmplvars";
    $sql4 = "SELECT id, name FROM $tbl2 ORDER BY $tbl2.id DESC";
    $drop4 = $modx->dbQuery($sql4);
  while ($row = mysql_fetch_array($drop4)) {
    $templatevar.='<option value="'.$row['id'].'">'.$row['name'].'</option>';
  }
    $templatevar.='</select>';
    
        $templatevar2='<select name="deltemplatevarid"><option ></option>';
    $tbl2 = $modx->dbConfig['dbase'] . "." . $modx->dbConfig['table_prefix'] . "site_tmplvars";
    $sql4 = "SELECT id, name FROM $tbl2 ORDER BY $tbl2.id DESC";
    $drop4 = $modx->dbQuery($sql4);
  while ($row = mysql_fetch_array($drop4)) {
    $templatevar2.='<option value="'.$row['id'].'">'.$row['name'].'</option>';
  }
    $templatevar2.='</select>';
    
    
    $template='<select name="template"><option ></option>';
    $tbl2 = $modx->dbConfig['dbase'] . "." . $modx->dbConfig['table_prefix'] . "site_templates";
    $sql4 = "SELECT id, templatename FROM $tbl2 ORDER BY $tbl2.id DESC";
    $drop4 = $modx->dbQuery($sql4);
  while ($row = mysql_fetch_array($drop4)) {
    $template.='<option value="'.$row['id'].'">'.$row['templatename'].'</option>';
  }
    $template2=array();
    $tbl2 = $modx->dbConfig['dbase'] . "." . $modx->dbConfig['table_prefix'] . "site_templates";
    $sql4 = "SELECT id, templatename FROM $tbl2 ORDER BY $tbl2.id DESC";
    $drop4 = $modx->dbQuery($sql4);
    $aa="tostring1";
    $template2[$aa]="Blank template";
    $aa="tostring0";
    $template2[$aa]="Whithout template";
  while ($row = mysql_fetch_array($drop4)) {
    $aa="tostring".$row['id'];
    $template2[$aa]=$row['templatename'];
  }
    
    
    
    $idparent='<select name="parent"><option ></option><option value="0">0 || ROOT</option>';
    $tbl2 = $modx->dbConfig['dbase'] . "." . $modx->dbConfig['table_prefix'] . "site_content";
    $sql4 = "SELECT id, pagetitle FROM $tbl2 ORDER BY $tbl2.id ASC";
    $drop4 = $modx->dbQuery($sql4);
  while ($row = mysql_fetch_array($drop4)) {
    $idparent.='<option value="'.$row['id'].'">'.$row['id'].' || '.$row['pagetitle'].'</option>';
  }
    $idparent.='</select>';
    $tbl2 = $modx->dbConfig['dbase'] . "." . $modx->dbConfig['table_prefix'] . "documentgroup_names";
    $sql4 = "SELECT id, name FROM $tbl2 ORDER BY $tbl2.id DESC";
    $drop4 = $modx->dbQuery($sql4);
    $priweb=0;
    $permission="";
  while ($row = mysql_fetch_array($drop4)) {
    $priweb++;
    $permission.='<label><input name="private'.$priweb.'" type="checkbox" value="'.$row['id'].'"/>'.$row['name'].'</label><br />';
  }
  function ceroyes($take) {
  if ($take == 0) {
    return "yes";
  }
  else {
    return "no";
  }
  }
  function cerono($take) {
  if ($take == 0) {
    return "no";
  }
  else {
    return "yes";
  }
  }
    // echo $row["nombre"];
    // echo $row['apellido'];
    $folder_to_start = 0;
    $tbl =$modx->dbConfig['dbase'] . "." . $modx->dbConfig['table_prefix'] . "site_content";
    $sql = "SELECT id, pagetitle, menuindex, template, privateweb, published, deleted, hidemenu, parent  FROM $tbl WHERE parent = $folder_to_start ORDER BY menuindex ASC , pagetitle ASC ";
    $result = $modx->dbQuery($sql);
    $children = array();
    for($i=0;
    $i<$modx->recordCount($result);
  $i++) {
    array_push($children,$modx->fetchRow($result));
  }
    $menu = "";
    $childrenCount = count($children);
  if($children==false) {
    return 'No children to display..';
  }
    $a=0;
    $form ='<div style="background-color:#FF3600;
    font-family:Arial, Helvetica, sans-serif;
    font-size:25px;
    color:#ffffff;
    padding:8px"><strong>SuperFast</strong></div><div style="background-color:#d6e3ff;
    font-family:Arial, Helvetica, sans-serif;
    font-size:15px;
    color:#4292c6;
    padding:8px">Por Javier Arraiza, (xyzvisual) <a href="http://www.marker.es"> www.marker.es</a></div><div style="padding:8px;
    background-color:#eff3ff;
    font-family:Arial, Helvetica, sans-serif;
    font-size:14px;
    color:#1c3c53;
    font-weight:bold" ><form method="post" action=""><input name="enviado" type="hidden" value="yes" />';
    for($x=0;
    $x<$childrenCount;
  $x++) {
    $a++;
    $tbl =$modx->dbConfig['dbase'] . "." . $modx->dbConfig['table_prefix'] . "site_content";
    $sql = "SELECT id, pagetitle, menuindex, template, privateweb, published, deleted, hidemenu, parent FROM $tbl WHERE parent = ".$children[$x]['id']." ORDER BY menuindex ASC , pagetitle ASC ";
    $result = $modx->dbQuery($sql);
    $children2 = array();
    for($i=0;
    $i<$modx->recordCount($result);
  $i++) {
    array_push($children2,$modx->fetchRow($result));
  }
    $childrenCount2 = count($children2);
    $form.='<div style="'.$style.';margin-left:0px;" class="selectedheader">id '.$children[$x]['id'].' || '.$children[$x]['pagetitle'].'<input name="send'.$a.'" type="checkbox" value="'.$children[$x]['id'].'"/><div style="padding:3px;
    background-color:#FFFFFF;
    color:#4292c6;font-weight:normal">template :: '.$template2["tostring".$children[$x]['template']].' || private:: '.cerono($children[$x]['privateweb']).' || published:: '.cerono($children[$x]['published']).' || deleted:: '.cerono($children[$x]['deleted']).' || hidemenu:: '.cerono($children[$x]['hidemenu']).' || id parent:: '.$children[$x]['parent'].'</div></div>'."\n";
  if($children2!=false) {
    for($y=0;
    $y<$childrenCount2;
  $y++) {
    $a++;
    $tbl =$modx->dbConfig['dbase'] . "." . $modx->dbConfig['table_prefix'] . "site_content";
    $sql = "SELECT id, pagetitle, menuindex, template, privateweb, published, deleted, hidemenu, parent  FROM $tbl WHERE parent = ".$children2[$y]['id']." ORDER BY menuindex ASC , pagetitle ASC ";
    $result = $modx->dbQuery($sql);
    $children3 = array();
    for($i=0;
    $i<$modx->recordCount($result);
  $i++) {
    array_push($children3,$modx->fetchRow($result));
  }
    $childrenCount3 = count($children3);
    $form.='<div style="'.$style.';margin-left:20px;" class="selectedheader">id '.$children2[$y]['id'].' || '.$children2[$y]['pagetitle'].'<input name="send'.$a.'" type="checkbox" value="'.$children2[$y]['id'].'"/><div style="padding:3px;
    background-color:#FFFFFF;
    color:#4292c6;font-weight:normal">template :: '.$template2["tostring".$children2[$y]['template']].' || private:: '.cerono($children2[$y]['privateweb']).' || published:: '.cerono($children2[$y]['published']).' || deleted:: '.cerono($children2[$y]['deleted']).' || hidemenu:: '.cerono($children2[$y]['hidemenu']).' || id parent:: '.$children2[$y]['parent'].'</div></div>'."\n";
  if($children3!=false) {
    for($z=0;
    $z<$childrenCount3;
  $z++) {
    $a++;
    $tbl =$modx->dbConfig['dbase'] . "." . $modx->dbConfig['table_prefix'] . "site_content";
    $sql = "SELECT id, pagetitle, menuindex, template, privateweb, published, deleted, hidemenu, parent  FROM $tbl WHERE parent = ".$children3[$z]['id']." ORDER BY menuindex ASC , pagetitle ASC ";
    $result = $modx->dbQuery($sql);
    $children4 = array();
    for($i=0;
    $i<$modx->recordCount($result);
  $i++) {
    array_push($children4,$modx->fetchRow($result));
  }
    $childrenCount4 = count($children4);
    $form.='<div style="'.$style.';margin-left:40px;" class="selectedheader">id '.$children3[$z]['id'].' || '.$children3[$z]['pagetitle'].'<input name="send'.$a.'" type="checkbox" value="'.$children3[$z]['id'].'"/><div style="padding:3px;
    background-color:#FFFFFF;
    color:#4292c6;font-weight:normal">template :: '.$template2["tostring".$children3[$z]['template']].' || private:: '.cerono($children3[$z]['privateweb']).' || published:: '.cerono($children3[$z]['published']).' || deleted:: '.cerono($children3[$z]['deleted']).' || hidemenu:: '.cerono($children3[$z]['hidemenu']).' || id parent:: '.$children3[$z]['parent'].'</div></div>'."\n";
  if($children4!=false) {
    for($w=0;
    $w<$childrenCount4;
  $w++) {
    $a++;
    $tbl =$modx->dbConfig['dbase'] . "." . $modx->dbConfig['table_prefix'] . "site_content";
    $sql = "SELECT id, pagetitle, menuindex, template, privateweb, published, deleted, hidemenu, parent  FROM $tbl WHERE parent = ".$children4[$w]['id']." ORDER BY menuindex ASC , pagetitle ASC ";
    $result = $modx->dbQuery($sql);
    $children5 = array();
    for($i=0;
    $i<$modx->recordCount($result);
  $i++) {
    array_push($children5,$modx->fetchRow($result));
  }
    $childrenCount5 = count($children5);
    $form.='<div style="'.$style.';margin-left:60px;" class="selectedheader">id '.$children4[$w]['id'].' || '.$children4[$w]['pagetitle'].'<input name="send'.$a.'" type="checkbox" value="'.$children4[$w]['id'].'"/><div style="padding:3px;
    background-color:#FFFFFF;
    color:#4292c6;font-weight:normal">template :: '.$template2["tostring".$children4[$w]['template']].' || private:: '.cerono($children4[$w]['privateweb']).' || published:: '.cerono($children4[$w]['published']).' || deleted:: '.cerono($children4[$w]['deleted']).' || hidemenu:: '.cerono($children4[$w]['hidemenu']).' || id parent:: '.$children4[$w]['parent'].'</div></div>'."\n";
  if($children5!=false) {
    for($s=0;
    $s<$childrenCount5;
  $s++) {
    $a++;
    $tbl =$modx->dbConfig['dbase'] . "." . $modx->dbConfig['table_prefix'] . "site_content";
    $sql = "SELECT id, pagetitle, menuindex, template, privateweb, published, deleted, hidemenu, parent  FROM $tbl WHERE parent =".$children5[$s]['id']." ORDER BY menuindex ASC , pagetitle ASC ";
    $result = $modx->dbQuery($sql);
    $children6 = array();
    for($i=0;
    $i<$modx->recordCount($result);
  $i++) {
    array_push($children6,$modx->fetchRow($result));
  }
    $childrenCount6 = count($children6);
    $form.='<div style="'.$style.';margin-left:80px;" class="selectedheader">id '.$children5[$s]['id'].' || '.$children5[$s]['pagetitle'].'<input name="send'.$a.'" type="checkbox" value="'.$children5[$s]['id'].'"/><div style="padding:3px;
    background-color:#FFFFFF;
    color:#4292c6;font-weight:normal">template :: '.$template2["tostring".$children5[$s]['template']].' || private:: '.cerono($children5[$s]['privateweb']).' || published:: '.cerono($children5[$s]['published']).' || deleted:: '.cerono($children5[$s]['deleted']).' || hidemenu:: '.cerono($children5[$s]['hidemenu']).' || id parent:: '.$children5[$s]['parent'].'</div></div>'."\n";
  if($children6!=false) {
    for($p=0;
    $p<$childrenCount6;
  $p++) {
    $a++;
    $form.='<div style="'.$style.';margin-left:100px;" class="selectedheader">id '.$children6[$p]['id'].' || '.$children6[$p]['pagetitle'].'<input name="send'.$a.'" type="checkbox" value="'.$children6[$p]['id'].'"/><div style="padding: 3px;
    background-color: #FFFFFF;
    color: #4292c6;font-weight:normal">template :: '.$template2["tostring".$children6[$p]['template']].' || private:: '.cerono($children6[$p]['privateweb']).' || published:: '.cerono($children6[$p]['published']).' || deleted:: '.cerono($children6[$p]['deleted']).' || hidemenu:: '.cerono($children6[$p]['hidemenu']).' || id parent:: '.$children6[$p]['parent'].'</div></div>'."\n";
  }
  }
  }
  }
  }
  }
  }
  }
  }
  }
  }
    $form.='<div style="background-color:#f7b242;
    margin:8px;
    padding:12px;
    ">access public<br /><input name="publiced" type="checkbox" value="1"/></div>';
    $form.='<div style="background-color:#f7b242;
    margin:8px;
    padding:12px;
    ">access private groups<br />'.$permission.'</div>';
    $form.='<div style="background-color:#4292c6;
    margin:8px;
    padding:12px;
    ">template<br />'.$template.'</div>';
    $form.='<div style="background-color:#4292c6;
    margin:8px;
    padding:12px;
    ">published<br /><input name="publish" type="checkbox" value="1"/></div>';
    $form.='<div style="background-color:#4292c6;
    margin:8px;
    padding:12px;
    ">unpublished<br /><input name="unpublish" type="checkbox" value="0"/></div>';
    $form.='<div style="background-color:#f7b242;
    margin:8px;
    padding:12px;
    ">deleted<br /><input name="delete" type="checkbox" value="1"/></div>';
    $form.='<div style="background-color:#f7b242;
    margin:8px;
    padding:12px;
    ">undeleted<br /><input name="undelete" type="checkbox" value="0"/></div>';
    $form.='<div style="background-color:#4292c6;
    margin:8px;
    padding:12px;
    ">show menu<br /><input name="showmenu" type="checkbox" value="0"/></div>';
    $form.='<div style="background-color:#4292c6;
    margin:8px;
    padding:12px;
    ">hide menu<br /><input name="hidemenu" type="checkbox" value="1"/></div>';
    $form.='<div style="background-color:#f7b242;
    margin:8px;
    padding:12px;
    ">parent id (be carefoul select a logic and posible parent)<br />'.$idparent.'</div>';
    $form.='<div style="background-color:#4292c6;
    margin:8px;
    padding:12px;
    ">Default content<br />!Check if realy want to change the contents whith this new content!<br /><input name="allowcontent" type="checkbox" value="1"/><br/><textarea name="content" cols="40" rows="4"></textarea></div>';
    $form.='<div style="background-color:#f7b242;
    margin:8px;
    padding:12px;
    ">Template varable content<br />'.$templatevar.'<br /><textarea name="contenttemplatevar" cols="40" rows="4"></textarea></div>';
    
    $form.='<div style="background-color:#f7b242;
    margin:8px;
    padding:12px;
    ">Delete template varable content<br />'.$templatevar2.'<br /><input name="deltemplatevarcheck" type="checkbox" value="borrar"/></div>';
    $form.='<input style="background-color:#4292c6;
    margin:8px;
    padding:12px;
    border:none" name="enviar" type="submit" value="send" /></form></div>';
  
  if(isset($_POST['enviado'])&&$_POST['enviado']=="yes") {
    for($q=1;
    $q<=$a;
  $q++) {   $fields="";
  if(isset($_POST["send$q"])&&$_POST["send$q"]!="") {
  if(!isset($_POST['template'])||$_POST['template']=="") {
    $fields.="";
  }
  else {
    $fields.="template='".$_POST['template']."', ";
  }
  if(!isset($_POST['publish'])||$_POST['publish']=="") {
    $fields.="";
  }
  else {
    $fields.="published='".$_POST['publish']."', ";
  }
  if(!isset($_POST['unpublish'])||$_POST['unpublish']=="") {
    $fields.="";
  }
  else {
    $fields.="published='".$_POST['unpublish']."', ";
  }
  if(!isset($_POST['delete'])||$_POST['delete']=="") {
    $fields.="";
  }
  else {
    $fields.="deleted='".$_POST['delete']."', ";
  }
  if(!isset($_POST['undelete'])||$_POST['undelete']=="") {
    $fields.="";
  }
  else {
    $fields.="deleted='".$_POST['undelete']."', ";
  }
  if(!isset($_POST['showmenu'])||$_POST['showmenu']=="") {
    $fields.="";
  }
  else {
    $fields.="hidemenu='".$_POST['showmenu']."', ";
  }
  if(!isset($_POST['hidemenu'])||$_POST['hidemenu']=="") {
    $fields.="";
  }
  else {
    $fields.="hidemenu='".$_POST['hidemenu']."', ";
  }
  if(!isset($_POST['parent'])||$_POST['parent']=="") {
    $fields.="";
  }
  else {
    $fields.="parent='".$_POST['parent']."', ";
    if ($_POST['parent']!=0){
    $modx->db->update("isfolder='1'",$modx->getFullTableName('site_content'), "id=".$_POST['parent']."");
  }
  }
  if(!isset($_POST['allowcontent'])||$_POST['allowcontent']=="") {
    $fields.="";
  }
  else {
    $fields.="content='".$_POST['content']."', ";
  }
  if(!isset($_POST['templatevar'])||$_POST['templatevar']=="") {
    $iddeltempvar="";
  }
  else {
    $iddeltempvar=$_POST['templatevar'];
  }
  if(!isset($_POST['contenttemplatevar'])||$_POST['contenttemplatevar']=="") {
    $tempvarcontent="";
  }
  else {
    $tempvarcontent=$_POST['contenttemplatevar'];
  }
  if(!isset($_POST['publiced'])||$_POST['publiced']=="") {
    $hacerpublic="";
  }
  else {
    $hacerpublic=$_POST['publiced'];
  }
  if(!isset($_POST['deltemplatevarcheck'])||$_POST['deltemplatevarcheck']=="") {
    $deltemplatevarcheck="";
  }else{$deltemplatevarcheck=$_POST['deltemplatevarcheck'];}

  if(!isset($_POST['deltemplatevarid'])||$_POST['deltemplatevarid']=="") {
    $deltemplatevarid="";
  }else{
  $deltemplatevarid=$_POST['deltemplatevarid'];
  
  } 
  
    $access=array();
    $counted="";
    for ($as=1;
    $as<=$priweb;
  $as++) {
  if(!isset($_POST["private$as"])||$_POST["private$as"]=="") {
  }
  else {
    $access[]=$_POST["private$as"];
    $counted++;
  }
  }
    $fields=substr_replace($fields,"",-2);
  if($fields=="") {
  }
  else {
    $modx->db->update($fields,$modx->getFullTableName('site_content'), "id=".$_POST["send$q"]."");
  }
  if($tempvarcontent==""||$iddeltempvar=="") {
  }
  else {
    $tbl2 = $modx->dbConfig['dbase'] . "." . $modx->dbConfig['table_prefix'] . "site_tmplvar_contentvalues";
    $sql4 = "SELECT tmplvarid, contentid, value FROM $tbl2 WHERE ($tbl2.contentid=".$_POST["send$q"].")";
    $drop4 = $modx->dbQuery($sql4);
    $droplimit4 = $modx->recordCount($drop4);
    $resultados="";
    $row5=mysql_fetch_array($drop4);
    $valueactual=$row5['value'];
    
    
  
  if ($valueactual!="") {
    $modx->db->update("value='$tempvarcontent'",$modx->getFullTableName('site_tmplvar_contentvalues'), "contentid=".$_POST["send$q"]." AND tmplvarid=$iddeltempvar");
  }
  if ($valueactual=="") {
    $sql = "INSERT INTO ".$modx->getFullTableName('site_tmplvar_contentvalues')." (value,contentid,tmplvarid) VALUES ('$tempvarcontent',".$_POST["send$q"].",$iddeltempvar)";
    $rs = $modx->db->query($sql);
  }
  }
  if ($deltemplatevarcheck=="borrar"&&$deltemplatevarid!=""){
  $sql = "DELETE FROM ".$modx->getFullTableName('site_tmplvar_contentvalues')." WHERE contentid=".$_POST["send$q"]." AND tmplvarid=$deltemplatevarid";
    $rs = mysql_query($sql);
  }else{}
    for ($ff=0;
    $ff<$counted;
  $ff++) {
  if ($access[$ff] !="") {
    $sql = "DELETE FROM ".$modx->getFullTableName('document_groups')." WHERE document='".$_POST["send$q"]."'";
    $rs = mysql_query($sql);
    $modx->db->update("privateweb='1',privatemgr='1'",$modx->getFullTableName('site_content'), "id=".$_POST["send$q"]."");
 
    $sql = "INSERT INTO ".$modx->getFullTableName('document_groups')." (document_group,document) VALUES (".$access["$ff"].", ".$_POST["send$q"].")";
    $rs = mysql_query($sql);
  }
  }
  if ($hacerpublic!="") {
    $sql = "DELETE FROM ".$modx->getFullTableName('document_groups')." WHERE document='".$_POST["send$q"]."'";
    $rs = mysql_query($sql);
    $modx->db->update("privateweb='0',privatemgr='0'",$modx->getFullTableName('site_content'), "id=".$_POST["send$q"]."");
  }
  }
  else {
  }
  }
  $modx->sendRedirect("index.php?a=106");}
  else {
  }
    return $form;

?>
