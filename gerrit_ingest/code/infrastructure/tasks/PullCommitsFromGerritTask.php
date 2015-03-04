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
 * Class PullCommitsFromGerritTask
 */
final class PullCommitsFromGerritTask extends CliController  {

    function process(){

        set_time_limit(0);

        $batch_size = PullCommitsFromGerritTaskBatchSize;
        if(isset($_GET['batch_size'])){
            $batch_size = intval(trim(Convert::raw2sql($_GET['batch_size'])));
            echo sprintf('batch_size set to %s', $batch_size);
        }

        $manager = new GerritIngestManager (
            new GerritAPI(GERRIT_BASE_URL, GERRIT_USER, GERRIT_PASSWORD),
            new SapphireBatchTaskRepository,
            new SapphireCLAMemberRepository,
            new BatchTaskFactory,
            SapphireTransactionManager::getInstance()
        );

        $members_updated = $manager->processCommits($batch_size);

        echo $members_updated;
    }
}