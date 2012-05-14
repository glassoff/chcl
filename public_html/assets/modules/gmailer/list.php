<!--p>Сохранить список как <input type="text" name="list-name" value="" /></p>
<p>Загрузить список 
<select name="lists">
	<option value="0">По умолчанию</option>
</select></p-->
<script>
window.addEvent('domready', function(){
	var tolistCount = $$('.tolist-checkbox').length; 
	checkListAll();
	showCount();
	$$('input[name=tolistall]').addEvent('change', function(){
		$$('.tolist-checkbox').set('checked', this.get('checked'));
		showCount();		
	});

	$$('.tolist-checkbox').addEvent('change', function(){
		checkListAll();
		showCount();
	});
	function showCount(){
		var listCount = $$('.tolist-checkbox:checked').length;
		
		$('list-count').set('text', listCount);
	}
	function checkListAll(){
		if($$('.tolist-checkbox:checked').length == tolistCount){
			$$('input[name=tolistall]').set('checked', true);
		}
		else{
			$$('input[name=tolistall]').set('checked', false);
		}
	}

});
function changeSort(by){
	$$('input[name=sortby]').set('value', by);
	postForm('changeSort');
}
</script>
<?php

$slist = '';

$sortby = isset($_POST['sortby']) && $_POST['sortby'] ? $_POST['sortby'] : "users.id";
$sortto = isset($_POST['sortto']) && $_POST['sortto'] ? $_POST['sortto'] : "DESC";
	
if($action=='changeSort'){
	if($sortto=='ASC') $sortto = 'DESC';
	elseif($sortto=='DESC') $sortto = 'ASC';
}

$sortsql = "ORDER BY $sortby $sortto";

$sql = "SELECT users.*, info.*, list.unsubscribe FROM modx_web_users users 
		LEFT JOIN modx_web_user_attributes info ON (users.id = info.internalKey)
		LEFT JOIN modx_temailinglist list ON (list.internalKey = users.id) WHERE (info.subscribe='1') $sortsql";

$result = $modx->db->query($sql);

$slist .= '
<input type="hidden" name="sortby" value="'.$sortby.'"/>
<input type="hidden" name="sortto" value="'.$sortto.'"/>

<table width="100%" border="0">
	<tr>
		<td class="gridHeader"><input type="checkbox" name="tolistall"/></td>
		<td class="gridHeader"><a onclick="changeSort(\'users.id\');return false;" href="#">id</a></td>
		<td class="gridHeader"><a onclick="changeSort(\'info.fname, info.sname, info.lname, info.type, info.company\');return false;" href="#">Название</a></td>
		<td class="gridHeader"><a onclick="changeSort(\'info.email\');return false;" href="#">Email</a></td>
		<!--td class="gridHeader"></td-->		
	</tr>
';

$i = 0;
while($row = $modx->db->getRow($result)){
	if(!$row['email'])
		continue;
	if($i%2)
		$tdClass = 'gridItem';
	else
		$tdClass = 'gridAltItem';

	$checked = 'checked';
	if($row['unsubscribe']=='1')
		$checked = '';
		
	$slist .= '
		<tr>
			<td class="'.$tdClass.'"><input class="tolist-checkbox" value="'.$row['internalKey'].'" '.$checked.' type="checkbox" name="tolist[]"/></td>
			<td class="'.$tdClass.'">'.$row['internalKey'].'</td>
			<td class="'.$tdClass.'">'.$row['fname'].' '.$row['sname'].' '.$row['lname'].' '.$row['type'].' '.$row['company'].'</td>
			<td class="'.$tdClass.'">'.$row['email'].'</td>
			<!--td class="'.$tdClass.'"></td-->
		</tr>
	';

	$i++;
}

$slist .= '
</table>
';

echo $slist;

?>