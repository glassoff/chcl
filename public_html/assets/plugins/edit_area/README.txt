/**
 * @name       EditArea Plugin for MODx
 * @purpose    Replaces standard textarea with EditArea (c) C.Dolivet
 * @status     UAT 
 * @version    0.3.3 - 12/12/2007
 * @revision   42
 * @EditArea   Packaged version: 0.7.0.2
 * @SVN        http://svn.ackwa.fr/modx/assets/plugins/edit_area  (read-only access for modx/modx)
 *
 * @required   MODx      0.9.5 Rev.2000+
 *             EditArea  0.7.0.2   
 *
 * @confirmed  MODx      0.9.6.1 Rev.3118
 *
 * @author     Gildas       <modx@ackwa.fr>
 *             pixelchutes  <http://www.pixelchutes.com>
 *               
 * @copyright  Copyright (c) 2006-2007 www.ackwa.fr
 * @license    GNU General Public License 2.0
 *
 * @see        http://www.cdolivet.net/index.php?page=editArea
 *
 * @install    See README.txt
 * @history    See plugin_history.txt
 * @todo       See plugin_todo.txt
 * @bugs       See plugin_bugs.txt
 */

INSTALLATION
------------

    - Upload /assets/plugins/edit_area

        NOTE: If needed, chmod 755 for folders and chmod 644 for *.*

    - Create a new plugin (or edit if already exists)
    
        Title       : EditArea
        Description : <strong>0.3.3</strong> - Allows text formatting, search and replace and real-time syntax highlighting

    - Copy and Paste ALL of the following into "Plugin configuration" on the Configuration tab:
    
	    &eadbg=Debug Enabled?;list;true,false;false &font_size=Font Size;list;8,9,10,11,12;9 &defaultHeight=Initialize editor height;list;300px,400px,500px,600px,700px;500px &min_height=Minimum editor height;list;200,300,400;400 &start_highlight=Initialize with highlighting enabled?;list;true,false;true &allow_toggle=Allow editor toggling?;list;true,false;true &allow_resize=Allow editor resizing?;list;y,n;y &fullscreen=Initialize editor in fullscreen mode?;list;true,false;false &replace_tab_with_spaces=Replace tab with spaces?;list;// No,/* Yes */;/* Yes */ &tab_as_spaces=How many spaces per tab?;list;3,4,5;4 &plugins=Active Plugins;string;modx &catchunload=Try/Catch on Unload?;list;yes,no;yes

    - If you want to add support for the PHP compressor, you need to add this "Plugin configuration": 
    
        &compressor=PHP Compressor Enabled?;list;0,1;0 
   
    - Select one or more system events from this list on the System Events tab:

        OnChunkFormRender  (Chunk    editor - html highlighting)
        OnDocFormRender    (Document editor - html highlighting)
        OnModFormRender    (Module   editor - php  highlighting)
        OnPluginFormRender (Plugin   editor - php  highlighting)
        OnSnipFormRender   (Snippet  editor - php  highlighting)
        OnTempFormRender   (Template editor - html highlighting)


CORE HACKS (optional)
----------
    - If you want to use EditArea to view and Edit file in the FileManager, you need to follow these steps :
    
    1 - To invoke a new "virtual" event add these lines at the beginning of manager/includes/footer.inc.php  :

           // Ackwa Hack : Create & Invoke a virtual "OnManagerPageRender" event
           $modx->pluginEvent['OnManagerPageRender'] = array('EditArea');
           $out = $modx->invokeEvent("OnManagerPageRender", array('action' => (isset($action) ? ($action + 0) : 0)));
           if (isset($out) && is_array($out)) echo implode('', $out);

    2 - Then because Javascript submit() bypass the "onsubmit" handler, you need to modify manager/actions/file.dynamic.php.
      - Add this line just before the </form> (line 552)
    
           <input type="submit" name="save" style="display:none">

      - Modify the "onclick" handler of the save link (ie. Button1 - line 559) to replace :
    
           document.editFile.submit(); 
    
        by :
    
           document.editFile.save.click();


NOTES
-----
    - Additional Notes/To Do/Change Log/etc can be found in /assets/plugins/edit_area/core
