<?php

class SummitSchedPageDesign extends SummitPage {
    private static $db = array (
        'SchedEmbedURL' => 'Text'
    );
        
    public function getCMSFields() {
        $fields = parent::getCMSFields();
                
        $fields->addFieldToTab('Root.Main', new TextField('SchedEmbedURL','Enter The Sched Embed URL'), 'Content');
        
        return $fields;    

    }    
    
    
}


class SummitSchedPageDesign_Controller extends SummitPage_Controller {

    public function init() {
        parent::init();           
	}
}