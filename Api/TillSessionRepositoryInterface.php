<?php

namespace Zero1\OpenPos\Api;

use Zero1\OpenPos\Api\Data\TillSessionInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * Interface TillSessionRepositoryInterface
 *
 * @api
 */
interface TillSessionRepositoryInterface
{
    /**
     * Create or update a till session.
     *
     * @param TillSessionInterface $tillSession
     * @return TillSessionInterface
     */
    public function save(TillSessionInterface $tillSession);

    /**
     * Get a till session by ID
     *
     * @param int $id
     * @return TillSessionInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException If till session with the specified ID does not exist.
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($id);

    /**
     * Retrieve till sessions which match a specified criteria.
     *
     * @param SearchCriteriaInterface $criteria
     */
    public function getList(SearchCriteriaInterface $criteria);

    /**
     * Delete a till session
     *
     * @param TillSessionInterface $tillSession
     * @return TillSessionInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException If till session with the specified ID does not exist.
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(TillSessionInterface $tillSession);

    /**
     * Delete a till session by ID
     *
     * @param int $id
     * @return TillSessionInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException If till session with the specified ID does not exist.
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($id);
}
