<?php 
$howMany = isset($howMany) ? $howMany : 10;
function stripAlias($alias) {
    $alias = strtolower ( $alias );
    if(strtoupper($modx->config['etomite_charset'])=='UTF-8') $alias = utf8_decode($alias);
    $alias = strtr($alias, array(chr(196) => 'Ae', chr(214) => 'Oe', chr(220) => 'Ue', chr(228) => 'ae', chr(246) => 'oe', chr(252) => 'ue', chr(223) => 'ss'));
    $alias = strip_tags($alias); 
    $alias = 
    //$alias = strtolower($alias); 
    $alias = preg_replace('/&.+?;/', '', $alias); // kill entities 
    $alias = preg_replace('/[^\.%A-Za-z0-9 _-]/', '', $alias); 
    $alias = preg_replace('/\s+/', '_', $alias); 
    $alias = preg_replace('|-+|', '-', $alias); 
    $alias = trim($alias, '-'); 
    return $alias;
}
    $templatevar='<select name="templatevar"><option ></option>';
    $tbl2 = $modx->dbConfig['dbase'] . "." . $modx->dbConfig['table_prefix'] . "site_tmplvars";
    $sql4 = "SELECT id, name FROM $tbl2 ORDER BY $tbl2.id DESC";
    $drop4 = $modx->dbQuery($sql4);
while ($row = mysql_fetch_array($drop4)) {
    $templatevar.='<option value="'.$row['id'].'">'.$row['name'].'</option>';
}
    $templatevar.='</select>';    
    $template='<select name="template"><option ></option>';
    $tbl2 = $modx->dbConfig['dbase'] . "." . $modx->dbConfig['table_prefix'] . "site_templates";
    $sql4 = "SELECT id, templatename FROM $tbl2 ORDER BY $tbl2.id DESC";
    $drop4 = $modx->dbQuery($sql4);
while ($row = mysql_fetch_array($drop4)) {
    $template.='<option value="'.$row['id'].'">'.$row['templatename'].'</option>';
}

  $templatearray=array();
    $tbl2 = $modx->dbConfig['dbase'] . "." . $modx->dbConfig['table_prefix'] . "site_templates";
    $sql4 = "SELECT id, templatename FROM $tbl2 ORDER BY $tbl2.id DESC";
    $drop4 = $modx->dbQuery($sql4);
while ($row = mysql_fetch_array($drop4)) {
    $templatearray[$row['id']]=$row['templatename'];
}

    $template.='</select>';
    $idparent='<select name="parent" value="" ><option >0 || root of site</option>';
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
$form ='<style type="text/css">
.former {
    margin:7px;
    border-color#ffffff;
    border-style:solid;
    border-width:1px;
}
</style>
<div style="background-color:#4292c6;
    font-family:Arial, Helvetica, sans-serif;
    font-size:25px;
    color:#ffffff;
    padding:8px"><strong>SuperPage</strong></div><div style="padding:8px;
    background-color:#4292c6;
    font-family:Arial, Helvetica, sans-serif;
    font-size:14px;
    color:#1c3c53;
    font-weight:bold"><form method="post" action=""><input name="enviado" type="hidden" value="yes" />';
    $a=0;
    for($x=1;$x<=$howMany;$x++) {
        $a++;
        $form.='<input class="former" name="page'.$x.'" type="text" />Page name '.$x.'<br />';
    }
    $form.='<div style="background-color:#4292c6;
    margin:8px;
    padding:12px;">template<br/>'.$template.'</div>';
    $form.='<div style="background-color:#f7b242;
    margin:8px;
    padding:12px;
    ">access public<br /><input name="publiced" type="checkbox" value="0"/></div>';
    $form.='<div style="background-color:#f7b242;
    margin:8px;
    padding:12px;
    ">access private groups<br />'.$permission.'</div>';
    $form.='<div style="background-color:#4292c6;
    margin:8px;
    padding:12px;
    ">published<br/><input name="publish" type="checkbox" value="1"/></div>';
    $form.='<div style="background-color:#f7b242;
    margin:8px;
    padding:12px;
    ">deleted<br/><input name="delete" type="checkbox" value="1"/></div>';
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
    $form.='<input style="background-color:#4292c6;
    margin:8px;
    padding:12px;
    border:none" name="enviar" type="submit" value="send" /></form></div>';


  if(isset($_POST['enviado'])&&$_POST['enviado']=="yes") {
    for($q=1;$q<=$a;$q++) {
  if(isset($_POST["page$q"])&&$_POST["page$q"]!="") {
  $pagetitle=mysql_escape_string($_POST["page$q"]);
if(!isset($_POST['template'])||$_POST['template']=="") {
    $template="";
}
else {
    $template=$_POST['template'];
}
if(!isset($_POST['publish'])||$_POST['publish']=="") {
    $published=0;
}
else {
    $published=1;
}

if(!isset($_POST['delete'])||$_POST['delete']=="") {
    $delete=0;
}
else {
    $delete=1;
}

if(!isset($_POST['hidemenu'])||$_POST['hidemenu']=="") {
    $hidemenu=0;
}
else {
    $hidemenu=1;
}
if(!isset($_POST['parent'])||$_POST['parent']=="0 || root of site") {
    $parent=0;
}
else {
    $parent=$_POST['parent'];
    $modx->db->update("isfolder='1'",$modx->getFullTableName('site_content'), "id=".$parent."");
    
}
if(!isset($_POST['allowcontent'])||$_POST['allowcontent']=="") {
    $content="";
}
else {
    $content=mysql_escape_string($_POST['content']);
}
if(!isset($_POST['publiced'])||$_POST['publiced']=="") {
    $hacerpublic="";
}
else {
    $hacerpublic=$_POST['publiced'];
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
    if($pagetitle=="") {
}
else {
$healthy = array("б", "й", "н","у","ъ","с","ь"," ","Б","Й","Н","У","Ъ","С","Ь");
$yummy  = array("a", "a", "i","o","u","n","u","_","a","e","i","o","u","n","u");
$alias = str_replace($healthy, $yummy, $pagetitle);
$alias = stripAlias(strtolower(trim($alias))); 
$sql = "INSERT INTO ".$modx->getFullTableName('site_content')." (pagetitle,alias,template,published,deleted,hidemenu,parent,content) VALUES ('".$pagetitle."','".$alias."','".$template."','".$published."','".$delete."','".$hidemenu."','".$parent."','".$content."')";
$rs = $modx->db->query($sql);
}
}else{}
$id2 = $modx->db->getInsertId();
for ($ff=0;
    $ff<$counted;
  $ff++) {
  if ($access[$ff] !="") {
    $sql = "DELETE FROM ".$modx->getFullTableName('document_groups')." WHERE document='".$id2."'";
    $rs = mysql_query($sql);
    $modx->db->update("privateweb='1',privatemgr='1'",$modx->getFullTableName('site_content'), "id=".$id2."");
    for ($ff=0;
    $ff<$counted;
$ff++) {
    $sql = "INSERT INTO ".$modx->getFullTableName('document_groups')." (document_group,document) VALUES (".$access["$ff"].", ".$id2.")";
    $rs = mysql_query($sql);
}
}}}
if ($hacerpublic!="") {
    $sql = "DELETE FROM ".$modx->getFullTableName('document_groups')." WHERE document='".$id2."'";
    $rs = mysql_query($sql);
    $modx->db->update("privateweb='0',privatemgr='0'",$modx->getFullTableName('site_content'), "id=".$id2."");
}

}



echo $form;

 

?>
