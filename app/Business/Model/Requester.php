<?php

namespace App\Business\Model;

/**
 * API Requester information.
 * This class will be loaded in AuthServiceProvider and
 * used in controllers that need to know who is the API requester by using Dependency Injection (DI).
 *
 * @package App\Business\Model
 */
class Requester
{
    private $userId; //user id
    private $isUserSA; //user id
    private $roleIds = []; //role ids
    private $tenantId;//tenant id
    private $token; //token
    private $tokenRenewed;
    private $companyId;
    private $appId; //app id
    private $logId;

    /**
     * @return mixed
     */
    public function getUserId() : int
    {
        return $this->userId;
    }

    /**
     * @param mixed $userId
     */
    public function setUserId(int $userId)
    {
        $this->userId = $userId;
    }

    /**
     * @return mixed
     */
    public function getRoleIds() : array
    {
        return $this->roleIds;
    }

    /**
     * @param mixed $userId
     */
    public function setIsUserSA(bool $isUserSA)
    {
        $this->isUserSA = $isUserSA;
    }

    /**
     * @return mixed
     */
    public function getIsUserSA() : bool
    {
        return $this->isUserSA;
    }

    /**
     * @param mixed $userId
     */
    public function setRoleIds(array $roleIds)
    {
        $this->roleIds = $roleIds;
    }

    /**
     * @return mixed
     */
    public function getTenantId() : int
    {
        return $this->tenantId;
    }

    /**
     * @param mixed $tenantId
     */
    public function setTenantId($tenantId)
    {
        $this->tenantId = $tenantId;
    }

    /**
     * @return mixed
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param mixed $token
     */
    public function setToken($token)
    {
        $this->token = $token;
    }

    public function getTokenRenewed()
    {
        return $this->tokenRenewed;
    }

    public function setTokenRenewed($tokenRenewed)
    {
        $this->tokenRenewed = $tokenRenewed;
    }

    /**
     * @return mixed
     */
    public function getCompanyId()
    {
        return $this->companyId;
    }

    /**
     * @param mixed $companyId
     */
    public function setCompanyId($companyId)
    {
        $this->companyId = $companyId;
    }

    /**
     * @return mixed
     */
    public function getAppId()
    {
        return $this->appId;
    }

    /**
     * @param mixed $appId
     */
    public function setAppId($appId)
    {
        $this->appId = $appId;
    }

    /**
     * @return mixed $logId
     */
    public function getLogId() : int
    {
        return $this->logId;
    }

    /**
     * @param mixed $userId
     */
    public function setLogId(int $logId)
    {
        $this->logId = $logId;
    }

}
