<?php

/**
 * Copyright 2016 OpenStack Foundation
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
 * Class CertifiedOpenStackAdministratorExam
 */
final class CertifiedOpenStackAdministratorExam extends DataObject implements ICertifiedOpenStackAdministratorExam
{
    private static $db = array
    (
        'Code'                 => 'Varchar(255)',
        'CertificationNumber'  => 'Varchar(255)',
        'ExternalID'           => 'Varchar(255)',
        'ExpirationDate'       => 'SS_Datetime',
        'PassFailDate'         => 'SS_Datetime',
        'ModifiedDate'         => 'SS_Datetime',
        'Status'               => "Enum('New,Pending,Pass,No Pass','New')",
    );


    public static $valid_status = array('New','Pending','Pass','No Pass');

    private static $has_one = array
    (
        'Owner' => 'Member',
    );

    /**
     * @return int
     */
    public function getIdentifier()
    {
        // TODO: Implement getIdentifier() method.
    }

    /**
     * @param array|string $status
     * @param string $pass_date
     * @param string $cert_nbr
     * @param string $code
     * @param string $modified_date
     * @param string $expiration_date
     * @return $this
     * @throws EntityValidationException
     */
    public function update($status, $pass_date, $cert_nbr, $code, $modified_date, $expiration_date)
    {
        if(!$this->isValidStatus($status)) throw new EntityValidationException(sprintf("invalid status %s", $status));
        $this->Status              = $status;
        $this->CertificationNumber = $cert_nbr;
        $this->Code                = $code;
        $this->ModifiedDate        = $modified_date;

        if(!empty($expiration_date))
            $this->ExpirationDate = $expiration_date;

        if(!empty($pass_date))
            $this->PassFailDate = $pass_date;
        return $this;
    }

    /**
     * @param string $status
     * @return bool
     */
    public function isValidStatus($status){
        return in_array($status, self::$valid_status);
    }
}