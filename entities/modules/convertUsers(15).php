<?php 
$sql = "SELECT wu.id,wu.username,wua.email FROM modx_web_users wu 
LEFT JOIN modx_web_user_attributes wua ON (wu.id=wua.internalKey)
WHERE (wua.email != wu.username)";

$result = $modx->dbQuery($sql);
if ($modx->recordCount($result)>0){
    while($row = $modx->db->getRow($result)){
        if ($row['email']){
            $sql = "UPDATE modx_web_users SET username='".$row['email']."' WHERE (id='".$row['id']."')";
            $resultUpdate = $modx->dbQuery($sql);
            if ($resultUpdate){
                echo "update ok for " . $row['username'] . " set to " . $row['email'] . "<br>";
            }
            else{
                echo "ERROR update for " . $row['username'] . "<br>";
            }
        }
        else{
            echo "no email <br>";
        }
    }
}
else{
    echo "not found users for convert";
}
?>
