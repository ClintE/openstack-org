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
class SummitAppEventsApi extends AbstractRestfulJsonApi {

    /**
     * @var IEntityRepository
     */
    private $summit_repository;

    /**
     * @var ISummitEventRepository
     */
    private $summitevent_repository;

    /**
     * @var ISummitPresentationRepository
     */
    private $summitpresentation_repository;

    /**
     * @var ISummitService
     */
    private $summit_service;

    public function __construct
    (
        ISummitRepository $summit_repository,
        ISummitEventRepository $summitevent_repository,
        ISummitAttendeeRepository $summitattendee_repository,
        ISummitPresentationRepository $summitpresentation_repository,
        ISummitService $summit_service
    )
    {
        parent::__construct();
        $this->summit_repository             = $summit_repository;
        $this->summitevent_repository        = $summitevent_repository;
        $this->summitattendee_repository     = $summitattendee_repository;
        $this->summitpresentation_repository = $summitpresentation_repository;
        $this->summit_service                = $summit_service;
    }

    protected function isApiCall(){
        $request = $this->getRequest();
        if(is_null($request)) return false;
        return true;
    }

    /**
     * @return bool
     */
    protected function authorize(){
        if(!Permission::check('ADMIN_SUMMIT_APP_FRONTEND_ADMIN')) return false;
        return $this->checkOwnAjaxRequest();
    }

    protected function authenticate() {
        return true;
    }


    static $url_handlers = array(
        'POST '                       => 'createEvent',
        'PUT $EVENT_ID!/publish'      => 'publishEvent',
        'PUT $EVENT_ID!'              => 'updateEvent',
        'GET unpublished/$Source!'    => 'getUnpublishedEventsBySource',
        'DELETE $EVENT_ID!/unpublish' => 'unpublishEvent',
        'POST $EVENT_ID!/share'       => 'shareEmail',
    );

    static $allowed_actions = array(
        'getUnpublishedEventsBySource',
        'publishEvent',
        'unpublishEvent',
        'createEvent',
        'updateEvent',
        'shareEmail',
    );

    public function getUnpublishedEventsBySource(SS_HTTPRequest $request) {
        try {
            $query_string = $request->getVars();
            $summit_id    = intval($request->param('SUMMIT_ID'));
            $source       = strtolower(Convert::raw2sql($request->param('Source')));
            $valid_sources = array('tracks', 'track_list', 'presentations', 'events');

            if(!in_array($source, $valid_sources)) return $this->validationError(array('invalid requested source'));

            $search_term   = isset($query_string['search_term']) ? Convert::raw2sql($query_string['search_term']) : null;
            $status        = isset($query_string['status']) ? Convert::raw2sql($query_string['status']) : null;
            $track_list_id = isset($query_string['track_list_id']) ? intval($query_string['track_list_id']) : null;
            $track_id      = isset($query_string['track_id']) ? intval($query_string['track_id']) : null;
            $event_type_id = isset($query_string['event_type_id']) ? intval($query_string['event_type_id']) : null;
            $page          = isset($query_string['page']) ? intval($query_string['page']) : 1;
            $page_size     = isset($query_string['page_size']) ? intval($query_string['page_size']) : 10;
            $order         = isset($query_string['order']) ? Convert::raw2sql($query_string['order']) : null;
            $expand        = isset($query_string['expand']) ? Convert::raw2sql($query_string['expand']) : null;

            switch ($source)
            {
                case 'track_list':
                {
                    list($page, $page_size, $count, $data) = $this->summitpresentation_repository->getUnpublishedBySummitAndTrackList($summit_id, $track_list_id, $status, $search_term, $page,$page_size, $order);
                }
                break;
                case 'tracks':
                {
                    list($page, $page_size, $count, $data) = $this->summitpresentation_repository->getUnpublishedBySummitAndTrack($summit_id, $track_id, $status, $search_term, $page,$page_size, $order);
                }
                    break;
                case 'presentations':
                {
                    list($page, $page_size, $count, $data) = $this->summitpresentation_repository->getUnpublishedBySummit($summit_id, null, $status, $search_term, $page,$page_size, $order);
                }
                break;
                case 'events':
                {
                    list($page, $page_size, $count, $data) = $this->summitevent_repository->getUnpublishedBySummit($summit_id, $event_type_id, $search_term, $page,$page_size, $order);
                }
                break;
            }

            $events = array();
            foreach ($data as $e)
            {
                $entry = array
                (
                    'id'          => intval($e->ID),
                    'title'       => $e->Title,
                    'description' => !empty($e->Description)? $e->Description : $e->ShortDescription,
                    'type_id'     => intval($e->TypeID),
                    'class_name'  => $e->ClassName,
                );

                if ($e instanceof Presentation)
                {
                    $speakers = array();
                    if(!empty($expand) && strstr($expand, 'speakers')!== false)
                    {
                        foreach ($e->Speakers() as $s) {
                            array_push($speakers, array('id' => intval($s->ID), 'name' => $s->getName()));
                        }
                        $entry['speakers'] = $speakers;
                    }
                    else {
                        foreach ($e->Speakers() as $s) {
                            array_push($speakers, $s->ID);
                        }
                        $entry['speakers_id'] = $speakers;
                    }
                    $entry['moderator_id'] = intval($e->ModeratorID);
                    $entry['track_id']     = intval($e->CategoryID);
                    $entry['level']        = $e->Level;
                    $entry['status']       = $e->SelectionStatus();
                }
                array_push($events, $entry);
            }

            $total_pages = ($page_size) ? ceil($count/$page_size) : 1;

            return $this->ok(
                array
                (
                    'data'        => $events,
                    'page'        => $page,
                    'page_size'   => $page_size,
                    'total_pages' => $total_pages
                )
            );
        }
        catch(Exception $ex)
        {
            SS_Log::log($ex->getMessage(), SS_Log::ERR);
            return $this->serverError();
        }
    }

    public function publishEvent(SS_HTTPRequest $request)
    {
        try
        {
           if(!$this->isJson()) return $this->validationError(array('invalid content type!'));
           $query_string = $request->getVars();
           $summit_id    = intval($request->param('SUMMIT_ID'));
           $event_id     = intval($request->param('EVENT_ID'));
           $event_data   = $this->getJsonRequest();
           $summit = $this->summit_repository->getById($summit_id);
           if(is_null($summit)) throw new NotFoundEntityException('Summit', sprintf(' id %s', $summit_id));
           $this->summit_service->publishEvent($summit, $event_data);
           return $this->ok();
        }
        catch(EntityValidationException $ex1)
        {
            SS_Log::log($ex1->getMessage(), SS_Log::WARN);
            return $this->validationError($ex1->getMessages());
        }
        catch(NotFoundEntityException $ex2)
        {
            SS_Log::log($ex2->getMessage(), SS_Log::WARN);
            return $this->notFound($ex2->getMessages());
        }
        catch(Exception $ex)
        {
            SS_Log::log($ex->getMessage(), SS_Log::ERR);
            return $this->serverError();
        }
    }

    public function unpublishEvent(SS_HTTPRequest $request){

        try
        {
            if(!$this->isJson()) return $this->validationError(array('invalid content type!'));
            $query_string = $request->getVars();
            $summit_id    = intval($request->param('SUMMIT_ID'));
            $event_id     = intval($request->param('EVENT_ID'));
            $summit       = $this->summit_repository->getById($summit_id);
            if(is_null($summit)) throw new NotFoundEntityException('Summit', sprintf(' id %s', $summit_id));
            $event        = $this->summitevent_repository->getById($event_id) ;
            if(is_null($event)) throw new NotFoundEntityException('SummitEvent', sprintf(' id %s', $event_id));
            $this->summit_service->unpublishEvent($summit, $event);
            return $this->ok();
        }
        catch(EntityValidationException $ex1)
        {
            SS_Log::log($ex1->getMessage(), SS_Log::WARN);
            return $this->validationError($ex1->getMessages());
        }
        catch(NotFoundEntityException $ex2)
        {
            SS_Log::log($ex2->getMessage(), SS_Log::WARN);
            return $this->notFound($ex2->getMessage());
        }
        catch(Exception $ex)
        {
            SS_Log::log($ex->getMessage(), SS_Log::ERR);
            return $this->serverError();
        }
    }

    public function createEvent(SS_HTTPRequest $request)
    {
        try
        {
            if(!$this->isJson()) return $this->validationError(array('invalid content type!'));
            $summit_id    = intval($request->param('SUMMIT_ID'));
            $event_data   = $this->getJsonRequest();
            $summit = $this->summit_repository->getById($summit_id);
            if(is_null($summit)) throw new NotFoundEntityException('Summit', sprintf(' id %s', $summit_id));

            $event = $this->summit_service->createEvent($summit, $event_data);

            return $this->ok($event->toMap());
        }
        catch(EntityValidationException $ex1)
        {
            SS_Log::log($ex1->getMessage(), SS_Log::WARN);
            return $this->validationError($ex1->getMessages());
        }
        catch(ValidationException $ex2)
        {
            SS_Log::log($ex2->getResult()->messageList(), SS_Log::WARN);
            return $this->validationError($ex2->getResult()->messageList());
        }
        catch(NotFoundEntityException $ex3)
        {
            SS_Log::log($ex3->getMessage(), SS_Log::WARN);
            return $this->notFound($ex3->getMessages());
        }
        catch(Exception $ex)
        {
            SS_Log::log($ex->getMessage(), SS_Log::ERR);
            return $this->serverError();
        }
    }

    public function updateEvent(SS_HTTPRequest $request)
    {
        try
        {
            if(!$this->isJson()) return $this->validationError(array('invalid content type!'));
            $summit_id    = intval($request->param('SUMMIT_ID'));
            $event_id     = intval($request->param('EVENT_ID'));
            $event_data   = $this->getJsonRequest();
            $event_data['id'] = $event_id;
            $summit = $this->summit_repository->getById($summit_id);
            if(is_null($summit)) throw new NotFoundEntityException('Summit', sprintf(' id %s', $summit_id));

            $event = $this->summit_service->updateEvent($summit, $event_data);

            return $this->ok($event->toMap());
        }
        catch(EntityValidationException $ex1)
        {
            SS_Log::log($ex1->getMessage(), SS_Log::WARN);
            return $this->validationError($ex1->getMessages());
        }
        catch(ValidationException $ex2)
        {
            SS_Log::log($ex2->getResult()->messageList(), SS_Log::WARN);
            return $this->validationError($ex2->getResult()->messageList());
        }
        catch(NotFoundEntityException $ex3)
        {
            SS_Log::log($ex3->getMessage(), SS_Log::WARN);
            return $this->notFound($ex3->getMessages());
        }
        catch(Exception $ex)
        {
            SS_Log::log($ex->getMessage(), SS_Log::ERR);
            return $this->serverError();
        }
    }

    /**
     * @return SS_HTTPResponse
     */
    public function shareEmail(SS_HTTPRequest $request){
        try {
            $event_id     = intval($request->param('EVENT_ID'));
            $event        = $this->summitevent_repository->getById($event_id) ;
            if(is_null($event)) throw new NotFoundEntityException('SummitEvent', sprintf(' id %s', $event_id));

            $data = $this->getJsonRequest();
            if (!$data) return $this->serverError();

            if (!$data['from'] || !$data['to']) {
                throw new EntityValidationException('Please enter From and To email addresses.');
            }

            if (!$data['token'])
                throw new EntityValidationException('Validation Error');
            else {
                $token = Session::get("SummitAppEventPage.ShareEmail");
                $token_count = Session::get("SummitAppEventPage.ShareEmailCount");
            }

            if ($data['token'] != $token || $token_count > 5) {
                throw new EntityValidationException('Validation Error');
            }

            $subject = 'Fwd: '.$event->Title;
            $body = $event->Title.'<br>'.$event->Description.'<br><br>Check it out: '.$event->getLink();

            $email = EmailFactory::getInstance()->buildEmail($data['from'], $data['to'], $subject, $body);

            return $this->ok($email->send());

        }
        catch(EntityValidationException $ex1){
            SS_Log::log($ex1,SS_Log::WARN);
            return $this->validationError($ex1->getMessages());
        }
        catch(Exception $ex){
            SS_Log::log($ex,SS_Log::ERR);
            return $this->serverError();
        }
    }
}