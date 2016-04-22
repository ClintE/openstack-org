<?php


class PresentationCategory extends DataObject
{

    private static $db = array(
        'Title'          => 'Varchar',
        'Description'    => 'Text',
        'SessionCount'   => 'Int',
        'AlternateCount' => 'Int',
        'VotingVisible'  => 'Boolean',
        'ChairVisible'   => 'Boolean',
        'Code'           => 'Varchar(5)',
    );

    private static $defaults = array(
        'VotingVisible' => true,
        'ChairVisible' => true
    );

    private static $has_one = array(
        'Summit' => 'Summit'
    );

    private static $has_many = array(
        'ChangeRequests' => 'SummitCategoryChange'
    );

    private static $belongs_many_many = array(
        'TrackChairs'   => 'SummitTrackChair',
        'CategoryGroup' => 'PresentationCategoryGroup'
    );

    private static $summary_fields = array(
        'Title' => 'Title'
    );

    private static $searchable_fields = array(
        'Title'
    );

    public function getCMSFields()
    {
        return FieldList::create(TabSet::create('Root'))
            ->text('Title')
            ->text('Code','Code','',5)
            ->textarea('Description')
            ->numeric('SessionCount', 'Number of sessions')
            ->numeric('AlternateCount', 'Number of alternates')
            ->checkbox('VotingVisible', "This category is visible to voters")
            ->checkbox('ChairVisible', "This category is visible to track chairs")
            ->hidden('SummitID', 'SummitID');
    }

    protected function onAfterWrite() {
        parent::onAfterWrite();
        $this->Summit()->LastEdited = SS_Datetime::now()->Rfc2822();
        $this->Summit()->write();
    }

    protected function validate()
    {
        $valid = parent::validate();
        if(!$valid->valid()) return $valid;

        $summit_id = isset($_REQUEST['SummitID']) ?  $_REQUEST['SummitID'] : $this->SummitID;

        $summit   = Summit::get()->byID($summit_id);

        if(!$summit){
            return $valid->error('Invalid Summit!');
        }

        $count = intval(PresentationCategory::get()->filter(array('SummitID' => $summit->ID, 'Title' => trim($this->Title), 'ID:ExactMatch:not' => $this->ID))->count());

        if($count > 0)
            return $valid->error(sprintf('Presentation Category "%s" already exists!. please set another one', $this->Title));

        return $valid;
    }

    public function getFormattedTitleAndDescription()
    {
        return '<h4 class="category-label">' . $this->Title . '</h4> <p>' . $this->Description . '</p>';
    }

    public function getCategoryGroups() {
        return $this->CategoryGroup();
    }

    /**
     * @param int $member_id
     * @return int
     */
    public function isTrackChair($member_id)
    {
        return $this->exists()? intval($this->TrackChairs()->filter('MemberID', $member_id)->count()):0;
    }

    public function MemberList($memberid)
    {

        // See if there's a list for the current member
        $MemberList = SummitSelectedPresentationList::get()->filter(array(
            'MemberID' => $memberid,
            'CategoryID' => $this->ID
        ))->first();

        // if a selection list doesn't exist for this member and category, create it
        if (!$MemberList && $this->isTrackChair($memberid)) {
            $MemberList = new SummitSelectedPresentationList();
            $MemberList->ListType = 'Individual';
            $MemberList->CategoryID = $this->ID;
            $MemberList->MemberID = $memberid;
            $MemberList->write();
        }

        if ($MemberList) {
            return $MemberList;
        }


    }

    public function GroupList()
    {

        // See if there's a list for the group
        $GroupList = SummitSelectedPresentationList::get()->filter(array(
            'ListType' => 'Group',
            'CategoryID' => $this->ID
        ))->first();

        // if a group selection list doesn't exist for this category, create it
        if (!$GroupList) {
            $GroupList = new SummitSelectedPresentationList();
            $GroupList->ListType = 'Group';
            $GroupList->CategoryID = $this->ID;
            $GroupList->write();
        }

        return $GroupList;

    }


    /**
     * @param Member $member
     * @return boolean
     */
    public function canView($member = null)
    {
        return Permission::check("ADMIN") || Permission::check("ADMIN_SUMMIT_APP") || Permission::check("ADMIN_SUMMIT_APP_SCHEDULE");
    }

    /**
     * @param Member $member
     * @return boolean
     */
    public function canEdit($member = null)
    {
        return Permission::check("ADMIN") || Permission::check("ADMIN_SUMMIT_APP") || Permission::check("ADMIN_SUMMIT_APP_SCHEDULE");
    }
}
