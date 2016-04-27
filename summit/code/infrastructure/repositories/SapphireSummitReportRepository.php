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
final class SapphireSummitReportRepository extends SapphireRepository implements ISummitReportRepository
{

    public function __construct()
    {
        parent::__construct(new SummitReport());
    }

    /**
     * @param string $name
     * @return ISummitReport
     */
    public function getByName($name)
    {
        $query = new QueryObject(new $this->entity_class);
        $query->addAndCondition(QueryCriteria::equal('Name', $name));
        return $this->getBy($query);
    }
}