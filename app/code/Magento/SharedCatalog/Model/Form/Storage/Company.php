<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SharedCatalog\Model\Form\Storage;

/**
 * Class Company Storage
 */
class Company
{
    /**#@+
     * Session keys
     */
    const SESSION_KEY_DEFAULT_SHARED_CATALOG_ID = 'default_shared_catalog_id';
    const SESSION_KEY_SHARED_CATALOG_ID = 'shared_catalog_id';
    const SESSION_KEY_SHARED_CATALOG_COMPANIES_IDS = 'shared_catalog_companies_ids';
    const SESSION_KEY_SHARED_CATALOG_ASSIGNED_COMPANIES_IDS = 'shared_catalog_assigned_companies_ids';
    /**#@-*/

    /**
     * @var \Magento\Framework\Session\Generic
     */
    protected $session;

    /**
     * @var string
     */
    protected $key;

    /**
     * @param \Magento\Framework\Session\Generic $session
     * @param string $key
     */
    public function __construct(
        \Magento\Framework\Session\Generic $session,
        $key
    ) {
        $this->session = $session;
        $this->key = $key;
    }

    /**
     * Set default shared catalog id
     *
     * @param int $catalogId
     * @return $this
     */
    public function setDefaultCatalogId($catalogId)
    {
        return $this->setSessionData(self::SESSION_KEY_DEFAULT_SHARED_CATALOG_ID, $catalogId);
    }

    /**
     * Get default shared catalog id
     *
     * @return int
     */
    public function getDefaultCatalogId()
    {
        return $this->getSessionData(self::SESSION_KEY_DEFAULT_SHARED_CATALOG_ID);
    }

    /**
     * Set shared catalog id
     *
     * @param int $catalogId
     * @return $this
     */
    public function setSharedCatalogId($catalogId)
    {
        return $this->setSessionData(self::SESSION_KEY_SHARED_CATALOG_ID, $catalogId);
    }

    /**
     * Get shared catalog id
     *
     * @return int
     */
    public function getSharedCatalogId()
    {
        return $this->getSessionData(self::SESSION_KEY_SHARED_CATALOG_ID);
    }

    /**
     * Set companies
     *
     * @param array $companiesIds
     * @return $this
     */
    public function setCompanies(array $companiesIds)
    {
        $this->setSessionData(self::SESSION_KEY_SHARED_CATALOG_COMPANIES_IDS, $companiesIds);
        return $this->assignCompanies($companiesIds);
    }

    /**
     * Get companies
     *
     * @return array
     */
    public function getCompanies()
    {
        return $this->getSessionData(self::SESSION_KEY_SHARED_CATALOG_COMPANIES_IDS) ?: [];
    }

    /**
     * Assign companies
     *
     * @param array $companiesIds
     * @return $this
     */
    public function assignCompanies(array $companiesIds)
    {
        $assignedCompanies = $this->getAssignedCompanies();
        foreach ($companiesIds as $companyId) {
            $assignedCompanies[$companyId] = true;
        }
        return $this->setAssignedCompanies($assignedCompanies);
    }

    /**
     * Unassign companies
     *
     * @param array $companiesIds
     * @return $this
     */
    public function unassignCompanies(array $companiesIds)
    {
        // not unassign from default catalog
        if ($this->getSharedCatalogId() === $this->getDefaultCatalogId()) {
            return $this;
        }

        $assignedCompanies = $this->getAssignedCompanies();
        foreach ($companiesIds as $companyId) {
            if (isset($assignedCompanies[$companyId])) {
                $assignedCompanies[$companyId] = false;
            }
        }
        return $this->setAssignedCompanies($assignedCompanies);
    }

    /**
     * Is company assigned
     *
     * @param int $companyId
     * @return bool
     */
    public function isCompanyAssigned($companyId)
    {
        return in_array($companyId, $this->getAssignedCompaniesIds());
    }

    /**
     * Is company unassigned
     *
     * @param int $companyId
     * @return bool
     */
    public function isCompanyUnassigned($companyId)
    {
        return in_array($companyId, $this->getUnassignedCompaniesIds());
    }

    /**
     * Get assigned companies ids
     *
     * @return array
     */
    public function getAssignedCompaniesIds()
    {
        $assignedCompanies = $this->getAssignedCompanies();
        $assignedIds = [];
        foreach ($assignedCompanies as $companyId => $isAssigned) {
            if ($isAssigned) {
                $assignedIds[] = $companyId;
            }
        }
        return $assignedIds;
    }

    /**
     * Get unassigned companies ids
     *
     * @return array
     */
    public function getUnassignedCompaniesIds()
    {
        $assignedCompanies = $this->getAssignedCompanies();
        $unassignedIds = [];
        foreach ($assignedCompanies as $companyId => $isAssigned) {
            if (in_array($companyId, $this->getCompanies()) && !$isAssigned) {
                $unassignedIds[] = $companyId;
            }
        }
        return $unassignedIds;
    }

    /**
     * Get assigned companies
     *
     * @return array
     */
    protected function getAssignedCompanies()
    {
        return $this->getSessionData(self::SESSION_KEY_SHARED_CATALOG_ASSIGNED_COMPANIES_IDS) ?: [];
    }

    /**
     * Set assigned companies
     *
     * @param array $companiesIds
     * @return Company
     */
    protected function setAssignedCompanies(array $companiesIds)
    {
        return $this->setSessionData(self::SESSION_KEY_SHARED_CATALOG_ASSIGNED_COMPANIES_IDS, $companiesIds);
    }

    /**
     * Get session data
     * @param string $paramKey
     * @return mixed
     */
    private function getSessionData($paramKey)
    {
        return $this->session->getData($this->getParamSessionKey($paramKey));
    }

    /**
     * Set session data
     *
     * @param string $paramKey
     * @param mixed $value
     * @return $this
     */
    private function setSessionData($paramKey, $value)
    {
        $this->session->setData(
            $this->getParamSessionKey($paramKey),
            $value
        );
        return $this;
    }

    /**
     * Get session key for param
     *
     * @param string $paramKey
     * @return string
     */
    private function getParamSessionKey($paramKey)
    {
        return sprintf('%s_%s', $this->key, $paramKey);
    }
}
