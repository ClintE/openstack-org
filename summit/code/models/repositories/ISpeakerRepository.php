<?php

/**
 * Copyright 2015 OpenStack Foundation
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
interface ISpeakerRepository extends IEntityRepository
{
    /**
     * @param string $term
     * @param int $limit
     * @return array
     */
    public function searchByTerm($term, $limit = 10);

    /**
     * @param ISummit $summit
     * @param string $term
     * @return IPresentationSpeaker[]
     */
    public function searchBySummitAndTerm(ISummit $summit, $term);

    /**
     * @param ISummit $summit
     * @param int $page
     * @param int $page_size
     * @param string $term
     * @param string $sort_by
     * @param string $sort_dir
     * @return array
     */
    public function getBySummit(ISummit $summit, $page= 1, $page_size = 10, $term = '', $sort_by = 'id', $sort_dir = 'asc');

    /**
     * @param int $member_id
     * @return IPresentationSpeaker
     */
    public function getByMemberID($member_id);
}