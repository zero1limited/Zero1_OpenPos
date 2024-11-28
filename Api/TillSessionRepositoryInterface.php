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
     * @param TillSessionInterface $page
     * @return TillSessionInterface
     */
    public function save(TillSessionInterface $page);

    /**
     * Get a till session by Id
     *
     * @param int $id
     * @return TillSessionInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException If Problem with the specified ID does not exist.
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
     * @param TillSessionInterface $page
     * @return TillSessionInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException If Problem with the specified ID does not exist.
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(TillSessionInterface $page);

    /**
     * Delete a till session by Id
     *
     * @param int $id
     * @return TillSessionInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException If customer with the specified ID does not exist.
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($id);
}
