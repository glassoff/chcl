/**********************************/
/* [HyperFlexSearchForm:module#3] */
/**********************************/

        // we show the warning about the words excluded from search
        $SearchForm .= '<br /><span class="FSF_Warning">'.$searchWarning_t.'</span>';
        
        // Stabilo snippet management
        
        // query to check the existence of the plugin "HyperStabilo"
        $sql_checkPlugin_t = "SELECT `id` FROM ".$tbl_pgn_t." WHERE `name`='HyperStabilo'";
        $sql_checkPlugin_t .= " OR `name`='Stabilo';"; // for compatibility reason
        $rs_checkPlugin_i = $modx->db->query($sql_checkPlugin_t);
        // if the snippet exists
        if (mysql_num_rows($rs_checkPlugin_i) > 0) {
            $stabiloLink_t = '?search='.urlencode($searchString);
        } else {
            $stabiloLink_t = '';
        }
      
/***********************************/
/* [/HyperFlexSearchForm:module#3] */
/***********************************/