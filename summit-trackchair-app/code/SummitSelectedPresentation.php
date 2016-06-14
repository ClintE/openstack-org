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
class SummitSelectedPresentation extends DataObject
{

    /**
     * @var array
     */
    private static $db = [
        'Order' => 'Int',
    ];

    /**
     * @var array
     */
    private static $has_one = [
        'SummitSelectedPresentationList' => 'SummitSelectedPresentationList',
        'Presentation' => 'Presentation',
        'Member' => 'Member'
    ];

    /**
     * @return int
     */
    public function PresentationPosition()
    {

        $Presentations = SummitSelectedPresentation::get()->filter([
            'SummitSelectedPresentationListID' => $this->SummitSelectedPresentationList()->ID,
            'Order:not' => 0
        ])->sort('Order', 'ASC');
        $PresentationPosition = 0;

        $counter = 1;

        if ($Presentations) {
            foreach ($Presentations as $Presentation) {
                if ($Presentation->ID == $this->ID) {
                    $PresentationPosition = $counter;
                }
                $counter = $counter + 1;
            }
        }

        $Presentations = SummitSelectedPresentation::get()->filter([
            'SummitSelectedPresentationListID' => $this->SummitSelectedPresentationList()->ID,
            'Order' => 0
        ])->sort('Order', 'ASC');

        if ($Presentations) {
            foreach ($Presentations as $Presentation) {
                if ($Presentation->ID == $this->ID) {
                    $PresentationPosition = $counter;
                }
                $counter = $counter + 1;
            }
        }
        
        return $PresentationPosition;
    }

}