<?php
require_once('JPush/JPush.php');

define("JPUSH_APP_KEY", "c5e990b1c872e42f0a2c555b");
define("JPUSH_MASTER_SECRET", "27a17e91c8686b30914c1538");

function push_to_client($alias, $time){
    $client = new JPush(JPUSH_APP_KEY, JPUSH_MASTER_SECRET);
    try{
        $result = $client->push()
            ->setPlatform('all')
            ->addAlias($alias)
			->setMessage("$time", 'test title', 'test type', array("time"=>$time))
            ->setOptions(null, 60)
            ->send();
    }catch (Exception $e){
        return false;
    }
    return true;
}

?>