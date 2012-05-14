<?php 
	$action = isset($_POST['action']) ? $_POST['action'] : '';
	include_once('inc/header.php');
?>
<?php
echo ' 
        <div class="subTitle" id="bttn"> 
                <span class="right"><img src="media/style' . $theme . '/images/_tx_.gif" width="1" height="5" alt="" /><br />Рассылка писем</span> 
                <div class="bttnheight"><a id="Button5" onclick="document.location.href=\'index.php?a=106\';">
                    <img src="media/style' . $theme . '/images/icons/close.gif" alt="" /> Закрыть</a>
                </div> 
                <div class="stay"></div> 
        </div>';
?>         


<div class="sectionHeader">&nbsp;Рассылка новостей</div>
<div class="sectionBody"> 
	<div class="tab-pane" id="docManagerPane">
           <script type="text/javascript"> 
                tpResources = new WebFXTabPane( document.getElementById( "docManagerPane" ) ); 
           </script>
           
		<div class="tab-page" id="tabTemplates">  
			<h2 class="tab">Отправка</h2>  
			<script type="text/javascript">tpResources.addTabPage( document.getElementById( "tabTemplates" ) );</script>
			<?php
				switch($action) 
				{
					//send mail
					case 'send':
						if($_POST['newsletter']){
							include_once('mail.php');
							break;
						}
						else{
							echo '<div style="color:red;">Вы не выбрали письмо для отправки</div>';
						}
					//main page - mail setup
					default:				
						include_once('mailsetup.php');
						break;
				}			 
			?> 
		</div>
           
		<div class="tab-page" id="tabMailingList">  
		        <h2 class="tab">Список получателей</h2>  
		        <script type="text/javascript">tpResources.addTabPage( document.getElementById( "tabMailingList" ) );</script> 
		        <?php 
		        	//echo '<iframe id="editor" src="' . $path . 'edit.php" frameborder=0></iframe>';
		        	include_once('list.php'); 
		        ?>
		</div>
		<div class="tab-page" id="tabMailConstructor">  
		        <h2 class="tab">Конструктор</h2>  
		        <script type="text/javascript">tpResources.addTabPage( document.getElementById( "tabMailConstructor" ) );</script> 
		        <?php 
		        	//echo '<iframe id="editor" src="' . $path . 'edit.php" frameborder=0></iframe>';
		        	include_once('constructor.php'); 
		        ?>
		</div>
		           	
	</div>
</div> 

<?php 
	//$action = isset($_POST['action']) ? $_POST['action'] : '';
	//include_once('inc/header.php');

	// action directive
	/*switch($action) 
	{
		//send mail
		case 'send':			
			include_once('mail.php');
			break;

		//edit mailing list
		case 'edit':			
			//include_once('edit.php');
			echo '<iframe id="editor" src="' . $path . 'edit.php" frameborder=0></iframe>';
			break;

		//main page - mail setup
		default:				
			include_once('mailsetup.php');
			break;
	}*/

	include_once('inc/footer.php');
?>

