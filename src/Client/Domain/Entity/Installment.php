<?php
/**
 * Copyright (c) 2018 Alma / Nabla SAS
 *
 * THE MIT LICENSE
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated
 * documentation files (the "Software"), to deal in the Software without restriction, including without limitation
 * the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and
 * to permit persons to whom the Software is furnished to do so, subject to the following conditions:
 * The above copyright notice and this permission notice shall be included in all copies or substantial portions of the
 * Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE
 * WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF
 * CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS
 * IN THE SOFTWARE.
 *
 * @author    Alma / Nabla SAS <contact@getalma.eu>
 * @copyright Copyright (c) 2018 Alma / Nabla SAS
 * @license   https://opensource.org/licenses/MIT The MIT License
 *
 */

namespace Alma\Client\Domain\Entity;

class Installment extends AbstractEntity
{
    /** States of an instalment of a payment, as the API sends them */
    const STATE_PENDING = 'pending';
    const STATE_PAID = 'paid';
    const STATE_COVERED = 'covered';

    /**
     * State of an instalment of a forecast schedule — an eligibility's, not a payment's yet.
     * The API never sends it: Eligibility::getInstallments() gives it to the rows Alma sends without one.
     */
    const STATE_FORECAST = 'forecast';

    protected int $customerFee;

    protected int $customerInterest;

    protected int $dueDate;

    protected int $purchaseAmount;

    protected string $state;

    /** Set only when Alma sends total_amount (an eligibility's rows do) */
    protected int $totalAmount;

    /** Mapping of required fields */
    protected array $requiredFields = [
        'customerFee'      => 'customer_fee',
        'customerInterest' => 'customer_interest',
        'dueDate'          => 'due_date',
        'purchaseAmount'   => 'purchase_amount',
        'state'            => 'state',
    ];

    /** Mapping of optional fields */
    protected array $optionalFields = [
        'totalAmount' => 'total_amount',
    ];

    /**
     * Returns the customer fee in cents
     * @return int
     */
    public function getCustomerFee(): int
    {
        return $this->customerFee;
    }

    /**
     * Returns the customer interest in cents
     * @return int
     */
    public function getCustomerInterest(): int
    {
        return $this->customerInterest;
    }

    /**
     * Returns the due date as a UNIX timestamp
     * @return int
     */
    public function getDueDate(): int
    {
        return $this->dueDate;
    }

    /**
     * Returns the purchase amount in cents
     * @return int
     */
    public function getPurchaseAmount(): int
    {
        return $this->purchaseAmount;
    }

    /**
     * Returns the state of the installment
     * @return string
     */
    public function getState(): string
    {
        return $this->state;
    }

    /**
     * Returns the total amount of the installment in cents: what Alma sends as total_amount when it does,
     * the sum of the purchase amount, the customer fee and the customer interest otherwise.
     * Alma's backend is the master of the data: a value it sent is never replaced by a computed one.
     * @return int
     */
    public function getTotalAmount(): int
    {
        return isset($this->totalAmount) ? $this->totalAmount : $this->getComputedTotalAmount();
    }

    /**
     * Returns the purchase amount plus the customer fee plus the customer interest, in cents
     * @return int
     */
    public function getComputedTotalAmount(): int
    {
        return $this->purchaseAmount + $this->customerFee + $this->customerInterest;
    }

    /**
     * Whether the total amount Alma sent, if any, equals the sum of the purchase amount, the customer fee
     * and the customer interest. True when Alma sent none.
     * @return bool
     */
    public function hasConsistentTotalAmount(): bool
    {
        return !isset($this->totalAmount) || $this->totalAmount === $this->getComputedTotalAmount();
    }
}
