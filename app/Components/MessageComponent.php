<?php 
namespace App\Components;

class MessageComponent {
    public static $initialized = false;
    
    public static function init() {
        if (!self::$initialized) {
            self::$initialized = true;
            self::registerAssets();
        }
    }
    
    private static function registerAssets() {
    }
    
    public static function render($message, $loggedInProfileId) {
        //self::init();
        
        if (isset($message->senderProfile->id) && $message->senderProfile->id != 0) {            
            if ($message->senderProfile->id === $loggedInProfileId){
                // Outgoing message
                include VIEWS_PATH .'/components/outgoingMessage.php';
            }else{
                // Incoming message
                include VIEWS_PATH .'/components/incommingMessage.php';
            }
        }
    }
}

?>