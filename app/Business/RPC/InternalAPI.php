<?php

namespace App\Business\RPC;

use App\Business\Dao\JobDao;
use App\Business\Model\Requester;

/**
 * API for fellow services.
 *
 * Note:
 *
 *   _Keep the details inside_
 *
 *   Let's say some data in this service is needed by other services.
 *   Instead of defining getDetails() method to be called from other
 *   services, create attachDetails() method. This way, the other
 *   services only need to know that the data they want has been
 *   attached to an object they pass in without any knowledge of
 *   specific fields.
 *
 */
class InternalAPI
{
    public function __construct(
        JobDao $jobDao,
        Requester $requester
    )
    {
        $this->jobDao = $jobDao;
        $this->requester = $requester;
    }

    /**
     * Take an array and attach position-related fields to it.
     *
     * @param array  $obj          The initial object
     * @param string $positionCode Which position do you want?
     *
     * @return array
     */
    public function attachPositionDetails(array $obj, string $positionCode)
    {
        // TODO: Dummy implementation
        $obj['position'] = $positionCode;
        return $obj;
    }

    /**
     * Take an array and get job-related fields to it.
     *
     * @param array  $obj          The initial object
     * @param string $jobCode Which job do you want?
     *
     * @return array
     */
    public function getJob(array $obj, string $jobCode)
    {
        $obj['position'] = $jobCode;
        $this->requester->setTenantId((int) env('TENANT_ID'));
        $this->requester->setCompanyId((int) env('COMPANY_ID'));
        $data = $this->jobDao->getOneCode($jobCode);

        return $data;
    }
}
