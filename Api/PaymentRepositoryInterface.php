<?php

namespace Zero1\OpenPos\Api;

use Zero1\OpenPos\Api\Data\PaymentInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * Interface PaymentRepositoryInterface
 *
 * @api
 */
interface PaymentRepositoryInterface
{
    /**
     * Create or update a payment.
     *
     * @param PaymentInterface $payment
     * @return PaymentInterface
     */
    public function save(PaymentInterface $payment);

    /**
     * Get a payment by ID
     *
     * @param int $id
     * @return PaymentInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException If payment with the specified ID does not exist.
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($id);

    /**
     * Retrieve payments which match a specified criteria.
     *
     * @param SearchCriteriaInterface $criteria
     */
    public function getList(SearchCriteriaInterface $criteria);

    /**
     * Delete a payment
     *
     * @param PaymentInterface $payment
     * @return PaymentInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException If payment with the specified ID does not exist.
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(PaymentInterface $payment);

    /**
     * Delete a payment by ID
     *
     * @param int $id
     * @return PaymentInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException If payment with the specified ID does not exist.
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($id);
}
