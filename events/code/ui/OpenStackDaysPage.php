<?php
/**
 * Copyright 2014 Openstack Foundation
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * http://www.apache.org/licenses/LICENSE-2.0
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 **/
/**
 * Defines the OpenStackDaysPage page type
 */
class OpenStackDaysPage extends Page {
    private static $db = array(
        'AboutDescription' => 'HTMLText',
        'HostIntroAndFAQs' => 'HTMLText',
        'ToolkitDesc'      => 'HTMLText',
    );

    private static $has_one = array();

    private static $has_many = array(
        'AboutVideos'        => 'OpenStackDaysVideo.About',
        'HeaderPics'         => 'OpenStackDaysImage',
        'OfficialGuidelines' => 'OpenStackDaysDoc.OfficialGuidelines',
        'PlanningTools'      => 'OpenStackDaysDoc.PlanningTools',
        'Artwork'            => 'OpenStackDaysDoc.Artwork',
        'Collaterals'        => 'OpenStackDaysVideo.Collaterals',
        'Media'              => 'OpenStackDaysDoc.Media',
        'Videos'             => 'OpenStackDaysVideo', //dummy
        'Docs'               => 'OpenStackDaysDoc', //dummy
    );

    private static $many_many_extraFields = array();

    function getCMSFields() {
        $fields = parent::getCMSFields();
        $fields->insertBefore(new Tab('About'), 'Settings');
        $fields->insertBefore(new Tab('Host'), 'Settings');

        // header
        $fields->removeByName('Content');
        $fields->addFieldToTab(
            'Root.Main',
            $uploadField = new UploadField('HeaderPics','Header Pictures')
        );
        $uploadField->setAllowedMaxFileNumber(10);
        $uploadField->setFolderName('openstackdays');

        // About
        $fields->addFieldToTab(
            'Root.About',
            $about_desc = new HtmlEditorField('AboutDescription','Intro Text',$this->AboutDescription)
        );
        $config = new GridFieldConfig_RecordEditor(10);
        $config->getComponentByType('GridFieldDataColumns')->setDisplayFields(
            array('YoutubeID' => 'YoutubeID', 'Caption' => 'Caption', 'Active' => 'Active')
        );
        $config->addComponent(new GridFieldSortableRows('SortOrder'));
        $fields->addFieldToTab(
            'Root.About ',
            new GridField('AboutVideos', 'Videos', $this->AboutVideos(), $config)
        );

        // Host
        $fields->addFieldToTab(
            'Root.Host',
            $about_desc = new HtmlEditorField('HostIntroAndFAQs','Intro Text',$this->HostIntroAndFAQs)
        );

        $fields->addFieldToTab(
            'Root.Host',
            $about_desc = new HtmlEditorField('ToolkitDesc','Toolkit Text',$this->ToolkitDesc)
        );

        $fields->addFieldToTab(
            'Root.Host',
            $guidelines = new UploadField('OfficialGuidelines', 'Official Guidelines')
        );
        $guidelines->setFolderName('openstackdays');
        $guidelines->setField('Category','OfficialGuidelines');

        $fields->addFieldToTab(
            'Root.Host',
            $tools = new UploadField('PlanningTools', 'Planning Tools')
        );
        $tools->setFolderName('openstackdays');
        $tools->setField('Category','PlanningTools');
        $tools->setAttribute('relationAutoSetting',false);

        $fields->addFieldToTab(
            'Root.Host',
            $artwork = new UploadField('Artwork', 'Artwork For Print')
        );
        $artwork->setFolderName('openstackdays');
        $artwork->setField('Category','Artwork');

        /*$fields->addFieldToTab(
            'Root.Host',
            $collaterals = new UploadField('Collaterals', 'Video / Presentations / Collateral')
        );
        $collaterals->setFolderName('openstackdays');
        $collaterals->setField('Category','Collaterals');*/

        $config = new GridFieldConfig_RecordEditor(10);
        $config->getComponentByType('GridFieldDataColumns')->setDisplayFields(
            array('YoutubeID' => 'YoutubeID', 'Caption' => 'Caption', 'Active' => 'Active')
        );
        $config->addComponent(new GridFieldSortableRows('SortOrder'));
        $fields->addFieldToTab(
            'Root.Host ',
            new GridField('Collaterals', 'Video / Presentations / Collateral', $this->Collaterals(), $config)
        );

        $fields->addFieldToTab(
            'Root.Host',
            $media = new UploadField('Media', 'PR / Media')
        );
        $media->setFolderName('openstackdays');
        $media->setField('Category','Media');

        return $fields;
	}

}
/**
 * Class OpenStackDaysPage_Controller
 */
class OpenStackDaysPage_Controller extends Page_Controller {

    private $event_manager;

	private static $allowed_actions = array (
	);

	function init() {
	    parent::init();
        Requirements::css('events/css/openstackdays.css');
        $this->buildEventManager();
	}

    function buildEventManager() {
        $this->event_manager = new EventManager(
            $this->repository,
            new EventRegistrationRequestFactory,
            null,
            new SapphireEventPublishingService,
            new EventValidatorFactory,
            SapphireTransactionManager::getInstance()
        );
    }

    function FutureOpenstackDaysEvents($num) {
        $filter_array = array('EventEndDate:GreaterThanOrEqual'=> date('Y-m-d'));
        $filter_array['EventCategory'] = 'Openstack Days';
        $pulled_events = EventPage::get()->filter($filter_array)->sort(array('EventStartDate'=>'ASC','EventContinent'=>'ASC'))->limit($num);

        return $pulled_events;
    }

    function EventsYearlyCountText() {
        $this_year = date('Y');
        $event_count = EventPage::get()->where("YEAR(EventStartDate) = '".$this_year."' AND EventPage.EventCategory = 'Openstack Days'")
                                       ->sort(array('EventStartDate'=>'ASC','EventContinent'=>'ASC'))->count();


        return "There are more than <strong>".$event_count." OpenStack Days</strong> scheduled for ".$this_year;
    }

    public function getFeaturedEvents(){
        return FeaturedEvent::get('FeaturedEvent');
    }

}
